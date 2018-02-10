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



function hooks_list( $hook_name = '' ){
	global $wp_filter;
	$wp_hooks = $wp_filter;

	// для версии 4.4 - переделаем в массив
	if( is_object( reset($wp_hooks) ) ){
		foreach( $wp_hooks as & $object ) $object = $object->callbacks;
		unset($object);
	}

	if( $hook_name ){
		$hooks[ $hook_name ] = $wp_hooks[ $hook_name ];

		if( ! is_array($hooks[$hook_name]) ){
			trigger_error( "Nothing found for '$hook_name' hook", E_USER_WARNING );
			return;
		}
	}
	else {
		$hooks = $wp_hooks;
		ksort( $wp_hooks );
	}

	$out = '';
	foreach( $hooks as $name => $funcs_data ){
		ksort( $funcs_data );
		$out .= "\nхук\t<b>$name</b>\n";
		foreach( $funcs_data as $priority => $functions ){
			$out .= "$priority";
			foreach( array_keys($functions) as $func_name ) $out .= "\t$func_name\n";
		}
	}

	echo '<'.'pre>'. $out .'</pre'.'>';
}

?>
