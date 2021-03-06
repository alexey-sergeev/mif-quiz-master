<?php

//
// Обработка результатов теста
// 
//


defined( 'ABSPATH' ) || exit;



class mif_qm_process_results extends mif_qm_process_core { 

    
    function __construct()
    {
        parent::__construct();
        // $this->post_types_init();
    }


    // 
    // Иницализация типов записей
    // 

    public function post_types_init()
    {
    
        // 
        // Тип записей - "Оценки (результаты) теста"
        // 

        register_post_type( 'quiz_result', array(
            'label'  => null,
            'labels' => array(
                'name'               => __( 'Оценки тестов', 'mif-qm' ), // основное название для типа записи
                'singular_name'      => __( 'Оценка', 'mif-qm' ), // название для одной записи этого типа
                'add_new'            => __( 'Создать оценку', 'mif-qm' ), // для добавления новой записи
                'add_new_item'       => __( 'Создание оценки', 'mif-qm' ), // заголовка у вновь создаваемой записи в админ-панели.
                'edit_item'          => __( 'Редактирование оценки', 'mif-qm' ), // для редактирования типа записи
                'new_item'           => __( 'Новая оценка', 'mif-qm' ), // текст новой записи
                'view_item'          => __( 'Посмотреть оценку', 'mif-qm' ), // для просмотра записи этого типа.
                'search_items'       => __( 'Найти оценку', 'mif-qm' ), // для поиска по этим типам записи
                'not_found'          => __( 'Оценка не найдена', 'mif-qm' ), // если в результате поиска ничего не было найдено
                'not_found_in_trash' => __( 'Не найдено в корзине', 'mif-qm' ), // если не было найдено в корзине
                'parent_item_colon'  => '', // для родителей (у древовидных типов)
                'menu_name'          => __( 'Оценки', 'mif-qm' ), // название меню
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
            'capability_type'     => 'post',
            //'capabilities'      => 'post', // массив дополнительных прав для этого типа записи
            'map_meta_cap'        => true, // Ставим true чтобы включить дефолтный обработчик специальных прав
            'hierarchical'        => true,
            // 'supports'            => array( 'title', 'editor', 'author', 'custom-fields', 'revisions' ), // 'title','editor','author','thumbnail','excerpt','trackbacks','custom-fields','comments','revisions','page-attributes','post-formats'
            'supports'            => array( 'title', 'editor', 'author', 'custom-fields' ), // 'title','editor','author','thumbnail','excerpt','trackbacks','custom-fields','comments','revisions','page-attributes','post-formats'
            'taxonomies'          => array(),
            'has_archive'         => true,
            'rewrite'             => array( 'slug' => 'quiz_result' ),
            'query_var'           => true,

        ) );
    }



    //
    // Обновить данные об оценках
    //

    public function update( $new_result, $quiz_id, $user_id = NULL, $rescan = false )
    {
        if ( $user_id == NULL ) $user_id = get_current_user_id();

        $results = $this->get( $quiz_id, $user_id );
        
        // Данные - из текущих результатов, либо пустые
        
        $data = ( isset( $results['data'] ) ) ? (array) $results['data'] : array();
        
        // Если требуется обновление результатов, то пересчитать данные из снимков

        if ( $rescan ) {

            $data = array();

            $args = array(
                'post_type'   => 'quiz_snapshot',
                'post_status' => 'publish',
                'orderby'     => 'date',
                'order'       => 'DESC',
                'post_parent' => $quiz_id,
                'meta_key'    => 'owner',
                'meta_value'  => $this->get_user_token( $user_id )
            );

            $snapshots = get_posts( $args );

            foreach ( (array) $snapshots as $snapshot ) {
                
                $process_inspector = new mif_qm_process_inspector( $snapshot->ID );
                $data[] = $process_inspector->get_result( $snapshot->ID );

            }

        }

        if ( isset( $results['ID'] ) ) {

            // Данные есть. ДОбавить новые и обновить

            // $data = (array) $results['data'];
            if ( ! empty( $new_result ) ) $data[] = $new_result;

            // Посчитать данные о итоговой оценке

            $data = $this->calculate_current( $data, $quiz_id );

            $args = array(
                        'ID' => $results['ID'],
                        'post_content' => $this->to_xml( $data )
                        );

            $res = $this->companion_update( $args );
                            
        } else {

            // Результатов нет, создать новую запись

            // $data = array();
            if ( ! empty( $new_result ) ) $data[] = $new_result;

            // Посчитать данные о итоговой оценке

            $data = $this->calculate_current( $data, $quiz_id );

            $args = array(
                        'post_type' => 'quiz_result',
                        'post_content' => $this->to_xml( $data ),
                        'post_status' => 'publish',
                        'quiz' => $quiz_id,
                        'user' => $user_id,
                        );

            $res = $this->companion_insert( $args );

        }
        
        if ( $res ) {
            
            // Обновить результаты в данных плобального рейтинга, если такой рейтинг ведется

            do_action( 'mif_qm_process_result_update_global', $data, $quiz_id, $user_id );

            // Если известен ID снимка, то снать со снимка статус черновика

            if ( isset( $new_result['snapshot'] ) ) {
            
                $this->companion_update( array( 'ID' => $new_result['snapshot'], 'post_status' => 'publish' ) );

            }

            // Снять статус архивного результата, если он есть

            $qm_members_core = new mif_qm_members_core();
            $user_token = $this->get_user_token( $user_id );

            $qm_members_core->member_from_archive( $user_token, $quiz_id );
            
            // $members = $qm_members_core->get( $quiz_id );
            

            // if ( isset( $members[$user_token]['result'] ) && $members[$user_token]['result'] == 'archive' ) {

            //     unset( $members[$user_token]['result'] );
            //     $qm_members_core->update( $members, $quiz_id );

            // };

        };

        return $res;
    }



    //
    // Получить данные о текущих оценках многих пользователей для теста
    //

    public function get_xlsx_arr( $quiz_id, $archive = false )
    {
        $arr = array();

        $cells = array( 
            'n' => __( '№', 'mif-qm' ), 
            'fullname' => __( 'Ф. И. О.', 'mif-qm' ), 
            'group' => __( 'Группа', 'mif-qm' ), 
            'rating' => __( 'Оценка', 'mif-qm' ), 
            'max' => __( 'Макс.', 'mif-qm' ), 
            'percent' => __( '%', 'mif-qm' ), 
            'success' => __( 'Результат', 'mif-qm' ), 
            'duration' => __( 'Секунд', 'mif-qm' ), 
            'time' => __( 'Дата', 'mif-qm' ), 
        );

        $success_desc = array( 
            'yes' => __( 'Принято', 'mif-qm' ),
            'no' => __( 'Не принято', 'mif-qm' )
        );

        $arr[] = array();
        $arr[] = array( __( 'Результаты', 'mif-qm' ) );
        $arr[] = array();
        $arr[] = array( __( 'Тест:', 'mif-qm' ), get_the_title( $quiz_id ) );
        $arr[] = array( __( 'URL:', 'mif-qm' ), get_permalink( $quiz_id ) );
        $arr[] = array( __( 'Дата:', 'mif-qm' ), $this->get_time() );
        $arr[] = array();
        $arr[] = array();

        $row = array();
        foreach ( $cells as $key => $value ) $row[] = $value;
        $arr[] = $row;

        $qm_members_core = new mif_qm_members_core();

        $result_list = $this->get_list( $quiz_id, false, $archive );

        $n = 1;

        foreach ( (array) $result_list as $member => $results ) {

            $result = $this->get_current_result( $quiz_id, $member );

            $row = array(
                    $n++,
                    $qm_members_core->get_fullname( $member, $quiz_id ),
                    $qm_members_core->get_group( $member, $quiz_id ),
                    $result['rating'],
                    $result['max'],
                    $result['percent'],
                    $success_desc[$result['success']],
                    $result['duration'],
                    $result['time'],
            );

            $arr[] = $row;

            // $arr[] = array(
            //         'n' => $n++,
            //         'fullname' => $qm_members_core->get_fullname( $member, $quiz_id ),
            //         'group' => $qm_members_core->get_fullname( $member, $quiz_id ),
            //         'rating' => $result['rating'],
            //         'max' => $result['max'],
            //         'percent' => $result['percent'],
            //         'success' => $success_desc[$result['success']],
            //         'duration' => $result['duration'],
            //         'time' => $result['time'],
            // );
                
        }


        return $arr;
    }


    //
    // Получить данные о текущих оценках многих пользователей для теста
    //

    public function get_list( $quiz_id, $user_token = false, $archive = false )
    {
        $arr = array();

        $result_args = array(
            'post_type'   => 'quiz_result',
            'post_status'   => 'publish',
            'orderby'     => 'date',
            'order'       => 'DESC',
            'numberposts' => -1,            
            'post_parent' => $quiz_id,
        );

        // Если указан пользователь, то показать только его результаты

        if ( $user_token ) {

            $result_args['meta_key'] = 'owner';
            $result_args['meta_value'] = $user_token;

        } else {

            $qm_members_screen = new mif_qm_members_screen( $quiz_id );
            $members = $qm_members_screen->get( $quiz_id );
            $archive_members = $qm_members_screen->get_archive_members( $quiz_id );

            $arr2 = array();

            foreach ( $members as $owner => $member ) {
                
                // if ( isset( $member['result'] ) && $member['result'] == 'archive' ) continue;
                if ( in_array( $owner, $archive_members ) ) continue;
                $arr2[] = $owner;

            };

            $result_args['meta_key'] = 'owner';
            $result_args['meta_value'] = $arr2;
            
            if ( $archive)  $result_args['meta_compare'] = 'NOT IN';
            
         }

        $results = get_posts( $result_args );

        foreach ( (array) $results as $result ) {
            
            $owner = get_post_meta( $result->ID, 'owner', true );
            
            $result_data = $this->to_array( $result->post_content );
            
            $arr[$owner] = $result_data;

        }

        // p($arr);

        return $arr;
    }


    
    //
    // Получить текущий результат пользователя
    //

    public function get_current_result( $quiz_id, $user_token = false )
    {
        // $user_token = $this->get_user_token( $user_id );
        if ( is_numeric( $user_token ) ) $user_token = $this->get_user_token( $user_token );

        $arr = $this->get_list( $quiz_id, $user_token );

        if ( ! isset( $arr[$user_token] ) ) return false;

        $ret = false;

        foreach ( (array) $arr[$user_token] as $item ) {

            if ( isset( $item['current'] ) && $item['current'] == 'yes' ) $ret = $item;

        }
        
        return $ret;
    }



    //
    // Получить данные об оценках по конкретному тесту конкретного пользователя
    //

    public function get( $quiz_id, $user_id = NULL )
    {
        if ( $user_id == NULL ) $user_id = get_current_user_id();

        $result_args = array(
            'post_type'   => 'quiz_result',
            'orderby'     => 'date',
            'order'       => 'DESC',
            'meta_key'    => 'owner',
            'meta_value'  => $this->get_user_token( $user_id ),
            'post_parent' => $quiz_id,
        );

        $results = get_posts( $result_args );

        if ( empty( $results ) ) {

            return false;

        } else {

            // Удалить лишние результаты, если они есть.

            foreach ( (array) $results as $key => $result ) {

                if ( $key === 0 ) continue;
                wp_trash_post( $result->ID );

            }

            $result = $results[0];
            
            return array( 'ID' => $result->ID, 'data' => $this->to_array( $result->post_content ) );

        }

    }



    //
    // Получить список тестов, по которым есть результат у пользователя
    //

    public function get_results( $args = array() )
    {
        // $user_id = ( ! empty( $args['user'] ) ) ? $args['user'] : get_current_user_id();

        $result_args = array(
            'numberposts' => -1,
            'post_type'   => 'quiz_result',
            'orderby'     => 'date',
            'order'       => 'DESC',
            // 'meta_key'    => 'owner',
            // 'meta_value'  => $this->get_user_token( $user_id ),
        );

        if ( isset( $args['numberposts'] ) ) $result_args['numberposts'] = $args['numberposts'];
        if ( isset( $args['author'] ) ) $result_args['author'] = $args['author'];
        
        if ( isset( $args['user'] ) ) {

            $result_args['meta_key'] = 'owner';
            $result_args['meta_value'] = $this->get_user_token( $args['user'] );

        }

        $results = get_posts( $result_args );

        return $results;
    }


    //
    // Посчитать данные об итоговой оценке
    //

    private function calculate_current( $data = array(), $quiz_id = NULL )
    {
        
        if ( empty( $data ) ) return array();

        // Если один результат, то он и текущий

        if ( count( $data ) === 1 ) {

            $data[0]['current'] = 'yes';
            return $data;

        }

        // Предварительная подготовка

        $success_flag = false;

        foreach ( (array) $data as $key => $result ) {
            
            // Очистить текущие данные итоговой оценки

            if ( isset( $data[$key]['success'] ) && $data[$key]['success'] == 'yes' ) $success_flag = true;
            if ( isset( $data[$key]['current'] ) ) unset( $data[$key]['current'] );
            if ( isset( $data[$key]['average'] ) ) unset( $data[$key] );

        }

        // Узнать режим и вычислить результат

        $quiz_core = new mif_qm_quiz_core();
        $quiz = $quiz_core->parse( $quiz_id );

        if ( $this->is_param( 'better', $quiz ) || $this->is_param( 'latest', $quiz ) ) {

            // Режим - "лучшая" или "последняя"
            // Найти лучший элемент по времени или по баллу (в зависимости от настройки теста)

            $index = array();

            foreach ( (array) $data as $key => $result ) {
                
                // Пропускать неуспешные результаты, если есть успешные
                
                if ( $success_flag && ( isset( $result['success'] ) && $result['success'] == 'no' ) ) continue;

                // Построить индекс (в зависимости от режима)

                if ( $this->is_param( 'better', $quiz ) ) {

                    $index[$result['percent']] = $key;
                    
                } elseif ( $this->is_param( 'latest', $quiz ) ) {

                    $index[ $this->get_timestamp( $result['time'] ) ] = $key;

                }

            }
            
            // Найти максимальное значение. Этот элемент и считать текущим

            $current_id = $index[ max( array_keys( $index ) ) ];
            $data[$current_id]['current'] = 'yes';

        } elseif ( $this->is_param( 'average', $quiz ) ) {
            
            // Режим - "средняя"

            $index = array();
            $arr = array( 'rating' => 0, 'percent' => 0, 'duration' => 0, 'success' => 'no' );
          
            foreach ( (array) $data as $key => $result ) {

                $index[ $this->get_timestamp( $result['time'] ) ] = $key;
                $arr['rating'] += $result['rating'];
                $arr['percent'] += $result['percent'];
                $arr['duration'] += $result['duration'];

                if ( $result['success'] == 'yes' ) $arr['success'] = 'yes';

            }

            $current = $data[ $index[ max( array_keys( $index ) ) ] ];

            $current['rating'] = round( $arr['rating'] / count( $data ) );
            $current['percent'] = round( $arr['percent'] / count( $data ) );
            $current['duration'] = round( $arr['duration'] / count( $data ) );
            $current['success'] = $arr['success'];

            $current['current'] = 'yes';
            $current['average'] = 'yes';

            // Добавить элемент в начало

            array_unshift( $data, $current );

        }

        return $data;
    }



    //
    // Преобразовать массив результатов в xml-запись
    //

    private function to_xml( $arr )
    {
        $xml = new SimpleXMLElement( '<?xml version="1.0" encoding="UTF-8"?><results/>' );

        foreach ( (array) $arr as $item ) {

            $result = $xml->addChild( 'result' );

            foreach ( (array) $item as $key => $value ) {

                if ( in_array( $key, array( 'time', 'user', 'quiz', 'snapshot', 'max', 'current' ) ) ) {

                    $result->addAttribute( $key, $value );

                } else {
                    
                    $result->addChild( $key, $value );

                }

            }

        }

        $xml_core = new mif_qm_xml_core();
        $xml = $xml_core->get_formatted_xml( $xml );

        // p(esc_html($xml));

        return $xml;
    }



    //
    // Преобразовать xml-запись результатов в массив
    //

    public function to_array( $xml )
    {
        $arr = array();

        try {

            $xml = new SimpleXMLElement( $xml );

        } catch ( Exception $e ) {

            return $arr;

        }

        foreach ( $xml as $result ) {

            $item = array();

            foreach ( $result->attributes() as $key => $value ) $item[$key] = (string) $value;
            foreach ( $result->children() as $key => $value ) $item[$key] = (string) $value;

            $arr[] = $item;
            
        }

        return $arr;
    }

}

?>