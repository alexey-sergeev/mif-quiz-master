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

        $num = 1;
        foreach ( (array) $this->quiz['parts'] as $a => $part ) {
            foreach ( (array) $part['questions'] as $b => $question ) 
            {
                $this->quiz['parts'][$a]['questions'][$b]['num'] = $num++;
            }
        }

        foreach ( (array) $this->quiz['parts'] as $part ) {

            $mif_qm_part_screen = new mif_qm_part_screen( $part );
            $mif_qm_part_screen->show( array( 'action' => $this->action ) );

        }

    }

    
            
    // 
    // Возвращает заголовок теста
    // 
    
    public function get_quiz_header()
    {
        // $header = '<p><br />';
        // $header .= ( isset( $this->quiz['title'] ) ) ? '<h2>' . $this->quiz['title'] . '</h2>' : '';
        // $header .= '<hr />';

        $header .= ( isset( $this->quiz['title'] ) ) ? $this->quiz['title'] : '';

        return apply_filters( 'mif_qm_question_screen_get_question_header', $header, $this->quiz );
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