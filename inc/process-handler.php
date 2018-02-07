<?php

//
// Процесс обработки пользовательских ответов
// 
//


defined( 'ABSPATH' ) || exit;



class mif_qm_process_handler extends mif_qm_core_core { 

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
        // Ставить время получения ответ (это признак завершенности), если ответ формально-корректен

        foreach ( (array) $this->quiz['parts'] as $p_key => $part ) {

            foreach ( (array) $part['questions'] as $q_key => $question ) {

                // !!! Здесь проверять - если данные есть, а в настройках теста исправлять нельзя, то пропускать

                $id = $question['id'];
                if ( empty( $answers_data[$id] ) ) continue;

                $data = $answers_data[$id];
                $q_flag = false;

                // Запомнить старые данные

                $old_result = $this->quiz['parts'][$p_key]['questions'][$q_key];
                
                // Одиночный или множественный выбор

                if ( in_array( $question['type'], array( 'single', 'multiple' ) ) ) {
                    
                    // Сохранить данные о сделанном выборе

                    $q_flag = false;

                    foreach ( (array) $question['answers'] as $a_key => $answer ) {

                        $hash = $this->get_hash( $answer['caption'] );

                        if ( in_array( $hash, (array) $data ) ) {

                            $this->quiz['parts'][$p_key]['questions'][$q_key]['answers'][$a_key]['result'] = 'yes';
                            $q_flag = true;

                        } else {

                            $this->quiz['parts'][$p_key]['questions'][$q_key]['answers'][$a_key]['result'] = 'no';

                        }

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
                    
                    // Сохранить данные о выбранных парах
                    
                    $q_flag = true;
                    
                    foreach ( (array) $question['answers'] as $a_key => $answer ) {
                        
                        $hash = $this->get_hash( $answer['status'] );
                        $index_id = ( isset( $data[$hash] ) ) ? $data[$hash] : 'undefined';
                        $result = ( isset( $index[$index_id] ) ) ? $index[$index_id] : 'undefined';
                        
                        if ( isset( $index[$data[$hash]] ) ) {
                            
                            $this->quiz['parts'][$p_key]['questions'][$q_key]['answers'][$a_key]['result'] = $index[$data[$hash]];

                        } else {
                            
                            $q_flag = false;
                            
                        }
                            
                    }

                }
                    
                // Текст с автопроверкой
                
                if ( in_array( $question['type'], array( 'text' ) ) ) {
                    
                    // Сохранить данные 

                    $q_flag = true;
                    
                    foreach ( (array) $question['answers'] as $a_key => $answer ) {
                        
                        $hash = $this->get_hash( serialize( $answer['meta'] ) );
                        
                        if ( isset( $data[$hash] ) && $data[$hash] !== '' ) {
                            
                            $this->quiz['parts'][$p_key]['questions'][$q_key]['answers'][$a_key]['result'] = $data[$hash];
                            
                        } else {
                            
                            $q_flag = false;

                        }

                    }

                }

                // Открытый ввод
                
                if ( in_array( $question['type'], array( 'open' ) ) ) {
                    
                    // Сохранить данные

                    $result = array();
                    $q_flag = false;
                    
                    foreach ( (array) $question['answers'] as $a_key => $answer ) {
                        
                        $hash = $this->get_hash( serialize( $answer ) );

                        if ( $answer['type'] == 'text' ) {

                            if ( isset( $data[$hash] ) ) {
                                
                                $result['text'] = $data[$hash];
                                $result['user'] = $this->get_user_token();
                                $result['time'] = $this->get_time();
                                $q_flag = true;
                                
                            }
                            
                            
                        } elseif ( $answer['type'] == 'file' ) {
                            
                            // !!! Здесь обрабатывать данные о полученных файлах
                            // p( $_FILES );
                            
                            // if ( есть файлы ) {
                                
                                //     $result['files'] = array( данные о файлах );
                                //     $q_flag = true;
                                
                                // }
                                
                        }
                        
                    }
                    
                    if ( $q_flag ) $this->quiz['parts'][$p_key]['questions'][$q_key]['processed']['messages'][] = $result;

                }


                // Взять новые данные

                $new_result = $this->quiz['parts'][$p_key]['questions'][$q_key];

                // Записать метку времени, если были корректны данные ответа. И поставить признак, что данные обновились

                if ( $q_flag && $old_result !== $new_result ) {

                    $this->quiz['parts'][$p_key]['questions'][$q_key]['processed']['submitted'] = $this->get_time();
                    $this->is_modified = true;

                }
                                
            }

            // Проверить, все ли вопросы раздела в итоге завершены

            $p_flag = true;

            foreach ( (array) $part['questions'] as $q_key => $question ) {
                
                // if ( empty( $this->quiz['parts'][$p_key]['questions'][$q_key]['processed']['submitted'] ) ) $p_flag = false;
                if ( ! $this->is_submitted( $this->quiz['parts'][$p_key]['questions'][$q_key] ) ) $p_flag = false;

            }
            
            // Поставить метку времени, если раздел завершен
            
            if ( $p_flag ) $this->quiz['parts'][$p_key]['processed']['submitted'] = $this->get_time();

        }


        // p($this->quiz);


        return $this->quiz;
    }


    
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



    // 
    // Возвращает информацию о том, был ли изменен массив, или нет
    // 

    public function is_modified()
    {
        return $this->is_modified;
    }



}


?>