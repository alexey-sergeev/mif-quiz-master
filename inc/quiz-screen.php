<?php

//
// Экранные методы для работы с тестом
// 
//


defined( 'ABSPATH' ) || exit;

include_once dirname( __FILE__ ) . '/core-core.php';
// include_once dirname( __FILE__ ) . '/question-screen.php';
include_once dirname( __FILE__ ) . '/part-screen.php';
include_once dirname( __FILE__ ) . '/quiz-templates.php';
// include_once dirname( __FILE__ ) . '/question-templates.php';


class mif_qm_quiz_screen extends mif_qm_quiz_core {
// class mif_qm_quiz_screen {

    private $quiz = array();

    // Режим отображения теста (view, run)

    private $action = '';
    
    function __construct( $quiz )
    {

        parent::__construct();

        // Привести XML в array, если надо
        
        if ( ! is_array( $quiz ) ) {
            
            $xml = new mif_qm_xml_core();
            $quiz = $xml->to_array( $quiz );
            
        }
    
        $this->quiz = apply_filters( 'mif_qm_screen_quiz',  $quiz );
    
    }



    public function show( $args = array() )
    {
        $defaults = array( 'action' => 'view' );
        
        $r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

        // Установить текущий режим отображения

        $this->action = $action;
        
        // Подключить шаблон из темы оформления или локальный

        if ( $template = locate_template( 'quiz.php' ) ) {
           
            load_template( $template, false );

        } else {

            load_template( dirname( __FILE__ ) . '/../templates/quiz.php', false );

        }

    }



    // 
    // Выводит разделы
    // 

    public function the_parts()
    {
        if ( empty( $this->quiz ) ) return;
        
        global $mif_qm_part_screen;

        // Пронумеровать все вопросы, кроме случая, когда отображаются отдельные вопросы в режиме run

        if ( ! ( $this->action == 'run' && in_array( 'question', (array) $this->quiz['param']['settings'] ) ) ) {
            
            $num = 1;

            foreach ( (array) $this->quiz['parts'] as $p_key => $part ) 
                foreach ( (array) $part['questions'] as $q_key => $question ) {
                    
                    $prefix = '';

                    if ( in_array( 'part', (array) $this->quiz['param']['settings'] ) && isset( $this->quiz['processed']['numbers']['current'] ) ) {
                     
                        $part_number = (int) $this->quiz['processed']['numbers']['current'] + 1;
                        $prefix = $part_number . '.';
                        
                    }
                    
                    $this->quiz['parts'][$p_key]['questions'][$q_key]['num'] = $prefix . $num;

                    $num++;
                }

        }
        
        foreach ( (array) $this->quiz['parts'] as $part ) {

            $mif_qm_part_screen = new mif_qm_part_screen( $part, $this->quiz );
            $mif_qm_part_screen->show( array( 'action' => $this->action ) );

        }

    }


    
            
    // 
    // Возвращает навигацию теста
    // 
    
    public function get_quiz_navigation()
    {
        global $post;
        $quiz_id = $post->ID; // !!!

        $nav = '';
        $arr = array();
        
        if ( isset( $this->quiz['processed']['numbers'] ) ) {
            
            $max = (int) $this->quiz['processed']['numbers']['max'];
            $current = (int) $this->quiz['processed']['numbers']['current'];

            $disabled_class = ( in_array( 'navigation', (array) $this->quiz['param']['settings'] ) ) ? '' : ' disabled';

            for ( $i = 0; $i < $max; $i++ ) {

                $class = ( $i === $current ) ? ' active' : $disabled_class;
                $link = ( $disabled_class == '' ) ? $this->get_link( $i ) : '#';
                $num = $i + 1;
                
                $arr[] = '<li class="page-item' . $class . '"><a class="page-link" href="' . $link . '" data-num="' . $i . '" data-nonce="' . wp_create_nonce( 'mif-qm' ) . '" data-quiz_id="' . $quiz_id . '">' . $num . '</a></li>';

            }
            
            $nav = '<nav><ul class="quiz-navigation pagination justify-content-center">' . implode( '', $arr ) . '</ul></nav>';
        }

        return apply_filters( 'mif_qm_question_screen_get_quiz_navigation', $nav, $this->quiz );
    }

    
            
