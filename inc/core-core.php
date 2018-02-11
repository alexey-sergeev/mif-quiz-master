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
        $time = ( function_exists( 'current_time' ) ) ? current_time('mysql') : date( 'r' );
        return apply_filters( 'mif_qm_core_core_get_time', $time );
    }
    
    
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
            
            $ret = $user->user_login;
            
        } elseif ( is_user_logged_in() ) {
            
            $user = wp_get_current_user();
            $ret = $user->user_login;
            
        } else {
            
            $ret = false;
    
        }
        
        return apply_filters( 'mif_qm_core_core_get_user_token', $ret, $user_id );
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
            
        } elseif ( $mode == 'quiz') {
            
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
            
        } elseif ( $mode == 'quiz') {
            
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
        // !!! Здесь еще солить!

        $out = md5( $value );
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



    // //
    // // Права доступа
    // //

    // public function user_can( $token )
    // {
    //     global $post;

    //     switch ( $token ) {

    //         case 'edit-quiz':

    //             return current_user_can( 'edit_post', $post->ID );
                                
    //         break;
    //     }

    // }
}

?>