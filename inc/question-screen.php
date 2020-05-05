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
    
    // Данные теста

    private $quiz = array();
    
    // Данные текущего ответа
    
    private $answer = array();
    
    // Режим отображения теста (view, run)
    
    private $action = '';
    


    function __construct( $question, $quiz = array() )
    {
        parent::__construct();

        $this->quiz = $quiz;
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
        $disabled = ( in_array( $this->action, array( 'view', 'result' ) ) ) ? ' disabled' : '';
        $name = $this->question['id'];
        
        $checked = '';
        if ( $this->action == 'view' && $this->answer['data']['status'] == 'yes' ) $checked = ' checked';
        if ( $this->action == 'run' && isset( $this->answer['data']['result'] ) && $this->answer['data']['result'] == 'yes' ) $checked = ' checked';
        if ( $this->action == 'result' && isset( $this->answer['data']['result'] ) && $this->answer['data']['result'] == 'yes' ) $checked = ' checked';

        if ( $type == 'single' ) {
            
            $value = $this->get_hash( $this->answer['data']['caption'] );
            $marker = '<input type="radio" name="answers[' . $name . ']" value="' . $value . '" class="form-check-input" ' . $disabled . $checked . ' />';
            
        } elseif ( $type == 'multiple' ) {
            
            $value = $this->get_hash( $this->answer['data']['caption'] );
            $marker = '<input type="checkbox" name="answers[' . $name . '][]" value="' . $value . '" class="form-check-input" ' . $disabled . $checked . ' />';
            
        } elseif ( in_array( $type, array( 'sort', 's-sort', 'm-sort' ) ) ) {
            
            $name = $name . '_' . $this->get_hash( $this->answer['data']['status'] );
            // $value = $this->get_hash( $this->answer['data']['caption'] );
            $value = ( isset( $this->answer['data']['result'] ) ) ? $this->get_hash( $this->answer['data']['result'] ) : '';
            // $marker_status = ( isset( $this->answer['data']['result'] ) ) ? $this->get_hash( $this->answer['data']['result'] ) : $this->answer['data']['status'];

            $marker = '';
            $marker .= '<span class="marker">' . $this->answer['data']['status'] . '</span>';
            $marker .= '<input type="hidden" name="answers[' . $name . ']" value="' . $value . '">';
            
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
        $type = $this->question['type'];

        if ( in_array( $type, array( 'sort', 's-sort', 'm-sort' ) ) && $this->is_submitted( $this->question ) ) {

            $answer = $this->answer['data']['result'];

        } else {

            $answer = $this->answer['data']['caption'];

        }

        return apply_filters( 'mif_qm_question_screen_get_answer_caption', $answer, $this->answer );
    }
    
    

    // 
    // Возвращает значок связи
    // 
    
    public function get_answer_linker()
    {
        $linker = '';

        $checked = ( isset( $this->answer['data']['result'] ) ) ? ' checked' : '';
        // $linker .= '<span class="linker' . $checked . '"><i class="fa fa-chain-broken text-warning" aria-hidden="true"></i><i class="fa fa-chain text-success" aria-hidden="true"></i></span>';
        $linker .= '<span class="linker' . $checked . '"><span class="unlink"><i class="fas fa-unlink text-warning"></i></span><span class="link"><i class="fas fa-link text-success"></i></span></span>';

        return apply_filters( 'mif_qm_question_screen_get_answer_mover', $linker, $this->answer, $this->action );
    }
    


    // 
    // Возвращает значок перемещения
    // 
    
    public function get_answer_mover()
    {
        $mover = '';

        if ( $this->action == 'view' ) {

            $mover .= '<div class="mover bg-success text-white"><i class="fa fa-check" aria-hidden="true"></i></div>';
            
        } elseif ( $this->action == 'result' ) {
            
            $mover .= '';

        } else {
            
            $caption = $this->get_hash( $this->answer['data']['caption'] );
            $mover .= '<div class="mover" data-caption="' . $caption . '" title="' . __( 'Перетащите вверх или вниз', 'mif-qm' ) . '"><i class="fa fa-sort" aria-hidden="true"></i></div>';

        }

        return apply_filters( 'mif_qm_question_screen_get_answer_mover', $mover, $this->answer, $this->action );
    }
    

    
    // 
    // Возвращает поле для ручного ввода (текст или файл)
    // 
    
    public function get_answer_handmake()
    {
        $type = $this->question['type'];
        
        if ( ! in_array( $type, array( 'open', 'text' ) ) ) return;
        
        $answer = $this->answer['data'];
        
        $disabled = ( in_array( $this->action, array( 'view', 'result' ) ) ) ? ' disabled' : '';
        $size = ( isset( $answer['size'] ) ) ? (int) $answer['size'] : 1;
        $name = $this->question['id'] . '_' . $this->get_hash( serialize( $answer['meta'] ) );

        $text = '';
        
        if ( $answer['type'] == 'text' ) {
            
            $placeholder = ( isset( $answer['caption'] ) ) ? ' placeholder="' . $answer['caption'] . '"' : '';
            
            $value = ( isset( $answer['result'] ) ) ? $answer['result'] : '';

            if ( $size == 1 ) {
                
                $text = '<input type="text" name="answers[' . $name . ']"' . $placeholder . $disabled . ' value="' . $value . '" class="form-control" />';
                
            } else {
                
                $text = '<textarea name="answers[' . $name . ']"' . $placeholder . ' rows="' . $size . '"' . $disabled . '>' . $value . '</textarea>';
                
            }
            
        } elseif ( $answer['type'] == 'file' ) {
            
            $multiple = ( $size > 1 ) ? 'multiple' : '';
            $accept = '';
            $caption = '';
            
            if ( isset( $answer['meta'] ) ) {

                $meta = $answer['meta'];
                $accept = ' accept=".' . implode( ',.', $meta ) . '"';
                $caption = __( 'Допустимые форматы', 'mif-qm' ) . ': ' . implode( ', ', $meta );

            }

            if ( isset( $answer['caption'] ) ) $text .= '<div>' . $answer['caption'] . '</div>';
            $text .= '<div class="mt-3 mb-3 p-3 border"><input type="file" name="' . $name . '[]"' . $multiple . $accept . $disabled . ' aria-describedby="' . $id . '" class="form-control-file" /></div>';
            if ( $caption ) $text .= '<div><small>' . $caption . '</small></div>';
            if ( $multiple ) $text .= '<div><small>' . __( 'Можно выбрать несколько файлов', 'mif-qm' ) . '</small></div>';

        } else {

            return;

        }


        // В режиме просмотра - показать мета-информацию (правильные ответы или типы допустимых файлов)

        if ( in_array( $this->action, array( 'view', 'result' ) ) ) {

            if ( $this->question['type'] == 'open' ) {

                $arr = array();
                $arr[] = __( 'Ручная проверка ответа', 'mif-qm' );

                if ( $answer['type'] == 'file' ) {

                    // Указать параметры загрузки файлов

                    $arr[] = ( $answer['size'] > 1 ) ? __( 'Загрузка нескольких файлов', 'mif-qm' ) : __( 'Загрузка одного файла', 'mif-qm' );
                    $arr[] = ( isset( $answer['meta'] ) ) ? __( 'Допустимые типы файлов', 'mif-qm' ) . ': <strong>' . implode( '</strong>, <strong>', $answer['meta'] ) . '</strong>' : __( 'Любые типы файлов', 'mif-qm' );

                }
                
                $text .= '<div class="media meta ' . $answer['type'] . '">
                <div class="marker ml-2 mr-3 bg-warning text-white"><i class="far fa-hand-paper" aria-hidden="true"></i></div>
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
        $header = '';

        if ( $this->action == 'result' ) {
            
            $header .= '<div class="row no-gutters">';
            $header .= '<div class="col-9">';
            
            $header .= '<h3>' . __( 'Вопрос', 'mif-qm' ) . ' ' . $this->question['num'] . '</h3>';

            $header .= '</div><div class="col-3 pt-1 text-right">';

            $inspection_mode = $this->get_inspection_mode( $this->quiz );

            $rating = ( isset( $this->question['processed']['rating'][$inspection_mode] ) ) ? $this->question['processed']['rating'][$inspection_mode] : 0;
            // $success = ( isset( $this->part['processed']['success'][$inspection_mode] ) ) ? $this->part['processed']['success'][$inspection_mode] : '';
            
            // $class = ( $success == 'no' ) ? ' text-danger' : ' text-success';
            $class = ' bg-warning text-white';
            if ( $rating == 1 ) $class = ' bg-success text-light';
            if ( $rating == 0 ) $class = ' bg-danger text-light';

            $header .= '<span class="p-2 pl-3 pr-3 rounded font-weight-bold' . $class . '">';
            $header .= round( $rating * 100 ) . '%';
            $header .= '</span>';

            $header .= '</div>';
            $header .= '</div>';
            

        } else {
        
            if ( isset( $this->question['num'] ) ) $header = '<h3>' . __( 'Вопрос', 'mif-qm' ) . ' ' . $this->question['num'] . '</h3>';
        
        }

        return apply_filters( 'mif_qm_question_screen_get_question_header', $header, $this->question );
    }
        
    
    // 
    // Возвращает вопрос вопроса
    // 
    
    public function get_question_question()
    {
        $qm_parser = new mif_qm_parser();
        
        $title = $qm_parser->parse( $this->question['title'] );

        return apply_filters( 'mif_qm_question_screen_get_question_question', $title, $this->question );
    }
        
    
    // 
    // Возвращает параметр вопроса
    // 
    
    public function get_question_data( $key )
    {
        return apply_filters( 'mif_qm_question_screen_get_question_data', $this->question[$key], $this->question, $key );
    }
    
    
    // 
    // Возвращает идентификатор для вопроса
    // 
    
    public function get_question_id()
    {
        $id = $this->question['id'];
        return apply_filters( 'mif_qm_question_screen_get_question_id', $id, $this->question );
    }
    
    
    // 
    // Возвращает классы для вопроса
    // 
    
    public function get_question_classes()
    {
        $classes = array( 'question', $this->question['type'] );
        return apply_filters( 'mif_qm_question_screen_get_question_classes', implode( ' ', $classes ), $classes, $this->question );
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
    // Возвращает классы для перемещаемого блока (сортировка)
    // 
    
    public function get_draggable_classes()
    {
        $classes = ( $this->action == 'run' ) ? ' active' : '';
        return apply_filters( 'mif_qm_question_screen_get_draggable_classes', $classes, $this->answer, $this->action );
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

        // if ( in_array( $this->action, array( 'view', 'result' ) ) && isset( $this->answer['data']['status'] ) ) {
        if ( in_array( $this->action, array( 'view' ) ) && isset( $this->answer['data']['status'] ) ) {

            if ( $this->answer['data']['status'] == 'yes' ) $classes[] = 'correct table-success';
            if ( $this->answer['data']['status'] == 'no' ) $classes[] = 'incorrect';
            
        }

        // if ( $this->action == 'view' && isset( $this->answer['data']['status'] ) ) {

        //     if ( $this->answer['data']['status'] == 'yes' ) $classes[] = 'correct table-success';
        //     if ( $this->answer['data']['status'] == 'no' ) $classes[] = 'incorrect';
            
        // } elseif ( $this->action == 'result' && isset( $this->answer['data']['resume'] ) ) {

        //     if ( $this->answer['data']['resume'] == 'correct' ) $classes[] = 'correct table-success';
        //     if ( $this->answer['data']['resume'] == 'incorrect' ) $classes[] = 'incorrect table-danger';
            
        // }

        return apply_filters( 'mif_qm_question_screen_get_answer_classes', implode( ' ', $classes ), $this->answer, $this->action, $classes );
    }
    
    
    
    
    // 
    // Возвращает маркер правильности ответа
    // 
    
    public function get_answer_result_marker()
    {
        $out = '';

        if ( $this->action == 'result' && isset( $this->answer['data']['resume'] ) ) {

            // if ( $this->answer['data']['resume'] == 'correct' ) $out .= '<i class="fas fa-plus text-success"></i>';
            // if ( $this->answer['data']['resume'] == 'incorrect' ) $out .= '<i class="fas fa-minus text-danger"></i>';
            if ( $this->answer['data']['resume'] == 'correct' ) $out = '<span class="result-caption bg-success p-1 pl-2 pr-2 m-3 text-light rounded">' . __( 'правильно', 'mif-qm' ) . '</span>';
            if ( $this->answer['data']['resume'] == 'incorrect' ) $out = '<span class="result-caption bg-danger p-1 pl-2 pr-2 m-3 text-light rounded">' . __( 'ошибка', 'mif-qm' ) . '</span>';
            if ( isset( $this->answer['data']['expired'] ) && $this->answer['data']['expired'] == 'yes' ) $out = '<span class="result-caption bg-warning p-1 pl-2 pr-2 m-3 text-light rounded">' . __( 'время', 'mif-qm' ) . '</span>';

        }
      
        return apply_filters( 'mif_qm_question_screen_get_answer_result_marker', $out, $this->answer, $this->action );
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