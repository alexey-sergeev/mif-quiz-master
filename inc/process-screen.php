<?php

//
// Экранные методы процесса выполнения теста
// 
//


defined( 'ABSPATH' ) || exit;

include_once dirname( __FILE__ ) . '/process-core.php';
include_once dirname( __FILE__ ) . '/process-templates.php';


class mif_qm_process_screen extends mif_qm_process_core {

    // Результат прохождения последнего теста

    private $result = array();


    private $result_list = array();
    private $quiz_id = NULL;
    private $quiz = array();


    function __construct()
    {
        parent::__construct();
    }


    
    // 
    // Выводит страницу начала теста
    // 

    public function the_startpage( $quiz_id = NULL )
    {
        $this->quiz_id = $quiz_id;

        $user_id = false; //!!!

        $process_results = new mif_qm_process_results();
        $this->result = $process_results->get_current_result( $quiz_id );

        $quiz_core = new mif_qm_quiz_core();
        $this->quiz = $quiz_core->parse( $quiz_id );

        // Подключить шаблон из темы оформления или локальный

        if ( $template = locate_template( 'start.php' ) ) {
           
            load_template( $template, false );

        } else {

            load_template( dirname( __FILE__ ) . '/../templates/start.php', false );

        }    

    }


    
    // 
    // Выводит список результатов
    // 

    public function the_result_list( $result_list = array() )
    {

        $this->result_list = $result_list;

        // Подключить шаблон из темы оформления или локальный

        if ( $template = locate_template( 'result-list.php' ) ) {
           
            load_template( $template, false );

        } else {

            load_template( dirname( __FILE__ ) . '/../templates/result-list.php', false );

        }    

    }    


    
    // 
    // Выводит результат
    // 

    public function the_result( $result = array() )
    {

        $this->result = $result;

        // Подключить шаблон из темы оформления или локальный

        if ( $template = locate_template( 'result.php' ) ) {
           
            load_template( $template, false );

        } else {

            load_template( dirname( __FILE__ ) . '/../templates/result.php', false );

        }    

    }    


    
    // 
    // Возвращает список результатов
    // 
    
    public function get_result_list( $result_list = NULL )
    {
        $out = '';

        if ( $result_list ) $this->result_list = $result_list;

        // Если нет результатов, то выйти

        if ( empty( $this->result_list ) ) {

            $out .= $this->alert( __( 'Результатов пока ещё нет', 'mif-qm'), 'warning' );
            return apply_filters( 'mif_qm_process_screen_get_result_list_empty', $out, $this->result_list );

        }

        $user_token = $this->get_user_token();

        $title = ( count( $this->result_list ) == 1 && isset( $this->result_list[$user_token] ) ) ? __( 'Ваши результаты', 'mif-qm') : __( 'Все результаты', 'mif-qm');
        
        $out .= '<div class="mt-5 text-center"><h3>' . $title . '</h3></div>';

        $class = ( count( $this->result_list ) == 1 ) ? ' all' : '';

        $out .= '<div class="result_list container' . $class . '">';
        
        $out .= '<div class="p-2 pr-0 text-right">';
        $out .= '<a href="#" class="show-current">' . __( 'актуальные', 'mif-qm') . '</a>';
        $out .= '<span class="show-all">' . __( 'актуальные', 'mif-qm') . '</span>';
        $out .= ' | ';
        $out .= '<a href="#" class="show-all">' . __( 'все', 'mif-qm') . '</a>';
        $out .= '<span class="show-current">' . __( 'все', 'mif-qm') . '</span>';
        $out .= '</div>';

        foreach ( (array) $this->result_list as $owner => $item ) {
            
            $fio = ( $owner ) ? $this->get_display_name( $owner ) : 'anonymous';
            $out .= '<div class="row bg-light p-2 font-weight-bold">';
            $out .= '<div>' . $fio . '</div>';
            $out .= '</div>';

            $out .= $this->get_owner_results( $owner );
            
        }

        $out .= '</div>';

        // p($this->result_list);

        return apply_filters( 'mif_qm_process_screen_get_result_list', $out, $this->result_list );
    }



    // 
    // Возвращает список результатов конкретного пользователя
    // 
    
