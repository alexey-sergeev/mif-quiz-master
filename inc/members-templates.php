<?php

//
// Функции шаблона пользователей
// 
//


defined( 'ABSPATH' ) || exit;



//
// Выводит панель настройки доступа к тесту
//

function mif_qm_the_access_mode_panel()
{
    global $mif_qm_members_screen;
    echo $mif_qm_members_screen->get_access_mode_panel();
}



//
// Выводит список имеющихся заявок
//

function mif_qm_the_members_requests()
{
    global $mif_qm_members_screen;
    echo $mif_qm_members_screen->get_members_requests();
}



//
// Выводит заголовок раздела
//

function mif_qm_the_members_part( $role = 'student' )
{
    global $mif_qm_members_screen;
    echo $mif_qm_members_screen->get_members_part( $role );
}

?>