<?php

//
// Разные функции
// 
//


defined( 'ABSPATH' ) || exit;





//
// Проверка права доступа
//

function mif_qm_user_can( $token, $post_id = NULL )
{
    // Если не передан id записи, то проверяем для текущей

    if ( $post_id === NULL ) {
        
        global $post;
        $post_id = $post->ID;

    }
    
    // Проверяем

    switch ( $token ) {

        case 'edit-quiz':

            return current_user_can( 'edit_post', $post_id );
                            
        break;
    }

}


?>
