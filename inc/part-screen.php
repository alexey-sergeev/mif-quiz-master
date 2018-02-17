<?php

//
// Экранные методы для работы с разделом теста
// 
//


defined( 'ABSPATH' ) || exit;

include_once dirname( __FILE__ ) . '/question-screen.php';
include_once dirname( __FILE__ ) . '/part-templates.php';
include_once dirname( __FILE__ ) . '/param-screen.php';


class mif_qm_part_screen extends mif_qm_part_core {

    // Данные всего раздела

    private $part = array();
    
    // Данные теста

    private $quiz = array();
    
    // Режим отображения теста (view, run)
    
    private $action = '';
    

    function __construct( $part, $quiz = array() )
    {
        parent::__construct();
     
        $this->quiz = $quiz;        
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

            $mif_qm_question_screen = new mif_qm_question_screen( $question, $this->quiz );
            $mif_qm_question_screen->show( $this->action );

        }

    }

        
    
    // 
    // Возвращает заголовок раздела
    // 
    
    public function get_part_header()
    {
        // if ( in_array( $this->action, array( 'view', 'result' ) ) ) {
        if ( $this->action == 'view' ) {

            $header = '<p><br />';
            $header .= ( isset( $this->part['title'] ) ) ? '<h2>' . $this->part['title'] . '</h2>' : '';
            // $header .= '<hr />';
            
        } elseif ( $this->action == 'result' ) {
            
            $header = '<p><br />';
            $header .= '<div class="row no-gutters border-bottom">';
            $header .= '<div class="col-9">';

            $header .= ( isset( $this->part['title'] ) ) ? '<h2>' . $this->part['title'] . '</h2>' : '';

            $header .= '</div><div class="col-3 pt-1 text-right">';


            $inspection_mode = $this->get_inspection_mode( $this->quiz );

            $success = ( isset( $this->part['processed']['success'][$inspection_mode] ) ) ? $this->part['processed']['success'][$inspection_mode] : '';
            if ( $success == 'no' ) $header .= '<span class="p-2 pl-3 pr-3 text-light rounded font-weight-bold bg-danger">' . __( 'не пройдено', 'mif-qm' ) . '</span>';

            // $rating = ( isset( $this->part['processed']['rating'][$inspection_mode] ) ) ? $this->part['processed']['rating'][$inspection_mode] : '';
            // $success = ( isset( $this->part['processed']['success'][$inspection_mode] ) ) ? $this->part['processed']['success'][$inspection_mode] : '';
            // $max = $this->get_max_rating( $this->part, 'part' );
            // $percent = round( 100 * $rating / $max );
            
            // $class = ( $success == 'no' ) ? ' text-danger' : ' text-success';

            // $header .= '<span class="p-2 pl-3 pr-3 bg-light rounded font-weight-bold' . $class . '">';

            // // $header .= $rating;
            // $header .= $percent . '%';

            // // if ( $success == 'yes' ) $header .= ' / ' . __( 'пройдено', 'mif-qm' );
            // if ( $success == 'no' ) $header .= ' / ' . __( 'не пройдено', 'mif-qm' );

            // $header .= '</span>';

            $header .= '</div>';
            $header .= '</div>';
            
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

        } elseif ( $this->action == 'result' ) {

            // $inspection_mode = $this->get_inspection_mode( $this->quiz );

            // $rating = ( isset( $this->part['processed']['rating'][$inspection_mode] ) ) ? $this->part['processed']['rating'][$inspection_mode] : '';
            // $success = ( isset( $this->part['processed']['success'][$inspection_mode] ) ) ? $this->part['processed']['success'][$inspection_mode] : '';
            
            // // p($rating);
            // // p($success);

            // // $class = ( $success == 'no' ) ? ' bg-danger' : ' bg-success';
            // $class = ( $success == 'no' ) ? ' text-danger' : ' text-success';
            // $out = '';
            // // $out .= '<span class="p-2 pl-3 pr-3 text-light rounded' . $class . '">';
            // $out .= '<span class="p-2 pl-3 pr-3 bg-light rounded' . $class . '">';
            // $out .= $rating;

            // if ( $success == 'yes' ) $out .= ' / ' . __( 'пройдено', 'mif-qm' );
            // if ( $success == 'no' ) $out .= ' / ' . __( 'не пройдено', 'mif-qm' );

            // $out .= '</span>';
            
        } else {

            $out = '';

        }
        
        return apply_filters( 'mif_qm_part_get_part_param', $out, $this->part, $this->action );
    }
    

}

?>