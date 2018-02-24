<?php

//
// Проверка завершенных тестов
// 
//


defined( 'ABSPATH' ) || exit;



class mif_qm_process_inspector extends mif_qm_process_core { 

    private $quiz = array();
    // private $result = false;

    function __construct( $quiz = array(), $recount = false )
    {
        parent::__construct();
        $this->quiz = $this->quiz_inspection( $quiz, $recount );
    }

    
    //
    // Получить результаты теста
    //

    public function get_result( $snapshot_id = false, $inspection_mode = '' )
    {
        // Если тест не проверен, то и результатов нет
        
        if ( empty( $this->quiz['processed']['rating'] ) ) return false;
        if ( empty( $this->quiz['processed']['success'] ) ) return false;

        // Уточнить режим оценки

        $inspection_mode = $this->get_inspection_mode( $this->quiz, $inspection_mode );

        // if ( ! in_array( $inspection_mode, array( 'strict', 'balanced', 'detailed' ) ) ) {

        //     $inspection_mode = 'balanced'; 
        //     if ( $this->is_param( 'strict', $this->quiz ) ) $inspection_mode = 'strict'; 
        //     if ( $this->is_param( 'detailed', $this->quiz ) ) $inspection_mode = 'detailed'; 

        // }

        // Рассчитать баллы, проценты и статус завершенности
        
        $rating = round( $this->quiz['processed']['rating'][$inspection_mode] );

        $max_rating = $this->get_max_rating( $this->quiz );
        $percent = ( $max_rating != 0 ) ? round( 100 * $rating / $max_rating ) : 0;

        $success = $this->quiz['processed']['success'][$inspection_mode];
        $success_rating = $this->get_success_rating( $this->quiz );
        if ( $rating < $success_rating ) $success = 'no';

        // Оформить результат

        // !!! надо рассчитать id теста
        
        // $result = $this->get_signature();
        $result['time'] = $this->quiz['processed']['inspected']['time'];
        
        // !!! надо пытаться выяснить id снимка, если это не указано
        
        $result['snapshot'] = $snapshot_id;
        $result['max'] = $max_rating;
        $result['rating'] = $rating;
        $result['percent'] = $percent;
        $result['success'] = $success;

        //  Рассчитать время выполнения теста

        $duration = $this->get_timestamp( $this->quiz['processed']['inspected']['time'] ) - $this->get_timestamp( $this->quiz['processed']['created']['time'] );

        $result['duration'] = $duration;

        // !!! Сюда также версию и информацию по результатам разделов

        return $result;
    }


    
    //
    // Рассчитать результаты теста
    //

