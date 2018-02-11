<?php

//
// Ядро процесса обработки
// 
//


defined( 'ABSPATH' ) || exit;

include_once dirname( __FILE__ ) . '/process-core.php';
include_once dirname( __FILE__ ) . '/process-handler.php';
include_once dirname( __FILE__ ) . '/process-inspector.php';
include_once dirname( __FILE__ ) . '/process-screen.php';



class mif_qm_process_process extends mif_qm_process_core { 

    // // Идентификатор записи, где хранится снимок теста.
    
    // private $snapshot_id = NULL;
    private $quiz_id = NULL;
    private $quiz = array();


    function __construct( $quiz_id = NULL )
    {
        parent::__construct();

        $this->quiz_id = $this->get_quiz_id( $quiz_id );

        $quiz_core = new mif_qm_quiz_core();
        $this->quiz = $quiz_core->parse( $this->quiz_id );

    }



    // 
    // Получить список результатов теста
    // 

    public function get_result_list()
    {
        $process_results = new mif_qm_process_results();
        $result_list = $process_results->get_list( $this->quiz_id );

        return $result_list;
    }



    // 
    // Получить результаты теста
    // 

    public function get_result( $args = array() )
    {
        $snapshot_data = $this->get_snapshot_data( $args );
        $quiz = $this->get_quiz( $snapshot_data );
        
        $process_inspector = new mif_qm_process_inspector( $quiz );
        $result = $process_inspector->get_result( $snapshot_data->ID );

        if ( $result ) {

            // Если есть результат, то внести его в базу результатов и вернуть для показа пользователю

            $process_results = new mif_qm_process_results();
            $process_results->update( $result );

            return $result;

        } else {

            return false;

        }
    }
   


    // 
    // Вернуть часть теста для отображения на экране
    // 
    // Возвращает числовой код, если есть проблемы
    //
    //     -1 - показывать страницу с кнопкой начала теста
    //      0 - тест завершен
    //      1 - закончилось количество попыток прохождения теста
    //      2 - что-то пошло не так
    //
    //

