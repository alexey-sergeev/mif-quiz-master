<?php

//
// Ядро плагина Quiz Master
// 
//


defined( 'ABSPATH' ) || exit;

include_once dirname( __FILE__ ) . '/core-core.php';

include_once dirname( __FILE__ ) . '/quiz-core.php';
include_once dirname( __FILE__ ) . '/quiz-screen.php';

include_once dirname( __FILE__ ) . '/members-core.php';
include_once dirname( __FILE__ ) . '/members-screen.php';

include_once dirname( __FILE__ ) . '/xml-core.php';
include_once dirname( __FILE__ ) . '/process-process.php';

include_once dirname( __FILE__ ) . '/process-snapshots.php';
include_once dirname( __FILE__ ) . '/process-results.php';




class mif_qm_init extends mif_qm_core_core { 

        
    
    function __construct()
    {
        parent::__construct();

        $this->post_types_init();

        add_filter( 'the_content', array( $this, 'add_quiz_content' ) );
        add_action( 'save_post_quiz', array( $this, 'delete_my_drafts' ) );
        // add_action( 'save_post_quiz_snapshot', array( $this, 'update_results' ), 10 );
        add_action( 'trashed_post', array( $this, 'update_results' ), 10 );

        // add_action( 'wp_ajax_mif-qm-quiz-submit', array( $this, 'ajax_quiz_submit' ) );
        add_action( 'wp_ajax_run', array( $this, 'ajax_quiz_submit' ) );
        add_action( 'wp_ajax_result', array( $this, 'ajax_quiz_submit' ) );
        add_action( 'wp_ajax_view', array( $this, 'ajax_quiz_submit' ) );
        add_action( 'wp_ajax_members', array( $this, 'ajax_quiz_submit' ) );
        // add_action( 'wp_ajax_members-manage', array( $this, 'ajax_members_manage' ) );


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


    //
    // Вывод теста на страницах
    //

    public function add_quiz_content( $content = '' )
    {
        global $post;
        global $mif_qm_quiz_screen;
        global $mif_qm_process_screen;
        global $mif_qm_members_screen;

        $mif_qm_process_screen = new mif_qm_process_screen();
        
        if ( ! is_user_logged_in() ) {
            
            $mif_qm_process_screen->alert( __( 'У вас нет прав доступа. Возможно, вам надо просто войти.', 'mif-qm' ), 'danger' );
            return false;
            
        }

        
        if ( empty( $post ) ) {
            
            $post_id = (int) $_REQUEST['quiz_id'];
            $post = get_post( $post_id );
            
        }
        
        
        if ( $post->post_type == 'quiz' ) {
            
            echo '<div id="mif-qm-ajax-container">';

            $process = new mif_qm_process_process( $post->ID );
            $action = $process->get_action();
            
            if ( $action == 'view' ) {

                // Просмотр теста
                
                if ( mif_qm_user_can( 'view-quiz', $post->ID ) ) {

                    $quiz_core = new mif_qm_quiz_core();
                    $quiz = $quiz_core->parse( $post->ID );
                    
                    $mif_qm_quiz_screen = new mif_qm_quiz_screen( $quiz );
                    $mif_qm_quiz_screen->show( array( 'action' => 'view' ) );

                } else {

                    $mif_qm_process_screen->alert( __( 'У вас нет прав доступа', 'mif-qm' ), 'danger' );

                }
                
            } elseif ( $action == 'run' ) {
                
                // Процесс прохождения теста

                $process = new mif_qm_process_process( $post->ID );
                $quiz_stage = $process->get_quiz_stage();
                
                if ( is_numeric( $quiz_stage ) ) {
                    
                    $arr = array(   '-1' => __( 'Страница начала теста', 'mif-qm' ),
                                    '0' => __( 'Тест завершен', 'mif-qm' ),
                                    '1' => __( 'Закончилось число попыток прохождения теста', 'mif-qm' ),
                                    '2' => __( 'Что-то пошло не так', 'mif-qm' ) );
                    
                    if ( $quiz_stage === -1 ) {

                        // Показать страницу с кнопкой начала теста

                        $mif_qm_process_screen->the_startpage( $post->ID );

                    } elseif ( $quiz_stage === 0 ) {
                        
                        // Тест завершен
                        
                        $result = $process->get_result( array( 'quiz' => $post->ID ) );
                        
                        if ( $result ) {
                            
                            $mif_qm_process_screen->the_result( $result );
                            
                        } else {
                            
                            $mif_qm_process_screen->alert( __( 'Что-то пошло не так', 'mif-qm' ) . ' (code: 1)', 'danger' );
                            
                        }
                        
                    } elseif ( $quiz_stage === 1 ) {

                        // Закончилось число попыток прохождения теста
                        
                        $mif_qm_process_screen->alert( $arr[$quiz_stage], 'warning' );
                        
                    } else {

                        // Что-то пошло не так
                        
                        $mif_qm_process_screen->alert( __( 'Что-то пошло не так', 'mif-qm' ) . ' (code: 2)', 'danger' );
                        
                    }
                    
                } else {

                    // Показать очередную порцию ворпосов испытуемому

                    $mif_qm_quiz_screen = new mif_qm_quiz_screen( $quiz_stage );
                    $mif_qm_quiz_screen->show( array( 'action' => 'run' ) );

                }

            } elseif ( $action == 'result' ) {
                
                // Смотрим результаты
                    
                if ( isset( $_REQUEST['id'] ) ) {

                    // Анализ конкретного теста

                    $result_id = (int) $_REQUEST['id'];
                    
                    if ( $this->user_can( 'view-result', $result_id ) ) {
                        
                        $result = $process->get_quiz( $result_id );
                        
                        $mif_qm_quiz_screen = new mif_qm_quiz_screen( $result );
                        $mif_qm_quiz_screen->show( array( 'action' => 'result' ) );
                        
                    } else {

                        $mif_qm_process_screen->alert( __( 'Доступ ограничен', 'mif-qm' ), 'danger' );

                    }

                } else {

                    // Список всех результатов теста
                    
                    $user_token = ( isset( $_REQUEST['user'] ) ) ? sanitize_key( $_REQUEST['user'] ) : NULL;

                    $result_list = $process->get_result_list( $user_token );
                    $mif_qm_process_screen->the_result_list( $result_list );

                }    
               
            } elseif ( $action == 'members' ) {

                // Страница управления пользователями

                $mif_qm_members_screen = new mif_qm_members_screen( $post->ID );
                $mif_qm_members_screen->the_members();


            }

            echo '</div>';

            return false;
        }

        return $content;
    }



    // // 
    // // Управление пользователями
    // // 


    // public function ajax_members_manage()
    // {
    //     p($_REQUEST);
    //     check_ajax_referer( 'mif-qm' );
    //     // $this->add_quiz_content();
    //     wp_die();
    // }



    // 
    // Удаляет черновики результатов пользователя если он меняет сам тест
    // 


    public function ajax_quiz_submit()
    {
        // p($_REQUEST);
        check_ajax_referer( 'mif-qm' );
        $this->add_quiz_content();
        wp_die();
    }



    // 
    // Удаляет черновики результатов пользователя если он меняет сам тест
    // 

    public function delete_my_drafts( $quiz_id )
    {

        $result_args = array(
            'numberposts' => -1,
            'post_type'   => 'quiz_snapshot',
            'post_status' => 'draft',
            // 'author'      => get_current_user_id(),
            'meta_key'    => 'owner',
            'meta_value'  => $this->get_user_token( get_current_user_id() ),
            'post_parent' => $quiz_id,
        );
    
        $results = get_posts( $result_args );

        foreach ( (array) $results as $result ) {

            $process_snapshots = new mif_process_snapshots();
            $process_snapshots->trash( $result->ID );
            // wp_trash_post( $result->ID );

        }

    }


    // 
    // Обновляет общие результаты пользователя, если в них что-то менялось
    // 

    public function update_results( $snapshot_id )
    {
        // Если не из админки, то ничего не делать
        
        // if ( ! is_admin() ) return;

        $snapshot = get_post( $snapshot_id );
        
        // Если это не снимок, то ничего не делать

        if ( ! ( $snapshot->post_type == 'quiz_snapshot' ) ) return;

        // Если менялся черновик, то ничего не делать

        if ( $snapshot->post_status == 'draft' ) return;

        $quiz_id = $snapshot->post_parent;

        $owner = get_post_meta( $snapshot_id, 'owner', true );
        $user_id = $this->get_user_id( $owner );

        // Обновить данные для пользователя и теста
        // Без новых данных, но с указанием всё пересчитать

        $process_results = new mif_qm_process_results();
        $process_results->update( array(), $quiz_id, $user_id, true );

    }


}

?>