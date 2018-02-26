<?php

//
// Ядро классов для работы с тестами
// 
//


defined( 'ABSPATH' ) || exit;

include_once dirname( __FILE__ ) . '/function.php';
include_once dirname( __FILE__ ) . '/xml-core.php';



class mif_qm_core_core  {

    // Идентификатор теста.
    private $quiz_id = NULL;

    // // Идентификатор записи, где хранится снимок теста.
    // private $snapshot_id = NULL;

    // Маркер для теста
    public $mark_quiz = '===';
    
    // Маркер для раздела теста
    public $mark_part = '==';
    
    // Маркер для описания параметров
    public $mark_param = '@';

    // Маркер для вопроса
    public $mark_question = '=';
    
    // Маркеры для выбираемых ответов
    public $mark_choice = '-+*~';
    
    // Маркеры для полей ввода
    public $mark_input = '>%';
    
    
    function __construct()
    {

        // Шаблон для выделения теста
        $this->pattern_quiz = '/^' . $this->mark_quiz . '/';
        
        // Шаблон для выделения разделов теста
        $this->pattern_part = '/^' . $this->mark_part . '/';
        
        // Шаблонs для выделения инормации о параметрах
        $this->pattern_param = '/^' . $this->mark_param . '/';
        
        // Шаблон для выделения вопросов (с ответами)
        $this->pattern_question = '/^[' . $this->mark_question . ']/';
        
        // Шаблоны для выделения вариантов ответов
        $this->pattern_choice = '/^[' . $this->mark_choice . ']/';
        $this->pattern_input = '/^[' . $this->mark_input . ']+/';
        $this->pattern_answers = '/^[' . $this->mark_choice . $this->mark_input . ']/';

        // Шаблон мета-информации ответов
        $this->pattern_meta = '/\(.*\)/U';

    }

    
    //
    // Получить данные для подписи теста
    //

    public function get_signature( $quiz_id = false, $user_id = false )
    {
        $arr = array();

        // $arr['time'] = ( function_exists( 'current_time' ) ) ? current_time('mysql') : date( 'r' );
        $arr['time'] = $this->get_time();
        
        if ( $user = $this->get_user_token( $user_id ) ) $arr['user'] = $user;
        if ( $quiz = $this->get_quiz_id( $quiz_id ) ) $arr['quiz'] = $quiz;
        
        return apply_filters( 'mif_qm_core_core_get_signature', $arr, $quiz_id, $user_id );
    }
    
    
    //
    // Получить время в человекопонятном формате
    //
    
    public function get_time()
    {
        // $time = ( function_exists( 'current_time' ) ) ? current_time( 'mysql' ) : date( 'r' );
        $time = current_time( 'mysql' );
        return apply_filters( 'mif_qm_core_core_get_time', $time );
    }


    //
    // Получить метку времени по переданным данным
    //
    
    public function get_timestamp( $time )
    {
        if ( empty( $time ) ) return;
        $timestamp = mysql2date( 'U', $time );
        return apply_filters( 'mif_qm_core_core_get_timestamp', $timestamp );
    }
    

    // //
    // // Получить строку продолжительности времени
    // //
    
    // public function get_duration_str( $second )
    // {
    //     $duration = '';

    //     $t1 = floor( $second / 60 );
    //     $t2 = $second % 60;

    //     if ( $t1 > 0 ) $duration .= $t1 . ' ' . __( 'мин.', 'mif-bp' ) . ' ';
    //     $duration .= $t2 . ' ' . __( 'сек.', 'mif-bp' );

    //     return apply_filters( 'mif_qm_core_core_get_duration_str', $duration, $second );
    // }
    

    //
    // Получить строку времени для вывода на экран
    //
    
    public function get_time_str( $time )
    {
        return apply_filters( 'mif_qm_core_core_get_time_str', $time );
    }


    //
    // Получить идентификатор текущего теста
    //

    public function get_quiz_id( $quiz_id = false )
    {
        global $post;
        
        if ( $quiz_id ) {

            $ret = $quiz_id;
        
        } elseif ( isset( $post->ID ) ) {

            $ret = $post->ID;

        } else {

            $ret = false;

        }

        return apply_filters( 'mif_qm_core_core_get_quiz_id', $ret, $quiz_id );
    }


    
    //
    // Получить идентификатор текущего пользователя
    //

