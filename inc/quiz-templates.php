<?php

//
// Функции шаблонов теста
// 
//


defined( 'ABSPATH' ) || exit;




// //
// // Выводит меню теста
// //

// function mif_qm_the_quiz_menu()
// {
//     global $mif_qm_quiz_screen;
//     echo $mif_qm_quiz_screen->get_quiz_menu();
// }



//
// Выводит навигацию теста
//

function mif_qm_the_quiz_navigation()
{
    global $mif_qm_quiz_screen;
    echo $mif_qm_quiz_screen->get_quiz_navigation();
}



//
// Выводит кнопку продолжения теста
//

function mif_qm_the_next_button()
{
    global $mif_qm_quiz_screen;
    echo $mif_qm_quiz_screen->get_quiz_next_button();
}



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


// //
// // Выводит класс активного пункта меню
// //

// function mif_qm_the_menu_class( $action = 'view', $class = '', $flag = true )
// {
//     global $mif_qm_quiz_screen;
//     echo $mif_qm_quiz_screen->get_menu_class( $action, $class, $flag );
// }







?>