    public function get_owner_results( $owner )
    {
        if ( ! isset( $this->result_list[$owner] ) ) return;

        $out = '';

        // Сортировка по времени

        $index = array();

        foreach ( (array) $this->result_list[$owner] as $key => $result ) {

            // $index[ $this->get_timestamp( $result['time'] ) ] = $key;
            $index[$key] = $this->get_timestamp( $result['time'] );

        }

        arsort( $index, SORT_NUMERIC );

        // Сформировать вывод

        // foreach ( (array) $this->result_list[$owner] as $result ) {
        foreach ( $index as $key => $value ) {

            $result = $this->result_list[$owner][$key];

            $marker = '';
            $class = '';
            $class_bg = '';
            $class_pb = '';
            $current = ' current';
            
            if ( $result['success'] == 'yes' ) {
                
                $marker = '<i class="fas fa-check text-success"></i>';
                $class = ' text-success';
                $class_bg = ' alert-success';
                // $class_pb = ' bg-success';
                
            } elseif ( $result['success'] == 'no' ) {
                
                $marker = '<i class="fas fa-times text-danger"></i>';
                $class = ' text-danger';
                $class_bg = ' alert-danger';
                // $class_pb = ' bg-danger';
                
            }
            
            if ( ! ( isset( $result['current'] ) && $result['current'] == 'yes' ) ) {
                
                $current = ' all';
                // $class = ' text-muted';
                // $class_bg = ' bg-light';
                $marker = '';

            }

            // $link = '?action=results&id=' . $result['snapshot'];
            $link = '<a href="?action=result&id=' . $result['snapshot'] . '"><i class="fas fa-external-link-alt"></i></a>';

            if ( isset( $result['average'] ) && $result['average'] == 'yes' ) {

                $link = '<span title="' . __( 'Средний результат по всем попыткам', 'mif-qm' ) . '"><i class="fas fa-calculator"></i></span>';

            }
            
            $out .= '<div class="row' . $current . '">';
            $out .= '<div class="col-1 mt-2 mb-2 text-center">' . $marker . '</div>';
            $out .= '<div class="col-3 pt-1 pl-0 pr-0 mt-2"><div class="progress"><div class="progress-bar' . $class_pb . '" role="progressbar" style="width: ' . $result['percent'] . '%" aria-valuemin="0" aria-valuemax="100"></div></div></div>';
            $out .= '<div class="col-2 pl-0 pr-0 mt-2 mb-2 text-center' . $class . '">' . $result['rating'] . ' ' . __( 'из', 'mif-qm' ) . ' ' . $result['max'] . ' (' . $result['percent'] . '%)</div>';
            // $out .= '<div class="col-2 mt-2 mb-2' . $class . '">' . $result['rating'] . '/' . $result['max'] . ' (' . $result['percent'] . '%)</div>';
            $out .= '<div class="col-3 mt-2 mb-2 pl-0 pr-0 text-center' . $class_bg . '">' . $this->get_time_str( $result['time'] ) . '</div>';
            $out .= '<div class="col-2 mt-2 mb-2 pl-0 pr-0 text-center">' . $this->get_duration_str( $result['duration'] ) . '</div>';
            
            if ( $this->user_can( 'view-result', $result['snapshot'] ) ) $out .= '<div class="col-1 mt-2 mb-2 text-center">' . $link . '</div>';
            
            $out .= '</div>';
            
        }

        return apply_filters( 'mif_qm_process_screen_get_owner_results', $out, $owner, $this->result_list );
    }


    
    // 
    // Возвращает конкретное значение элемента результата
    // 
    
    public function get_result( $key = false )
    {
        $result = ( isset( $this->result[$key] ) ) ? $this->result[$key] : '';
        return apply_filters( 'mif_qm_process_screen_get_result', $result, $key, $this->result );
    }


    // 
    // Возвращает текст успеха
    // 
    
    public function get_successed_caption()
    {
        $out = '';

        if ( isset( $this->result['success'] ) ) {

            $success = $this->result['success'];

            if ( $success == 'yes' )  {

                $out = '<span class="mr-2 text-success"><i class="fa fa-check" aria-hidden="true"></i></span><span class="text-success">' . __( 'Тест пройден', 'mif-qm') .'</span>';
                
            } elseif ( $success == 'no' ) {
                
                $out = '<span class="mr-2 text-danger"><i class="fa fa-times" aria-hidden="true"></i></span><span class="text-danger">' . __( 'Тест не пройден', 'mif-qm') . '</span>';
            }

        }

        return apply_filters( 'mif_qm_process_screen_get_successed_caption', $out, $this->result );
    }

    

