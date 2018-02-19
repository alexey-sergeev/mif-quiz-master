<?php

//
// Экранные методы для работы с пользователями
// 
//


defined( 'ABSPATH' ) || exit;

include_once dirname( __FILE__ ) . '/members-core.php';
include_once dirname( __FILE__ ) . '/members-templates.php';


class mif_qm_members_screen extends mif_qm_members_core { 

    private $quiz_id;
    
    // Массив пользователей

    private $members = array();

    function __construct( $quiz_id )
    {
        parent::__construct();
        $this->quiz_id = $quiz_id;
    }



    //
    // Вывести окно списков пользователей
    //

    public function the_members()
    {
        // Смотреть пользователей может только эксперт и выше

        if ( ! ( $this->access_level( $this->quiz_id ) > 1 ) ) return;

        // Получить список пользователей

        $this->members = $this->get( $this->quiz_id );

        // Подключить шаблон из темы оформления или локальный

        if ( $template = locate_template( 'members.php' ) ) {
           
            load_template( $template, false );

        } else {

            load_template( dirname( __FILE__ ) . '/../templates/members.php', false );

        }    

    }



    //
    // Вывести список пользователей для конкретной роли
    //

    public function get_members_part( $role = 'student' )
    {
        // Если роль не мастер и не студент, то посмотреть, есть ли такие записи

        if ( ! in_array( $role, array( 'master' ) ) ) {

            $present = array();
            foreach ( (array) $this->members as $item ) $present[$item['role']] = 'yes';

        }
        
        // Показывать дальше, если мастер или студент или для другой роли кто-то реально есть

        if ( ! ( isset( $present[$role] ) || in_array( $role, array( 'master', 'student' ) ) ) ) return;

        $out = '';
        $roles = $this->get_roles();

        $out .= '<div class="mt-5">';
        $out .= '<div class="row no-gutters">';
        $out .= '<div class="bg-light col-12 p-2 font-weight-bold">' . $roles[$role]['name'] . '</div>';
        $out .= '</div>';
        
        foreach ( (array) $this->members as $user_token => $data ) {

            if ( $data['role'] != $role ) continue;
            
            $out .= '<div class="row border-bottom p-2 no-gutters">';
            $out .= '<div class="col-12">' . $this->get_display_name( $user_token ) . '</div>';
            $out .= '</div>';
            
        }
        
        if ( $role == 'student' ) {
            
            if  ( ! isset( $present['student'] )  ) {

                $out .= '<div class="row no-gutters border-bottom">';
                $out .= '<div class="col-12 p-2 alert-warning">' . __( 'Пока никого нет', 'mif-qm' ) . '</div>';
                $out .= '</div>';

            }

            $out .= '<div class="add-form">';

            $out .= '<div class="row no-gutters add-textarea">';
            $out .= '<div class="col-10 pt-4">' . __( 'Укажите пользователей, которых надо добавить в список', 'mif-qm' ) . '</div>';
            $out .= '<div class="col-2 pt-4 text-right"><a href="#" class="cancel">' . __( 'отмена', 'mif-qm' ) . '</a></div>';
            
            $out .= '<div class="col-12 pt-4"><textarea></textarea></div>';

            $out .= '<div class="col-12 text-center save-button">';
            $out .= '<button class="btn btn-primary m-2">' . __( 'Сохранить', 'mif-qm' ) . '</button>';
            $out .= '<input type="hidden" name="_wpnonce" value="' . wp_create_nonce( 'mif-qm' ) . '" />';
            $out .= '<input type="hidden" name="quiz_id" value="' . $this->quiz_id . '" />';
            // $out .= '<button class="btn btn-secondary m-2">' . __( 'Отменить', 'mif-qm' ) . '</button>';
            $out .= '</div>';

            $out .= '</div>';
            
            $out .= '<div class="row no-gutters add-button">';
            $out .= '<div class="col-12 text-center p-4"><button class="btn btn-primary">' . __( 'Добавить', 'mif-qm' ) . '</button></div>';
            $out .= '</div>';
            
            $out .= '</div>';

        }


        $out .= '</div>';

        return apply_filters( 'mif_qm_process_screen_get_members_part', $out, $this->members, $role );
    }



    //
    // Вывести панель настройки режима доступа
    //

    public function get_access_mode_panel()
    {
        $out = '';

        $checked = array( 'open' => '', 'request' => '', 'memberlist' => '', 'closed' => '' );
        $class = array( 'open' => ' bg-light', 'request' => ' bg-light', 'memberlist' => ' bg-light', 'closed' => ' bg-light' );

        $access_mode = $this->get_access_mode( $this->quiz_id );

        $checked[$access_mode] = ' checked';
        $class[$access_mode] = ' bg-primary text-light';
        
        // $out .= '<div class="text-center font-weight-bold mt-3">' . __( 'Режим доступа', 'mif-qm' ) . '</div>';
        $out .= '<div class="mt-5 text-center"><h3>' . __( 'Настройки доступа', 'mif-qm' ) . '</h3></div>';

        $out .= '<div class="access-mode row justify-content-center mt-4 pb-3">';

        $out .= '<div class="col-2 text-center border-right p-0' . $class['open'] . '"><label class="p-1 m-0 w-100">';
        $out .= '<span class="loading"><i class="fas fa-spinner fa-pulse"></i></span>';
        $out .= '<input type="radio" name="access_mode" value="open"' . $checked['open'] . '><br /><small>' . __( 'Открытый', 'mif-qm' ) . '</small>';
        $out .= '</label></div>';
        
        $out .= '<div class="col-2 text-center border-right p-0' . $class['request'] . '"><label class="p-1 m-0 w-100">';
        $out .= '<span class="loading"><i class="fas fa-spinner fa-pulse"></i></span>';
        $out .= '<input type="radio" name="access_mode" value="request"' . $checked['request'] . '><br /><small>' . __( 'По заявкам', 'mif-qm' ) . '</small>';
        $out .= '</label></div>';
        
        $out .= '<div class="col-2 text-center border-right p-0' . $class['memberlist'] . '"><label class="p-1 m-0 w-100">';
        $out .= '<span class="loading"><i class="fas fa-spinner fa-pulse"></i></span>';
        $out .= '<input type="radio" name="access_mode" value="memberlist"' . $checked['memberlist'] . '><br /><small>' . __( 'По спискам', 'mif-qm' ) . '</small>';
        $out .= '</label></div>';
        
        $out .= '<div class="col-2 text-center p-0' . $class['closed'] . '"><label class="p-1 m-0 w-100">';
        $out .= '<span class="loading"><i class="fas fa-spinner fa-pulse"></i></span>';
        $out .= '<input type="radio" name="access_mode" value="closed"' . $checked['closed'] . '><br /><small>' . __( 'Закрытый', 'mif-qm' ) . '</small>';
        $out .= '</label></div>';

        $out .= '<input type="hidden" name="_wpnonce" value="' . wp_create_nonce( 'mif-qm' ) . '" />';
        $out .= '<input type="hidden" name="quiz_id" value="' . $this->quiz_id . '" />';
        
        $out .= '</div>';

        return apply_filters( 'mif_qm_process_screen_get_access_mode_panel', $out, $this->members );
    }


}


?>
