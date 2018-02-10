<?php

//
// Создать XML-версию теста из массива
// 
//


defined( 'ABSPATH' ) || exit;



class mif_qm_xml_implode {

    
    function parse( $quiz )
    {
        $quiz_xml = new SimpleXMLElement( '<?xml version="1.0" encoding="UTF-8"?><quiz/>' );

        // Добавить заголовок
        
        if ( isset( $quiz['title'] ) ) $quiz_xml->addChild( 'title', $quiz['title'] );

        // Добавить параметры

        if ( isset( $quiz['param'] ) ) $this->add_param( $quiz_xml->addChild( 'param' ), $quiz['param'] );
        
        // Добавить разделы
        
        if ( isset( $quiz['parts'] ) ) {
            
            $parts_xml = $quiz_xml->addChild( 'parts' );
            
            foreach ( $quiz['parts'] as $part )  {
                
                $part_xml = $parts_xml->addChild( 'part' );
                
                if ( isset( $part['title'] ) ) $part_xml->addChild( 'title', $part['title'] );
                if ( isset( $part['param'] ) ) $this->add_param( $part_xml->addChild( 'param' ), $part['param'] );
                if ( isset( $part['questions'] ) ) $this->add_questions( $part_xml->addChild( 'questions' ), $part['questions'] );
                if ( isset( $part['processed'] ) ) $this->add_processed( $part_xml->addChild( 'processed' ), $part['processed'] );
                
            }
            
        }
        
        // Добавить информацию о процессе
        
        if ( isset( $quiz['processed'] ) ) $this->add_processed( $quiz_xml->addChild( 'processed' ), $quiz['processed'] );
        
        // Форматировать документ (переносы и отступы)
        
        $quiz_formatted_xml = $this->get_formatted_xml( $quiz_xml );

        // p( esc_html( $quiz_formatted_xml ) );
        
        return $quiz_formatted_xml;
    }

    

    //
    // Добавляет в xml-структуру блок вопросов
    //

    private function add_questions( $questions, $arr )
    {
        
        foreach ( $arr as $item ) {

            $question = $questions->addChild( 'question' );

            if ( isset( $item['title'] ) ) $question->addChild( 'title', $item['title'] );
            if ( isset( $item['type'] ) ) $question->addAttribute( 'type', $item['type'] );
            if ( isset( $item['id'] ) ) $question->addAttribute( 'id', $item['id'] );

            if ( isset( $item['answers'] ) ) $this->add_answers( $question->addChild( 'answers' ), $item['answers'] );
            if ( isset( $item['processed'] ) ) $this->add_processed( $question->addChild( 'processed' ), $item['processed'] );

        }

    }


    //
    // Добавляет в xml-структуру блок ответов
    //

    private function add_answers( $answers, $arr )
    {

        foreach ( $arr as $item ) {
            
            $answer = $answers->addChild( 'answer' );
            
            foreach ( $item as $key => $value ) {

                if ( in_array( $key, array( 'type', 'size' ) ) ) {

                    $answer->addAttribute( $key, esc_attr( $value ) );
                    
                } elseif ( in_array( $key, array( 'meta', 'result' ) ) ) {

                    foreach ( (array) $value as $item ) $answer->addChild( $key, esc_attr( $item ) );

                } else {
                    
                    $answer->addChild( $key, esc_attr( $value ) );

                }
            
            }

        }

    }


    //
    // Добавляет в xml-структуру блок параметров
    //

    private function add_param( $param, $arr )
    {

        foreach ( $arr as $key => $value ) {

            if ( is_array( $value ) ) {

                foreach ( $value as $item ) $param->addChild( $key, $item );

            } else {

                $param->addChild( $key, $value );

            }

        }

    }


    //
    // Добавляет в xml-структуру блок информации о процессе
    //

    private function add_processed( $processed, $arr )
    {

        foreach ( $arr as $key => $value ) {

            if ( is_array( $value ) ) {

                // foreach ( $value as $item ) $processed->addChild( $key, $item );

                $arr_item = $processed->addChild( $key );
                foreach ( $value as $key_item => $value_item ) {

                    if ( is_array( $value_item ) ) {

                        foreach ( $value_item as $key_item2 => $value_item2 ) {

                            $item2 = $arr_item->addChild( 'item', $value_item2 );
                            $item2->addAttribute( 'key', $key_item2 );
                                
                        }

                    } else {

                        $item = $arr_item->addChild( 'item', $value_item );
                        $item->addAttribute( 'key', $key_item );

                    }

                }


            } else {

                $processed->addChild( $key, $value );

            }

        }

    }


    //
    // Возвращает xml-код в отформатированном виде
    //
    
    public function get_formatted_xml( $xml )
    {
        $dom = new DOMDocument( '1.0' );
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        $dom->loadXML( $xml->asXML() );

        return  $dom->saveXML();
    }

}

?>