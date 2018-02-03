<?php

//
// Экранные методы для работы с разделом теста
// 
//


defined( 'ABSPATH' ) || exit;

include_once dirname( __FILE__ ) . '/question-screen.php';
include_once dirname( __FILE__ ) . '/part-templates.php';
include_once dirname( __FILE__ ) . '/param-screen.php';


class mif_qm_part_screen {

    // Данные всего раздела

    private $part = array();
    
    // Режим отображения теста (view, run)
    
    private $action = '';
    

    function __construct( $part )
    {
     //   parent::__construct();
        $this->part = apply_filters( 'mif_qm_screen_part_part', $part );
    }


    function show( $args = array() )
    {
        $defaults = array( 'action' => 'view' );

        $r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

        // Установить текущий режим отображения

        $this->action = $action;
        
        // Подключить шаблон из темы оформления или локальный

        if ( $template = locate_template( 'part.php' ) ) {
           
            load_template( $template, false );

        } else {

            load_template( dirname( __FILE__ ) . '/../templates/part.php', false );

        }

    }


    //
    // Выводит вопросы раздела
    //

    function the_questions()
    {
        global $mif_qm_question_screen;

        foreach ( (array) $this->part['questions'] as $question ) {

            $mif_qm_question_screen = new mif_qm_question_screen( $question );
            $mif_qm_question_screen->show( $this->action );

        }

    }

        
    
    // 
    // Возвращает заголовок раздела
    // 
    
    public function get_part_header()
    {
        if ( $this->action == 'view' ) {

            $header = '<p><br />';
            $header .= ( isset( $this->part['title'] ) ) ? '<h2>' . $this->part['title'] . '</h2>' : '';
            // $header .= '<hr />';

        } else {

            $header = '';

        }

        return apply_filters( 'mif_qm_question_screen_get_question_header', $header, $this->part, $this->action );
    }

        
    
    // 
    // Возвращает параметры раздела
    // 
    
    public function get_part_param()
    {
        if ( $this->action == 'view' ) {

            $screen = new mif_qm_param_screen( $this->part['param'], 'part' );
            $out = $screen->get_show();

        } else {

            $out = '';

        }
        
        return apply_filters( 'mif_qm_part_get_part_param', $out, $this->part, $this->action );
    }
    

}

?>