    // 
    // Возвращает панель с пояснениями на странице начала теста
    // 
    
    public function get_stat_panel()
    {
        $param_screen = new mif_qm_param_screen();
        $out = $param_screen->get_stat_panel( $this->quiz );

        return apply_filters( 'mif_qm_process_screen_get_stat_panel', $out, $this->quiz );
    }

    

    // 
    // Возвращает панель с результатами
    // 
    
    public function get_result_panel( $result = NULL )
    {
        if ( $result ) $this->result = $result;

        if ( empty( $this->result ) ) return;

        $out = '';
    
        // $out .= '<div class="bg-light text-center">'; 

        $out .= '<div class="p-3 m-3 h2">'; 
        $out .= $this->get_result( 'rating' ) . ' ' . __( 'из', 'mif-qm') . ' ' . $this->get_result( 'max' ) . ' (' . $this->get_result( 'percent' ) . '%)';
        $out .= '</div>';

        $class = ' bg-success';
        if ( isset( $this->result['success'] ) && $this->result['success'] == 'no' ) $class = ' bg-danger';

        $out .= '<div class="row justify-content-center"><div class="progress w-50 " style="height: 1.5rem;">';
        $out .= '<div class="progress-bar' . $class . '" role="progressbar" style="width: ' . $this->get_result( 'percent' ) . '%" aria-valuemin="0" aria-valuemax="100"></div></div>';
        $out .= '</div>';
        
        $out .= '<div class="m-4 pb-4 h4">' . $this->get_successed_caption() . '</div>';
        
        // $out .= '</div>';

        return apply_filters( 'mif_qm_process_screen_get_result_panel', $out, $this->quiz );
    }


    

    // 
    // Возвращает ссылку на тест
    // 
    
    public function get_result_back()
    {
        $out = '';

        $user_id = false; // !!! Здесь думать, где брать для анонимных
        $user_token = $this->get_user_token( $user_id );

        if ( ! ( count( $this->result_list ) == 1 && isset( $this->result_list[$user_token] ) ) ) return;

        // Делать ссылку, если показывается результат только одного пользователя - текущего

        $out .= '<div class="p-2 mt-5 mb-3 text-center bg-light">';
        $out .= '<a href="' . get_permalink( $this->quiz_id ). '" class="font-weight-bold">' . __( 'Вернуться к тесту', 'mif-qm') . '</a>';
        $out .= '</div>';

        return apply_filters( 'mif_qm_process_screen_get_result_back', $out );
    }


    

    // 
    // Возвращает ссылку на результаты теста
    // 
    
    public function get_result_link()
    {
        global $post;
        $quiz_id = $post->ID; // !!! Думать
        $user_id = false; // !!! Здесь думать, где брать для анонимных
        $user_token = $this->get_user_token( $user_id );

        $process = new mif_qm_process_process( $quiz_id );
        $result_list = $process->get_result_list( $this->get_user_token() );

        // Если нет результатов, то ссылку не выводить

        if ( ! isset( $result_list[$user_token] ) ) return;
        if ( ! count( $result_list[$user_token] ) > 0 ) return;

        $out = '';
        
        if ( $this->is_result( $quiz_id, $user_id ) ) {

            $out .= '<div class="pt-3 pb-4"><hr /></div>';
            $out .= '<a href="?action=result&user=' . $user_token . '" class="font-weight-bold">' . __( 'Ваши результаты', 'mif-qm') . '</a>';

        }

        return apply_filters( 'mif_qm_process_screen_get_result_link', $out, $this->result );
    }

    

    // 
    // Возвращает кнопку начала теста
    // 
    
