<?php

//
// Ядро плагина Quiz Master
// 
//


defined( 'ABSPATH' ) || exit;





class mif_qm_core extends mif_qm_core_core { 

    // Названия домашней старницы, профиля и др.

    protected $post_name_home = 'home';
    protected $post_name_profile = 'profile';
    protected $post_name_workroom = 'workroom';
    protected $post_name_results = 'results';
    protected $post_name_help = 'help';

    
    function __construct()
    {
        parent::__construct();

        $this->post_types_init();

    }



    // 
    // Иницализация типов записей
    // 

    private function post_types_init()
    {
        // 
        // Таксономия и тип записей - "Тест"
        // 

        register_taxonomy( 'quiz_category', array( 'quiz' ), array(
            'hierarchical' => true,
            'labels' => array(
                'name' => __( 'Категории тестов', 'mif-qm' ),
                'singular_name' => __( 'Тесты', 'mif-qm' ),
                'search_items' =>  __( 'Найти', 'mif-qm' ),
                'all_items' => __( 'Все', 'mif-qm' ),
                'parent_item' => __( 'Родительская категория', 'mif-qm' ),
                'parent_item_colon' => __( 'Родительская категория:', 'mif-qm' ),
                'edit_item' => __( 'Редактировать категорию', 'mif-qm' ),
                'update_item' => __( 'Обновить категорию', 'mif-qm' ),
                'add_new_item' => __( 'Добавить новую категорию', 'mif-qm' ),
                'new_item_name' => __( 'Новое имя категории', 'mif-qm' ),
                'menu_name' => __( 'Категории тестов', 'mif-qm' ),
            ),
            'show_ui' => true,
            'query_var' => true,
            'rewrite' => array( 'slug' => 'quizzes' ),
        ) );

        register_post_type( 'quiz', array(
            'label'  => null,
            'labels' => array(
                'name'               => __( 'Тесты', 'mif-qm' ), // основное название для типа записи
                'singular_name'      => __( 'Тест', 'mif-qm' ), // название для одной записи этого типа
                'add_new'            => __( 'Создать тест', 'mif-qm' ), // для добавления новой записи
                'add_new_item'       => __( 'Создание теста', 'mif-qm' ), // заголовка у вновь создаваемой записи в админ-панели.
                'edit_item'          => __( 'Редактирование теста', 'mif-qm' ), // для редактирования типа записи
                'new_item'           => __( 'Новый тест', 'mif-qm' ), // текст новой записи
                'view_item'          => __( 'Посмотреть тест', 'mif-qm' ), // для просмотра записи этого типа.
                'search_items'       => __( 'Найти тест', 'mif-qm' ), // для поиска по этим типам записи
                'not_found'          => __( 'Тест не найден', 'mif-qm' ), // если в результате поиска ничего не было найдено
                'not_found_in_trash' => __( 'Не найдено в корзине', 'mif-qm' ), // если не было найдено в корзине
                'parent_item_colon'  => '', // для родителей (у древовидных типов)
                'menu_name'          => __( 'Тесты', 'mif-qm' ), // название меню
            ),
            'description'         => '',
            'public'              => true,
            'publicly_queryable'  => null,
            'exclude_from_search' => null,
            'show_ui'             => null,
            'show_in_menu'        => true, // показывать ли в меню адмнки
            'show_in_admin_bar'   => null, // по умолчанию значение show_in_menu
            'show_in_nav_menus'   => null,
            'show_in_rest'        => null, // добавить в REST API. C WP 4.7
            'rest_base'           => null, // $post_type. C WP 4.7
            'menu_position'       => 20,
            'menu_icon'           => 'dashicons-forms', 
            'capability_type'   => 'post',
            //'capabilities'      => 'post', // массив дополнительных прав для этого типа записи
            'map_meta_cap'      => true, // Ставим true чтобы включить дефолтный обработчик специальных прав
            'hierarchical'        => false,
            'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'revisions' ), // 'title','editor','author','thumbnail','excerpt','trackbacks','custom-fields','comments','revisions','page-attributes','post-formats'
            'taxonomies'          => array(),
            'has_archive'         => true,
            'rewrite'             => array( 'slug' => 'quiz' ),
            'query_var'           => true,

        ) );

        
        $process_snapshots = new mif_process_snapshots();
        $process_snapshots->post_types_init();

        $process_results = new mif_qm_process_results();
        $process_results->post_types_init();

        $members_core = new mif_qm_members_core();
        $members_core->post_types_init();

    }




}

?>