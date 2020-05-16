<?php
/*
Plugin Name: MIF Quiz Master
Plugin URI: https://github.com/alexey-sergeev/mif-quiz-master
Description: Плагин для составления тестов
Author: Алексей Н. Сергеев
Version: 1.4.3
Author URI: https://vk.com/alexey_sergeev
*/

defined( 'ABSPATH' ) || exit;

include_once dirname( __FILE__ ) . '/inc/qm-init.php';



add_action( 'init', 'qm_init' );

function qm_init()
{
    global $qm;
    $qm = new mif_qm_init();
}



add_action( 'wp', 'qm_download' );

function qm_download()
{
    new mif_qm_download();
}



add_action( 'wp_enqueue_scripts', 'mif_qm_customizer_styles' );

function mif_qm_customizer_styles() 
{
    // Font Awesome
    
    // wp_register_style( 'font-awesome', plugins_url( 'lib/fontawesome/css/font-awesome.min.css', __FILE__ ) );
	// wp_enqueue_style( 'font-awesome' );

    // wp_enqueue_script( 'fa-v4-shim', plugins_url( 'lib/fontawesome/js/fa-v4-shim.js', __FILE__ ) );
    wp_enqueue_script( 'font-awesome-js', plugins_url( 'lib/fontawesome/js/fontawesome-all.js', __FILE__ ), '', '1.1.0' );
    
    // Twitter bootstrap
    
    wp_register_style( 'bootstrap', plugins_url( 'lib/bootstrap/css/bootstrap.min.css', __FILE__ ) );
	wp_enqueue_style( 'bootstrap' );
    wp_enqueue_script( 'bootstrap', plugins_url( 'lib/bootstrap/js/bootstrap.min.js', __FILE__ ) );

    // Выноски bootstrap
    
    wp_register_style( 'callout', plugins_url( 'lib/callout.css', __FILE__ ) );
	wp_enqueue_style( 'callout' );
    
    // Локальные стили

    wp_register_style( 'qm-styles', plugins_url( 'mif-qm-styles.css', __FILE__ ), '', '1.4.0' );
    wp_enqueue_style( 'qm-styles' );

    // JS-методы
    wp_enqueue_script( 'mif_qm_js_helper', plugins_url( 'js/quiz-master.js', __FILE__ ), '', '1.4.0' );

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
    

if ( ! function_exists( 'hooks_list' ) ) {


    function hooks_list( $hook_name = '' ){
        global $wp_filter;
        $wp_hooks = $wp_filter;

        // для версии 4.4 - переделаем в массив
        if( is_object( reset($wp_hooks) ) ){
            foreach( $wp_hooks as & $object ) $object = $object->callbacks;
            unset($object);
        }

        if( $hook_name ){
            $hooks[ $hook_name ] = $wp_hooks[ $hook_name ];

            if( ! is_array($hooks[$hook_name]) ){
                trigger_error( "Nothing found for '$hook_name' hook", E_USER_WARNING );
                return;
            }
        }
        else {
            $hooks = $wp_hooks;
            ksort( $wp_hooks );
        }

        $out = '';
        foreach( $hooks as $name => $funcs_data ){
            ksort( $funcs_data );
            $out .= "\nхук\t<b>$name</b>\n";
            foreach( $funcs_data as $priority => $functions ){
                $out .= "$priority";
                foreach( array_keys($functions) as $func_name ) $out .= "\t$func_name\n";
            }
        }

        echo '<'.'pre>'. $out .'</pre'.'>';
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