    public function get_start_button()
    {
        
        global $post;
        $quiz_id = $post->ID; // !!! думать, как сделать для шорткодов
        $user_id = false; // !!! Здесь думать, где брать для анонимных

        $members_core = new mif_qm_members_core();
        $status = $members_core->member_status( $quiz_id, $user_id );
        $level = $members_core->member_level( $status );
        $access_mode = $members_core->get_access_mode( $quiz_id );

        // Фраза о количестве попыток и др.

        $count = $this->get_attempt_count( $quiz_id, $user_id );
        $caption_count = $this->get_count_caption( $count );
        $caption_mode = $this->get_results_mode();

        $captions_arr = array();
        if ( $caption_count ) $captions_arr[] = $caption_count;
        if ( $caption_mode ) $captions_arr[] = $caption_mode;

        $captions = '';
        $captions = implode( '<br />', $captions_arr );

        // p($status);
        // p($access_mode);

        $out = '';
        $button = '';
        $caption = '';

        // Тест закрытый

        if ( $access_mode =='closed' ) {
            
            if ( $level > 1 ) {
                
                // Пользователь - эксперт или выше

                $button = __( 'Начать тестирование', 'mif-qm');

            } else {
                
                // Пользователь - студент или ниже
                
                $caption .= '<p>' . __( 'В настоящее время доступ к тесту закрыт.', 'mif-qm') . '</p>';
                $caption .= '<p>' . __( 'Cвяжитесь со своим преподавателем, если не считаете это правильным.', 'mif-qm') . '</p>';
                
            }
            
        } 

        // Доступ по спискам

        if ( $access_mode =='memberlist' ) {
            
            if ( $level > 0 ) {
              
                // Пользователь - студент или выше

                $button = __( 'Начать тестирование', 'mif-qm');
                if ( $captions ) $caption .= '<p>' . $captions . '</p>';
                // if ( $caption_count ) $caption .= '<p>' . $caption_count . '</p>';
                // if ( $caption_mode ) $caption .= '<p>' . $caption_mode . '</p>';

            } else {
                
                // Пользователь - никто

                // !!! Здесь проверять возможность инвайтов

                $invite = false;

                if ( $invite ) {

                    // !!! Думать, как тут поступить

                } else {

                    $caption .= '<p>' . __( 'Вы не имеете доступа к данному тесту.', 'mif-qm') . '</p>';
                    $caption .= '<p>' . __( 'Cвяжитесь со своим преподавателем, если не считаете это правильным.', 'mif-qm') . '</p>';
                    

                }

            }
            
        } 

        // Доступ по заявкам

        if ( $access_mode =='request' ) {
            
            if ( $level > 0 ) {
              
                // Пользователь - студент или выше

                $button = __( 'Начать тестирование', 'mif-qm');
                if ( $captions ) $caption .= '<p>' . $captions . '</p>';
                // if ( $caption_count ) $caption .= '<p>' . $caption_count . '</p>';
                // if ( $caption_mode ) $caption .= '<p>' . $caption_mode . '</p>';

            } else {
                
                // Пользователь - никто

                // !!! Здесь проверять возможность инвайтов
                // !!! Думать, как это учитывать в заявках

                $invite = false;

                if ( $status == 'request' ) {
                    
                    // Пользователь ранее отправлял заявку
                    
                    $caption .= '<p>' . __( 'Ваша заявка отправлена', 'mif-qm') . '</p>';
                    
                } else {
                    
                    $caption .= '<p>' . __( 'Вы сможете пройти тест после подтверждения заявки.', 'mif-qm') . '</p>';

                }
                
                $button = __( 'Отправить заявку', 'mif-qm');
                
            }
            
        } 

        // Открытый доступ

        if ( $access_mode =='open' ) {
            
            $button = __( 'Начать тестирование', 'mif-qm');
            if ( $captions ) $caption .= '<p>' . $captions . '</p>';
            // if ( $caption_count ) $caption .= '<p>' . $caption_count . '</p>';
            // if ( $caption_mode ) $caption .= '<p>' . $caption_mode . '</p>';
            
        } 

        // $process_results = new mif_qm_process_results();
        // $result = $process_results->get_current_result( $quiz_id, $user_id );

        // if ( $result ) {

        //     $out .= $this->get_result_panel( $result );
        //     $out .= '<div class="pt-5 pb-5"><hr /></div>';

        // }


        // Показать кнопку или сообщение
        
        if ( $button ) {

            // Показать кнопку

            if ( empty( $caption ) ) $caption = '<br />';
            
            $out .= '<div class="mt-3">';

            if ( $count != 0 ) {
                
                $out .= '<form method="post">';
                $out .= '<input type="hidden" name="quiz_id" value="' . $quiz_id . '">';
                $out .= '<input type="hidden" name="action" value="run">';
                $out .= '<input type="hidden" name="start" value="yes">';
                $out .= '<button class="btn-primary btn-lg">' . $button . '</button>';
                $out .= '<input type="hidden" name="_wpnonce" value="' . wp_create_nonce( 'mif-qm' ) . '" />';
                $out .= '</form>';
                
            }   
            
            $out .= '<div class="mt-4 font-italic">' . $caption . '</div>';
            $out .= '</div>';

        } else {

            // Показать сообщение

            $out .= '<div class="mt-3 pb-3">';
            $out .= $caption;
            $out .= '</div>';

        }

        return apply_filters( 'mif_qm_process_screen_get_start_button', $out );
    }



