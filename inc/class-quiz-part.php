<?php

//
// Класс для обработки текстового описания разделов теста в структурированный массив
// 
//


defined( 'ABSPATH' ) || exit;

include_once dirname( __FILE__ ) . '/class-question.php';


class mif_qm_quiz_part  {

    // Маркер для вопроса

    private $mark_question = '=';
    
   
    
    
    function __construct()
    {

        // Шаблоны для выделения вопросов (с ответами)

        $this->pattern_question = '/^[' . $this->mark_question . ']/';
       
    }

    

    //
    // Преобразует текстовое описание теста в структурированный массив
    //

    function parse( $text )
    {
        
        // Получить массив текстовых описаний вопросов (с ответами)
        
        $questions_raw = $this->get_questions_raw( $text );
        
        $quiz = array();
        $question = new mif_qm_question();

        foreach( $questions_raw as $item ) {

            $quiz[] = $question->parse( $item );

        }
        
        p($quiz);
            
        return $quiz;
    }



    //
    // Составляет массив вопросов (с ответами) в текстовом формате "как есть"
    //

    private function get_questions_raw( $text )
    {
        
        $arr = preg_split( '/\\r\\n?|\\n/', $text );
    
        $n = -1;
        $flag = true;
        $quizess_txt = array();

        foreach ( $arr as $item ) {

            $item = strim( $item );

            if ( preg_match( '/^=/', $item ) || $flag ) {

                // Нашелся новый вопрос или мы начинаем работу

                $n++;
                $quizess_txt[$n] = '';
                $flag = false;

            }

            $quizess_txt[$n] .= $item . "\n";

        }
        
        return $quizess_txt;
    }



}






?>