    private function quiz_inspection( $quiz, $recount = false )
    {
        // Если передано число, то преобразовать это в тест

        if ( is_numeric( $quiz ) ) {

            $data = get_post( $quiz );
            $xml_core = new mif_qm_xml_core();
            $quiz = $xml_core->to_array( $data->post_content );

        }
        
        // Если данные результата есть и пересчитать не просят, то ничего и не делать

        if ( isset( $quiz['processed']['rating'] ) && isset( $quiz['processed']['success'] ) && $recount == false ) return $quiz;

        // Считать результат

        $q_open_flag = false;
        $q_rating = array( 'strict' => 0, 'balanced' => 0, 'detailed' => 0 );
        $q_success = array( 'strict' => 'yes', 'balanced' => 'yes', 'detailed' => 'yes' );

        foreach ( (array) $quiz['parts'] as $p_key => $part ) {
            
            $p_open_flag = false;
            $p_rating = array( 'strict' => 0, 'balanced' => 0, 'detailed' => 0 );
            $p_success = array( 'strict' => 'yes', 'balanced' => 'yes', 'detailed' => 'yes' );

            foreach ( (array) $part['questions'] as $q_key => $question ) {

                $count = 0;
                $total = 0;

                if ( in_array( $question['type'], array( 'single', 'multiple' ) ) ) {

                    // Выбор. Сопоставить результаты.
                    
                    foreach ( (array) $question['answers'] as $a_key => $answer ) {

                        // if ( $answer['status'] == 'no' && $answer['result'] == 'no' ) continue;
                        // if ( $answer['status'] == 'yes' ) $corrected++;

                        $total++;

                        if ( $answer['status'] == $answer['result'] ) {
                            
                            $count++;
                            $quiz['parts'][$p_key]['questions'][$q_key]['answers'][$a_key]['resume'] = 'correct';

                        } else {

                            $quiz['parts'][$p_key]['questions'][$q_key]['answers'][$a_key]['resume'] = 'incorrect';

                        }

                    }
                    
                    $rating = $this->calculate_rating( $count, $total, $question['type'] );
                    
                } elseif ( in_array( $question['type'], array( 'sort', 's-sort', 'm-sort', 'text' ) ) ) {
                    
                    // Сортировки и текст. Сопоставить результаты.
                    // Правильно - совпало
                    // Неправильно - не совпало

                    $count = 0;
                    $total = 0;

                    foreach ( (array) $question['answers'] as $a_key => $answer ) {

                        $result = mb_strtoupper( $answer['result'] );
                        $meta = array_map( 'mb_strtoupper', (array) $answer['meta'] );

                        $total++;

                        if ( in_array( $result, $meta ) ) {
                            
                            $count++;
                            $quiz['parts'][$p_key]['questions'][$q_key]['answers'][$a_key]['resume'] = 'correct';

                        } else {

                            $quiz['parts'][$p_key]['questions'][$q_key]['answers'][$a_key]['resume'] = 'incorrect';

                        }

                    }
                    
                    $rating = $this->calculate_rating( $count, $total, $question['type'] );
                    
                } elseif ( in_array( $question['type'], array( 'open' ) ) ) {
                    
                    $q_open_flag = true;
                    $p_open_flag = true;
                    
                }
                
                $quiz['parts'][$p_key]['questions'][$q_key]['processed']['rating'] = $rating;
                // p($quiz['parts'][$p_key]['questions'][$q_key]);

                foreach ( $p_rating as $key => $value ) $p_rating[$key] += $rating[$key];
                
            }

            if ( ! $p_open_flag ) {

                // Рассчитать абсолютные значения рейтинга

                // $param_interpretation = new mif_qm_param_interpretation( 'rating', $quiz['parts'][$p_key]['param']['rating'], 'part' );
                // $rating_param = $param_interpretation->get_clean_value();

                $rating_param = $this->get_clean( 'rating', $quiz['parts'][$p_key], 'part' );
                
                foreach ( $p_rating as $key => $value ) $p_rating[$key] *= $rating_param;

                // Рассчитать статус завершенности (если это применимо)

                $success_rating = $this->get_success_rating( $quiz['parts'][$p_key], 'part' );

                if ( $success_rating ) {

                    // Учточнить значение успеха для раздела

                    foreach ( $p_success as $key => $value ) {

                        if ( $p_rating[$key] < $success_rating ) $p_success[$key] = 'no';

                    }

                }

                $quiz['parts'][$p_key]['processed']['rating'] = $p_rating;
                $quiz['parts'][$p_key]['processed']['success'] = $p_success;

            }

            // Уточнить результаты всего теста

            foreach ( $q_rating as $key => $value ) {
    
                $q_rating[$key] += $p_rating[$key];
                if ( $p_success[$key] == 'no' ) $q_success[$key] = 'no';
    
            }

        }

        if ( ! $q_open_flag ) {
            // !!! надо рассчитать id теста
            $quiz['processed']['inspected'] = $this->get_signature();
            $quiz['processed']['rating'] = $q_rating;
            $quiz['processed']['success'] = $q_success;

        }

        return $quiz;
    }



    //
    // Возвращает значение порога положительной оценки в баллах
    //

    private function get_success_rating( $item = array(), $mode = 'quiz' )
    {
        $success_rating = 0;

        $success_param = $this->get_clean( 'success', $item, $mode, true );

        // p($success_param);
        if ( ! empty( $success_param['value'] ) ) {
            
            // Если есть порог положительной оценки ...

            // ... узнать это порог

            $success_rating = $success_param['value'];
            // p($success_rating);

            // Если это значение в процентах ...

            if ( isset( $success_param['unit'] ) && $success_param['unit'] == 'percent' ) {

                // ... пересчитать его в баллы

                $max_value = $this->get_max_rating( $item, $mode );
                $success_rating = $max_value * $success_rating / 100;
                
            }

        }
        return $success_rating;
    }



    //
    // Вычислить результат
    //

    private function calculate_rating( $count, $total, $type )
    {
        if ( $total == 0 ) return;

        $arr = array( 'strict' => 0, 'balanced' => 0, 'detailed' => 0 );

        // Детальная оценка - процент правильных
        
        $arr['detailed'] = $count / $total;
        
        // Строгая оценка - 1 (всё правильно) или 0 (есть хоть какая-то ошибка)
        
        $arr['strict'] = ( $arr['detailed'] == 1 ) ? 1 : 0;
        
        // Сбалансированная оценка - 1 (всё правильно), 0.5 (есть лишь одна ошибка) или 0 (больше одной ошибки)

        $arr['balanced'] = 0;

        if ( in_array( $type, array( 'single', 'multiple', 'text' ) ) ) {

            if ( $count == $total ) {

                $arr['balanced'] = 1;

            } elseif ( $total > 1 && $total - $count == 1 ) {

                $arr['balanced'] = 0.5;

            }

        } elseif ( in_array( $type, array( 'sort', 's-sort', 'm-sort' ) ) ) {

            if ( $count == $total ) {

                $arr['balanced'] = 1;

            } elseif ( $total - $count == 2 ) {

                $arr['balanced'] = 0.5;

            }

        }

        return $arr;
    }
        



    //
    // Получить тест с рассчитанным результатом
    //

    public function get_quiz()
    {
        return $this->quiz;
    }



}

?>