    // 
    // Возвращает кнопку результата
    // 
    
    public function get_result_button()
    {
        $out = '';

        $quiz_id = ( isset( $this->result['quiz'] ) ) ? $this->result['quiz'] : false;
        $user_id = false; // !!! Здесь думать, где брать для анонимных
        $count = $this->get_attempt_count( $quiz_id, $user_id );
        $caption = $this->get_count_caption( $count );

        global $post;
        $quiz_id = $post->ID; // !!! Здесь думать, где брать для шорткода

        if ( $count > 0 ) {
            
            $out .= '<form method="post">';
            $out .= '<input type="hidden" name="quiz_id" value="' . $quiz_id . '">';
            $out .= '<input type="hidden" name="action" value="run">';
            $out .= '<button class="btn-primary btn-lg">' . __( 'Пройти еще раз', 'mif-qm') . '</button>';
            $out .= '<input type="hidden" name="_wpnonce" value="' . wp_create_nonce( 'mif-qm' ) . '" />';
            $out .= '</form>';
            
        } 

        $out .= '<div class="mt-3 font-italic">' . $caption . '</div>';

        return apply_filters( 'mif_qm_process_screen_get_result_button', $out, $caption, $this->result );
    }



    // 
    // Возвращает сообщение о количестве попыток
    // 

    public function get_count_caption( $count )
    {
        $caption = '';

        if ( $count === -1 ) {

            // Ничего не делать

        } elseif ( $count === 0 ) {

            $caption .= __( 'все попытки закончилось', 'mif-qm' );
            
        } elseif ( $count === 1 ) {

            $caption .= __( 'осталась одна попытка', 'mif-qm' );
            
        } elseif ( $count === 2 ) {
            
            $caption .= __( 'осталось две попытки', 'mif-qm' );
            
        } else {
            
            $caption .= __( 'есть несколько попыток', 'mif-qm' );

        }

        return apply_filters( 'mif_qm_process_screen_get_count_caption', $caption, $count );
    }



    // 
    // Возвращает сообщение о способе подсчета результата
    // 

    public function get_results_mode()
    {
        $caption = '';

        if ( $this->is_param( 'better', $this->quiz ) ) $caption .= __( 'учитывается лучший результат', 'mif-qm' );
        if ( $this->is_param( 'latest', $this->quiz ) ) $caption .= __( 'учитывается последний результат', 'mif-qm' );
        if ( $this->is_param( 'average', $this->quiz ) ) $caption .= __( 'учитывается средний результат', 'mif-qm' );

        return apply_filters( 'mif_qm_process_screen_get_results_mode', $caption );
    }

    

    // 
    // Возвращает класс успеха
    // 
    
    public function get_successed_class()
    {
        $class = 'success';
        if ( isset( $this->result['success'] ) && $this->result['success'] == 'no' ) $class = 'danger';
        return apply_filters( 'mif_qm_process_screen_get_successed_class', $class, $this->result );
    }


    // 
    // Возвращает класс меню
    // 
    
    public function get_menu_class( $action = 'view', $class = '', $flag = true )
    {
        // $process_core = new mif_qm_process_core();
    
        // if ( $process_core->get_action() === $action ) {
        if ( $this->get_action() === $action ) {
    
            $res1 = ' ' . $class;
            $res2 = '';
            
        } else {
            
            $res1 = '';
            $res2 = ' ' . $class;
    
        }
    
        $out = ( $flag ) ? $res1 : $res2;

        return apply_filters( 'mif_qm_process_screen_get_menu_class', $out );
    }
    

            
    // 
    // Возвращает меню теста
    // 
    
