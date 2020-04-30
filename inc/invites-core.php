<?php

//
// Ядро системы приглашений
// 
//


defined( 'ABSPATH' ) || exit;



class mif_qm_invites_core extends mif_qm_core_core  { 


    public $page404 = '404';

   
    function __construct()
    {
        parent::__construct();

    }


    // 
    // Иницализация типов записей
    // 

    public function post_types_init()
    {
    
        // 
        // Тип записей - "Приглашение"
        // 

        register_post_type( 'quiz_invite', array(
            'label'  => null,
            'labels' => array(
                'name'               => __( 'Приглашения', 'mif-qm' ), // основное название для типа записи
                'singular_name'      => __( 'Приглашение', 'mif-qm' ), // название для одной записи этого типа
                'add_new'            => __( 'Создать приглашение', 'mif-qm' ), // для добавления новой записи
                'add_new_item'       => __( 'Создание приглашений', 'mif-qm' ), // заголовка у вновь создаваемой записи в админ-панели.
                'edit_item'          => __( 'Редактирование приглашений', 'mif-qm' ), // для редактирования типа записи
                'new_item'           => __( 'Новые приглашения', 'mif-qm' ), // текст новой записи
                'view_item'          => __( 'Посмотреть приглашения', 'mif-qm' ), // для просмотра записи этого типа.
                'search_items'       => __( 'Найти приглашения', 'mif-qm' ), // для поиска по этим типам записи
                'not_found'          => __( 'Приглашения не найдены', 'mif-qm' ), // если в результате поиска ничего не было найдено
                'not_found_in_trash' => __( 'Не найдено в корзине', 'mif-qm' ), // если не было найдено в корзине
                'parent_item_colon'  => '', // для родителей (у древовидных типов)
                'menu_name'          => __( 'Приглашения', 'mif-qm' ), // название меню
            ),
            'description'         => '',
            'public'              => true,
            'publicly_queryable'  => null,
            'exclude_from_search' => null,
            'show_ui'             => null,
            // 'show_in_menu'        => true, // показывать ли в меню адмнки
            'show_in_menu'        => 'edit.php?post_type=quiz', // показывать ли в меню адмнки
            'show_in_admin_bar'   => null, // по умолчанию значение show_in_menu
            'show_in_nav_menus'   => null,
            'show_in_rest'        => null, // добавить в REST API. C WP 4.7
            'rest_base'           => null, // $post_type. C WP 4.7
            // 'menu_position'       => 20,
            // 'menu_icon'           => 'dashicons-forms', 
            'capability_type'   => 'post',
            //'capabilities'      => 'post', // массив дополнительных прав для этого типа записи
            'map_meta_cap'      => true, // Ставим true чтобы включить дефолтный обработчик специальных прав
            'hierarchical'        => true,
            'supports'            => array( 'title', 'editor', 'author', 'custom-fields', 'revisions' ), // 'title','editor','author','thumbnail','excerpt','trackbacks','custom-fields','comments','revisions','page-attributes','post-formats'
            'taxonomies'          => array(),
            'has_archive'         => true,
            'rewrite'             => array( 'slug' => 'quiz_invite' ),
            'query_var'           => true,

        ) );
    }

    //
    // Добавить новый инвайт
    //
    
    public function add( $quiz_id, $data = array(), $emailer = false )
    {
        // Добавить данные о создании
  
        $data['invite_creator'] = $this->get_user_token();
        $data['invite_time'] = $this->get_time();

        // Сформировать предполагаемый код приглашения

        $invite_code = rand( 0, 9 ) . rand( 0, 9 ) . rand( 0, 9 ) . rand( 0, 9 ) . '-' . rand( 0, 9 ) . rand( 0, 9 ) . rand( 0, 9 );

        $args = array(
            'post_title' => $data['fullname'],
            'post_type' => 'quiz_invite',
            'post_name' => $invite_code,
            'post_content' => $this->to_xml( $data ),
            'post_status' => 'publish',
            'quiz' => $quiz_id,
            'user' => false,
            );

        $invation_id = $this->companion_insert( $args );

        $invite_data = get_post( $invation_id );

        // Сделать рассылку?
        
        if ( $emailer ) {
            
            $invite = $this->get_invite_from_data( $invite_data );
            do_action( 'mif_qm_invate_emailer', $invite, $quiz_id );

        }    
        
        return $invite_data;
    }



