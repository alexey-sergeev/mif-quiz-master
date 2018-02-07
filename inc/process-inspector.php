<?php

//
// Проверка завершенных тестов
// 
//


defined( 'ABSPATH' ) || exit;



class mif_qm_process_inspector extends mif_qm_core_core { 

    private $quiz = array();
    private $result = false;

    function __construct( $quiz = array() )
    {
        parent::__construct();
        $this->quiz = $quiz;

        $this->quiz_inspection();
    }

    
    //
    // Получить результаты теста
    //

    public function get_result()
    {

        $rating = 0;
        $percent = 0;
        $success = 0;

        if ( ! $this->result ) {



        }




        $this->result = array( 
                    'rating' => $rating,
                    'percent' => $percent,
                    'success' => $success,
                    );

        return $this->result;
    }


    
    //
    // Рассчитать результаты теста
    //

    private function quiz_inspection()
    {
        $quiz = $this->quiz;
        $open_flag = false;

        foreach ( (array) $quiz['parts'] as $p_key => $part ) {

            foreach ( (array) $part['questions'] as $q_key => $question ) {

                if ( in_array( $question['type'], array( 'single', 'multiple' ) ) ) {

                    // Выбор. Сопоставить результаты.
                    // Правильно - yes и yes
                    // Неправильно - yes и no или no и yes
                    // Игнорируется - no и no
                    
                    $count = 0;
                    $total = 0;

                    foreach ( (array) $question['answers'] as $a_key => $answer ) {

                        if ( $answer['status'] == 'no' && $answer['result'] == 'no' ) continue;

                        $total++;

                        if ( $answer['status'] == $answer['result'] ) {
                            
                            $count++;
                            $quiz['parts'][$p_key]['questions'][$q_key]['answers'][$a_key]['resume'] = 'correct';

                        } else {

                            $quiz['parts'][$p_key]['questions'][$q_key]['answers'][$a_key]['resume'] = 'incorrect';

                        }

                    }

                    $quiz['parts'][$p_key]['questions'][$q_key]['processed']['percent'] = $count / $total;
                    
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
                    
                    $quiz['parts'][$p_key]['questions'][$q_key]['processed']['percent'] = $count / $total;
                    p($quiz['parts'][$p_key]['questions'][$q_key]);

                } 
                // elseif ( in_array( $question['type'], array( 'text' ) ) ) {
                    

                // } 
                elseif ( in_array( $question['type'], array( 'open' ) ) ) {

                    $open_flag = true;

                }

            }

        }

        if ( $open_flag ) {

            // Был вопрос с открытым вводом - надо ждать результатов

        }

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