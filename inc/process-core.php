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
        $this->quiz_id = $this->get_quiz_id( $quiz_id );
    }



    // 
    // Вернуть идентификатор текущего действия
    // 
    
    public function get_action( $quiz_id = false )
    {

        if ( ! $action = wp_cache_get( 'mif_qm_action', $quiz_id ) ) {

            $action = ( isset( $_REQUEST['action'] ) ) ? sanitize_key( $_REQUEST['action'] ) : 'run';
            wp_cache_set( 'mif_qm_action', $action, $quiz_id );

        }

        return $action;
    }



    // 
    // Есть ли результаты для данного пользователя?
    //

    public function is_result( $quiz_id = false, $user_id = false )
    {
        global $post;
        if ( ! $quiz_id ) $quiz_id = $post->ID;
        if ( ! $user_id ) $user_id = get_current_user_id();

        $args = array(
            'post_status'   => 'publish',
            'post_type'     => 'quiz_result',
            'meta_key'      => 'owner',
            'meta_value'    => $this->get_user_token( $user_id ),
            'post_parent'   => $quiz_id,
        );
        
        $result = get_posts( $args );

        return ( $result ) ? true : false;
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
        
        // Если пользователь эксперт и выше, то не ограничено

        $members_core = new mif_qm_members_core();
        $status = $members_core->member_status( $quiz_id, $user_id );
        $level = $members_core->member_level( $status );

        if ( $level > 1 ) return -1;

        // if ( mif_qm_user_can( 'edit-quiz', $quiz_id ) ) return -1;

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


    //
    // Получить строку продолжительности времени
    //
    
    public function get_duration_str( $second )
    {
        $duration = '';

        $t1 = floor( $second / 60 );
        $t2 = $second % 60;

        if ( $t1 > 0 ) $duration .= $t1 . ' ' . __( 'мин.', 'mif-bp' ) . ' ';
        $duration .= $t2 . ' ' . __( 'сек.', 'mif-bp' );

        return apply_filters( 'mif_qm_core_core_get_duration_str', $duration, $second );
    }        
}


?>