    public function get_quiz_stage( $args = array() )
    {
        // Есть ли текущий снимок?

        $snapshot = $this->get_current_snapshot( $args );
        
        // Есть ли режим автоматического начала?
        
        $auto = $this->is_param( 'auto', $this->quiz );
        
        // Нажата ли кнопка "начать тест"?

        $start_btn = ( isset( $_REQUEST['start'] ) && $_REQUEST['start'] == 'yes' ) ? true : false;

        // Вернуть -1, если ничего указанного нет

        if ( ! ( $snapshot || $auto || $start_btn ) ) return -1;

        // Получить данные снимка (взять текущий, или сформировать новый)

        $snapshot_data = $this->get_snapshot_data( $args );
        
        // Была ошибка - вернуть ее код

        if ( is_numeric( $snapshot_data ) ) return $snapshot_data;
        
        // Получить массив теста с учетом результата и последних данных пользователя

        $quiz = $this->process_handler( $snapshot_data );

        // Проверить корректность индекса

        $quiz = $this->check_index( $quiz );

        // Сформировать только один раздел как текущий этап теста
        // Этот раздел может :
        //      1) быть простым разделом (режим part)
        //      2) содержать только один вопрос (режим question)
        //      3) содержать все вопросы (режим quiz)
        //
        
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
                
                // Все разделы завершены и номер того, что надо показать, не известен. Тест завершен.
                
                return 0;                

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
                return 0;

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
    // Сформировать массив теста из хранимых данных
    //

    private function get_quiz( $snapshot_data )
    {
        $xml_core = new mif_qm_xml_core();
        $quiz = $xml_core->to_array( $snapshot_data->post_content );
        return $quiz;
    }



    //
    // Получить массив теста из объекта текущих результатов с учетом последних данных пользователя
    //
    //

    public function process_handler( $snapshot_data )
    {
        // Сформировать массив теста из хранимых данных

        // $xml_core = new mif_qm_xml_core();
        // $quiz = $xml_core->to_array( $snapshot_data->post_content );
        $quiz = $this->get_quiz( $snapshot_data );
        
        // Проверить данные пользовательского запроса (новые ответы)
        
        $process_handler = new mif_qm_process_handler( $quiz );
        $quiz = $process_handler->parse_request_answers();

        // p($quiz);

        // Если получены новые данные пользователя
        
        if ( $process_handler->is_modified() ) {
            
            // Сохранить их в базе данных
            // !!! Здесь проверять, свои ли данные сохраняет пользователь ???

            $xml_core = new mif_qm_xml_core();
            
            $args = array(
                        'ID' => $snapshot_data->ID,
                        'post_content' => $xml_core->to_xml( $quiz )
                        );

            $res = $this->update_snapshot_data( $args );
            // p( $res );

        }

        return $quiz;
    }



    // 
    // Обновить данные результатов теста
    // Возвращает: true или false в зависимости от успеха результата
    //

    public function update_snapshot_data( $args = array() )
    {
        if ( empty( $args['ID'] ) ) return false;
        // if( ! current_user_can( 'edit_post', $args['ID'] ) ) return false;
        // $res = wp_update_post( $args );
        $process_snapshots = new mif_process_snapshots();
        $res = $process_snapshots->update( $args );

        return $res;
    }



    // 
    // Вернуть идентификатор текущего элемента (номер раздела или вопроса)
    //      Возвращает:
    //          1) номер, если навигация включена, номер просили и он есть
    //          2) номер первого незавершенного элемента, если ошиблись с номером или не просили
    //          3) -1, если 1 и 2 не получается (в тесте все элементы завершены)
    //

    public function get_num( $quiz = array(), $mode = 'question' )
    {
        // Взять номер из переменной запроса

        $num = isset( $_REQUEST['num'] ) ? (int) ( $_REQUEST['num'] ) : -1;

        
        if ( $this->is_param( 'navigation', $quiz ) ) {
            
            // Проверить по индексу - если он есть, но номер для него не подходит, то номер поставить в -1

            if ( ! empty( $quiz['processed']['index'] ) && ! isset( $quiz['processed']['index'][$num] ) ) $num = -1;
    
            // Если номера нет, то пытаться искать очередной незавершенный
    
            if ( $num == -1 ) $num = $this->get_current_elem( $quiz, $mode );

        } else {
            
            $num = $this->get_current_elem( $quiz, $mode );

        }

        return $num;
    }
    
    
    
    //
    // Найти первый незавершенный элемент (раздел или вопрос)
    //

    public function get_current_elem( $quiz, $mode )
    {

        $index = $quiz['processed']['index'];

        foreach ( (array) $index as $key => $item ) {

            if ( $mode == 'part' ) {

                if ( ! $this->is_submitted( $quiz['parts'][$item] ) ) return $key;
                
            } else {
                
                $arr = explode( '.', $item );
                if ( ! $this->is_submitted( $quiz['parts'][$arr[0]]['questions'][$arr[1]] ) ) return $key;

            }

        }

        return -1;
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
    // Вернуть данные текущей записи снимка теста
    // 
    // Возвращает код ошибки, либо объект записи с экземпляром теста
    //
    //      Ошибки: 1 - закончилось количество попыток прохождения теста
    //              2 - что-то пошло не так
    //
    
    private function get_snapshot_data( $args = array() )
    {
        $error = 0;

        // Сформировать список аргументов для поиска результатов (идентификатор пользователя и теста)

        $defaults = array( 'user' => get_current_user_id(), 'quiz' => $this->quiz_id );
        $args = wp_parse_args( $args, $defaults );

        // $snapshot_args = array(
        //     // 'numberposts' => 0,
        //     'post_type'   => 'quiz_snapshot',
        //     'post_status' => 'draft',
        //     'orderby'     => 'date',
        //     'order'       => 'DESC',
        //     // 'author'      => $args['user'],
        //     'meta_key'      => 'owner',
        //     'meta_value'    => $this->get_user_token( $args['user'] ),
        //     'post_parent' => $args['quiz'],
        // );
    
        // $snapshots = get_posts( $snapshot_args );

        $process_snapshots = new mif_process_snapshots();
        $snapshots = $process_snapshots->get( $args );
        
        if ( empty( $snapshots ) ) {

            // Текущих результатов нет. Сформировать их (проверить, не закончилось ли количество попыток?)

            $quiz_core = new mif_qm_quiz_core();
            $quiz = $quiz_core->get_snapshot( $args['quiz'] );
            $xml_core = new mif_qm_xml_core();
            $quiz_xml = $xml_core->to_xml( $quiz );

            if ( isset( $quiz['param']['attempt'] ) && ! mif_qm_user_can( 'edit-quiz', $args['quiz'] ) ) {
                
                // Есть ограничение по количеству попыток. Проверить, не превышено ли оно
                // Для текущего пользователя, который может редактировать тест (его автор или редактор сайта) - ограничение не действует

                $attempt = $this->get_clean( 'attempt', $quiz );

                if ( $attempt > 0 ) {
                    
                    $snapshot_args = array(
                        'post_status'   => 'publish',
                        'post_type'     => 'quiz_snapshot',
                        // 'post_author'   => $args['user'],
                        'meta_key'      => 'owner',
	                    'meta_value'    => $this->get_user_token( $args['user'] ),
                        'post_parent'   => $args['quiz'],
                    );
        
                    $snapshot_publish = get_posts( $snapshot_args );

                    if ( count( $snapshot_publish ) >= $attempt ) $error = 1;

                };
            }

            // Если превышения нет - формируем новую заготовку результатов

            if ( $error === 0 ) {

                // Узнать имя и автора записи для будущего черновика снимка

                $quiz_post = get_post( $args['quiz'] );
                $quiz_title = $this->get_user_token( $args['user'] ) . ' — ' . $quiz_post->post_title . ' ('. $quiz_post->ID . ')';
                $quiz_author = $quiz_post->post_author;

                // Сохраняем в виде нового черновика quiz_snapshot

                $snapshot_args = array(
                    'post_title'    => $quiz_title,
                    'post_content'  => $quiz_xml,
                    'post_status'   => 'draft',
                    'post_type'     => 'quiz_snapshot',
                    // 'post_author'   => $args['user'],
                    'post_author'   => $quiz_author,
                    'post_parent'   => $args['quiz'],
                    'comment_status' => 'closed',
                    'ping_status'   => 'closed', 
                    'meta_input'    => array( 'owner' => $this->get_user_token( $args['user'] ) ),
                );

                // remove_filter( 'content_save_pre', 'wp_filter_post_kses' ); 
                // $snapshot_id = wp_insert_post( $snapshot_args );

                $process_snapshots = new mif_process_snapshots();
                $snapshot_id = $process_snapshots->insert( $snapshot_args );

                // Взять сохранненный результат

                if ( $snapshot_id ) {
                    
                    $snapshot = get_post( $snapshot_id );

                } else {

                    $error = 2;

                }

            }

        } else {

            // Результаты есть. Взять последний

            $snapshot = $snapshots[0];

            if ( count( $snapshots ) > 1 ) {

                // Почему-то таких результатов несколько. Удалить лишние.

                $process_snapshots = new mif_process_snapshots();

                foreach ( (array) $snapshots as $key => $value ) {

                    if ( $key === 0 ) continue;
                    $process_snapshots->trash( $value->ID );
    
                    // wp_trash_post( $snapshot->ID );

                }
            }
        }

        if ( $error === 0 ) {

            return $snapshot;
            
        } else {
            
            return $error;

        }
    }



    //
    // Возвращает текущий снимок
    //

    private function get_current_snapshot( $args = array() )
    {

        $defaults = array( 'user' => get_current_user_id(), 'quiz' => $this->quiz_id );
        $args = wp_parse_args( $args, $defaults );

        $process_snapshots = new mif_process_snapshots();
        $snapshots = $process_snapshots->get( $args );

        $snapshot = ( isset( $snapshots[0] ) ) ? $snapshots[0] : false;

        return $snapshot;
    }


}


?>