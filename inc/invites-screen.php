<?php

//
// Экранные методы для работы с приглашениями
// 
//


defined( 'ABSPATH' ) || exit;

include_once dirname( __FILE__ ) . '/invites-core.php';
include_once dirname( __FILE__ ) . '/invites-templates.php';


class mif_qm_invites_screen extends mif_qm_invites_core { 

    // private $quiz_id;

    // Массив приглашений

    private $invites = array();
    
    function __construct( $quiz_id )
    {
        parent::__construct();
        $this->quiz_id = $quiz_id;
    }



    //
    // Вывести окно списков приглашений
    //

    public function the_invites()
    {
        // Смотреть приглашения может только эксперт и выше

        $members_core = new mif_qm_members_core();

        if ( ! ( $members_core->access_level( $this->quiz_id ) > 1 ) ) return;

        // Получить список пользователей

        // $this->invites = $this->get( $this->quiz_id );

        // Подключить шаблон из темы оформления или локальный

        if ( $template = locate_template( 'invites.php' ) ) {
           
            load_template( $template, false );

        } else {

            load_template( dirname( __FILE__ ) . '/../templates/invites.php', false );

        }    

    }


    //
    // Форма добавления инвайтов
    //

    public function get_add_form()
    {
        if ( ! ( mif_qm_access_level() > 2 ) ) return;
        
        $out = '';

        $out .= '<div class="p-2 pl-4 pr-4 mt-5 mb-4 bg-light no-gutters">';
        
        $out .= '<div class="mt-3 text-center"><h4>' . __( 'Новые приглашения', 'mif-qm' ) . '</h4></div>';

        $out .= '<form method="POST">';
        $out .= '<div>';
        $out .= '<p class="text-center">' . __( 'Укажите список обучающихся для подготовки приглашений', 'mif-qm' ) . '</p>';

        $out .= '<input type="text" name="new-group" placeholder="' . __( 'Группа', 'mif-qm' ) . '">';
        $out .= '<textarea name="members-list" style="height: 15rem;" class="mt-3"></textarea>';
        $out .= '<label><input type="checkbox" name="emailer_now" class="mr-2" value="on">' . __( 'Сразу оповестить по почте', 'mif-qm' ) . '</label>';

        $out .= '<div class="text-center m-4">';
        $out .= '<button class="btn">' . __( 'Создать приглашения', 'mif-qm' ) . '</button>';
        $out .= '<span class="loading p-2" style="margin-right: -32px"><i class="fas fa-spinner fa-pulse"></i></span>';
        $out .= '</div>';
        
        $out .= '<br />';
        $out .= '</div>';
        
        
        $out .= '<input type="hidden" name="action" value="invites" />';
        $out .= '<input type="hidden" name="_wpnonce" value="' . wp_create_nonce( 'mif-qm' ) . '" />';
        $out .= '<input type="hidden" name="quiz_id" value="' . $this->quiz_id . '" />';
        
        $out .= '</form>';

        $out .= '</div>';

        return apply_filters( 'mif_qm_invites_screen_get_add_form', $out );
    }


    //
    // Список приглашений
    //

