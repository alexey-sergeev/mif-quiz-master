<?php

//
// Экранные методы для работы с вопросами
// 
//


defined( 'ABSPATH' ) || exit;

//include_once dirname( __FILE__ ) . '/class-screen-core.php';
include_once dirname( __FILE__ ) . '/question-templates.php';


class mif_qm_question_screen extends mif_qm_question_core {

    // Данные всего вопроса

    private $question = array();
    
    // Данные текущего ответа
    
    private $answer = array();
    
    // Режим отображения теста (view, run)
    
    private $action = '';
    


    function __construct( $question )
    {

        parent::__construct();
        $this->question = apply_filters( 'mif_qm_question_screen_question', $question );

    }


    function show( $action = 'view' )
    {
        // Установить текущий режим отображения

        $this->action = $action;
        
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
        $disabled = ( $this->action == 'view' ) ? ' disabled' : '';
        $name = $this->question['id'];
        
        $checked = '';
        if ( $this->action == 'view' && $this->answer['data']['status'] == 'yes' ) $checked = ' checked';
        
        if ( $type == 'single' ) {
            
            $value = $this->get_hash( $this->answer['data']['caption'] );
            $marker = '<input type="radio" name="' . $name . '" value="' . $value . '" class="form-check-input" ' . $disabled . $checked . ' />';
            
        } elseif ( $type == 'multiple' ) {
            
            $value = $this->get_hash( $this->answer['data']['caption'] );
            $marker = '<input type="checkbox" name="' . $name . '[]" value="' . $value . '" class="form-check-input" ' . $disabled . $checked . ' />';
            
        } elseif ( in_array( $type, array( 'sort', 's-sort', 'm-sort' ) ) ) {
            
            $name = $name . '_' . $this->get_hash( $this->answer['data']['status'] );
            $value = $this->get_hash( $this->answer['data']['caption'] ); // !!! Здесь из-за замешивания надо будет менять
            
            $marker = '';
            $marker .= '<span class="marker">' . $this->answer['data']['status'] . '</span>';
            $marker .= '<input type="hidden" name="' . $name . '" value="' . $value . '">';
            
        } else {
            
            $marker = '';

        }
        
        return apply_filters( 'mif_qm_question_screen_get_answer_mark', $marker, $this->answer );
    }

    
    // 
    // Возвращает формулировку ответа
    // 
    
    public function get_answer_caption()
    {
        // $id = md5( $this->answer['data']['answer'] );
        // $answer = '<label for="' . $id . '">' . $this->answer['data']['answer'] . '</label>';
        $answer = $this->answer['data']['caption'];

        return apply_filters( 'mif_qm_question_screen_get_answer_caption', $answer, $this->answer );
    }
    
    
    // 
    // Возвращает поле для ручного ввода (текст или файл)
    // 
    
