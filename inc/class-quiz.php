<?php

//
// Класс для обработки полных тестов
// 
//


defined( 'ABSPATH' ) || exit;

include_once dirname( __FILE__ ) . '/class-part.php';


class mif_qm_quiz extends mif_qm_core {


    function __construct()
    {

        parent::__construct();

    }

   

    //
    // Преобразует текстовое описание теста в структурированный массив
    //

    function parse( $text )
    {
        
        // Получить массив текстовых описаний разелов теста
        
        $quiz_parts_raw = $this->get_parts_raw( $text );
        
        $quiz = array();
        $part = new mif_qm_part();

        foreach( $quiz_parts_raw as $item ) {

            $quiz[] = $part->parse( $item );

        }
        
        p($quiz);
            
        return $quiz;
    }

    


    //
    // Составляет массив разделов теста в текстовом формате "как есть"
    //

    private function get_parts_raw( $text )
    {
        
        $arr = preg_split( '/\\r\\n?|\\n/', $text );
    
        $n = -1;
        $flag = true;
        $quiz_parts_txt = array();

        foreach ( $arr as $item ) {

            $item = strim( $item );

            if ( preg_match( $this->pattern_quiz_part, $item ) || $flag ) {

                // Нашелся новый раздел или мы начинаем работу

                $n++;
                $quiz_parts_txt[$n] = '';
                $flag = false;

            }

            $quiz_parts_txt[$n] .= $item . "\n";

        }
        
        return $quiz_parts_txt;
    }



}






?>