    public function get_user_token( $user_id = false )
    {
        
        if ( $user_id && $user = get_user_by( 'ID', $user_id ) ) {
            
            // $ret = $user->user_login;
            $ret = $user->user_nicename;
            
        } elseif ( is_user_logged_in() ) {
            
            $user = wp_get_current_user();
            // $ret = $user->user_login;
            $ret = $user->user_nicename;
            
        } else {
            
            $ret = false;
    
        }
        
        return apply_filters( 'mif_qm_core_core_get_user_token', $ret, $user_id );
    }


    
    //
    // Получить ID пользователя по его токену
    //

    public function get_user_id( $user_token = '' )
    {
        if ( $user = get_user_by( 'slug', $user_token ) ) {

            return apply_filters( 'mif_qm_core_core_get_user_id', $user->ID, $user_token);

        } else {

            return false;

        }

    }

    
    //
    // Получить имя пользователя для вывода на экран
    //

    public function get_display_name( $user_token = '' )
    {
        $display_name = '';
        if ( $user = get_user_by( 'slug', $user_token ) ) $display_name = $user->display_name;

        $out = ( $display_name ) ? $display_name : $user_token;

        return apply_filters( 'mif_qm_core_core_get_display_name', $out, $user_token );
    }


    
    //
    // Получить количество вопросов
    // 

    public function get_question_count( $item = array(), $mode = 'quiz' )
    {
        $count = 0;

        if ( $mode =='part' ) {

            $number = $this->get_clean( 'number', $item, $mode );
            $count = count( (array) $item['questions'] );

            if ( $number > 0 && $number < $count ) $count = $number;
            
        } elseif ( $mode == 'quiz' && isset( $item['parts'] ) ) {
            
            foreach ( (array) $item['parts'] as $part ) {
                
                $p_number = $this->get_clean( 'number', $part, $mode );
                $p_count = count( (array) $part['questions'] );
    
                if ( $p_number > 0 && $p_number < $p_count ) $p_count = $p_number;

                $count += $p_count;

            }
        }

        return $count;
    }


    
    //
    // Получить максимально возможное значение рейтинга
    // 

    public function get_max_rating( $item = array(), $mode = 'quiz' )
    {
        $max_rating = 0;

        if ( $mode =='part' ) {

            $rating = $this->get_clean( 'rating', $item, $mode );
            $count = $this->get_question_count( $item, 'part' );
            $max_rating = $rating * $count;
            // $max_rating = $rating * count( (array) $item['questions'] );
            
        } elseif ( $mode == 'quiz' && isset( $item['parts'] ) ) {
            
            foreach ( (array) $item['parts'] as $part ) {
                
                $rating = $this->get_clean( 'rating', $part, 'part' );
                $count = $this->get_question_count( $part, 'part' );
                $max_rating += $rating * $count;
                // $max_rating += $rating * count( (array) $part['questions'] );

            }

        }

        return $max_rating;
    }

    


    //
    // Получить чистые данные
    //  $flag - вернуть с пояснениями (в массиве)

    public function get_clean( $key = '', $item = '', $mode = 'quiz', $flag = false )
    {
        if ( ! isset( $item['param'][$key] ) ) return;

        $interpretation = new mif_qm_param_interpretation( $key, $item['param'][$key], $mode );
        $arr = $interpretation->get_clean_value();

        if ( $flag ) {

            return $arr;
            
        } else {
            
            return $arr['value'];

        }

    }


    //
    // Получить пользовательское hash-значение для текстовой строки
    //  

    public function get_hash( $value )
    {
        $nonce = wp_create_nonce( 'mif-qm-salt' );
        $out = md5( $value . $nonce );
        return $out;
    }

    

    //
    // Узнать, имеется ли данный параметр?
    //

    public function is_param( $param, $quiz = array() )
    {
        $ret = false;
        if ( in_array( $param, $quiz['param']['settings'] ) ) $ret = true;

        return $ret;
    }    

    

    //
    // Узнать, имеется ли результат по попросу, разделу или тесту?
    //
    
    public function is_submitted( $item = array() )
    {
        $ret = false;
        if ( isset( $item['processed']['submitted'] ) && $item['processed']['submitted'] ) $ret = true;
        
        return $ret;
    }    
    

    
    //
    // Учточнить режим оценивания теста
    //
    
