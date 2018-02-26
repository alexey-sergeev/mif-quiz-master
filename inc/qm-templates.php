<?php

//
// Функции шаблонов теста
// 
//


defined( 'ABSPATH' ) || exit;




//
// Выводит категории для поиска
//

function mif_qm_the_category()
{
    global $mif_qm_screen;
    echo $mif_qm_screen->get_category();
}



//
// Выводит навигацию теста
//

function mif_qm_the_catalog()
{
    global $mif_qm_screen;
    echo $mif_qm_screen->get_catalog();
}



//
// Выводит форму ввода инвайта
//

function mif_qm_the_invite_form()
{
    global $mif_qm_screen;
    echo $mif_qm_screen->get_invite_form();
}



//
// Выводит блок "Ваши тесты"
//

function mif_qm_the_you_quizess()
{
    global $mif_qm_screen;
    echo $mif_qm_screen->get_you_quizess();
}



//
// Выводит ссылку на страницу сайта
//

function mif_qm_the_url( $page = '' )
{
    global $mif_qm_screen;
    echo $mif_qm_screen->get_url( $page );
}



?>