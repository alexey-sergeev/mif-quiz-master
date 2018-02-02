<?php

//
// Ядро плагина Quiz Master
// 
//


defined( 'ABSPATH' ) || exit;

include_once dirname( __FILE__ ) . '/core-core.php';
include_once dirname( __FILE__ ) . '/quiz-core.php';
include_once dirname( __FILE__ ) . '/quiz-screen.php';

include_once dirname( __FILE__ ) . '/xml-core.php';
include_once dirname( __FILE__ ) . '/process-core.php';



class mif_qm_core  {

        
    
    function __construct()
    {
        $this->post_types_init();

        add_filter( 'the_content', array( $this, 'add_quiz_content' ) );

    }

  

    // 
    // Иницализация типов записей
    // 

    private function post_types_init()
    {

        // Таксономия и тип записей - "Тест"


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
    
    }


    //
    // Вывод теста на страницах
    //

    public function add_quiz_content( $content )
    {
        global $post;
        global $mif_qm_quiz_screen;

        if ( $post->post_type == 'quiz' ) {

            $process_core = new mif_qm_process_core();

            if ( $process_core->get_action() == 'view' ) {

                $quiz_core = new mif_qm_quiz_core();
                $quiz = $quiz_core->parse();
                $mif_qm_quiz_screen = new mif_qm_quiz_screen( $quiz );
                $mif_qm_quiz_screen->show( array( 'action' => 'view' ) );

            } elseif ( $process_core->get_action() == 'run' ) {

                $quiz_core = new mif_qm_quiz_core();
                $quiz = $quiz_core->get_exemplar();
                $mif_qm_quiz_screen = new mif_qm_quiz_screen( $quiz );
                $mif_qm_quiz_screen->show( array( 'action' => 'run' ) );

            }

            return false;
        }


        return $content;
    }

}

?>