    public function get_inspection_mode( $quiz = array(), $inspection_mode = 'balanced' )
    {
        
        if ( ! in_array( $inspection_mode, array( 'strict', 'balanced', 'detailed' ) ) ) {
            
            $inspection_mode = 'balanced';
            if ( $this->is_param( 'strict', $quiz ) ) $inspection_mode = 'strict'; 
            if ( $this->is_param( 'detailed', $quiz ) ) $inspection_mode = 'detailed'; 
            
        }
        
        return $inspection_mode; 
    }
    
    

    
    //
    // Возвращает уровень доступа пользователя
    //      0 - нет доступа
    //      1 - прохождение теста (ученик)
    //      2 - просмотр результатов (эксперт)
    //      3 - проверка ответов (ассистент)
    //      4 - редактирование ответов (тьютор)
    //      5 - редактирование теста (мастер)
    //

    public function access_level( $quiz_id = NULL, $user_id = NULL )
    {
        if ( $quiz_id == NULL ) {
            
            global $post;
            $quiz_id = $post->ID;
            
        }

        if ( $user_id == NULL ) $user_id = get_current_user_id();

        $quiz = get_post( $quiz_id );

        // Автор теста всегда является мастером
        
        if ( $user_id == $quiz->post_author ) return 5;

        return 1;
    }


    //
    // Права доступа
    //

    public function user_can( $token, $post_id = NULL )
    {
        // Если не передан id записи, то проверяем для текущей
    
        if ( $post_id === NULL ) {
            
            global $post;
            $post_id = $post->ID;
    
        }
        
        // Проверяем
    
        switch ( $token ) {
    
            case 'edit-quiz':
    
                return current_user_can( 'edit_post', $post_id );
                                
            break;
    
            case 'view-quiz':
    
                return current_user_can( 'edit_post', $post_id );
                                
            break;
    
            case 'view-result':

                // Для этого параметра надо указывать ID результата (не теста)

                // Узнать id теста

                $result = get_post( $post_id );
                $quiz_id = $result->post_parent;

                // Если пользователь эксперт или более

                if ( $this->access_level( $quiz_id ) > 1 ) return true;
                
                // Узнать владельца результата
                
                $owner = get_post_meta( $post_id, 'owner', true );

                // Узнать текущего пользователя

                $current_user_token = $this->get_user_token();
                
                // Текущий тест

                $quiz_core = new mif_qm_quiz_core();
                $quiz = $quiz_core->parse( $quiz_id );
                
                // Если тест предполагает просмотр своих результатов и пользователь запрашивает как раз его, то - да

                if ( $this->is_param( 'resume', $quiz ) && $owner == $current_user_token ) return true;
                                
            break;
        }

        return false;    
    }


    


    // 
    // Обновляет связанные записи (снимки теста, результаты или др.)
    // 

    public function companion_update( $args = array() )
    {
        // if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'mif-qm') ) return false;
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
        // if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'mif-qm') ) return false;

        if ( empty( $args['post_type'] ) ) return false;
        if ( empty( $args['post_content'] ) ) return false;

        global $post;
        
        if ( empty( $args['quiz'] ) ) $args['quiz'] = $post->ID;
        if ( empty( $args['post_status'] ) ) $args['post_status'] = 'publish';

        // В параметрах можно указать 'user' => false, тогда пользователь упоминаться не будет

        if ( ! isset( $args['user'] ) ) $args['user'] = get_current_user_id();
        
        // Узнать имя и автора записи для будущей связанной записи
        
        $quiz_post = get_post( $args['quiz'] );
        $prefix = ( ! empty( $args['user'] ) ) ? $this->get_user_token( $args['user'] ) . ' — ' : '';
        $title = ( isset( $args['post_title'] ) ) ? $args['post_title'] : $prefix . $quiz_post->post_title . ' ('. $quiz_post->ID . ')';
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
            // 'meta_input'    => array( 'owner' => $this->get_user_token( $args['user'] ) ),
        );

        if ( ! empty ( $args['user'] ) ) $companion_args['meta_input'] = array( 'owner' => $this->get_user_token( $args['user'] ) );
        if ( ! empty ( $args['post_name'] ) ) $companion_args['post_name'] = $args['post_name'];

        remove_filter( 'content_save_pre', 'wp_filter_post_kses' ); 
        $companion_id = wp_insert_post( $companion_args );
        
        return $companion_id;
    }



    //
    // Вернуть автора теста
    //

    public function get_quiz_author( $quiz_id )
    {
        $quiz_id = $this->get_quiz_id( $quiz_id );
        $quiz = get_post( $quiz_id );
        $quiz_author = $this->get_user_token( $quiz->post_author );

        return $quiz_author;
    }


}

?>