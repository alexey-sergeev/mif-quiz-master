<?php

//
// Ядро системы приглашений
// 
//


defined( 'ABSPATH' ) || exit;



class mif_qm_invites_core extends mif_qm_core_core  { 

   
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
    
    public function add( $quiz_id, $data = array() )
    {
        // Добавить данные о создании
  
        $data['creator'] = $this->get_user_token();
        $data['time'] = $this->get_time();

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

        $invite_data = get_post( $data_id );

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
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
            'name' => $invite_code,
            );

        $data = get_posts( $args );
        
        if ( isset( $data[0] ) ) $invite = $this->get_invite_from_data( $data[0] );
        
        return $invite;
    }



    //
    // Удалить инвайт по его коду
    //

    public function remove( $invite_code )
    {
        $invite = $this->get( $invite_code );
        $ret = wp_trash_post( $invite['invite_id'] );

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

                $members_core = new mif_qm_members_core();
                $members_core->set_request( get_current_user_id(), $invite['quiz_id'], true );

                $this->remove( $invite_code );

                return get_permalink( $invite['quiz_id'] );

            } else {

                return false;

            }

            // !!! Здесь еще думать про разные варианты тире

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

        // Если есть новый запрос на инвайты, то добавить их

        if ( isset( $_REQUEST['members-list'] ) ) {

            $list = esc_html( $_REQUEST['members-list'] );
            $arr = explode( "\n", $list );
            $arr = array_map( 'trim', $arr );
            $arr = array_diff( $arr, array( '' ) );
            $arr = array_reverse( $arr );
            
            // !!! Здесь можно более глубоко смотреть строку - искать почту для рассылки и др.

            // Добавить инвайты

            foreach ( (array) $arr as $item ) $this->add( $quiz_id, array( 'fullname' => $item ) );

        }

        // Получить все инвайты

        $args = array(
            'numberposts' => -1,
            'post_type' => 'quiz_invite',
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
            'post_parent' => $quiz_id,
            );

        $data = get_posts( $args );

        foreach ( (array) $data as $invite_data ) {

            // $invite = $this->to_array( $invite_data->post_content );
            // $invite['quiz_id'] = $invite_data->post_parent;
            // $invite['invite_code'] = $invite_data->post_name;

            // $invites[] = $invite;

            $invites[] = $this->get_invite_from_data( $invite_data );

        }

        return $invites;
    }



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
    
        return $invite;
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