    //
    // Получить инвайт по его коду
    //

    public function get( $invite_code )
    {
        $invite = false;
        
        if ( empty( $invite_code ) ) return false;

        $args = array(
            'post_type' => 'quiz_invite',
            'post_status' => array( 'publish', 'trash' ),
            'orderby' => 'ID',
            'order' => 'DESC',
            'post_name__in' => array( $invite_code, $invite_code . '__trashed' ),
            );

        $data = get_posts( $args );
        
        if ( isset( $data[0] ) ) $invite = $this->get_invite_from_data( $data[0] );
        
        return $invite;
    }



    //
    // Удалить инвайт по его коду
    //

    public function remove( $invite_code, $delete = false )
    {
        $invite = $this->get( $invite_code );
        
        if ( $delete ) {

            // Удалить насовсем

            $ret = wp_delete_post( $invite['invite_id'] );

        } else {
            
            // В корзину

            $invite['owner'] = $this->get_user_token();
            
            $args = array(
                'ID' => $invite['invite_id'],
                'post_content' => $this->to_xml( $invite )
            );        
            
            $ret = $this->companion_update( $args );                        
            
            if ( $ret ) $ret = wp_trash_post( $invite['invite_id'] );

        }
            
        return $ret;
    }



    //
    // Обработать инвайт, введенный пользователем
    //

    public function processed()
    {
        $out = '';

        if ( isset( $_REQUEST['invite_code'] ) ) {

            $invite_code = sanitize_key( $_REQUEST['invite_code'] );
            $invite = $this->get( $invite_code );

            if ( $invite ) {

                if ( $invite['invite_status'] == 'publish' ) {
                    
                    $members_core = new mif_qm_members_core();
                    $members_core->set_request( get_current_user_id(), $invite['quiz_id'], $invite );
                    $this->remove( $invite_code );

                    return get_permalink( $invite['quiz_id'] );

                }

                if ( isset( $invite['owner'] ) && $invite['owner'] == $this->get_user_token() ) {

                    return get_permalink( $invite['quiz_id'] );

                } else {

                    return home_url() . '/' . $this->page404;

                }

            } else {

                return  home_url() . '/' . $this->page404;

            }

        } else {

            return false;

        }

    }



    //
    // Получить список инвайтов по тесту
    //

