<?php

//
// Экранные методы для работы с вопросами
// 
//


defined( 'ABSPATH' ) || exit;

include_once dirname( __FILE__ ) . '/class-screen-core.php';


class mif_qm_screen_question extends mif_qm_screen_core {

    // Данные всего вопроса

    private $question = array();
    
    // Данные текущего ответа
    
    private $answer = array();
    
    // Режим отображения теста (view, run)
    
    private $mode = '';
    


    function __construct( $question )
    {

        parent::__construct();
        $this->question = apply_filters( 'mif_qm_screen_question_question', $question );

    }


    function show( $mode = 'view' )
    {
        // Установить текущий режим отображения

        $this->mode = $mode;
        
        // Определить имя требуемого шаблона
        
        $file_tpl = 'question-' . sanitize_file_name( $this->question['type'] ) . '.php';
        
        // Подключить шаблон из темы оформления или локальный

        if ( $template = locate_template( $file_tpl ) ) {
           
            load_template( $template, false );

        } else {

            load_template( dirname( __FILE__ ) . '/../templates/' . $file_tpl, false );

        }
                
        // p( $this->question );

//        return apply_filters( 'mif_qm_screen_core_question_show', $out, $this->question );
    }
    
    
    // 
    // Возвращает маркер ответа
    // 
    
    public function get_answer_marker()
    {
        $type = $this->question['type'];
        $id = md5( $this->answer['data']['answer'] );
        $disabled = ( $this->mode == 'view' ) ? ' disabled' : '';
        $question_num = ( isset( $this->question['num'] ) ) ? $this->question['num'] : md5( serizlize( $this->question ) );

        $checked = '';
        if ( $this->mode == 'view' && $this->answer['data']['status'] == 'yes' ) $checked = ' checked';

        if ( $type == 'single' ) {
            
            // $marker = '<input type="radio" name="" value="' . $id . '" id="' . $id . '" ' . $disabled . $checked . ' />';
            $marker = '<input type="radio" name="question' . $question_num . '" value="' . $id . '" class="form-check-input" ' . $disabled . $checked . ' />';
            
        } elseif ( $type == 'multiple' ) {
            
            // $marker = '<input type="checkbox" name="" value="' . $id . '" id="' . $id . '" ' . $disabled . $checked . ' />';
            $marker = '<input type="checkbox" name="question' . $question_num . '[]" value="' . $id . '" class="form-check-input" ' . $disabled . $checked . ' />';
            
        } elseif ( $type == 'sort' ) {
            
            $answer_num = ( (int) $this->answer['num'] ) + 1;
            $marker = '<span class="num">' . $answer_num . '</span>';
            
        } elseif ( $type == 'matching' ) {
            
            $marker = '<span class="label">' . $this->answer['data']['label'] . '</span>';
            
        } else {
            
            $marker = '';

        }
        
        return apply_filters( 'mif_qm_screen_question_get_answer_mark', $marker, $this->answer );
    }

    
    // 
    // Возвращает формулировку ответа
    // 
    
    public function get_answer_answer()
    {
        // $id = md5( $this->answer['data']['answer'] );
        // $answer = '<label for="' . $id . '">' . $this->answer['data']['answer'] . '</label>';
        $answer = $this->answer['data']['answer'];

        return apply_filters( 'mif_qm_screen_question_get_answer_answer', $answer, $this->answer );
    }
    
    
    // 
    // Возвращает поле для ручного ввода (текст или файл)
    // 
    
