<?php

//
// Экранные методы для работы с пользователями
// 
//


defined( 'ABSPATH' ) || exit;

include_once dirname( __FILE__ ) . '/members-core.php';
include_once dirname( __FILE__ ) . '/members-templates.php';


class mif_qm_members_screen extends mif_qm_members_core { 

    // private $quiz_id;
    
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
        // Если роль не мастер, то посмотреть, есть ли такие записи

        if ( ! in_array( $role, array( 'master' ) ) ) {

            $present = array();
            foreach ( (array) $this->members as $item ) $present[$item['role']] = 'yes';

        }
        
        // Показывать дальше, если мастер или студент или для другой роли кто-то реально есть

        if ( ! ( isset( $present[$role] ) || in_array( $role, array( 'master', 'student' ) ) ) ) return;

        $out = '';
        $roles = $this->get_roles();

        // Вывести шапку

        $out .= $this->members_list_elem( $roles[$role]['name'], 'header', $role );
        
        // Вывести пользователей

        foreach ( (array) $this->members as $user_token => $data ) {

            if ( $data['role'] != $role ) continue;
            
            $out .= $this->members_list_elem( $user_token, 'item', $role );
            
        }
        
        // Вывести сообщение, если студенты и их нет

        if ( $role == 'student' && empty( $present['student'] ) ) {
            
            $out .= '<div class="row no-gutters border-bottom">';
            $out .= '<div class="col-12 p-2 alert-warning">' . __( 'Пока никого нет', 'mif-qm' ) . '</div>';
            $out .= '</div>';
            
        }
        
        // Вывести нижнюю часть

        $out .= $this->members_list_elem( '', 'footer', $role );


        // $out .= '</div>';