    public function get_invites()
    {
        $out = '';
        
        $invites = $this->get_list( $this->quiz_id );
        
        $out .= '<div class="mt-5 mb-5 text-center"><h3>' . __( 'Приглашения', 'mif-qm' ) . '</h3></div>';
        
        $out .= '<div class="mt-5">';
        $out .= '<form>';

        // Кнопки выбора группы (если они есть)

        $qm_members_screen = new mif_qm_members_screen( $this->quiz_id );
        $out .= $qm_members_screen->get_groups_panel( $invites );

        // Выбрать только то, что попросили показать

        $invites_filtered = array();
        $group_rq = ( isset( $_REQUEST['group'] ) ) ? sanitize_key( $_REQUEST['group'] ) : '';

        foreach ( (array) $invites as $invite ) {
                
            if ( $group_rq ) {

                $group_id = ( isset( $invite['group'] ) ) ? md5( $invite['group'] ) : md5( '' );
                if ( $group_rq != $group_id ) continue;

            }

            $invites_filtered[] = $invite;

        }

        // Кнопки скачивания

        $out .= '<div class="row">';

        $out .= '<div class="mb-3 col-6">';
        $out .= '<a href="' . get_permalink( $this->quiz_id ) . '?download=invites-docx" class="btn p-2 pl-3 pr-3 mr-2 bg-light"><i class="fas fa-file-word"></i> docx</a>';
        $out .= '<a href="' . get_permalink( $this->quiz_id ) . '?download=invites-xlsx" class="btn p-2 pl-3 pr-3 mr-2 bg-light"><i class="fas fa-file-excel"></i> xlsx</a>';
        $out .= '</div>';

        $out .= '<div class="mb-3 col-6 text-right">';
        $out .= '<span class="btn p-2 pl-3 pr-3 mr-2 bg-light"><strong>' . __( 'Всего', 'mif-qm' ) . ':</strong> ' . count( $invites_filtered ) . ' ' . __( 'шт.', 'mif-qm' ) . '</span>';
        $out .= '</div>';

        $out .= '</div>';

        // Какое-нибудь сообщение, если есть

        $out .= $this->get_message();

        // Список инвайтов

        $out .= '<div class="row no-gutters border-bottom">';
        $out .= '<div class="col-12 bg-light text-center p-2 font-weight-bold">' . __( 'Фамилии, имена, отчества и коды', 'mif-qm' ) . '</div>';
        $out .= '</div>';

        if ( count( $invites_filtered ) > 0 ) {
   
            foreach ( (array) $invites_filtered as $invite ) {
                
                // if ( $group_rq ) {

                //     $group_id = ( isset( $invite['group'] ) ) ? md5( $invite['group'] ) : md5( '' );
                //     if ( $group_rq != $group_id ) continue;

                // }

                $out .= '<div class="row no-gutters border-bottom invite-item">';
                
                $out .= '<div class="col-1 text-center">';
                
                if ( mif_qm_access_level( $this->quiz_id ) > 2 ) {
                    
                    $out .= '<label class="p-2 m-0 w-100"><input type="checkbox" name="invites[]" value="' . $invite['invite_code'] . '" id="chk-' . $invite['invite_code'] . '" class="members"></label>';
                    
                }
                
                $out .= '</div>';
                
                $out .= '<div class="col-7 p-2">';
                
                $out .= '<div><a href="#" class="beak mr-3"><i class="fas fa-angle-right"></i></a>' . $invite['fullname'] . '</div>';
                
                $out .= '<div class="wrap mif-none"><ul class="list-unstyled ml-0 pl-4 pt-2 pb-2 mt-2 bg-light">';
                
                if ( ! empty( $invite['group'] ) ) $out .= '<li><small>' . __( 'Группа', 'mif-qm' ) . ': ' .  $invite['group'] . '</small></li>';
                
                $out .= '<li><small>' . __( 'Создано', 'mif-qm' ) . ': ' .  $this->get_display_name( $invite['invite_creator'] ) . '</small></li>';
                $out .= '<li><small>' . __( 'Дата создания', 'mif-qm' ) . ': ' .  $this->get_time( $invite['invite_time'] ) . '</small></li>';
                
                if ( ! empty( $invite['username'] ) ) $out .= '<li><small>' . __( 'Имя пользователя', 'mif-qm' ) . ': ' .  $invite['username'] . '</small></li>';
                if ( ! empty( $invite['email'] ) ) $out .= '<li><small>' . __( 'Email', 'mif-qm' ) . ': ' .  $invite['email'] . '</small></li>';
                
                // Можно еще что-то добавить
                
                $out = apply_filters( 'mif_qm_invates_screen_get_invates_data', $out, $invites );

                $out .= '</ul></div>';
                
                $out .= '</div>';

                $out .= '<div class="col-2 p-2">' . $invite['invite_code'] . '</div>';

                $out .= '<div class="col-2 text-right p-2">';

                if ( mif_qm_access_level( $this->quiz_id ) > 2 ) {

                    $email_flag = false;

                    if ( ! empty( $invite['username'] ) ) $email_flag = true;
                    if ( ! empty( $invite['email'] ) ) $email_flag = true;

                    // Можно учесть новые способы отправки

                    $email_flag = apply_filters( 'mif_qm_invates_screen_get_invates_email_flag', $email_flag, $invites );

                    $out .= '<span class="loading pr-2"><i class="fas fa-spinner fa-pulse"></i></span>';

                    if ( $email_flag ) $out .= '<a href="#" class="invite-manage-btn text-secondary mr-2" data-do="emailer" data-invite="' . $invite['invite_code'] . '" title="' . __( 'Оповестить', 'mif-qm' ) . '"><i class="fas fa-at"></i></a>';
                    $out .= '<a href="#" class="invite-manage-btn text-secondary mr-2" data-do="remove" data-invite="' . $invite['invite_code'] . '" title="' . __( 'Удалить', 'mif-qm' ) . '"><i class="fas fa-user-times"></i></a>';

                }

                $out .= '</div>';

                $out .= '</div>';
                
                $n++;    
                
            }
            
            if ( mif_qm_access_level( $this->quiz_id ) > 2 ) {
                
                $out .= '<div class="col-12"><label class="pt-3 pr-2 pb-3 p-0 m-0"><input type="checkbox" name="select_all" value="no" class="mr-2">' . __( 'выбрать всех', 'mif-qm' ) . '</label></div>';
                
                $out .= '<div>';
                
                $out .= '<button class="btn mr-2" name="remove">' . __( 'Удалить', 'mif-qm' ) . '</button>';
                $out .= '<button class="btn mr-2" name="emailer">' . __( 'Оповестить', 'mif-qm' ) . '</button>';
                
                $out .= '<span class="loading pl-2"><i class="fas fa-spinner fa-pulse"></i></span>';
                
                $out .= '</div>';

            }

        } else {
            
            $out .= '<div class="row no-gutters border-bottom">';
            $out .= '<div class="col-12 p-1 pl-3 alert-warning">' . __( 'Приглашения отсутствуют', 'mif-qm' ) . '</div>';
            $out .= '</div>';
            
        }

        $out .= '<input type="hidden" name="action" value="invites" />';
        $out .= '<input type="hidden" name="_wpnonce" value="' . wp_create_nonce( 'mif-qm' ) . '" />';
        $out .= '<input type="hidden" name="quiz_id" value="' . $this->quiz_id . '" />';

       $out .= '</form>';
       $out .= '</div>';

        return apply_filters( 'mif_qm_invites_screen_get_add_form', $out );
    }

}


?>
