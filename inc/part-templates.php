<?php

//
// Функции шаблонов раздела
// 
//


defined( 'ABSPATH' ) || exit;



//
// Выводит заголовок раздела
//

function mif_qm_the_part_header()
{
    global $mif_qm_part_screen;
    echo $mif_qm_part_screen->get_part_header();
}


//
// Выводит параметры раздела
//

function mif_qm_the_part_param()
{
    global $mif_qm_part_screen;
    echo $mif_qm_part_screen->get_part_param();
}


//
// Выводит вопросы
//

function mif_qm_the_questions()
{
    global $mif_qm_part_screen;
    $mif_qm_part_screen->the_questions();
}







?>