    public function get_list( $quiz_id )
    {
        $arr = array();
        $invites = array();

        // p($_REQUEST);

        $arr_request = array();

        if ( isset( $_REQUEST['invites'] ) ) {

            if ( is_array( $_REQUEST['invites'] ) ) {
                
                $arr_request = array_map( 'sanitize_key', $_REQUEST['invites'] );
                
            } else {

                $arr_request[] = sanitize_key( $_REQUEST['invites'] );

            } 

        }
        
        if ( isset( $_REQUEST['do'] ) && mif_qm_access_level() > 2 ) {
            
            // Запрос на удаление инвайтов
            
            if ( $_REQUEST['do'] == 'remove' ) {

                foreach ( $arr_request as $item ) {

                    $invite = $this->get( $item );
                    if ( $quiz_id != $invite['quiz_id'] ) continue;

                    $this->remove( $item, true );
                }

            }

            // Запрос на повторную рассылку сообщений
            
            if ( $_REQUEST['do'] == 'emailer' ) {

                foreach ( $arr_request as $item ) {

                    $invite = $this->get( $item );
                    if ( $quiz_id != $invite['quiz_id'] ) continue;

                    do_action( 'mif_qm_invate_emailer', $invite, $quiz_id );
                }

            }


        }

        // Если есть новый запрос на инвайты, то добавить их

        if ( isset( $_REQUEST['members-list'] ) && mif_qm_access_level() > 2 ) {

            $list = esc_html( $_REQUEST['members-list'] );
            $arr = explode( "\n", $list );
            $arr = array_map( 'trim', $arr );
            // $arr = array_diff( $arr, array( '' ) );
            
            // $arr = array_reverse( $arr );
            
            // !!! Здесь можно более глубоко смотреть строку - искать почту для рассылки и др.

            // p($arr);

            $arr2 = array();
            $group = ( isset( $_REQUEST['new-group'] ) ) ? trim( sanitize_text_field( $_REQUEST['new-group'] ), '= ' ) : '';

            foreach ( $arr as $item ) {

                if ( empty( $item ) ) continue;
                if ( preg_match( '/^#/', $item ) ) continue;

                if ( preg_match( '/^=/', $item ) ) {

                    $group = trim( $item, '= ' );
                    continue;

                }

                $data = array();

                $item = preg_replace( '/\s+/', ' ', $item );

                $arr3 = explode( ' ', $item );
                $fullname_arr = array();

                foreach ( $arr3 as $item2 ) {

                    if ( preg_match( '/^@/', $item2 ) ) {
                    
                        // Нашли системное имя пользователя (логин)
                        
                        $data['username'] = trim( $item2, '@ ' );
                        continue;
                        
                    }
                    
                    if ( preg_match( '/\S@/', $item2 ) ) {
                        
                        // Нашли email

                        $data['email'] = $item2;
                        continue;

                    }

                    // Здесь можем еще что-то найти

                    $data = apply_filters( 'mif_qm_invates_core_get_list_data', $data, $item2 );
                    
                    // А здесь ничего не нашли. Это часть имени человека

                    $fullname_arr[] = $item2;

                }

                $fullname = implode( ' ', $fullname_arr );

                if ( empty( $fullname ) ) continue;

                $data['fullname'] = $fullname;
                if ( $group) $data['group'] = $group;

                $arr2[] = $data;
            }

            // p( $arr2 );

            // Добавить инвайты
            
            $arr2 = array_reverse( $arr2 );
            
            $emailer = ( isset( $_REQUEST['emailer_now'] ) ) ? sanitize_key( $_REQUEST['emailer_now'] ) : false;

            foreach ( (array) $arr2 as $invite ) $this->add( $quiz_id, $invite, $emailer );

        }

        // Получить все инвайты

        $args = array(
            'numberposts' => -1,
            'post_type' => 'quiz_invite',
            'post_status' => 'publish',
            'orderby' => 'ID',
            'order' => 'DESC',
            'post_parent' => $quiz_id,
            );

        $data = get_posts( $args );

        foreach ( (array) $data as $invite_data ) {

            $invites[] = $this->get_invite_from_data( $invite_data );

        }

        return $invites;
    }



    //
    // Получить группы из инвайтов
    //

    // protected function get_groups( $invites )
    // {
    //     $qm_members_core = new mif_qm_members_core();
    //     return $qm_members_core->get_groups( $invites );

    //     // $index = array();

    //     // foreach ( (array) $invites as $invite ) {

    //     //     // if ( ! isset( $invite['group'] ) ) continue;
    //     //     $group = ( isset( $invite['group'] ) ) ? $invite['group'] : '';
    //     //     $index[$invite['group']] = true;
            
    //     // }

    //     // $groups = ( count( $index ) > 0 ) ? array_keys( $index ) : array();

    //     // sort( $groups );
        
    //     // if ( $groups[0] == '' ) {

    //     //     unset( $groups[0] );
    //     //     $groups[] = '';

    //     // }

    //     // return $groups;
    // }        



    //
    // Получить приглашение из данных записи
    //

