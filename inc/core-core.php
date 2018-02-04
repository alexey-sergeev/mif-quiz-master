<?php

//
// Ядро классов для работы с тестами
// 
//


defined( 'ABSPATH' ) || exit;

include_once dirname( __FILE__ ) . '/function.php';
include_once dirname( __FILE__ ) . '/xml-core.php';



class mif_qm_core_core  {

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

    public function get_signature()
    {
        $arr = array();

        $arr['time'] = ( function_exists( 'current_time' ) ) ? current_time('mysql') : date( 'r' );

        if ( $user = $this->get_user_token() ) $arr['user'] = $user;
        if ( $quiz = $this->get_quiz_token() ) $arr['quiz'] = $quiz;

        return apply_filters( 'mif_qm_core_core_get_signature', $arr );
    }


    
    //
    // Получить идентификатор текущего теста
    //

    public function get_quiz_token( $quiz_id = false )
    {
        global $post;
        
        if ( $quiz_id ) {

            $ret = $quiz_id;
        
        } elseif ( isset( $post->ID ) ) {

            $ret = $post->ID;

        } else {

            $ret = false;

        }

        return apply_filters( 'mif_qm_core_core_get_quiz_token', $ret, $quiz_id );
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
    // Получить чистые данные
    //  $flag - вернуть с пояснениями (в массиве)

    public function get_clean( $key = '', $value = '', $mode = 'quiz', $flag = false )
    {
        $interpretation = new mif_qm_param_interpretation( $key, $value, $mode );
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