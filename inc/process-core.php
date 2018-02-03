<?php

//
// Ядро процесса обработки
// 
//


defined( 'ABSPATH' ) || exit;



class mif_qm_process_core extends mif_qm_core_core { 


    function __construct()
    {
        parent::__construct();

        // add_action( 'save_post', array( $this, 'delete_my_drafts' ) );
    }


    // 
    // Вернуть часть теста для отображения на экране
    // 
    // Возвращает код ошибки, если есть проблемы
    //
    //      Ошибки: 1 - закончилось количество попыток прохождения теста
    //              2 - что-то пошло не так
    //
    //

    public function get_quiz_stage( $args = array() )
    {

        $current_result = $this->get_quiz_result_data( $args );
        
        // Была ошибка - вернуть ее код

        if ( is_numeric( $current_result ) ) return $current_result;
        
        // Построить массив теста из полученных данных

        $xml_core = new mif_qm_xml_core();
        $quiz = $xml_core->to_array( $current_result->post_content );

        // Проверить корректность индекса

        $quiz = $this->check_index( $quiz );
        // Сформировать раздел как текущий этап теста
        
        $stage = array();
        $quiz_stage = $quiz;
        $index = $quiz['processed']['index'];
        
        if ( in_array( 'quiz', $quiz['param']['settings'] ) ) {
            
            // Режим отображения теста целиком
            
            foreach ( (array) $index as $item ) {
                
                $arr = explode( '.', $item );
                $stage[] = $quiz['parts'][$arr[0]]['questions'][$arr[1]];
                
            }
            
            $quiz_stage['parts'] = array( array( 'questions' => $stage ) );
            
        } elseif ( in_array( 'part', $quiz['param']['settings'] ) ) {
            
            // Режим отображения разделов

            $num = $this->get_num( $quiz, 'part' );
            
            if ( $num == -1 ) {
                
                // Все разделы завершены и номер того, что надо показать, не известен
                // Тест завершен? Подвести итог?

            } else {
                
                // Выбираем раздел с указанным номером
                
                $quiz_stage['parts'] = array( $quiz['parts'][$index[$num]] );

                
            }

            // Записать занные о текущем и максимальном количестве элементов
            
            $quiz_stage['processed']['numbers']['current'] = $num;
            $quiz_stage['processed']['numbers']['max'] = count( $index );

            // p($quiz_stage);
            
        } else {
            
            // Режим отображения вопросов отдельно
            
            $num = $this->get_num( $quiz, 'question' );
            
            if ( $num == -1 ) {
                
                // Все вопросы завершены и номер того, что надо показать, не известен
                // !!! Тест завершен? Подвести итог?

            } else {
                
                // Выбираем вопрос с указанным номером
                
                $arr = explode( '.', $index[$num] );
                $stage[] = $quiz['parts'][$arr[0]]['questions'][$arr[1]];

                $quiz_stage['parts'] = array( array( 'questions' => $stage ) );
                
            }

            // Записать занные о текущем и максимальном количестве элементов
            
            $quiz_stage['processed']['numbers']['current'] = $num;
            $quiz_stage['processed']['numbers']['max'] = count( $index );

            // p($quiz_stage);

        }
        
        
        
        // $quiz_stage = $quiz;
        
        // p($quiz_stage);
        
        return $quiz_stage;
    }



    // 
    // Вернуть идентификатор текущего элемента (номер раздела или вопроса)
    //      Возвращает:
    //          1) номер, если номер просили и он есть
    //          2) номер первого незавершенного элемента, если ошиблись с номером или не просили
    //          3) -1, если 1 и 2 не получается (в тесте все элементы завершены)
    //

    public function get_num( $quiz = array(), $mode = 'question' )
    {
        // Взять номер из переменной запроса

        $num = isset( $_REQUEST['num'] ) ? (int) ( $_REQUEST['num'] ) : -1;

        // Проверить по индексу - если он есть, но номер для него не подходит, то номер поставить в -1

        if ( ! empty( $quiz['processed']['index'] ) && ! isset( $quiz['processed']['index'][$num] ) ) $num = -1;

        // Если номера нет, то пытаться искать очередной незавершенный

        if ( $num == -1 ) $num = $this->get_current_elem( $quiz, $mode );

        return $num;
    }
    
    
    
