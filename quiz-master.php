<?php
/*
Plugin Name: MIF Quiz Master
Plugin URI: http://mif.vspu.ru
Description: Плагин для составления тестов
Author: Алексей Н. Сергеев
Version: 1.0
Author URI: https://vk.com/alexey_sergeev
*/

defined( 'ABSPATH' ) || exit;

include_once dirname( __FILE__ ) . '/inc/class-quiz.php';

// global $mif_qm_core;
// $mif_qm_core = new mif_qm_core();




// Функция получает текст записи, который можно изменить перед выводом на экран

add_filter( 'the_content', 'add_custom_content' );

function add_custom_content( $content ) 
{
    // global $mif_qm_core;
    global $post;
   
    // p($post);

    // $content = $mif_qm_core->parse( $post->post_content ) . $content;

    // $quiz_part = new mif_qm_quiz_part();
    // $quiz_part->parse( $post->post_content );

    $quiz = new mif_qm_quiz();
    $quiz->parse( $post->post_content );

    return $content;
}



if ( ! function_exists( 'p' ) ) {

    function p( $data )
    {
        print_r( '<pre>' );
        print_r( $data );
        print_r( '</pre>' );
    }

}


if ( ! function_exists( 'f' ) ) {
    
        function f( $data )
        {
            file_put_contents( '/tmp/qmlog.txt', date( "D M j G:i:s T Y - " ), FILE_APPEND | LOCK_EX );
            file_put_contents( '/tmp/qmlog.txt', print_r( $data, true ), FILE_APPEND | LOCK_EX );
            file_put_contents( '/tmp/qmlog.txt', "\n", FILE_APPEND | LOCK_EX );
        }
    
}
    
    

function strim( $st = '' )
{
    // Удаляет двойные пробелы, а также пробелы в начале и в конце строки

    $st = preg_replace( '/\s+/', ' ', $st );
    $st = trim( $st );
    
    return $st;
}


?>
