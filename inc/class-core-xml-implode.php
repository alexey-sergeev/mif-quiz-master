<?php

//
// Создать XML-версию теста из массива
// 
//


defined( 'ABSPATH' ) || exit;



class mif_qm_core_xml_implode  {

    
    function parse( $quiz )
    {
        $quiz_xml = new SimpleXMLElement( '<quiz/>' );

        if ( isset( $quiz['title'] ) ) $quiz_xml->addChild( 'title', $quiz['title'] );
        if ( isset( $quiz['param'] ) ) $this->add_param( $quiz_xml->addChild( 'param' ), $quiz['param'] );
        
        if ( isset( $quiz['parts'] ) ) {

            $parts_xml = $quiz_xml->addChild( 'parts' );

            foreach ( $quiz['parts'] as $part )  {

                $part_xml = $parts_xml->addChild( 'part' );

                if ( isset( $part['title'] ) ) $part_xml->addChild( 'title', $part['title'] );
                if ( isset( $part['param'] ) ) $this->add_param( $part_xml->addChild( 'param' ), $part['param'] );
                if ( isset( $part['questions'] ) ) $this->add_questions( $part_xml->addChild( 'questions' ), $part['questions'] );

            }
            
        }
        
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

            if ( isset( $item['answers'] ) ) $this->add_answers( $question->addChild( 'answers' ), $item['answers'] );

        }

    }


    //
    // Добавляет в xml-структуру блок ответов
    //

    private function add_answers( $answers, $arr )
    {

        foreach ( $arr as $item ) {
            
            if ( isset( $item['answer'] ) ) {

                $answer = $answers->addChild( 'answer', $item['answer'] );
                unset( $item['answer'] );

            } else {

                $answer = $answers->addChild( 'answer' );

            }
            
            foreach ( $item as $key => $value ) $answer->addAttribute( $key, esc_attr( $value ) );

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
    // Возвращает xml-код в отформатированном виде
    //
    
    private function get_formatted_xml( $xml )
    {
        $dom = new DOMDocument( '1.0' );
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        $dom->loadXML( $xml->asXML() );

        return  $dom->saveXML();
    }

}

?>