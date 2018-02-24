<?php

//
// Инит-файл плагина Quiz Master
// 
//


defined( 'ABSPATH' ) || exit;

include_once dirname( __FILE__ ) . '/core-core.php';
include_once dirname( __FILE__ ) . '/qm-core.php';
include_once dirname( __FILE__ ) . '/qm-screen.php';
include_once dirname( __FILE__ ) . '/qm-workroom.php';
include_once dirname( __FILE__ ) . '/qm-results.php';
include_once dirname( __FILE__ ) . '/qm-profile.php';

include_once dirname( __FILE__ ) . '/quiz-core.php';
include_once dirname( __FILE__ ) . '/quiz-screen.php';

include_once dirname( __FILE__ ) . '/members-core.php';
include_once dirname( __FILE__ ) . '/members-screen.php';

include_once dirname( __FILE__ ) . '/xml-core.php';
include_once dirname( __FILE__ ) . '/process-process.php';

include_once dirname( __FILE__ ) . '/process-snapshots.php';
include_once dirname( __FILE__ ) . '/process-results.php';




class mif_qm_init extends mif_qm_screen { 

    
    function __construct()
    {
        parent::__construct();

        add_filter( 'the_content', array( $this, 'add_quiz_content' ) );
        add_action( 'save_post_quiz', array( $this, 'delete_my_drafts' ) );
        // add_action( 'save_post_quiz_snapshot', array( $this, 'update_results' ), 10 );
        add_action( 'trashed_post', array( $this, 'update_results' ), 10 );

        // add_action( 'wp_ajax_mif-qm-quiz-submit', array( $this, 'ajax_quiz_submit' ) );
        add_action( 'wp_ajax_run', array( $this, 'ajax_quiz_submit' ) );
        add_action( 'wp_ajax_result', array( $this, 'ajax_quiz_submit' ) );
        add_action( 'wp_ajax_view', array( $this, 'ajax_quiz_submit' ) );
        add_action( 'wp_ajax_members', array( $this, 'ajax_quiz_submit' ) );

        add_action( 'wp_ajax_catalog', array( $this, 'ajax_catalog_submit' ) );
        // add_action( 'wp_ajax_members-manage', array( $this, 'ajax_members_manage' ) );


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
        
        // Установить текущую запись. Используется при обработке AJAX-запросов.
        
        if ( empty( $post ) ) {
            
            $post_id = (int) $_REQUEST['quiz_id'];
            $post = get_post( $post_id );
            
        }
        
        
        if ( $post->post_type == 'quiz' ) {

            // Если отображается тест

            // Без пароля - нельзя

            if ( ! is_user_logged_in() ) {
                
                $mif_qm_process_screen->alert( __( 'У вас нет прав доступа. Возможно, вам надо просто войти.', 'mif-qm' ), 'danger' );
                return false;
                
            }

            $content = $this->the_quiz();

        } elseif ( $post->post_type == 'page' ) {

            // Показывается страница
            
            if ( $post->post_name == $this->post_name_home ) {
                
                // Показывается страница с каталогом
                
                $this->the_home();
                // p($post);
                
            } elseif ( $post->post_name == $this->post_name_profile ) {
                
                // Показывается страница профиля

                $profile = new mif_qm_profile();
                $profile->the_profile();

            } elseif ( $post->post_name == $this->post_name_workroom ) {
                
                // Показывается страница мастерской

                $workroom = new mif_qm_workroom();
                $workroom->the_workroom();

            } elseif ( $post->post_name == $this->post_name_results ) {
                
                // Показывается страница всех результатов

                $results = new mif_qm_results();
                $results->the_results();

            } elseif ( $post->post_name == $this->post_name_help ) {
                
                // Показывается страница помощи

                // p($post);

            }


        }

        return $content;
    }



    // 
    // Показывает домашнюю страницу
    // 

    public function the_home()
    {

        global $mif_qm_screen;

        $mif_qm_screen = new mif_qm_screen();

        // Подключить шаблон из темы оформления или локальный

        if ( $template = locate_template( 'qm-home.php' ) ) {
           
            load_template( $template, false );

        } else {

            load_template( dirname( __FILE__ ) . '/../templates/qm-home.php', false );

        }
    }


    // 
    // Выводит всё, что связано со страницей теста
    // 

    public function the_quiz()
    {
        global $post;
        global $mif_qm_quiz_screen;
        global $mif_qm_process_screen;
        global $mif_qm_members_screen;

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



    // 
    // Точка входа для AJAX-запросов
    // 


    public function ajax_quiz_submit()
    {
        // p($_REQUEST);
        check_ajax_referer( 'mif-qm' );
        $this->add_quiz_content();
        wp_die();
    }



    // 
    // Точка входа для AJAX-запросов (главная страница)
    // 


    public function ajax_catalog_submit()
    {
        // p($_REQUEST);
        check_ajax_referer( 'mif-qm' );

        if ( isset( $_REQUEST['mode'] ) && $_REQUEST['mode'] == 'stat' ) {

            echo $this->get_catalog_stat();

        } else {

            echo $this->get_catalog();

        }

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