    // 
    // Возвращает кнопку продолжения
    // 
    
    public function get_quiz_next_button()
    {
        global $post;
        $quiz_id = $post->ID; // !!! думать, как сделать для шорткодов
        
        $btn = '';

        if ( $this->action == 'run' ) {
            
            $btn .= '<div class="m-5 text-center">';
            $btn .= '<button type="submit" class="btn btn-primary btn-lg">' . __( 'Далее', 'mif-qm' ) . '</button>';
            $btn .= '<span class="loading absolute pl-2 mt-2"><i class="fas fa-spinner fa-pulse"></i></span>';;
            $btn .= '</div>';
            $btn .= '<input type="hidden" name="action" value="run">';
            $btn .= '<input type="hidden" name="_wpnonce" value="' . wp_create_nonce( 'mif-qm' ) . '" />';
            $btn .= '<input type="hidden" name="quiz_id" value="' . $quiz_id . '" />';
            
            
            if ( isset( $this->quiz['processed']['numbers'] ) ) {
                
                $current = (int) $this->quiz['processed']['numbers']['current'];
                $next = $current + 1;
                
                $btn .= '<input type="hidden" name="num" value="' . $next . '">';
                
            }
            
        } elseif ( $this->action == 'result' ) {

            $btn .= '<div class="p-2 mt-5 mb-3 text-center bg-light">';
            $btn .= '<a href="' . get_permalink() .  '" class="font-weight-bold">' . __( 'Вернуться к тесту', 'mif-qm') . '</a><br />';
            $btn .= '<a href="' . get_permalink() .  '?action=result" class="font-weight-bold">' . __( 'Вернуться к результатам', 'mif-qm') . '</a>';
            $btn .= '</div>';           

        }
        // $link = $this->get_link( $next );
        // $btn = '<div class="m-5 text-center"><a class="next-btn btn btn-primary btn-lg text-white" href="' . $link . '">' . __( 'Далее', 'mif-qm' ) . '</a></div>';

        return apply_filters( 'mif_qm_question_screen_get_quiz_next_button', $btn, $this->quiz, $this->action );
    }

   

   // 
   // Возвращает ссылку на элемент теста с указанным номером
   // 
    
    public function get_link( $num )
    {
        // $link = '?action=run&num=' . $num . '#top';
        $link = '?action=run&num=' . $num;
        return apply_filters( 'mif_qm_question_screen_get_link', $link, $num,  $this->quiz );
    }
     
    
    
    // 
    // Возвращает заголовок теста
    // 
    
    public function get_quiz_header()
    {
        $header = ( isset( $this->quiz['title'] ) ) ? $this->quiz['title'] : '';
        return apply_filters( 'mif_qm_question_screen_get_quiz_header', $header, $this->quiz );
    }

        
    
    // 
    // Возвращает параметры теста
    // 
    
    public function get_quiz_param()
    {
        $out = '';
        
        if ( $this->action == 'view' ) {
    
            $screen = new mif_qm_param_screen( $this->quiz['param'], 'quiz' );
            $out = $screen->get_show( $this->quiz );

        } elseif ( $this->action == 'result' ) {


            $out .= '<div class="bg-light p-4 text-center">'; 

            $out .= '<h3 class="font-weight-normal">' . __( 'Результат теста', 'mif-qm' ) . '</h3>';

            // $snapshot_id = ( isset( $_REQUEST['id'] ) ) ? (int) $_REQUEST['id'] : false;

            $process_inspector = new mif_qm_process_inspector( $this->quiz );
            // $result = $process_inspector->get_result( $snapshot_id );
            $result = $process_inspector->get_result();

            $process_screen = new mif_qm_process_screen();
            $out .= $process_screen->get_result_panel( $result );
            
            $out .= '</div>'; 


        } else {

            $out = '';

        }
        
        return apply_filters( 'mif_qm_part_get_part_param', $out, $this->quiz );
    }

}

?>