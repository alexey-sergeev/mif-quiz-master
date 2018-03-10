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
    global $mif_qm_process_screen;
    return $mif_qm_process_screen->user_can( $token, $post_id );
}



//
// Возвращает уровень доступа пользователя
//      0 - нет доступа
//      1 - прохождение теста (ученик)
//      2 - просмотр результатов (эксперт)
//      3 - проверка ответов (ассистент)
//      4 - редактирование ответов (тьютор)
//      5 - редактирование теста (мастер)
//

function mif_qm_access_level( $quiz_id = false, $user_id = false )
{
    $members_core = new mif_qm_members_core();
    return $members_core->access_level( $quiz_id, $user_id );
}




// function hooks_list( $hook_name = '' ){
// 	global $wp_filter;
// 	$wp_hooks = $wp_filter;

// 	// для версии 4.4 - переделаем в массив
// 	if( is_object( reset($wp_hooks) ) ){
// 		foreach( $wp_hooks as & $object ) $object = $object->callbacks;
// 		unset($object);
// 	}

// 	if( $hook_name ){
// 		$hooks[ $hook_name ] = $wp_hooks[ $hook_name ];

// 		if( ! is_array($hooks[$hook_name]) ){
// 			trigger_error( "Nothing found for '$hook_name' hook", E_USER_WARNING );
// 			return;
// 		}
// 	}
// 	else {
// 		$hooks = $wp_hooks;
// 		ksort( $wp_hooks );
// 	}

// 	$out = '';
// 	foreach( $hooks as $name => $funcs_data ){
// 		ksort( $funcs_data );
// 		$out .= "\nхук\t<b>$name</b>\n";
// 		foreach( $funcs_data as $priority => $functions ){
// 			$out .= "$priority";
// 			foreach( array_keys($functions) as $func_name ) $out .= "\t$func_name\n";
// 		}
// 	}

// 	echo '<'.'pre>'. $out .'</pre'.'>';
// }



//
// Отключить визуальный редактор
//

add_filter( 'user_can_richedit', 'disable_richedit' );

function disable_richedit( $wp_rich_edit )
{
    global $post;
    if ( preg_match( '/^quiz/', $post->post_type ) ) return false;

	return $wp_rich_edit;
}

?>
