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
    // Получает экземпляр теста для работы пользователя
    //
   
    public function get_exemplar( $data = NULL )
    {
        $quiz = ( is_array( $data ) ) ? $data : $this->parse( $data );

        $exemplar = array();

        $settings = (array) $quiz['param']['settings'];
        $parts = (array) $quiz['parts'];

        $quiz_index = array();

        // Замешать (или нет) и выбрать нужное количество вопросов в разделе
        
        foreach ( $parts as $key => $part ) {
            
            $questions = array();

            $questions_index = array_keys( (array) $part['questions'] );
            $number = (int) $part['param']['number'];
            $settings = (array) $part['param']['settings'];
            
            if ( in_array( 'random', $settings ) ) {
                
                shuffle( $questions_index );    
                
            }
            
            $questions_index = array_slice( $questions_index, 0, $number );

            foreach ( $questions_index as $index ) $questions[] = $part['questions'][$index];

            $parts[$key]['questions'] = $questions;
        }

        $quiz['parts'] = $parts;

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

        foreach( (array) $quiz_raw['parts'] as $item ) {

            $data = $part->parse( $item, $quiz['param'] );
            if ( $data ) $quiz['parts'][] = $data;

        }
        
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
            
            if ( $item == '' ) continue;
            
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