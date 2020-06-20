<?php

//
// Обработка снимков теста
// 
//


defined( 'ABSPATH' ) || exit;



class mif_process_snapshots extends mif_qm_process_core { 

    
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
        // Тип записей - "Результат (снимок) теста"
        // 

        register_post_type( 'quiz_snapshot', array(
            'label'  => null,
            'labels' => array(
                'name'               => __( 'Результаты тестов', 'mif-qm' ), // основное название для типа записи
                'singular_name'      => __( 'Результат', 'mif-qm' ), // название для одной записи этого типа
                'add_new'            => __( 'Создать результат', 'mif-qm' ), // для добавления новой записи
                'add_new_item'       => __( 'Создание результата', 'mif-qm' ), // заголовка у вновь создаваемой записи в админ-панели.
                'edit_item'          => __( 'Редактирование результата', 'mif-qm' ), // для редактирования типа записи
                'new_item'           => __( 'Новый результат', 'mif-qm' ), // текст новой записи
                'view_item'          => __( 'Посмотреть результат', 'mif-qm' ), // для просмотра записи этого типа.
                'search_items'       => __( 'Найти результат', 'mif-qm' ), // для поиска по этим типам записи
                'not_found'          => __( 'Результат не найден', 'mif-qm' ), // если в результате поиска ничего не было найдено
                'not_found_in_trash' => __( 'Не найдено в корзине', 'mif-qm' ), // если не было найдено в корзине
                'parent_item_colon'  => '', // для родителей (у древовидных типов)
                'menu_name'          => __( 'Результаты', 'mif-qm' ), // название меню
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
            // 'supports'            => array( 'title', 'editor', 'author', 'custom-fields', 'revisions' ), // 'title','editor','author','thumbnail','excerpt','trackbacks','custom-fields','comments','revisions','page-attributes','post-formats'
            'supports'            => array( 'title', 'editor', 'author', 'custom-fields' ), // 'title','editor','author','thumbnail','excerpt','trackbacks','custom-fields','comments','revisions','page-attributes','post-formats'
            'taxonomies'          => array(),
            'has_archive'         => true,
            'rewrite'             => array( 'slug' => 'quiz_snapshot' ),
            'query_var'           => true,

        ) );
    }


    //
    // Добавить новый снимок
    //

    public function insert( $args )
    {
        // remove_filter( 'content_save_pre', 'wp_filter_post_kses' ); 
        // $snapshot_id = wp_insert_post( $args );
        $snapshot_id = $this->companion_insert( $args );        
        return $snapshot_id;
    }
    
    
    //
    // Обновить снимок
    //
    
    public function update( $args )
    {
        $res = $this->companion_update( $args );
        // remove_filter( 'content_save_pre', 'wp_filter_post_kses' ); 
        // $res = wp_update_post( $args );
        return $res;
    }


    //
    // Удалить снимок
    //

    public function trash( $snapshot_id )
    {
        $res = wp_trash_post( $snapshot_id );
        return $res;
    }


    //
    // Получить снимок
    //

    public function get( $args )
    {
        $snapshot_args = array(
            'numberposts' => -1,
            'post_type'   => 'quiz_snapshot',
            'post_status' => 'draft',
            'orderby'     => 'date',
            'order'       => 'DESC',
            'meta_key'    => 'owner',
            'meta_value'  => $this->get_user_token( $args['user'] ),
            // 'post_parent' => $args['quiz'],
        );
    
        if ( isset( $args['quiz'] ) ) $snapshot_args['post_parent'] = $args['quiz'];
        if ( isset( $args['numberposts'] ) ) $snapshot_args['numberposts'] = $args['numberposts'];
        if ( isset( $args['post_status'] ) ) $snapshot_args['post_status'] = $args['post_status'];

        $snapshots = get_posts( $snapshot_args );

        return $snapshots;
    }

}

?>