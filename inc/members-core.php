<?php

//
// Ядро системы пользователей
// 
//


defined( 'ABSPATH' ) || exit;



class mif_qm_members_core extends mif_qm_core_core  { 

    // Описание ролей пользователей
    
    private $roles = array();
    
    // Режим доступа по умолчанию
    //      Все режимы доступа
    //          open - любой пользователь может пройти тест
    //          request - тест окрывается только после подтверждения пользователем
    //          memberlist - тест открыт только тем, кого тьютор добавил в список

    private $access_mode_default = 'request';
    
    function __construct()
    {
        parent::__construct();

        $this->roles = apply_filters( 'mif-qm-members_roles', array(

            // 'none'      => array(
            //                 'level' => 0,
            //                 'description' => __( 'Нет доступа', 'mif-qm' )
            //             ),
            'student'   => array(
                            'level' => 1,
                            'name' => __( 'Учащиеся', 'mif-qm' ),
                            'description' => __( 'Прохождение теста, просмотр своих результатов', 'mif-qm' )
                        ),
            'expert'    => array(
                            'level' => 2,
                            'name' => __( 'Эксперты', 'mif-qm' ),
                            'description' => __( 'Просмотр всех материалов', 'mif-qm' )
                        ),
            'assistant' => array(
                            'level' => 3,
                            'name' => __( 'Ассистенты', 'mif-qm' ),
                            'description' => __( 'Проверка ответов пользователей', 'mif-qm' )
                        ),
            'tutor'     => array(
                            'level' => 4,
                            'name' => __( 'Тьюторы', 'mif-qm' ),
                            'description' => __( 'Управление пользователями и ответами', 'mif-qm' )
                        ),
            'master'    => array(
                            'level' => 5,
                            'name' => __( 'Преподаватели', 'mif-qm' ),
                            'description' => __( 'Редактирование теста', 'mif-qm' )
                        ),
        ) );

    }


    // 
    // Иницализация типов записей
    // 

