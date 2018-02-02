<?php

//
// Функции шаблонов теста
// 
//


defined( 'ABSPATH' ) || exit;




//
// Выводит заголовок теста
//

function mif_qm_the_quiz_header()
{
    global $mif_qm_quiz_screen;
    echo $mif_qm_quiz_screen->get_quiz_header();
}


//
// Выводит параметры теста
//

function mif_qm_the_quiz_param()
{
    global $mif_qm_quiz_screen;
    echo $mif_qm_quiz_screen->get_quiz_param();
}


//
// Выводит разделы
//

function mif_qm_the_parts()
{
    global $mif_qm_quiz_screen;
    $mif_qm_quiz_screen->the_parts();
}


//
// Выводит адрес страницы редактора записи
//

function mif_qm_the_edit_post_link()
{
    global $post;
    echo get_edit_post_link( $post->ID );
}


//
// Выводит класс активного пункта меню
//

function mif_qm_the_menu_class( $action = 'view', $class = '', $flag = true )
{
    $process_core = new mif_qm_process_core();
    
    if ( $process_core->get_action() === $action ) {

        $res1 = ' ' . $class;
        $res2 = '';
        
    } else {
        
        $res1 = '';
        $res2 = ' ' . $class;

    }

    $out = ( $flag ) ? $res1 : $res2;
    echo $out;

}







?>