        return apply_filters( 'mif_qm_members_screen_get_members_part', $out, $this->members, $role );
    }


    //
    // Вывести панель настройки режима доступа
    //

    public function get_members_requests()
    {
        $requests = $this->get_requesters( $this->quiz_id, 'request' );

        if ( empty( $requests ) ) return;
        
        $out = '';
        
        $out .= $this->members_list_elem( __( 'Новые заявки', 'mif-qm' ), 'header', 'request' );
        
        foreach ( (array) $requests as $requester ) {
            
            $out .= $this->members_list_elem( $requester, 'item', 'request' );
            
        }
        
        $out .= $this->members_list_elem( '', 'footer', 'request' );


        return apply_filters( 'mif_qm_members_screen_get_members_requests', $out, $this->members, $requests );
    }




    //
    // Вывести элемент списка пользователей
    //

    private function members_list_elem( $data, $mode = 'item', $role = 'student' )
    {
        $out = '';
        $rem_msg = ( $role == 'request' ) ? __( 'Отклонить', 'mif-qm' ) : __( 'Удалить', 'mif-qm' );
        $add_msg = ( $role == 'student' ) ? __( 'Добавить', 'mif-qm' ) : __( 'Подтвердить', 'mif-qm' );
        $class =  ( $role == 'request' ) ? ' alert-warning' : '';

        if ( $mode == 'header' ) {

            // Заголовок
            
            if ( $this->access_level( $this->quiz_id ) > 2 ) $out .= '<form method="POST">';

            $out .= '<div class="row no-gutters border-bottom mt-5">';
            $out .= '<div class="bg-light col-12 p-2 font-weight-bold">' . $data . '</div>';
            $out .= '</div>';
            
        } elseif ( $mode == 'item' ) {
            
            // Элементы списка
            
            $out .= '<div class="row border-bottom no-gutters">';

            $out .= '<div class="col-1 text-center' . $class . '">';

            if ( $this->access_level( $this->quiz_id ) > 2 && in_array( $role, array( 'request', 'student' ) ) ) {
                
                $out .= '<label class="p-2 m-0 w-100"><input type="checkbox" name="members[]" value="' . $data . '" id="chk-' . $data . '" class="members"></label>';
                
            }

            $out .= '</div>';

            $display_name = $this->get_display_name( $data );
            $fullname = $this->get_fullname( $data );
            $link = $this->get_user_link( $data );

            if ( $display_name == $fullname ) {

                $out .= '<div class="col-9' . $class . '"><label for="chk-' . $data . '" class="p-0 pr-2 pt-2 pb-2 m-0 w-100"><a href="' . $link . '">' . $display_name . '</a></label></div>';

            } else {
                
                $out .= '<div class="col-9' . $class . '"><label for="chk-' . $data . '" class="p-0 pr-2 pt-2 pb-2 m-0 w-100">' . $fullname . ' (<a href="' . $link . '">' . $data . '</a>)</label></div>';

            }


            // $out .= '<div class="col-9' . $class . '"><label for="chk-' . $data . '" class="p-0 pr-2 pt-2 pb-2 m-0 w-100">' . $this->get_display_name( $data ) . '</label></div>';
            // $out .= '<div class="col-9' . $class . '"><label for="chk-' . $data . '" class="p-0 pr-2 pt-2 pb-2 m-0 w-100">' . $this->get_fullname( $data, $this->quiz_id ) . '</label></div>';
            
            // Вывести блок мелких кнопок

            $out .= '<div class="col-2 text-right p-2' . $class . '">';

            if ( $this->access_level( $this->quiz_id ) > 2 && ! ( $this->get_quiz_author( $this->quiz_id ) == $data ) ) {

                $out .= '<span class="loading pr-2"><i class="fas fa-spinner fa-pulse"></i></span>';

                if ( ! in_array( $role, array( 'request', 'master' ) ) ) $out .= '<a href="#" class="member-manage-btn text-secondary mr-2" data-do="promotion" data-member="' . $data . '" title="' . __( 'Повысить', 'mif-qm' ) . '"><i class="fas fa-arrow-up"></i></a>';
                if ( ! in_array( $role, array( 'request', 'student' ) ) ) $out .= '<a href="#" class="member-manage-btn text-secondary mr-2" data-do="demotion" data-member="' . $data . '" title="' . __( 'Понизить', 'mif-qm' ) . '"><i class="fas fa-arrow-down"></i></a>';

                if ( $role == 'request' ) $out .= '<a href="#" class="member-manage-btn text-secondary mr-2" data-do="add" data-member="' . $data . '" title="' . $add_msg . '"><i class="fas fa-user-plus"></i></a>';
                if ( in_array( $role, array( 'request', 'student' ) ) ) $out .= '<a href="#" class="member-manage-btn text-secondary mr-2" data-do="remove" data-member="' . $data . '" title="' . $rem_msg . '"><i class="fas fa-user-times"></i></a>';

            }
            
            $out .= '</div>';
            $out .= '</div>';
            
        } elseif ( $mode == 'footer' ) {
            
            // Нижняя часть
            
            if ( $this->access_level( $this->quiz_id ) > 2 ) {
            
                
                if ( in_array( $role, array( 'request', 'student' ) ) ) {
                    
                    $out .= '<div class="row no-gutters">';

                    $out .= '<div class="col-12"><label class="pt-3 pr-2 pb-3 p-0 m-0"><input type="checkbox" name="select_all" value="no" class="mr-2">' . __( 'выбрать всех', 'mif-qm' ) . '</label></div>';
                
                    $class = '';
                    $add_form = '';

                    if ( $role == 'student' ) {
                        
                        $class = ' noajax textarea-show';
                        $add_form = $this->get_add_form();

                    }

                    $out .= '<button class="btn mr-2' . $class . '" name="add">' . $add_msg . '</button>';
                    $out .= '<button class="btn mr-2" name="remove">' . $rem_msg . '</button>';
                    
                    $out .= '<span class="loading pl-2"><i class="fas fa-spinner fa-pulse"></i></span>';
                    
                    $out .= '</div>';

                    $out .= $add_form;

                }
                    
                
                if ( $this->access_level( $this->quiz_id ) > 2 ) {
                    
                    $out .= '<input type="hidden" name="action" value="members" />';
                    $out .= '<input type="hidden" name="_wpnonce" value="' . wp_create_nonce( 'mif-qm' ) . '" />';
                    $out .= '<input type="hidden" name="quiz_id" value="' . $this->quiz_id . '" />';
                    $out .= '<input type="hidden" name="premise" value="' . $role . '" />';
                    $out .= '</form>';
                    
                }
                
                // $out .= '</div>';
            }
            
        }

        return apply_filters( 'mif_qm_members_screen_members_list_elem', $out, $data, $mode );
    }




    //
    // Возвращает блок ввода пользователей
    //

    private function get_add_form()
    {
        $out = '';

        // $out .= '<form method="POST" class="members-add">';
        $out .= '<div class="add-form">';
        
        $out .= '<div class="add-textarea">';
        $out .= '<div class="p-2 pl-4 pr-4 mt-4 mb-4 bg-light row no-gutters">';
        $out .= '<div class="col-10 pt-4">' . __( 'Укажите пользователей, которых надо добавить в список', 'mif-qm' ) . '</div>';
        $out .= '<div class="col-2 pt-4 text-right"><a href="#" class="cancel">' . __( 'отмена', 'mif-qm' ) . '</a></div>';
        
        $out .= '<div class="col-12 pt-4"><textarea name="members-text"></textarea></div>';

        $out .= '<div class="col-12 text-center save-button">';
        $out .= '<button class="btn m-2">' . __( 'Сохранить', 'mif-qm' ) . '</button>';
        $out .= '<span class="loading absolute pl-2 mt-2"><i class="fas fa-spinner fa-pulse"></i></span>';
        // $out .= '<input type="hidden" name="_wpnonce" value="' . wp_create_nonce( 'mif-qm' ) . '" />';
        // $out .= '<input type="hidden" name="quiz_id" value="' . $this->quiz_id . '" />';
        $out .= '</div>';

        $out .= '</div>';
        $out .= '</div>';
        
        $out .= '</div>';
        // $out .= '</form>';

        return apply_filters( 'mif_qm_members_screen_get_add_form', $out );
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

        $disabled = ( mif_qm_access_level() > 2 ) ? '' : ' disabled';

        $checked[$access_mode] = ' checked';
        $class[$access_mode] = ' bg-primary text-light';
        
        // $out .= '<div class="text-center font-weight-bold mt-3">' . __( 'Режим доступа', 'mif-qm' ) . '</div>';
        $out .= '<div class="mt-5 text-center"><h3>' . __( 'Настройки доступа', 'mif-qm' ) . '</h3></div>';

        $out .= '<div class="access-mode row justify-content-center mt-4 pb-3">';

        $out .= '<div class="col-2 text-center border-right p-0' . $class['open'] . '"><label class="p-1 m-0 w-100">';
        $out .= '<span class="loading"><i class="fas fa-spinner fa-pulse"></i></span>';
        $out .= '<input type="radio" name="access_mode" value="open"' . $checked['open'] . $disabled . '><br /><small>' . __( 'Открытый', 'mif-qm' ) . '</small>';
        $out .= '</label></div>';
        
        $out .= '<div class="col-2 text-center border-right p-0' . $class['request'] . '"><label class="p-1 m-0 w-100">';
        $out .= '<span class="loading"><i class="fas fa-spinner fa-pulse"></i></span>';
        $out .= '<input type="radio" name="access_mode" value="request"' . $checked['request'] . $disabled . '><br /><small>' . __( 'По заявкам', 'mif-qm' ) . '</small>';
        $out .= '</label></div>';
        
        $out .= '<div class="col-2 text-center border-right p-0' . $class['memberlist'] . '"><label class="p-1 m-0 w-100">';
        $out .= '<span class="loading"><i class="fas fa-spinner fa-pulse"></i></span>';
        $out .= '<input type="radio" name="access_mode" value="memberlist"' . $checked['memberlist'] . $disabled . '><br /><small>' . __( 'По спискам', 'mif-qm' ) . '</small>';
        $out .= '</label></div>';
        
        $out .= '<div class="col-2 text-center p-0' . $class['closed'] . '"><label class="p-1 m-0 w-100">';
        $out .= '<span class="loading"><i class="fas fa-spinner fa-pulse"></i></span>';
        $out .= '<input type="radio" name="access_mode" value="closed"' . $checked['closed'] . $disabled . '><br /><small>' . __( 'Закрытый', 'mif-qm' ) . '</small>';
        $out .= '</label></div>';

        $out .= '<input type="hidden" name="_wpnonce" value="' . wp_create_nonce( 'mif-qm' ) . '" />';
        $out .= '<input type="hidden" name="quiz_id" value="' . $this->quiz_id . '" />';
        
        $out .= '</div>';

        return apply_filters( 'mif_qm_members_screen_get_access_mode_panel', $out, $this->members );
    }




    //
    // Вывести панель вывода группы пользователя
    //

    public function get_groups_panel( $members = array() )
    {
        $out = '';

        $groups = $this->get_groups( $members );

        if ( count( $groups ) > 1 ) {
            
            $group_rq = ( isset( $_REQUEST['group'] ) ) ? sanitize_key( $_REQUEST['group'] ) : '';
            
            $out .= '<div class="row mb-4 justify-content-center groups-btn">';
            
            $primary = 'btn-primary text-white m-1';
            $secondary = 'btn-light text-secondary m-1';
            
            $class = ( ! $group_rq ) ? $primary : $secondary;
            
            $out .= '<a class="btn ' . $class . '" href="#" role="button" data-group="">' . __( 'Все', 'mif-qm' ) . '</a>';
            
            foreach ( $groups as $g ) {
                
                $class = $secondary;
                $group = ( $g ) ? $g : __( 'без группы', 'mif-qm' );
                
                $md = md5( $g );
                $class = ( $group_rq == $md ) ? $primary : $secondary;
                
                $out .= '<a class="btn ' . $class . '" href="#" role="button" data-group="' . $md . '">' . $group . '</a>';
                
            }
            
            $out .= '<span class="loading p-2" style="margin-right: -32px"><i class="fas fa-spinner fa-pulse"></i></span>';
            
            $out .= '</div>';
            
        }

        return apply_filters( 'mif_qm_members_screen_get_groups_panel', $out, $members, $groups );
    }


}


?>
