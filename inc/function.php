<?php

//
// Разные функции
// 
//


defined( 'ABSPATH' ) || exit;





//
// Проверка права доступа
//

function mif_qm_user_can( $token )
{
    global $post;

    switch ( $token ) {

        case 'edit-quiz':

            return current_user_can( 'edit_post', $post->ID );
                            
        break;
    }

}


?>