    //
    // Найти первый незавершенный элемент (раздел или вопрос)
    //

    public function get_current_elem( $quiz, $mode )
    {
        // !!! Надо это еще написать.
     
        return 0;
    }


    
    //
    // Проверить индекс теста
    //

    private function check_index( $quiz )
    {

        // !!! Надо это еще написать.
        // Если индекс некорректен или его нет, то добавить в формате "один к одному"

        return $quiz;
    }
    
    
    
    // 
    // Вернуть данные текущей записи результатов теста
    // 
    // Возвращает код ошибки, либо объект записи с экземпляром теста
    //
    //      Ошибки: 1 - закончилось количество попыток прохождения теста
    //              2 - что-то пошло не так
    //
    
    private function get_quiz_result_data( $args = array() )
    {
        $error = 0;

        // Сформировать список аргументов для поиска результатов (идентификатор пользователя и теста)

        $defaults = array( 'user' => get_current_user_id(), 'quiz' => $this->get_quiz_token() );
        $args = wp_parse_args( $args, $defaults );

        $result_args = array(
            // 'numberposts' => 0,
            'post_type'   => 'quiz_result',
            'post_status' => 'draft',
            'orderby'     => 'date',
            'order'       => 'DESC',
            'author'      => $args['user'],
            'post_parent' => $args['quiz'],
        );
    
        $results = get_posts( $result_args );
        
        if ( empty( $results ) ) {

            // Текущих результатов нет. Сформировать их (проверить, не закончилось ли количество попыток?)

            $quiz_core = new mif_qm_quiz_core();
            $quiz = $quiz_core->get_exemplar( $args['quiz'] );
            $xml_core = new mif_qm_xml_core();
            $quiz_xml = $xml_core->to_xml( $quiz );

            if ( isset( $quiz['param']['attempt'] ) && ! mif_qm_user_can( 'edit-quiz', $args['quiz'] ) ) {
                
                // Есть ограничение по количеству попыток. Проверить, не превышено ли оно
                // Для текущего пользователя, который может редактировать тест (его автор или редактор сайта) - ограничение не действует

                $attempt = $this->get_clean( 'attempt', $quiz['param']['attempt'], 'quiz' );

                if ( $attempt > 0 ) {
                    
                    $result_args = array(
                        'post_status'   => 'publish',
                        'post_type'     => 'quiz_result',
                        'post_author'   => $args['user'],
                        'post_parent'   => $args['quiz'],
                    );
        
                    $results_publish = get_posts( $result_args );

                    if ( count( $results_publish ) >= $attempt ) $error = 1;

                };
            }

            // Если превышения нет - формируем новую заготовку результатов

            if ( $error === 0 ) {

                // Узнать имя записи, где хранится quiz

                $quiz_post = get_post( $args['quiz'] );
                $post_title = $quiz_post->post_title . ' ('. $quiz_post->ID . ')';

                // Сохраняем в виде нового черновика quiz_result

                $result_args = array(
                    'post_title'    => $post_title,
                    'post_content'  => $quiz_xml,
                    'post_status'   => 'draft',
                    'post_type'     => 'quiz_result',
                    'post_author'   => $args['user'],
                    'post_parent'   => $args['quiz'],
                    'comment_status' => 'closed',
                    'ping_status'    => 'closed', 
                );
                
                $result_id = wp_insert_post( $result_args );

                // Взять сохранненный результат

                if ( $result_id ) {
                    
                    $result = get_post( $result_id );

                } else {

                    $error = 2;

                }

            }

        } else {

            // Результаты есть. Взять последний

            $result = $results[0];

            if ( count( $results ) > 1 ) {

                // Почему-то таких результатов несколько. Удалить лишние.

                foreach ( (array) $results as $key => $result ) {

                    if ( $key === 0 ) continue;
                    wp_trash_post( $result->ID );

                }
            }
        }

        if ( $error === 0 ) {

            return $result;
            
        } else {
            
            return $error;

        }
    }



    // 
    // Вернуть идентификатор текущего действия
    // 

    public function get_action()
    {

        $action = isset( $_REQUEST['action'] ) ? sanitize_key( $_REQUEST['action'] ) : 'view';

        return $action;
    }



}


?>