    private function get_invite_from_data( $invite_data )
    {
        if ( empty( $invite_data ) ) return false;

        $invite = $this->to_array( $invite_data->post_content );
        $invite['invite_id'] = $invite_data->ID;
        $invite['quiz_id'] = $invite_data->post_parent;
        $invite['invite_code'] = $invite_data->post_name;
        $invite['invite_status'] = $invite_data->post_status;
    
        return $invite;
    }        



    //
    // Получить массив для формирования docx-файла
    //

    public function get_docx_arr( $quiz_id )
    {
        $arr = array();

        $arr['testname'] = get_the_title( $quiz_id );
        $arr['time'] = $this->get_time();

        $invites = $this->get_list( $quiz_id );

        $qm_members_core = new mif_qm_members_core();
        $groups = $qm_members_core->get_groups( $invites );

        foreach ( $groups as $g ) {

            $group = ( $g ) ? $g : __( 'Без группы', 'mif-qm' );

            $arr['fullname'][] = array( 'fullname' => '== ' . $group, 'invite_code' => '', 'site' => '' );

            foreach ( $invites as $i ) {

                if ( $i['group'] != $g ) continue;
                $arr['fullname'][] = array( 'fullname' => $i['fullname'], 'invite_code' => $i['invite_code'], 'site' => home_url() );
                
            }
            
        }
        return $arr;
    }


    //
    // Получить массив для формирования xlsx-файла
    //

    public function get_xlsx_arr( $quiz_id )
    {
        $arr = array();

        $invites = $this->get_list( $quiz_id );

        $cells = array( 
            'n' => __( '№', 'mif-qm' ), 
            'fullname' => __( 'Ф. И. О.', 'mif-qm' ), 
            'group' => __( 'Группа', 'mif-qm' ), 
            'invite_code' => __( 'Код', 'mif-qm' ), 
            'email' => __( 'Email', 'mif-qm' ), 
            'username' => __( 'Имя пользователя', 'mif-qm' ), 
            'invite_creator' => __( 'Кем создано', 'mif-qm' ), 
            'invite_time' => __( 'Дата', 'mif-qm' ), 
        );

        $cells = apply_filters( 'mif_qm_invites_core_get_xlsx_arr_cells', $cells, $quiz_id );

        $arr[] = array();
        $arr[] = array( __( 'Приглашения', 'mif-qm' ) );
        $arr[] = array();
        $arr[] = array( __( 'Тест:', 'mif-qm' ), get_the_title( $quiz_id ) );
        $arr[] = array( __( 'URL:', 'mif-qm' ), get_permalink( $quiz_id ) );
        $arr[] = array( __( 'Дата:', 'mif-qm' ), $this->get_time() );
        $arr[] = array();
        $arr[] = array();

        $row = array();
        foreach ( $cells as $key => $value ) $row[] = $value;

        $arr[] = $row;

        $n = 1;
        foreach ( $invites as $i ) {

            $i['n'] = $n++;

            $row = array();
            foreach ( $cells as $key => $value ) $row[] = ( isset( $i[$key] ) ) ? $i[$key] : '';

            $arr[] = $row;

        }

        return $arr;
    }



    //
    // Преобразовать инвайт в xml-запись
    //

    private function to_xml( $arr )
    {
        $xml = new SimpleXMLElement( '<?xml version="1.0" encoding="UTF-8"?><invite/>' );

        foreach ( (array) $arr as $key => $item ) $xml->addChild( $key, $item );

        $xml_core = new mif_qm_xml_core();
        $xml = $xml_core->get_formatted_xml( $xml );

        // p(esc_html($xml));

        return $xml;
    }



    //
    // Преобразовать xml-запись инвайта в массив
    //

    private function to_array( $xml )
    {
        $arr = array();

        if ( empty( $xml ) ) return $arr;

        try {

            $xml = new SimpleXMLElement( $xml );

        } catch ( Exception $e ) {

            return $arr;

        }

        foreach ( $xml as $key => $item ) $arr[$key] = (string) $item;

        return $arr;
    }

}

?>