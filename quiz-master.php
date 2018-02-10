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

include_once dirname( __FILE__ ) . '/inc/qm-init.php';
// include_once dirname( __FILE__ ) . '/inc/quiz-core.php';
// include_once dirname( __FILE__ ) . '/inc/quiz-screen.php';
// include_once dirname( __FILE__ ) . '/inc/class-xml-explode.php';
// include_once dirname( __FILE__ ) . '/inc/class-xml-implode.php';
// include_once dirname( __FILE__ ) . '/inc/quiz-templates.php';


add_action( 'init', 'qm_init' );

function qm_init()
{
    $qm = new mif_qm_init();
}


// Функция получает текст записи, который можно изменить перед выводом на экран

// add_filter( 'the_content', 'add_custom_content' );

// function add_custom_content( $content ) 
// {
//     global $post;
//     global $mif_qm_quiz_screen;
   
//     $quiz_core = new mif_qm_quiz_core();
//     // $xml = new mif_qm_xml_implode();
//     // $arr = new mif_qm_xml_explode();
    
//     $quiz = $quiz_core->parse( $post->post_content );
    
//     // p($quiz);
//     // $quiz_xml = $xml->parse( $quiz );
//     // p( esc_html( $quiz_xml ) );
//     // $quiz_array = $arr->parse( $quiz_xml );
//     // p( $quiz_array );

//     $mif_qm_quiz_screen = new mif_qm_quiz_screen( $quiz );
//     $mif_qm_quiz_screen->show( array( 'mode' => 'run' ) );


//     // p( $quiz_array_2 );

//     return $content;
// }



add_action( 'wp_enqueue_scripts', 'mif_qm_customizer_styles' );

function mif_qm_customizer_styles() 
{
    // Font Awesome
    
    wp_register_style( 'font-awesome', plugins_url( 'lib/fontawesome/css/font-awesome.min.css', __FILE__ ) );
	wp_enqueue_style( 'font-awesome' );

    // wp_enqueue_script( 'fa-v4-shim', plugins_url( 'lib/fontawesome/js/fa-v4-shim.js', __FILE__ ) );
    // wp_enqueue_script( 'font-awesome-js', plugins_url( 'lib/fontawesome/js/fontawesome-all.js', __FILE__ ) );

    // Twitter bootstrap
    
    wp_register_style( 'bootstrap', plugins_url( 'lib/bootstrap/css/bootstrap.min.css', __FILE__ ) );
	wp_enqueue_style( 'bootstrap' );

    // Выноски bootstrap
    
    wp_register_style( 'callout', plugins_url( 'lib/callout.css', __FILE__ ) );
	wp_enqueue_style( 'callout' );
    
    // Локальные стили

    wp_register_style( 'qm-styles', plugins_url( 'mif-qm-styles.css', __FILE__ ) );
    wp_enqueue_style( 'qm-styles' );

    // JS-методы
    wp_enqueue_script( 'mif_qm_js_helper', plugins_url( 'js/quiz-master.js', __FILE__ ) );

    // Плагин сортировки
    wp_enqueue_script( 'mif_qm_sortable', plugins_url( 'js/qm-sortable.js', __FILE__ ) );

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
