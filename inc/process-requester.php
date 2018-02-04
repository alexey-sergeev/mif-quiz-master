<?php

//
// Процесс обработки пользовательских ответов
// 
//


defined( 'ABSPATH' ) || exit;



class mif_qm_process_requester extends mif_qm_core_core { 

    private $quiz = array();
    private $is_modified = false;

    function __construct( $quiz = array() )
    {
        parent::__construct();
        $this->quiz = $quiz;
    }


    //
    // Обрабатывает данные пользователя и заносит их в массив
    //

    public function parse_request_answers()
    {

        if ( empty( $_REQUEST['answers'] ) ) return $this->quiz;

        $answers_data = $this->get_answers_data();

        // Перебрать все вопросы и добавить данные ответов, если они есть

        foreach ( (array) $this->quiz['parts'] as $p_key => $part )
            foreach ( (array) $part['questions'] as $q_key => $question ) {

                // !!! Здесь проверять - если данные есть, а в настройках теста исправлять нельзя, то пропускать

                $id = $question['id'];
                if ( empty( $answers_data[$id] ) ) continue;

                $data = $answers_data[$id];

                // Одиночный выбор

                if ( $question['type'] == 'single' ) {

                    // Отметить выбор

                    foreach ( (array) $question['answers'] as $a_key => $answer ) {

                        $hash = $this->get_hash( $answer['caption'] );
                        $this->quiz['parts'][$p_key]['questions'][$q_key]['answers'][$a_key]['result'] = ( $hash == $data ) ? 'yes' : 'no';

                    }

                }

                // Множественный выбор

                if ( $question['type'] == 'multiple' ) {

                    // Отметить выбор

                    foreach ( (array) $question['answers'] as $a_key => $answer ) {

                        $hash = $this->get_hash( $answer['caption'] );
                        $this->quiz['parts'][$p_key]['questions'][$q_key]['answers'][$a_key]['result'] = ( in_array( $hash, $data) ) ? 'yes' : 'no';

                    }

                }

                // Разные сортировки

                if ( in_array( $question['type'], array( 'sort', 's-sort', 'm-sort' ) ) ) {

                    // Индекс возможных ответов
                    
                    $index = array();

                    foreach ( (array) $question['answers'] as $a_key => $answer ) {

                        $hash = $this->get_hash( $answer['caption'] );
                        $index[$hash] = $answer['caption'];

                    }

                    // Сохранить данные

                    foreach ( (array) $question['answers'] as $a_key => $answer ) {

                        $hash = $this->get_hash( $answer['status'] );
                        $index_id = ( isset( $data[$hash] ) ) ? $data[$hash] : 'undefined';
                        $result = ( isset( $index[$index_id] ) ) ? $index[$index_id] : 'undefined';
                        
                        $this->quiz['parts'][$p_key]['questions'][$q_key]['answers'][$a_key]['result'] = $result;
                        
                    }
                    
                }
                
                // Текст и файлы
                
                if ( in_array( $question['type'], array( 'text', 'open' ) ) ) {
                    
                    // Сохранить данные 
                    
                    foreach ( (array) $question['answers'] as $a_key => $answer ) {
                        
                        $hash = $this->get_hash( serialize( $answer ) );
                        $result = ( isset( $data[$hash] ) ) ? $data[$hash] : 'undefined';
                        
                        $this->quiz['parts'][$p_key]['questions'][$q_key]['answers'][$a_key]['result'] = $result;
                        // p(serialize( $answer ));
                        // p($data);
                        // p($hash);
                        // p($answer);

                    }



                }


                // p($id);
                // p($answers_data);
                // p($question);

            }











        // $index = $this->get_answers_index();

        // p($index);
        // p($answers_data);
        // p($answers);
        p($this->quiz);


        return $this->quiz;
    }



    // //
    // // Получить индекс возможных ответов теста
    // //

    // private function get_answers_index()
    // {
    //     $index = array();

    //     foreach ( (array) $this->quiz['parts'] as $p_key => $part )
    //         foreach ( (array) $part['questions'] as $q_key => $question ) {

    //             p($question);

    //         }


    // }


    
    //
    // Получить обработанные данные REQUEST-запроса
    //

    private function get_answers_data( $answers = array() )
    {
        if ( empty( $answers ) ) $answers = $_REQUEST['answers'];

        $arr = array();

        foreach ( (array) $answers as $key => $value ) {

            $arr_key = explode( '_', $key );

            if ( ! $arr_key[0] == 'question' ) continue;

            $id = 'question_' . (int) $arr_key[1] . '_' . (int) $arr_key[2];
            $data_key = ( isset( $arr_key[3] ) ) ? sanitize_key( $arr_key[3] ) : '';

            if ( is_array( $value ) ) {

                $data_value = array_map( 'sanitize_key', $value );

            } else {

                $data_value = sanitize_textarea_field( $value );

            }

            if ( $data_key ) {
                
                // $arr[$id][] = array( 'key' => $data_key, 'value' => $data_value );
                $arr[$id][$data_key] = $data_value;

            } else {

                $arr[$id] = $data_value;

            }


        }

        return $arr;
    }


    // private function get_answers_data( $answers = array() )
    // {
    //     if ( empty( $answers ) ) $answers = $_REQUEST['answers'];

    //     $arr = array();

    //     foreach ( (array) $answers as $key => $value ) {

    //         $arr_key = explode( '_', $key );

    //         if ( ! $arr_key[0] == 'question' ) continue;

    //         // $arr_value = array( 'p' => (int) $arr_key[1], 'q' => (int) $arr_key[2] );
    //         $arr_value = array( 'id' => (int) $arr_key[1] . '.' . (int) $arr_key[2] );
    //         if ( isset( $arr_key[3] ) ) $arr_value['key'] = sanitize_key( $arr_key[3] );

    //         if ( is_array( $value ) ) {

    //             $arr_value['value'] = array_map( 'sanitize_key', $value );

    //         } else {

    //             $arr_value['value'] = sanitize_textarea_field( $value );

    //         }

    //         $arr[] = $arr_value;

    //     }

    //     return $arr;
    // }


    // 
    // Возвращает информацию о том, был ли изменен массив, или нет
    // 

    public function is_modified()
    {
        return $this->is_modified;
    }



}


?>