    public function get_answer_handmake()
    {
        $type = $this->question['type'];
        
        if ( ! in_array( $type, array( 'open', 'text' ) ) ) return;
        
        $answer = $this->answer['data'];
        
        $disabled = ( $this->action == 'view' ) ? ' disabled' : '';
        $size = ( isset( $answer['size'] ) ) ? (int) $answer['size'] : 1;
        // $id = md5( serialize( $this->answer ) );
        $name = $this->question['id'] . '_' . $this->get_hash( serialize( $this->answer ) );
        $text = '';
        
        if ( $answer['type'] == 'text' ) {
            
            $placeholder = ( isset( $answer['caption'] ) ) ? ' placeholder="' . $answer['caption'] . '"' : '';
            
            if ( $size == 1 ) {
                
                $text = '<input type="text" name="' . $name . '"' . $placeholder . $disabled . ' class="form-control" />';
                
            } else {
                
                $text = '<textarea name="' . $name . '"' . $placeholder . ' rows="' . $size . '"' . $disabled . '></textarea>';
                
            }
            
        } elseif ( $answer['type'] == 'file' ) {
            
            $multiple = ( $size > 1 ) ? 'multiple' : '';
            $accept = '';
            $caption = '';
            
            if ( isset( $answer['meta'] ) ) {

                // $meta = explode( '|', $answer['meta'] );
                $meta = $answer['meta'];
                $accept = ' accept=".' . implode( ',.', $meta ) . '"';
                $caption = __( 'Допустимые форматы', 'mif-qm' ) . ': ' . implode( ', ', $meta );

            }

            // if ( isset( $answer['answer'] ) ) $text = '<span class="caption">' . $answer['answer'] . '</span>';
            // $text .= '<span class="input"><input type="file" name=""' . $multiple . $accept . $disabled . ' /></span>';
            // if ( isset( $answer['answer'] ) ) $text = '<label for="' . $id . '">' . $answer['answer'] . '</label>';
            if ( isset( $answer['caption'] ) ) $text .= '<div>' . $answer['caption'] . '</div>';
            $text .= '<input type="file" name="' . $name . '"' . $multiple . $accept . $disabled . ' aria-describedby="' . $id . '" class="form-control-file" />';
            if ( $caption ) $text .= '<div><small>' . $caption . '</small></div>';
            if ( $multiple ) $text .= '<div><small>' . __( 'Можно выбрать несколько файлов', 'mif-qm' ) . '</small></div>';
            // if ( isset( $answer['caption'] ) ) $text .= '<small id="' . $id . '" class="form-text text-muted">' . $answer['caption'] . '</small>';

        } else {

            return;

        }


        // В режиме просмотра - показать мета-информацию (правильные ответы или типы допустимых файлов)

        if ( $this->action == 'view' ) {

            if ( $this->question['type'] == 'open' ) {

                $arr = array();
                $arr[] = __( 'Ручная проверка ответа', 'mif-qm' );

                if ( $answer['type'] == 'file' ) {

                    // Указать параметры загрузки файлов

                    $arr[] = ( $answer['size'] > 1 ) ? __( 'Загрузка нескольких файлов', 'mif-qm' ) : __( 'Загрузка одного файла', 'mif-qm' );
                    $arr[] = ( isset( $answer['meta'] ) ) ? __( 'Допустимые типы файлов', 'mif-qm' ) . ': <strong>' . implode( '</strong>, <strong>', $answer['meta'] ) . '</strong>' : __( 'Любые типы файлов', 'mif-qm' );

                }
                
                $text .= '<div class="media meta ' . $answer['type'] . '">
                <div class="marker ml-2 mr-3 bg-warning text-white"><i class="fa fa-hand-paper-o" aria-hidden="true"></i></div>
                <div  class="media-body"><ul><li>' . implode( '</li><li>', $arr ) . '</li></ul></div>
                </div>';
                
            } elseif ( $this->question['type'] == 'text' ) {
                
                // Указать правильные ответы
                
                // $meta = explode( '|', $answer['meta'] );
                $meta = $answer['meta'];
                $text .= '<div class="media meta ' . $answer['type'] . '">
                            <div class="marker ml-2 mr-3 bg-success text-white"><i class="fa fa-check" aria-hidden="true"></i></div>
                            <div  class="media-body"><ul><li>' . implode( '</li><li>', $meta ) . '</li></ul></div>
                        </div>';
            
            }

        }

        return apply_filters( 'mif_qm_question_screen_get_answer_text', $text, $this->answer );
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
        return apply_filters( 'mif_qm_question_screen_get_question_header', $header, $this->question );
    }
        
    
    // 
    // Возвращает параметр вопроса
    // 
    
    public function get_question_data( $key )
    {
        return apply_filters( 'mif_qm_question_screen_get_question_data', $this->question[$key], $this->question, $key );
    }
    
    
    // 
    // Возвращает классы для вопроса
    // 
    
    public function get_question_classes()
    {
        $classes = array( 'question', $this->question['type'] );
        return apply_filters( 'mif_qm_question_screen_get_question_classes', implode( ' ', $classes ), $classes );
    }
    
    

    // 
    // Возвращает классы для блока ответов
    // 
    
    public function get_answers_classes()
    {
        $classes = array();

        if ( $this->action == 'run' ) $classes[] = 'table-hover';

        return apply_filters( 'mif_qm_question_screen_get_answers_classes', implode( ' ', $classes ), $this->answer, $this->action, $classes );
    }



    // 
    // Возвращает классы для конкретного ответа
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

        if ( $this->action == 'view' && isset( $this->answer['data']['status'] ) ) {

            if ( $this->answer['data']['status'] == 'yes' ) $classes[] = 'correct table-success';
            if ( $this->answer['data']['status'] == 'no' ) $classes[] = 'incorrect';

        }

        return apply_filters( 'mif_qm_question_screen_get_answer_classes', implode( ' ', $classes ), $this->answer, $this->action, $classes );
    }
    
    
    // 
    // Возвращает значок перемещения
    // 
    
    public function get_answer_mover()
    {
        $mover = '';

        if ( $this->action == 'view' ) {

            $mover .= '<div class="mover bg-success text-white"><i class="fa fa-check" aria-hidden="true"></i></div>';
            
        } else {
            
            $mover .= '<div class="mover"><i class="fa fa-sort" aria-hidden="true"></i></div>';

        }

        return apply_filters( 'mif_qm_question_screen_get_answer_mover', $mover, $this->answer, $this->action );
    }
    
    
    // // 
    // // Возвращает классы для значка перемещения
    // // 
    
    // public function get_mover_classes()
    // {
    //     $classes = array();

    //     if ( $this->action == 'view' ) $classes[] = 'bg-success text-white';

    //     return apply_filters( 'mif_qm_question_screen_get_mover_classes', implode( ' ', $classes ), $this->answer, $classes );
    // }

}
    
?>