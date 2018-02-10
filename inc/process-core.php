<?php

//
// Ядро процесса обработки
// 
//


defined( 'ABSPATH' ) || exit;



class mif_qm_process_core extends mif_qm_core_core { 

    function __construct( $quiz_id = NULL )
    {
        parent::__construct();
    }



    // 
    // Вернуть идентификатор текущего действия
    // 
    
    public function get_action( $quiz_id = false )
    {
        global $post;

        if ( $quiz_id === false ) $quiz_id = $post->ID;

        $action = ( mif_qm_user_can( 'edit-quiz', $quiz_id ) ) ? 'view' : 'run';

        if ( isset( $_REQUEST['action'] ) ) $action = sanitize_key( $_REQUEST['action'] );

        // $action = isset( $_REQUEST['action'] ) ? sanitize_key( $_REQUEST['action'] ) : '';

        return $action;
    }
    


    // 
    // Обновляет связанные записи (снимки теста, результаты или др.)
    // 

    public function companion_update( $args = array() )
    {
        if ( empty( $args['ID'] ) ) return false;

        remove_filter( 'content_save_pre', 'wp_filter_post_kses' ); 
        $res = wp_update_post( $args );

        return $res;
    }

    

    // 
    // Добавляет связанные записи (снимки теста, результаты или др.)
    // 

    public function companion_insert( $args = array() )
    {
        if ( empty( $args['post_type'] ) ) return false;
        if ( empty( $args['post_content'] ) ) return false;

        global $post;
        
        if ( empty( $args['quiz'] ) ) $args['quiz'] = $post->ID;
        if ( empty( $args['user'] ) ) $args['user'] = get_current_user_id();
        if ( empty( $args['post_status'] ) ) $args['user'] = 'publish';

        // Узнать имя и автора записи для будущей связанной записи
        
        $quiz_post = get_post( $args['quiz'] );
        $title = $this->get_user_token( $args['user'] ) . ' — ' . $quiz_post->post_title . ' ('. $quiz_post->ID . ')';
        $author = $quiz_post->post_author;
        
        // Сохраняем в виде новой связанной записи
        
        $companion_args = array(
            'post_title'    => $title,
            'post_content'  => $args['post_content'],
            'post_type'     => $args['post_type'],
            'post_status'   => $args['post_status'],
            'post_author'   => $author,
            'post_parent'   => $args['quiz'],
            'comment_status' => 'closed',
            'ping_status'   => 'closed', 
            'meta_input'    => array( 'owner' => $this->get_user_token( $args['user'] ) ),
        );

        remove_filter( 'content_save_pre', 'wp_filter_post_kses' ); 
        $companion_id = wp_insert_post( $companion_args );
        
        return $companion_id;
    }



    // 
    // Возвращает количество оставшихся попыток для теста
    //      -1 - не ограничено
    //

    public function get_attempt_count( $quiz_id = false, $user_id = false )
    {
        global $post;
        if ( ! $quiz_id ) $quiz_id = $post->ID;
        if ( ! $user_id ) $user_id = get_current_user_id();
        
        // Если пользователь может редактировать тест (автор или редактор сайта), то не ограничено
        
        if ( mif_qm_user_can( 'edit-quiz', $quiz_id ) ) return -1;

        $quiz_core = new mif_qm_quiz_core( $quiz_id );
        $quiz = $quiz_core->parse();

        // Если в тесте нет ограничения, то и не ограничено

        if ( ! isset( $quiz['param']['attempt'] ) ) return -1;
        
        $attempt = $this->get_clean( 'attempt', $quiz );

        if ( $attempt === 0 )  return -1;

        // Есть ограничение по количеству попыток. Проверить, не превышено ли оно

        $snapshot_args = array(
            'post_status'   => 'publish',
            'post_type'     => 'quiz_snapshot',
            'meta_key'      => 'owner',
            'meta_value'    => $this->get_user_token( $user_id ),
            'post_parent'   => $quiz_id,
        );
        
        $snapshot_publish = get_posts( $snapshot_args );
        $count = count( $snapshot_publish );

        // Если достигнуто или превышено, то вернуть 0

        if ( $count >= $attempt ) return 0;
        
        // Вернуть количество

        return $attempt - $count;
    }
        
}


?>