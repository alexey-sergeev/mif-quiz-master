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

            $mif_qm_part_screen = new mif_qm_part_screen( $part );
            $mif_qm_part_screen->show( array( 'action' => $this->action ) );

        }

    }

    
            
    // // 
    // // Возвращает класс меню
    // // 
    
    // public function get_menu_class( $action = 'view', $class = '', $flag = true )
    // {
    //     $process_core = new mif_qm_process_core();
    
    //     if ( $process_core->get_action() === $action ) {
    
    //         $res1 = ' ' . $class;
    //         $res2 = '';
            
    //     } else {
            
    //         $res1 = '';
    //         $res2 = ' ' . $class;
    
    //     }
    
    //     $out = ( $flag ) ? $res1 : $res2;

    //     return apply_filters( 'mif_qm_quiz_screen_get_menu_class', $out, $this->quiz );
    // }
    

            
    // // 
    // // Возвращает меню теста
    // // 
    
    // public function get_quiz_menu()
    // {
    //     global $post;

    //     $menu = '';
        
    //     if ( mif_qm_user_can( 'edit-quiz' ) ) {

    //         $menu .= '<div class="btn-group mt-3 mb-3" role="group">';
    //         $menu .= '<a class="btn btn-outline-light pt-2' . $this->get_menu_class( 'view', 'bg-light' ) . '" href="?action=view"><i class="fa fa-2x fa-circle-o' . $this->get_menu_class( 'view', 'text-secondary', false ) . '" aria-hidden="true"></i><br /><small>' . __( 'Проверка', 'mif-qm' ) . '</small></a>';
    //         $menu .= '<a class="btn btn-outline-light pt-2' . $this->get_menu_class( 'run', 'bg-light' ) . '" href="?action=run"><i class="fa fa-2x fa-play' . $this->get_menu_class( 'run', 'text-secondary', false ) . '" aria-hidden="true"></i><br /><small>' . __( 'Просмотр', 'mif-qm' ) . '</small></a>';
    //         $menu .= '<a class="btn btn-outline-light pt-2' . $this->get_menu_class( 'edit', 'bg-light' ) . '" href="' . get_edit_post_link( $post->ID ) . '"><i class="fa fa-2x fa-pencil-square' . $this->get_menu_class( 'edit', 'text-secondary', false ) . '" aria-hidden="true"></i><br /><small>' . __( 'Редактор', 'mif-qm' ) . '</small></a>';
    //         $menu .= '<a class="btn btn-outline-light pt-2' . $this->get_menu_class( 'result', 'bg-light' ) . '" href="?action=result"><i class="fa fa-2x fa-check-square' . $this->get_menu_class( 'result', 'text-secondary', false ) . '" aria-hidden="true"></i><br /><small>' . __( 'Результаты', 'mif-qm' ) . '</small></a>';
    //         $menu .= '</div>';
    //     }

    //     return apply_filters( 'mif_qm_quiz_screen_get_quiz_navigation', $menu, $this->quiz );
    // }

    
            
    // 
    // Возвращает навигацию теста
    // 
    
    public function get_quiz_navigation()
    {
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
                
                $arr[] = '<li class="page-item' . $class . '"><a class="page-link" href="' . $link . '">' . $num . '</a></li>';

            }
            
            $nav = '<nav><ul class="pagination justify-content-center">' . implode( '', $arr ) . '</ul></nav>';
        }

        return apply_filters( 'mif_qm_question_screen_get_quiz_navigation', $nav, $this->quiz );
    }

    
            
    // 
    // Возвращает заголовок теста
    // 
    
    public function get_quiz_next_button()
    {
        $btn = '';

        $btn .= '<div class="m-5 text-center">';
        $btn .= '<button type="submit" class="btn btn-primary btn-lg">' . __( 'Далее', 'mif-qm' ) . '</button>';
        $btn .= '</div>';
        $btn .= '<input type="hidden" name="action" value="run">';
        
        
        if ( isset( $this->quiz['processed']['numbers'] ) ) {
            
            $current = (int) $this->quiz['processed']['numbers']['current'];
            $next = $current + 1;

            $btn .= '<input type="hidden" name="num" value="' . $next . '">';

        }

        // $link = $this->get_link( $next );
        // $btn = '<div class="m-5 text-center"><a class="next-btn btn btn-primary btn-lg text-white" href="' . $link . '">' . __( 'Далее', 'mif-qm' ) . '</a></div>';

        return apply_filters( 'mif_qm_question_screen_get_quiz_next_button', $btn, $this->quiz );
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
        $header .= ( isset( $this->quiz['title'] ) ) ? $this->quiz['title'] : '';
        return apply_filters( 'mif_qm_question_screen_get_quiz_header', $header, $this->quiz );
    }

        
    
    // 
    // Возвращает параметры теста
    // 
    
    public function get_quiz_param()
    {
        if ( $this->action == 'view' ) {
    
            $screen = new mif_qm_param_screen( $this->quiz['param'], 'quiz' );
            $out = $screen->get_show();

        } else {

            $out = '';

        }
        
        return apply_filters( 'mif_qm_part_get_part_param', $out, $this->quiz );
    }

}

?>