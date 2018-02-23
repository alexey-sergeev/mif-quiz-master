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



?>