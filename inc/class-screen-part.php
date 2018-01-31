<?php

//
// Экранные методы для работы с разделом теста
// 
//


defined( 'ABSPATH' ) || exit;

include_once dirname( __FILE__ ) . '/class-screen-question.php';
include_once dirname( __FILE__ ) . '/templates-part.php';


class mif_qm_screen_part extends mif_qm_screen_core {

    // Данные всего раздела

    private $part = array();
    
    // Режим отображения теста (view, run)
    
    private $mode = '';
    

    function __construct( $part )
    {
        parent::__construct();
        $this->part = apply_filters( 'mif_qm_screen_part_part', $part );
    }


    function show( $args = array() )
    {
        $defaults = array( 'mode' => 'view' );

        $r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

        // Установить текущий режим отображения

        $this->mode = $mode;
        
        // Определить имя требуемого шаблона
        
        $file_tpl = 'part.php';
        
        // Подключить шаблон из темы оформления или локальный

        if ( $template = locate_template( $file_tpl ) ) {
           
            load_template( $template, false );

        } else {

            load_template( dirname( __FILE__ ) . '/../templates/' . $file_tpl, false );

        }

    }


    //
    // Выводит вопросы раздела
    //

    function the_questions()
    {
        global $mif_qm_screen_question;

        // !!! Здесть проверку того, что надо выводить все вопросы, а не конкретный один вопрос

        foreach ( (array) $this->part['questions'] as $question ) 
        {

            $mif_qm_screen_question = new mif_qm_screen_question( $question );
            $mif_qm_screen_question->show( $this->mode );

        }


    }

        
    
    // 
    // Возвращает заголовок раздела
    // 
    
    public function get_part_header()
    {
        $header = '<p><br />';
        $header .= ( isset( $this->part['title'] ) ) ? '<h2>' . $this->part['title'] . '</h2>' : '';
        $header .= '<hr />';

        return apply_filters( 'mif_qm_screen_question_get_question_header', $header, $this->part );
    }

        
    
    // 
    // Возвращает параметры раздела
    // 
    
    public function get_part_param()
    {
        p($this->part['param']);
        
        $param = 'ok';

        return apply_filters( 'mif_qm_screen_question_get_question_header', $param, $this->part );
    }
    

}

?>