    public function get_quiz_menu()
    {
        global $post;

        $menu = '';
        $class = 'bg-secondary text-light align-middle';
        
        $members_core = new mif_qm_members_core();
        $access_level = $members_core->access_level( $this->quiz_id );

        // if ( mif_qm_user_can( 'edit-quiz' ) ) {
        if ( mif_qm_access_level() > 1 ) {

            $menu .= '<div class="btn-group mt-3 mb-3 quiz-menu" role="group">';
            $menu .= '<a class="btn btn-outline-light pt-2' . $this->get_menu_class( 'run', 'bg-light' ) . '" href="?action=run"><span class="' . $class . $this->get_menu_class( 'run', 'current' ) . '"><i class="fas fa-play"></i></span><br /><small>' . __( 'Тестирование', 'mif-qm' ) . '</small></a>';
            if ( mif_qm_access_level() > 3 ) $menu .= '<a class="btn btn-outline-light pt-2' . $this->get_menu_class( 'edit', 'bg-light' ) . '" href="' . get_edit_post_link( $post->ID ) . '"><span class="' . $class . $this->get_menu_class( 'edit', 'current' ) . '"><i class="fas fa-pencil-alt"></i></span><br /><small>' . __( 'Редактор', 'mif-qm' ) . '</small></a>';
            $menu .= '<a class="btn btn-outline-light pt-2' . $this->get_menu_class( 'view', 'bg-light' ) . '" href="?action=view"><span class="' . $class . $this->get_menu_class( 'view', 'current' ) . '"><i class="fas fa-check"></i></span><br /><small>' . __( 'Просмотр', 'mif-qm' ) . '</small></a>';
            $menu .= '<a class="btn btn-outline-light pt-2' . $this->get_menu_class( 'result', 'bg-light' ) . '" href="?action=result"><span class="' . $class . $this->get_menu_class( 'result', 'current' ) . '"><i class="fas fa-chart-bar"></i></span><br /><small>' . __( 'Результаты', 'mif-qm' ) . '</small></a>';
            $menu .= '<a class="btn btn-outline-light pt-2' . $this->get_menu_class( 'members', 'bg-light' ) . '" href="?action=members"><span class="' . $class . $this->get_menu_class( 'members', 'current' ) . '"><i class="far fa-user"></i></span><br /><small>' . __( 'Пользователи', 'mif-qm' ) . '</small></a>';
            $menu .= '<a class="btn btn-outline-light pt-2' . $this->get_menu_class( 'invites', 'bg-light' ) . '" href="?action=invites"><span class="' . $class . $this->get_menu_class( 'invites', 'current' ) . '"><i class="far fa-list-alt"></i></span><br /><small>' . __( 'Приглашения', 'mif-qm' ) . '</small></a>';
            // $menu .= '<a class="btn btn-outline-light pt-2' . $this->get_menu_class( 'settings', 'bg-light' ) . '" href="?action=settings"><span class="' . $class . $this->get_menu_class( 'settings', 'current' ) . '"><i class="fas fa-cogs"></i></span><br /><small>' . __( 'Настройки', 'mif-qm' ) . '</small></a>';
            $menu .= '</div>';
        }

        return apply_filters( 'mif_qm_process_screen_get_quiz_menu', $menu );
    }


    //
    // Окно входа на сайт
    //

    public function login_form( $caption )
    {
        $out = '';

        $out .= '<div class="p-5 mt-4 mb-4 bg-light text-center">';
        $out .= '<p>' . $caption . '</p>';
        $out .= '<p class="p-2"><form action="' . wp_login_url() . '">';
        $out .= '<input type="hidden" name="redirect_to" value="' . get_permalink() . '">';
        $out .= '<button class="btn btn-lg noajax">' . __( 'Войти', 'mif-qm' ) . '</button></form></p><br />';
        $out .= '</div>';

        echo apply_filters( 'mif_qm_process_screen_alert', $out, $caption, $class );

    }


    //
    // Окно сообщения
    //

    public function alert( $caption, $class = 'info' )
    {
        $out = '';

        $out .= '<div class="alert alert-' . $class . '" role="alert">';
        $out .= $caption;
        $out .= '</div>';

        echo apply_filters( 'mif_qm_process_screen_alert', $out, $caption, $class );

    }


}

?>