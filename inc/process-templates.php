<?php

//
// Функции шаблонов процесса
// 
//


defined( 'ABSPATH' ) || exit;



//
// Панель с пояснениями на странице начала теста
//

function mif_qm_the_start_panel()
{
    global $mif_qm_process_screen;
    echo $mif_qm_process_screen->get_start_panel();
}



//
// Ссылка на результаты теста
//

function mif_qm_the_result_link()
{
    global $mif_qm_process_screen;
    echo $mif_qm_process_screen->get_result_link();
}



//
// КНопка начала теста
//

function mif_qm_the_start_button()
{
    global $mif_qm_process_screen;
    echo $mif_qm_process_screen->get_start_button();
}



//
// КНопка на странице результатов
//

function mif_qm_the_result_button()
{
    global $mif_qm_process_screen;
    echo $mif_qm_process_screen->get_result_button();
}



//
// Выводит текст успеха
//

function mif_qm_the_successed_caption()
{
    global $mif_qm_process_screen;
    echo $mif_qm_process_screen->get_successed_caption();
}



//
// Выводит класс успеха
//

function mif_qm_the_successed_class()
{
    global $mif_qm_process_screen;
    echo $mif_qm_process_screen->get_successed_class();
}



//
// Выводит конкретное значение элемента результата
//

function mif_qm_the_result( $key = false )
{
    global $mif_qm_process_screen;
    echo $mif_qm_process_screen->get_result( $key );
}



//
// Выводит меню теста
//

function mif_qm_the_quiz_menu()
{
    global $mif_qm_process_screen;
    echo $mif_qm_process_screen->get_quiz_menu();
}



//
// Выводит класс активного пункта меню
//

function mif_qm_the_menu_class( $action = 'view', $class = '', $flag = true )
{
    global $mif_qm_process_screen;
    echo $mif_qm_process_screen->get_menu_class( $action, $class, $flag );
}


?>