    public function post_types_init()
    {
    
        // 
        // Тип записей - "Пользователь"
        // 

        register_post_type( 'quiz_members', array(
            'label'  => null,
            'labels' => array(
                'name'               => __( 'Пользователи тестов', 'mif-qm' ), // основное название для типа записи
                'singular_name'      => __( 'Пользователи', 'mif-qm' ), // название для одной записи этого типа
                'add_new'            => __( 'Создать пользователей', 'mif-qm' ), // для добавления новой записи
                'add_new_item'       => __( 'Создание пользователей', 'mif-qm' ), // заголовка у вновь создаваемой записи в админ-панели.
                'edit_item'          => __( 'Редактирование пользователей', 'mif-qm' ), // для редактирования типа записи
                'new_item'           => __( 'Новые пользователи', 'mif-qm' ), // текст новой записи
                'view_item'          => __( 'Посмотреть пользователей', 'mif-qm' ), // для просмотра записи этого типа.
                'search_items'       => __( 'Найти пользователей', 'mif-qm' ), // для поиска по этим типам записи
                'not_found'          => __( 'Пользователи не найдены', 'mif-qm' ), // если в результате поиска ничего не было найдено
                'not_found_in_trash' => __( 'Не найдено в корзине', 'mif-qm' ), // если не было найдено в корзине
                'parent_item_colon'  => '', // для родителей (у древовидных типов)
                'menu_name'          => __( 'Пользователи', 'mif-qm' ), // название меню
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
            'rewrite'             => array( 'slug' => 'quiz_members' ),
            'query_var'           => true,

        ) );
    }

    

    // 
    // Получить списки ролей
    // 

    public function get_roles()
    {
        return $this->roles;
    }



    // 
    // Получить список пользователей
    // 

    public function get( $quiz_id = false )
    {
        $quiz_id = $this->get_quiz_id( $quiz_id );

        if ( ! $arr = wp_cache_get( 'mif_qm_members', $quiz_id ) ) {

            $arr = array();
            // $quiz_members_id = NULL;

            // Получить данные из базы данных
            
            $data = $this->get_members_data( $quiz_id );
            
            // Получить массив пользователей
            
            if ( isset( $data->ID ) ) {

                // $quiz_members_id = $data->ID;
                $arr = $this->to_array( $data->post_content );

            }

            // Автор теста всегда является master'ом. Добавить его, если надо

            $quiz_author = $this->get_quiz_author( $quiz_id );
            
            if ( isset( $arr[$quiz_author]  ) ) {
                
                $arr[$quiz_author]['role'] = 'master';
                
            } else {
                
                $arr[$quiz_author] = array( 'role' => 'master', 'origin' => 'external' );

            }

            // Обновить список на основе данных запроса
            
            $arr_update = $this->members_update( $arr, $quiz_id );
            
            // Если есть обновление, то учесть это в массиве и в базе

            if ( ! ( $arr_update === false ) ) {

                $arr = $arr_update;
                $this->update( $arr, $quiz_id );

            }

            // Добавить данные по внешним пользователям, если они есть

            $arr = apply_filters( 'mif_qm_members_core_get_external', $arr, $quiz_id );

            $arr = $this->sort( $arr );

            wp_cache_set( 'mif_qm_members', $arr, $quiz_id );

        }

        return $arr;
    }



    // 
    // Сортирует список пользовтелей
    // 

    private function sort( $arr )
    {
        // Думать про более интересные способы сортировки

        ksort( $arr );

        return apply_filters( 'mif_qm_members_core_sort', $arr );
    }


    
    //
    // Возвращает уровень доступа пользователя
    //      0 - нет доступа
    //      1 - прохождение теста (ученик)
    //      2 - просмотр результатов (эксперт)
    //      3 - проверка ответов (ассистент)
    //      4 - редактирование ответов (тьютор)
    //      5 - редактирование теста (мастер)
    //

    public function access_level( $quiz_id = false, $user_id = false )
    {
        $quiz_id = $this->get_quiz_id( $quiz_id );

        if ( ! $level = wp_cache_get( 'mif_qm_access_level', $quiz_id . '-' . $user_id ) ) {

            $status = $this->accessed( $quiz_id, $user_id );
            $level = $this->member_level( $status );

            wp_cache_set( 'mif_qm_access_level', $level, $quiz_id . '-' . $user_id );

        }
        
        return $level;
    }



    //
    // Получает запись с данными о пользователях
    //

    private function get_members_data( $quiz_id = false )
    {
        $quiz_id = $this->get_quiz_id( $quiz_id );

        $args = array(
            'post_type'   => 'quiz_members',
            'orderby'     => 'date',
            'order'       => 'DESC',
            'post_parent' => $quiz_id,
        );

        $data = get_posts( $args );
        
        if ( ! empty( $data ) ) {
            
            $ret = $data[0];

            // Удалить лишние данные, если они есть.

            foreach ( (array) $data as $key => $item ) {

                if ( $key === 0 ) continue;
                wp_trash_post( $item->ID );

            }

        } else {

            // Если нет данных, то создать 

            $args = array(
                'post_type' => 'quiz_members',
                'post_content' => $this->to_xml( array() ),
                'post_status' => 'publish',
                'quiz' => $quiz_id,
                'user' => false,
                );

            $data_id = $this->companion_insert( $args );

            $ret = get_post( $data_id );

        }

        // $ret = ( isset( $data[0] ) ) ? $data[0] : false;

        return $ret;
    }


    //
    // Получить группы из списка пользователей
    //

    public function get_groups( $members )
    {
        $index = array();

        foreach ( (array) $members as $m ) {

            $group = ( isset( $m['group'] ) ) ? $m['group'] : '';
            $index[$m['group']] = true;
            
        }

        $groups = ( count( $index ) > 0 ) ? array_keys( $index ) : array();

        sort( $groups );
        
        if ( $groups[0] == '' ) {

            unset( $groups[0] );
            $groups[] = '';

        }

        return $groups;
    }    


    // //
    // // Пытается добавить пользователя в массив новых пользователей
    // //

    // private function self_making( $user_id = false, $quiz_id = false, $members = array() )
    // {
    //     $user_token = $this->get_user_token( $user_id )
    //     if ( ! $user_token ) return $members;

    //     $access_mode = get_access_mode( $quiz_id );

    //     if ( $access_mode == 'open' ) {

    //         $members[] = $user_token;
    //         return $members;

    //     }



    // }


    //
    // Проверяет статус пользователя и добавляет его в список, если можно
    //

    public function accessed( $quiz_id = false, $user_id = false )
    {
        $quiz_id = $this->get_quiz_id( $quiz_id );

        $status = $this->member_status( $quiz_id, $user_id );
        
        // Если есть нормальный статус, то отправить его
        
        if ( $this->member_level( $status ) > 0 ) return $status;
        
        // Если нет статуса, то пытаться отправить заявку (вдруг она есть?)
        
        $status = $this->set_request( $user_id, $quiz_id );
        
        return $this->get_context_role( $status, $quiz_id );
    }



    //
    // Возвращает уровень пользователя по его статусу
    //

    public function member_level( $status )
    {
        $level = 0;
        if ( isset( $this->roles[$status] ) ) $level = $this->roles[$status]['level'];
        return $level;
    }



    //
    // Проверяет, что пользователь является пользователем тесте
    //

    public function member_status( $quiz_id = false, $user_id = false )
    {
        $arr = $this->get( $quiz_id );
        $user_token = $this->get_user_token( $user_id );

        $ret = false;

        if ( isset( $arr[$user_token] ) ) {

            $ret = ( isset( $arr[$user_token]['role'] ) ) ? $arr[$user_token]['role'] : true;

        } else {
            
            $invites = $this->get_requesters( $quiz_id, 'invite' );
            if ( in_array( $user_token, (array) $invites ) ) $ret = $this->get_context_role( 'invite', $quiz_id );

            $requests = $this->get_requesters( $quiz_id, 'request' );
            if ( in_array( $user_token, (array) $requests ) ) $ret = $this->get_context_role( 'request', $quiz_id );

        }

        if ( empty( $ret ) ) {

            $access_mode = $this->get_access_mode( $quiz_id );
            if ( $access_mode == 'request' ) $ret = 'request_ready';

            // !!! Здесь еще проверять на invite_ready

        }

        return $ret;
    }


    //
    // Получает список запрашивающих доступ
    //

    public function get_requesters( $quiz_id = false, $meta_key = 'request' )
    {
        $quiz_id = $this->get_quiz_id( $quiz_id );

        $data = $this->get_members_data( $quiz_id );
        $arr = get_post_meta( $data->ID, $meta_key );

        return $arr;
    }


    
    //
    // Получает данные инвайта
    //

    public function get_invite_data( $quiz_id = false, $user_token = '' )
    {
        $quiz_id = $this->get_quiz_id( $quiz_id );
        $data = $this->get_members_data( $quiz_id );

        $arr = get_post_meta( $data->ID, 'invite_' . $user_token );

        $ret = ( isset( $arr[0] ) ) ? $arr[0] : array();

        return $ret;
    }


    

    //
    // Сохраняет в базе запрос пользователя и возвращает результат
    //

    public function set_request( $user_id, $quiz_id = false, $invite = false )
    {
        $quiz_id = $this->get_quiz_id( $quiz_id );

        $access_mode = $this->get_access_mode( $quiz_id );
        $start_btn = ( isset( $_REQUEST['start'] ) && $_REQUEST['start'] == 'yes' ) ? true : false;

        // Сохранять заявку - если пользователь ввел инвайт, нажал кнопку, или тест открытый (и доступен без кнопки)
        
        if ( ! ( $invite || $start_btn || $access_mode == 'open' ) ) return false;

        $data = $this->get_members_data( $quiz_id );
        $user_token = $this->get_user_token( $user_id );

        // Добавить завпрос с учетом проверки 

        $request_status = false;

        if ( $invite ) {

            // С инвайтом - можно записываться всегда

            $request_status = 'invite';
            
        } else {

            // Без инвайта - только для открытых и request-тестов

            if ( in_array( $access_mode, array( 'open', 'request' ) ) ) {
                
                $request_status = 'request';
                
            }
            
        }

        // Записать в базу, если есть чего записывать и если там этого уже нет

        if ( $request_status ) {

            $arr = get_post_meta( $data->ID, $request_status );
            if ( ! in_array( $user_token, $arr ) ) $ret = add_post_meta( $data->ID, $request_status, $user_token );

            // $ret = add_post_meta( $data->ID, $request_status, $user_token );
            if ( $request_status == 'invite' ) update_post_meta( $data->ID, 'invite_' . $user_token, $invite );

        }

        return $request_status;
    }

    

    
    //
    // Возвращает роль, исходя из контекста
    //

    public function get_context_role( $request_status = 'request', $quiz_id = false )
    {
        // По инвайту - всегда студент

        if ( $request_status == 'invite' ) return 'student';

        if ( $request_status == 'request' ) {
            
            $access_mode = $this->get_access_mode( $quiz_id );

            // Если тест открытый, то студент
            // Если тест по запросу, то request
            // В остальных случаях - никто
            
            if ( $access_mode == 'open' ) return 'student';
            if ( $access_mode == 'request' ) return 'request';
            return false;
        }

        return $request_status;        
    }

    


    //
    // Обновить список пользователей, если это пришло с запросом
    //

    public function members_update( $arr, $quiz_id = false )
    {
        $flag = false;
        
        $quiz_id = $this->get_quiz_id( $quiz_id );
        
        // Текущий пользователь не может обновлять списки других пользователей

        $user_token = $this->get_user_token();
        if ( ! ( $this->member_level( $arr[$user_token]['role'] ) > 2 ) ) return false;
            
        // Обработать инвайты

        $invited = $this->get_requesters( $quiz_id, 'invite' );

        if ( $invited ) {

            foreach ( (array) $invited as $item ) {
                
                if ( empty( $item ) ) continue;
                if ( isset( $arr[$item] ) ) continue;
                
                // !!! Думать, как здесь учесть и другие данные инвайта

                $data = $this->get_invite_data( $quiz_id, $item );

                $data['role'] = 'student';
                $data['time'] = $this->get_time();
                $data['creator'] = $this->get_user_token();

                // $arr[$item] = array( 'role' => 'student', 'time' => $this->get_time(), 'maker' => $this->get_user_token() );

                $arr[$item] = $data;

                $flag = true;
                
            }
        
            $this->remove_requests( $invited, $quiz_id, 'invite' );

        }
        

        // Очистить полученные данные и оформить как массив

        if ( isset( $_REQUEST['members'] ) && is_array( $_REQUEST['members'] ) ) {
            
            $arr_request = array_map( 'sanitize_key', $_REQUEST['members'] );
            
        } elseif ( isset( $_REQUEST['members'] ) && is_string( $_REQUEST['members'] ) ) {
            
            $arr_request[] = sanitize_key( $_REQUEST['members'] );

        } elseif ( ! empty( $_REQUEST['members-text'] ) ) {
            
            $new_data = sanitize_text_field( preg_replace( '/[^0-9a-z_-]/', ' ', $_REQUEST['members-text'] ) );
            $arr_request = explode( ' ', $new_data );
            
        } 
        // else {

        //     // Нет новых данных

        //     return false;

        // }

        // Добавить или удалить

        $do = ( isset( $_REQUEST['do'] ) ) ? sanitize_key( $_REQUEST['do'] ) : 'add';
        $premise = ( isset( $_REQUEST['premise'] ) ) ? sanitize_key( $_REQUEST['premise'] ) : 'student';
        
        // Изменение статуса студента или обработка запроса

        if ( $do == 'add' ) {

            // Добавить нового обучающегося самому, либо через подверждение заявки
            
            if ( in_array( $premise, array( 'request', 'student' ) ) ) {

                foreach ( (array) $arr_request as $user_token ) {

                    // Пропускать несуществующих пользователей
                    
                    $user_id = $this->get_user_id( $user_token );
                    if ( ! $user_id ) continue;

                    // Если пользователь в списке уже есть, то снова не добавлять

                    if ( isset( $arr[$user_token] ) ) continue;

                    // Добавить пользователя

                    $arr[$user_token] = array( 'role' => 'student', 'time' => $this->get_time(), 'creator' => $this->get_user_token() );
                    $flag = true;

                }

            }

        } elseif ( $do == 'remove' ) {

            // Удалить запросы
            
            if ( in_array( $premise, array( 'request', 'student' ) ) ) {

                $this->remove_requests( $arr_request, $quiz_id );

                if ( $premise == 'student' ) {

                    // Удалить студентов по списку

                    foreach ( (array) $arr_request as $user_token ) {
                        
                        unset( $arr[$user_token] );
                        $flag = true;

                    }

                }
            
            }

        } elseif ( $do == 'promotion' || $do == 'demotion' ) {
            
            // Изменение статуса для остальных ролей
            
            if ( isset( $this->roles[$premise] ) ) {
                
                // Узнать идентификатор нового статуса

                $index = array();
                $current = NULL;
                $n = 0;

                foreach ( $this->roles as $key => $item ) {

                    if ( $premise == $key ) $current = $n;
                    $index[$n] = $key;

                    $n++;
                }

                $new_role = $premise;
                $next = $current + 1;
                $prev = $current - 1;
                
                if ( $do == 'promotion' && isset( $index[ $next ] ) ) $new_role = $index[$next];
                if ( $do == 'demotion' && isset( $index[ $prev ] ) ) $new_role = $index[$prev];

                // Установить статус

                foreach ( (array) $arr_request as $user_token ) {
                                        
                    if ( empty( $arr[$user_token] ) ) continue;
                    
                    $arr[$user_token]['role'] = $new_role;
                    $arr[$user_token]['change_time'] = $this->get_time();
                    $arr[$user_token]['change_maker'] = $this->get_user_token();

                    $flag = true;
                    
                }

            }
                
        } elseif ( $do == 'result-archive' || $do == 'result-unarchive' ) {

            foreach ( (array) $arr_request as $user_token ) {
                                        
                if ( $do == 'result-archive' ) {

                    $this->member_to_archive( $user_token, $quiz_id );
                    
                } else {
                    
                    $this->member_from_archive( $user_token, $quiz_id );

                }
                
            }

        } 
        
        // Здесь можно делать рассылку пользователям об изменении статуса

        do_action( 'mif_qm_members_core_manage_request_notifications', $arr_request, $do, $premise );

        // Чистить список запросов - удалить тех, кто в основном списке
        
        $requesters = $this->get_requesters( $quiz_id, 'request' );
        
        foreach ( (array) $requesters as $user_token ) {
            
            if ( isset( $arr[$user_token] ) ) $this->remove_requests( $user_token, $quiz_id );
            
        }

        // Сформировать результат

        $ret = ( $flag ) ? $arr : false;

        return $ret;
    }



    //
    // Получить список архивных пользователей
    //

    public function get_archive_members( $quiz_id = false )
    {
        $quiz_id = $this->get_quiz_id( $quiz_id );
        $data = $this->get_members_data( $quiz_id );

        $arr = get_post_meta( $data->ID, 'archive' );

        return $arr;
    }



    //
    // Удалить у пользователя статус архивных результатов
    //

    public function member_from_archive( $user_token, $quiz_id = false )
    {
        $quiz_id = $this->get_quiz_id( $quiz_id );
        $data = $this->get_members_data( $quiz_id );

        $ret = delete_post_meta( $data->ID, 'archive', $user_token );

        return $ret;
    }



    //
    // Добавить пользователю статус архивных результатов
    //

    public function member_to_archive( $user_token, $quiz_id = false )
    {
        $quiz_id = $this->get_quiz_id( $quiz_id );
        $data = $this->get_members_data( $quiz_id );

        $ret = add_post_meta( $data->ID, 'archive', $user_token );

        return $ret;
    }



    //
    // Обновить данные о пользователях
    //

    protected function remove_requests( $arr, $quiz_id = false, $meta_key = 'request' )
    {
        $quiz_id = $this->get_quiz_id( $quiz_id );
        $data = $this->get_members_data( $quiz_id );

        foreach ( (array) $arr as $item ) {

            if ( empty( $item ) ) continue;
            delete_post_meta( $data->ID, $meta_key, $item );

            if ( $meta_key == 'invite' ) delete_post_meta( $data->ID, 'invite_' . $item );

        }

    }



    //
    // Обновить данные о пользователях
    //

    public function update( $arr = array(), $quiz_id = NULL )
    {
        // Подготовить данные для записи

        $arr2 = array();
        
        foreach ( $arr as $member_token => $data ) {

            if ( isset( $data['origin'] ) && $data['origin'] == 'external' ) continue;
            $arr2[$member_token] = $data;

        }
     
        $data = $this->get_members_data( $quiz_id );
        $quiz_members_id = $data->ID;

        if ( $quiz_members_id ) {

            // Данные хранятся в базе, надо обновлять

            $args = array(
                'ID' => $quiz_members_id,
                'post_content' => $this->to_xml( $arr2 )
                );

            $res = $this->companion_update( $args );            

            wp_cache_delete( 'mif_qm_members', $quiz_id );

        } 
        
        return $res;
    }



    // //
    // // Обновить статус пользователей
    // //

    // private function update_status( $members = array(), $status = 'active', $quiz_id = NULL )
    // {
    //     $arr = $this->get( $quiz_id );

    //     foreach ( (array) $members as $member ) {

    //         if ( isset( $arr['member'] ) ) $arr['member']['status'] = $status;

    //     }

    //     $res = $this->update( $arr, $quiz_id );
        
    //     return $res;
    // }



    //
    // Возвращает все данные пользователя
    //

    public function get_member( $user_token = '', $quiz_id = false )
    {
        $quiz_id = $this->get_quiz_id( $quiz_id );

        $members = $this->get();
        $member = ( isset( $members[$user_token] ) ) ? $members[$user_token] : false;

        return $member;
    }



    //
    // Возвращает имя с учетом данных инвайта
    //

    public function get_fullname( $user_token = '', $quiz_id = false )
    {
        $quiz_id = $this->get_quiz_id( $quiz_id );

        $members = $this->get();
        $fullname = ( isset( $members[$user_token]['fullname'] ) ) ? $members[$user_token]['fullname'] : $this->get_display_name( $user_token );

        return $fullname;
    }



    //
    // Возвращает группу пользователя (если есть)
    //

    public function get_group( $user_token = '', $quiz_id = false )
    {
        $quiz_id = $this->get_quiz_id( $quiz_id );

        $members = $this->get();
        $group = ( isset( $members[$user_token]['group'] ) ) ? $members[$user_token]['group'] : '';

        return $group;
    }


    //
    // Возвращает режим доступа к тесту
    //

    public function get_access_mode( $quiz_id = false )
    {
        $quiz_id = $this->get_quiz_id( $quiz_id );

        if ( isset( $_REQUEST['access_mode'] ) && mif_qm_access_level() > 2 ) {

            // Есть новые данные о режиме доступа. Сохранить их.

            $access_mode = sanitize_key( $_REQUEST['access_mode'] );
            $target_quiz_id = ( isset( $_REQUEST['quiz_id'] ) ) ? (int) $_REQUEST['quiz_id'] : $quiz_id;

            update_post_meta( $target_quiz_id, 'access_mode', $access_mode );

        }    

        $access_mode_from_db = get_post_meta( $quiz_id, 'access_mode', true );
        $access_mode =  ( ! empty( $access_mode_from_db ) ) ? $access_mode_from_db : $this->access_mode_default;

        return $access_mode;
    }



    // 
    // Вывести аватар пользователя
    // 

    function get_avatar( $user_token, $size = 25 )
    {
        $out = '';

        $user_id = $this->get_user_id( $user_token );
        $display_name = $this->get_display_name( $user_token );
        $link = $this->get_user_link( $user_token );

        $out .= '<span class="user-avatar"><a href="' . $link . '" title="' . $display_name . '">' . get_avatar( $user_id, $size, '', '', array( 'class' => 'rounded-circle' ) ) . '</a></span>';

        return $out;
    }
    

    //
    // Преобразовать массив результатов в xml-запись
    //

    private function to_xml( $arr )
    {
        $xml = new SimpleXMLElement( '<?xml version="1.0" encoding="UTF-8"?><members/>' );

        foreach ( (array) $arr as $user_token => $data ) {

            $member = $xml->addChild( 'member', $user_token );

            foreach ( (array) $data as $key => $value ) {
                
                $member->addAttribute( $key, $value );

            }

        }

        $xml_core = new mif_qm_xml_core();
        $xml = $xml_core->get_formatted_xml( $xml );

        // p(esc_html($xml));

        return $xml;
    }

    //
    // Преобразовать xml-запись пользователей в массив
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

        foreach ( $xml as $member ) {

            $item = array();

            foreach ( $member->attributes() as $key => $value ) $item[$key] = (string) $value;
            // foreach ( $member->children() as $key => $value ) $item[$key] = (string) $value;

            $arr[ (string) $member ] = $item;
            
        }

        return $arr;
    }



}

?>