<?php

//
// Функции шаблона приглашений
// 
//


defined( 'ABSPATH' ) || exit;



    //
    // Список инвайтов
    //

    function mif_qm_the_invites()
    {
        global $mif_qm_invites_screen;
        echo $mif_qm_invites_screen->get_invites();
    }



    //
    // Форма добавления инвайтов
    //

    function mif_qm_the_add_form()
    {
        global $mif_qm_invites_screen;
        echo $mif_qm_invites_screen->get_add_form();
    }


?>