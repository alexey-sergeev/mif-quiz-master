<?php

//
// Класс ядра обработки тестов
// 
//


defined( 'ABSPATH' ) || exit;

include_once dirname( __FILE__ ) . '/part-core.php';


class mif_qm_quiz_core extends mif_qm_core_core {

    // private $quiz_txt = '';

    function __construct()
    {

        // if ( $data === NULL ) {

        //     global $post;
        //     $this->quiz_txt = $post->post_content;
            
        // } elseif ( is_numeric( $data ) ) {
            
        //     $post = get_post( $data );
        //     $this->quiz_txt = $post->post_content;
            
        // } else {
            
        //     $this->quiz_txt = $data;

        // }

        parent::__construct();

    }




    //
    // Получает снимок теста для работы пользователя
    //
   
    public function get_snapshot( $data = NULL )
    {
        $quiz = ( is_array( $data ) ) ? $data : $this->parse( $data );

        $exemplar = array();

        $quiz_settings = (array) $quiz['param']['settings'];
        $parts = (array) $quiz['parts'];

        $quiz_index = array();

        // Замешать (или нет) и выбрать нужное количество вопросов в разделе
        
        foreach ( $parts as $key => $part ) {
            
            $questions = array();

            $questions_index = array_keys( (array) $part['questions'] );
            // $number = (int) $part['param']['number'];
            $number = $this->get_clean( 'number', $part, 'part' );

            $part_settings = (array) $part['param']['settings'];
            
            if ( $this->is_param( 'random', $part ) ) shuffle( $questions_index );
            
            if ( $number != 0 ) $questions_index = array_slice( $questions_index, 0, $number );

            foreach ( $questions_index as $index ) $questions[] = $part['questions'][$index];

            $parts[$key]['questions'] = $questions;
        }

        $quiz['parts'] = $parts;

        // Добавить метку о создании экземпляра

        $quiz['processed']['created'] = $this->get_signature();

        // Добавить данные об ограничении времени (если есть)

        $time = $this->get_clean( 'time', $quiz, 'quiz', true );

        if ( isset( $time['second'] ) ) {

            $timestamp = $this->get_timestamp();
            $quiz['processed']['deadline']['start'] = $timestamp;
            $quiz['processed']['deadline']['end'] = $timestamp + $time['second'];

        }

        if ( $this->is_param( 'part', $quiz ) ) {

            // Составить индекс для режима разделов
            
            $index = array_keys( $quiz['parts'] );
            
            if ( $this->is_param( 'random', $quiz ) ) shuffle( $index );
            $quiz['processed']['index'] = $index;
            
        } else {
            
            // Составить индекс для режима вопросов или теста

            $index = array();
            
            foreach ( (array) $quiz['parts'] as $p_key => $part )
                foreach ( (array) $part['questions'] as $q_key => $question ) $index[] = $p_key . '.' . $q_key;

            if ( $this->is_param( 'random', $quiz ) ) shuffle( $index );
            $quiz['processed']['index'] = $index;

        }


        // Замешать ответы в вопросах теста

        foreach ( (array) $quiz['parts'] as $p_key => $part ) 
            foreach ( (array) $part['questions'] as $q_key => $question ) {

                $question_core = new mif_qm_question_core();
                $quiz['parts'][$p_key]['questions'][$q_key] = $question_core->shuffle( $question );

            }



        // p($quiz);

        return $quiz;
    }



    //
    // Преобразует текстовое описание теста в структурированный массив
    //

    public function parse( $data = NULL )
    {

        $quiz_txt = $this->get_quiz_txt( $data );

        // Получить массив текстовых описаний разделов теста
        
        $quiz_raw = $this->get_parts_raw( $quiz_txt );

        $quiz = array();
        $part = new mif_qm_part_core();

        // Записать заголовок теста

        $quiz['title'] = ( isset( $quiz_raw['title'] ) ) ? $quiz_raw['title'] : '';
        
        // Записать структурированную информацию о параметрах

        $param = new mif_qm_param_core( $quiz_raw['param'], 'quiz' );
        // $quiz['param'] = $param->parse();
        $quiz['param'] = $param->explication();

        // Записать структурированную информацию о содержимом теста

        $n = 1;
        foreach( (array) $quiz_raw['parts'] as $item ) {

            $data = $part->parse( $item, $quiz['param'], $n );
            if ( $data ) {

                $quiz['parts'][] = $data;
                $n++;

            }

        }
        
        // Расставить идентификаторы вопросов

        foreach ( (array) $quiz['parts'] as $p_key => $part )
            foreach ( (array) $part['questions'] as $q_key => $question )
                $quiz['parts'][$p_key]['questions'][$q_key]['id'] = 'question_' . $p_key . '_' . $q_key;

        // p( $quiz );
            
        return $quiz;
    }



    //
    // Получить данные из записи, если передан не текст
    //
    
    private function get_quiz_txt( $data = NULL )
    {
        if ( $data === NULL ) {

            // Данные - из текущго поста

            global $post;
            $quiz_txt = $post->post_content;
            
        } elseif ( is_numeric( $data ) ) {
            
            // Данные - из поста с указанным номером

            $post = get_post( $data );
            $quiz_txt = $post->post_content;
            
        } else {
            
            // Данные - переданы в функцию

            $quiz_txt = $data;

        }

        return $quiz_txt;

    }


    //
    // Составляет массив разделов теста в текстовом формате "как есть"
    //

    private function get_parts_raw( $parts_txt )
    {
       
        $arr = preg_split( '/\\r\\n?|\\n/', $parts_txt );
        
        $n = -1;
        $flag = true;
        $quiz = array();
        
        foreach ( $arr as $item ) {
            
            $item = strim( $item );
            
            // if ( $item == '' ) continue;
            if ( $flag && $item == '' ) continue;
            
            // Заменить среднее и длинное тире в начале строк на обычный -

            $item = preg_replace( '/^–/', '-', $item );
            $item = preg_replace( '/^—/', '-', $item );

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