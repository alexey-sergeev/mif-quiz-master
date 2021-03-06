<?php

//
// Преобразовать XML-версию теста в массив
// 
//


defined( 'ABSPATH' ) || exit;

include_once dirname( __FILE__ ) . '/param-core.php';



class mif_qm_xml_explode {

   
    function parse( $quiz_xml )
    {
        $quiz = array();
        
        $xml = new SimpleXMLElement( $quiz_xml );

        // Заголовок
        
        if ( isset( $xml->title ) ) $quiz['title'] = (string) $xml->title;

        // Параметры

        if ( isset( $xml->param ) ) $quiz['param'] = $this->get_param( $xml->param, 'quiz' );
        
        // Разделы
        
        if ( isset( $xml->parts ) ) {
            
            foreach ( $xml->parts->part as $part_xml ) {
                
                $part = array();
                
                if ( isset( $part_xml->title ) ) $part['title'] = (string) $part_xml->title;
                if ( isset( $part_xml->param ) ) $part['param'] = $this->get_param( $part_xml->param, 'part' );
                if ( isset( $part_xml->questions ) ) $part['questions'] = $this->get_questions( $part_xml->questions->question );
                if ( isset( $part_xml->processed ) ) $part['processed'] = $this->get_processed( $part_xml->processed );

                $quiz['parts'][] = $part;
            }
            
        }
        
        // Информация о процессе

        if ( isset( $xml->processed ) ) $quiz['processed'] = $this->get_processed( $xml->processed );

        return $quiz;
    }


    
    //
    // Преобразовать XML-информацию о процессе в массив
    //

    private function get_processed( $processed_xml ) 
    {
        $processed = array();

        foreach ( $processed_xml->children() as $key_processed => $items ) {
            
            if ( isset( $items->item ) ) {
                
                $arr = array();

                foreach ( $items as $item ) {
                    
                    $key = (string) $item['key'];
                    $value = (string) $item;
                    $arr[$key] = $value;
                    
                }
                
                if ( $key_processed == 'messages' ) {

                    $processed[$key_processed][] = $arr;

                } else {

                    $processed[$key_processed] = $arr;

                }

            } else {
                
                $processed[$key_processed] = (string) $items;

            }

        }

        return $processed;
    }


    
    //
    // Преобразовать XML-вопросы в массив
    //

    private function get_questions( $questions_xml, $mode = 'part' ) 
    {
        $questions = array();

        foreach ( $questions_xml as $item ) {

            $question = array();

            $question['title'] = ( isset( $item->title ) ) ? (string) $item->title : 'none';
            $question['type'] = ( isset( $item['type'] ) ) ? (string) $item['type'] : 'none';
            if ( isset( $item['id'] ) ) $question['id'] = (string) $item['id'];
            if ( isset( $item->answers ) ) $question['answers'] = $this->get_answers( $item->answers->answer );
            if ( isset( $item->processed ) ) $question['processed'] = $this->get_processed( $item->processed );

            $questions[] = $question;
        }


        return $questions;
    }



    //
    // Преобразовать XML-ответы в массив
    //

    private function get_answers( $answers_xml ) 
    {
        $answers = array();

        foreach ( $answers_xml as $item ) {

            $answer = array();

            foreach ( $item->attributes() as $key => $value ) $answer[$key] = (string) $value;

            foreach ( $item as $key => $value ) {

                if ( in_array( $key, array( 'meta' ) ) ) {

                    $answer[$key][] = (string) $value;
                    
                } else {
                    
                    $answer[$key] = (string) $value;

                }

            }

            $answers[] = $answer;

        }

        return $answers;
    }



    //
    // Преобразовать XML-параметры в массив
    //

    private function get_param( $param_xml, $mode = 'part' ) 
    {

        $param_core = new mif_qm_param_core();

        $param = $param_core->param_init( $mode );

        foreach ( $param as $key => $value ) {

            if ( ! isset( $param_xml->$key ) ) continue;

            if ( is_array( $value ) ) {

                foreach ( $param_xml->$key as $item ) $param[$key][] = (string) $item;

            } else {

                $param[$key] = (string) $param_xml->$key;

            }

        }

        foreach ( $param as $key => $value ) {

            if ( empty( $value ) ) unset( $param[$key] );

        }

        return $param;
    }


}

?>