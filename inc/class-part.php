<?php

//
// Класс для обработки текстового описания разделов теста в структурированный массив
// 
//


defined( 'ABSPATH' ) || exit;

include_once dirname( __FILE__ ) . '/class-question.php';
include_once dirname( __FILE__ ) . '/class-param.php';


class mif_qm_part extends mif_qm_core {

    
    
    function __construct()
    {

        parent::__construct();
       
    }

    

    //
    // Преобразует текстовое описание теста в структурированный массив
    //

    function parse( $text )
    {
        
        // Получить массив текстовых описаний вопросов (с ответами)
        
        $part_raw = $this->get_part_raw( $text );
        
        $part = array();

        // Записать заголовок раздела

        $part['title'] = ( isset( $part_raw['title'] ) ) ? $part_raw['title'] : 'none';

        // Записать структурированную информацию о параметрах

        $param = new mif_qm_param();
        $part['param'] = $param->parse( $part_raw['param'] );

        // $part['param'] = ( isset( $part_raw['param'] ) ) ? $part_raw['param'] : array();

        // Записать структурированную информацию о вопросах

        $question = new mif_qm_question();

        foreach( (array) $part_raw['questions'] as $item ) {

            $part['questions'][] = $question->parse( $item );

        }
        
        // Если содержимого нет, то ничего не возвращать совсем

        if ( isset( $part['questions'] ) ) {

            return $part;

        } else {

            return false;

        }
    }



    //
    // Составляет массив вопросов (с ответами) в текстовом формате "как есть"
    //

    private function get_part_raw( $text )
    {
        
        $arr = preg_split( '/\\r\\n?|\\n/', $text );
    
        $n = -1;
        $flag = true;
        $part = array();

        foreach ( $arr as $item ) {

            $item = strim( $item );

            if ( $item == '' ) continue;
            
            if ( preg_match( $this->pattern_part, $item ) ) {

                // Заголовок раздела

                $part['title'] = trim( preg_replace( $this->pattern_part, '', $item ) );
                continue;

            }

            if ( preg_match( $this->pattern_param, $item ) ) {

                // Параметр раздела

                $part['param'][] = $item;
                continue;

            }

            if ( preg_match( $this->pattern_question, $item ) || $flag ) {

                // Нашелся новый вопрос или мы начинаем работу

                $n++;
                $part['questions'][$n] = '';
                $flag = false;

            }

            $part['questions'][$n] .= $item . "\n";

        }
        
        return $part;
    }



}






?>