    public function get_answer_handmake()
    {
        $type = $this->question['type'];

        if ( ! in_array( $type, array( 'open', 'text' ) ) ) return;

        $answer = $this->answer['data'];
        
        $disabled = ( $this->mode == 'view' ) ? ' disabled' : '';
        $size = ( isset( $answer['size'] ) ) ? (int) $answer['size'] : 1;
        $id = md5( serialize( $this->answer ) );
        $text = '';
        
        if ( $answer['type'] == 'text' ) {
            
            $placeholder = ( isset( $answer['answer'] ) ) ? ' placeholder="' . $answer['answer'] . '"' : '';
            
            if ( $size == 1 ) {
                
                $text = '<input type="text" name=""' . $placeholder . $disabled . ' class="form-control" />';
                
            } else {
                
                $text = '<textarea name=""' . $placeholder . ' rows="' . $size . '"' . $disabled . '></textarea>';
                
            }
            
        } elseif ( $answer['type'] == 'file' ) {
            
            $multiple = ( $size > 1 ) ? 'multiple' : '';
            $accept = '';
            
            if ( isset( $answer['meta'] ) ) {

                $meta = explode( '|', $answer['meta'] );
                $accept = ' accept=".' . implode( ',.', $meta ) . '"';;

            }

            // if ( isset( $answer['answer'] ) ) $text = '<span class="caption">' . $answer['answer'] . '</span>';
            // $text .= '<span class="input"><input type="file" name=""' . $multiple . $accept . $disabled . ' /></span>';
            // if ( isset( $answer['answer'] ) ) $text = '<label for="' . $id . '">' . $answer['answer'] . '</label>';
            $text .= '<input type="file" name=""' . $multiple . $accept . $disabled . ' aria-describedby="' . $id . '" class="form-control-file" />';
            if ( isset( $answer['answer'] ) ) $text .= '<small id="' . $id . '" class="form-text text-muted">' . $answer['answer'] . '</small>';

        } else {

            return;

        }


        // В режиме просмотра - показать мета-информацию (правильные ответы или типы допустимых файлов)

        if ( $this->mode == 'view' ) {

            if ( isset( $answer['meta'] ) ) {

                $meta = explode( '|', $answer['meta'] );
                
                // $text .= '<div class="meta ' . $answer['type'] . '"><ul><li>' . implode( '</li><li>', $meta ) . '</li></ul></div>';
                $text .= '<div class="media meta ' . $answer['type'] . '">
                                <div class="d-flex pl-2 pr-2 mr-1 text-success"><i class="fa fa-2x fa-check-circle" aria-hidden="true"></i></div>    
                                <div  class="media-body"><ul><li>' . implode( '</li><li>', $meta ) . '</li></ul></div>
                            </div>';

            }

        }

        return apply_filters( 'mif_qm_screen_question_get_answer_text', $text, $this->answer );
    }
    
    
    // 
    // Выбирает данные очередного ответа
    // 
    
    public function the_answer()
    {
        $num = ( ! isset( $this->answer['num'] ) ) ? 0 : $this->answer['num'] + 1;
        
        if ( isset( $this->question['answers'][$num] ) ) {
            
            $this->answer['num'] = $num;
            $this->answer['data'] = $this->question['answers'][$num];
            
            return true;
            
        } else { 
            
            return false;
            
        }
        
    }
        
    
    // 
    // Возвращает заголовок вопроса
    // 
    
    public function get_question_header()
    {
        if ( isset( $this->question['num'] ) ) $header = '<h3>' . __( 'Вопрос', 'mif-qm' ) . ' ' . $this->question['num'] . '</h3>';
        return apply_filters( 'mif_qm_screen_question_get_question_header', $header, $this->question );
    }
        
    
    // 
    // Возвращает параметр вопроса
    // 
    
    public function get_question_data( $key )
    {
        return apply_filters( 'mif_qm_screen_question_get_question_data', $this->question[$key], $this->question, $key );
    }
    
    
    // 
    // Возвращает классы для вопроса
    // 
    
    public function get_question_classes()
    {
        $classes = array( 'question', $this->question['type'] );
        return apply_filters( 'mif_qm_screen_question_get_question_classes', implode( ' ', $classes ), $classes );
    }
    
    
    // 
    // Возвращает классы для ответа
    // 
    
    public function get_answer_classes()
    {
        $classes = array( 'answer' );

        // Тип ответа (для текста и фалов)

        if ( isset( $this->answer['data']['type'] ) ) $classes[] = $this->answer['data']['type'];
        
        // Размер текстового поля

        $arr = array( 'qm-none', 'qm-small', 'qm-medium', 'qm-large', 'qm-x-large' );
        if ( isset( $this->answer['data']['size'] ) ) $classes[] = $arr[$this->answer['data']['size']];

        // Признак правильности

        if ( $this->mode == 'view' && isset( $this->answer['data']['status'] ) ) {

            if ( $this->answer['data']['status'] == 'yes' ) $classes[] = 'correct table-success';
            if ( $this->answer['data']['status'] == 'no' ) $classes[] = 'incorrect';

        }

        return apply_filters( 'mif_qm_screen_question_get_answer_classes', implode( ' ', $classes ), $this->answer, $this->mode, $classes );
    }
    
    
    // 
    // Возвращает значок перемещения
    // 
    
    public function get_answer_mover()
    {
        $mover = '';

        if ( $this->mode == 'view' ) {

            $mover .= '<div class="mover bg-success text-white"><i class="fa fa-check" aria-hidden="true"></i></div>';
            
        } else {
            
            $mover .= '<div class="mover"><i class="fa fa-sort" aria-hidden="true"></i></div>';

        }

        return apply_filters( 'mif_qm_screen_question_get_answer_mover', $mover, $this->answer, $this->mode );
    }
    
    
    // // 
    // // Возвращает классы для значка перемещения
    // // 
    
    // public function get_mover_classes()
    // {
    //     $classes = array();

    //     if ( $this->mode == 'view' ) $classes[] = 'bg-success text-white';

    //     return apply_filters( 'mif_qm_screen_question_get_mover_classes', implode( ' ', $classes ), $this->answer, $classes );
    // }

}
    
?>