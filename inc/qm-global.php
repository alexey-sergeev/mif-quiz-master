<?php

//
// Методы глобального рейтинга
// 
//


defined( 'ABSPATH' ) || exit;



class mif_qm_global extends mif_qm_core_core { 

    
    function __construct()
    {

        add_action( 'mif_qm_process_result_update_global', array( $this, 'update_rating' ), 10, 3 );
        add_action( 'mif_qm_init_the_quiz_after', array( $this, 'update_param' ), 10, 2 );
        add_filter( 'mif_qm_members_core_get_external', array( $this, 'add_external_members' ), 10, 2 );

    }

    

    // 
    // Добавить внешних пользователей
    // 

    public function add_external_members( $arr, $quiz_id )
    {
        if ( ! function_exists( 'get_global_postmeta' ) ) return;        
        
        $targets = get_global_postmeta( get_current_blog_id(), $quiz_id, 'mif_group' );

        $qm_members = new mif_qm_members_core();
        $roles = $qm_members->get_roles();

        // Кто может добавлять внешних пользователей?

        $masters = array();
        foreach ( $arr as $user_token => $data ) if ( in_array( $data['role'], array( 'master', 'tutor' ) ) ) $masters[$user_token] = $data['role'];

        // Перебрать все внешние источники данных

        foreach ( $targets as $t ) {

            $k = explode( ':', $t );
            $k1 = absint( $k[0] );
            $k2 = absint( $k[1] );
            
            $creator_id = absint( $k[2] );
            $creator = $this->get_user_token( $creator_id );

            // Учитывать данные, если их добавил master или tutor теста
            
            // if ( ! in_array( $creator, $masters ) ) continue;
            if ( ! isset( $masters[$creator] ) ) continue;

            $key = 'mif_group_' . $k1 . '_' . $k2;

            $data = get_users_by_metakey( $key );

            foreach ( $data as $user_id => $user_data ) {

                $user_token = $this->get_user_token( $user_id );

                $p = explode( ':', $user_data );
                $role = $p[1];

                if ( isset( $roles[$role] ) ) {

                    // Внешний пользователь есть и его роль понятна. Добавлять данные в список.
                    
                    $creator_role = $masters[$creator];

                    $creator_level = $roles[$creator_role]['level'];
                    $level = $roles[$role]['level'];

                    $new_level = ( $creator_level > $level ) ? $level : $creator_level;
                    $new_role = ( $creator_level > $level ) ? $role : $creator_role;

                    if ( isset( $arr[$user_token] ) ) {

                        $old_role = $arr[$user_token]['role'];
                        $old_level = $roles[$old_role]['level'];

                        if ( $new_level > $old_level ) $arr[$user_token]['role'] = $new_role;

                        // if ( $roles[$role]['level'] > $roles[$old_role]['level'] ) $arr[$user_token]['role'] = $role;

                    } else {

                        $arr[$user_token] = array( 'role' => $new_role, 'origin' => 'external', 'creator' => $creator, 'target' => $t );

                    }

                }

            }

        }

        return $arr;
    }


    // 
    // Обновить параметры теста в глобальной таблице
    // 

    public function update_param( $quiz_id, $action )
    {
        if ( ! function_exists( 'update_global_postmeta' ) ) return;        
        if ( $action != 'run' ) return;
        if ( mif_qm_access_level() < 5 ) return;

        // Получить весь тест

        $quiz_core = new mif_qm_quiz_core();
        $quiz = $quiz_core->parse( $post->ID );

        // Выбрать нужные параметры
        
        $arr = array();
        $qm_param_core = new mif_qm_param_core();

        $arr['count'] = $qm_param_core->get_question_count( $quiz );
        $arr['rating_max'] = $this->get_max_rating( $quiz );
        
        $success = $qm_param_core->get_clean( 'success', $quiz, 'quiz', true );
        if ( $success['description'] ) $arr['rating_success'] = $success['description'];

        $arr['url'] = get_permalink( $quiz_id );
        $arr['name'] = get_the_title( $quiz_id );

        $post = get_post( $quiz_id );
        $arr['excerpt'] = $post->post_excerpt;        

        // Записать параметры в глобальную таблицу

        $ret = update_global_postmeta( 
            get_current_blog_id(),
            $quiz_id, 
            'mif_param', 
            $arr
        );
        
        return $ret;
    }




    // 
    // Обновить данные глобального рейтинга
    // 

    public function update_rating( $data, $quiz_id, $user_id )
    {
        // Определить текущий результат

        $result = array();

        foreach ( (array) $data as $item ) {

            if ( isset( $item['current'] ) && $item['current'] == 'yes' ) $result = $item;

        }

        if ( empty( $result ) ) return false;

        // Сформировать запись результата по правилам API глобального рейтинга

        $index = array( 'rating', 'max', 'percent', 'duration', 'master' );
        $arr = array();

        foreach ( $index as $item ) {

            if ( isset( $result[$item] ) ) $arr[$item] = $result[$item];

        }

        $arr['status'] = 'reject';
        if ( isset( $result['success'] ) && $result['success'] == 'yes' ) $arr['status'] = 'success';
        
        // Здесь думать, как учитывать другие состояния
        // 
        // status – зачтено, не зачтено или др. Может принимать значения:
        // – waiting – ожидает оценки
        // – accept – зачетно
        // – rework – отправлено на доработку
        // – reject – не зачетно (окончательно)
        // 

        $arr['timestamp'] = $this->get_timestamp( $result['time'] );
        $arr['url'] = get_permalink( $quiz_id );
        
        // Записать результат

        $key = 'mif_rating_' . get_current_blog_id() . '_' . $quiz_id;
        $ret = update_user_meta( $user_id, $key, $arr );
        
        return $ret;
    }








}

?>