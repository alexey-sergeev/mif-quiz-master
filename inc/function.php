<?php

//
// Разные функции
// 
//


defined( 'ABSPATH' ) || exit;



//
// Отправка уведомлений (инвайты)
//

add_action( 'mif_qm_invate_emailer', 'mif_qm_emailer_invite', 10, 2 );

function mif_qm_emailer_invite( $invite, $quiz_id )
{
    // Пока так

    global $qm;
    global $qm_messages;

    $email = '';
    
    if ( isset( $invite['email'] ) ) {
        
        $email = $invite['email'];
        
    } elseif ( isset( $invite['username'] ) ) {
        
        $user_id = $qm->get_user_id( $invite['username'] );
        
        if ( $user_id ) {

            $user = get_userdata( $user_id );
            $email = $user->user_email;

        }

    }

    if ( ! $email ) {
     
        $qm_messages['emailer']['warning']['message'] = '<strong>Не удалось отправить:</strong>';
        $qm_messages['emailer']['warning']['list'][] = $invite['fullname'];

        return;

    }

    $to = $email;
    // $subject = '[Quiz Master] Новый тест';

    $quiz = get_post( $quiz_id );
    $subject = '[Новый тест] ' . $quiz->post_title;

    $message = "";

    $message .= $invite['fullname'] . "\n";
    $message .= "\n";
    $message .= "Вам отправлено приглашение для прохождения теста\n";
    $message .= "\n";
    $message .= "Код приглашения: " . $invite['invite_code'] . "\n";
    $message .= "Ссылка: " . home_url() . '/?invite_code=' . $invite['invite_code'] . "\n";
    $message .= "\n";
    $message .= "Кем отправлено: " . $qm->get_display_name( $invite['invite_creator'] ) . "\n";
    $message .= "Дата: " . $invite['invite_time'] . "\n";
    $message .= "\n";
    $message .= "---\n";
    $message .= "Всегда ваш,\n";
    $message .= "робот образовательного портала\n";

    $headers = 'From: Quiz Master <qm@edu.vspu.ru>';

    wp_mail( $to, $subject, $message, $headers );

    $qm_messages['emailer']['success']['message'] = '<strong>Отправлены приглашения:</strong>';
    $qm_messages['emailer']['success']['list'][] = $invite['fullname'];

}





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


//
// Ообработку текста в админке
//

add_action( 'admin_init', 'disable_post_kses' );

function disable_post_kses()
{
    remove_filter( 'content_save_pre', 'wp_filter_post_kses' );
}

?>
