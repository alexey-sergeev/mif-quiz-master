<?php

//
// Класс ядра обработки тестов
// 
//


defined( 'ABSPATH' ) || exit;

include_once dirname( __FILE__ ) . '/class-core-part.php';


class mif_qm_core_quiz extends mif_qm_core_core {


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
        
        $quiz_raw = $this->get_parts_raw( $text );
        
        $quiz = array();
        $part = new mif_qm_core_part();

        // Записать заголовок теста

        $quiz['title'] = ( isset( $quiz_raw['title'] ) ) ? $quiz_raw['title'] : __( 'Тест', 'mif-qm' );
        
        // Записать структурированную информацию о параметрах

        $param = new mif_qm_core_param( $quiz_raw['param'], 'quiz' );
        // $quiz['param'] = $param->parse();
        $quiz['param'] = $param->explication();

        // p( $param->parse() );
        // p( $param->explication() );

        // Записать структурированную информацию о содержимом теста

        foreach( (array) $quiz_raw['parts'] as $item ) {

            $data = $part->parse( $item, $quiz['param'] );
            if ( $data ) $quiz['parts'][] = $data;

        }
        
        // p( $quiz );
            
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
        $quiz = array();

        foreach ( $arr as $item ) {

            $item = strim( $item );

            if ( $item == '' ) continue;

            if ( preg_match( $this->pattern_quiz, $item ) ) {
                
                // Заголовок теста
                
                $quiz['title'] = trim( preg_replace( $this->pattern_quiz, '', $item ) );
                continue;

            }
            
            if ( preg_match( $this->pattern_param, $item ) ) {
                
                // Параметр теста
                
                $quiz['param'][] = $item;
                
            }

            if ( preg_match( $this->pattern_part, $item ) || $flag ) {

                // Нашелся новый раздел или мы начинаем работу
                
                $n++;
                $quiz['parts'][$n] = '';
                $flag = false;

            }

            $quiz['parts'][$n] .= $item . "\n";

        }
        
        return $quiz;
    }



}






?>