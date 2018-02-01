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







?>