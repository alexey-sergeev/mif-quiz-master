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
            'supports'            => array( 'title', 'editor', 'author', 'custom-fields', 'revisions' ), // 'title','editor','author','thumbnail','excerpt','trackbacks','custom-fields','comments','revisions','page-attributes','post-formats'
            'taxonomies'          => array(),
            'has_archive'         => true,
            'rewrite'             => array( 'slug' => 'quiz_result' ),
            'query_var'           => true,

        ) );
    }



    //
    // Обновить данные об оценках
    //

    public function update( $new_result, $quiz_id, $user_id = NULL )
    {
        if ( $user_id == NULL ) $user_id = get_current_user_id();

        $results = $this->get( $quiz_id, $user_id );

        if ( isset( $results['ID'] ) ) {

            // Данные есть. ДОбавить новые и обновить

            $data = (array) $results['data'];
            $data[] = $new_result;
            
            $args = array(
                        'ID' => $results['ID'],
                        'post_content' => $this->to_xml( $data )
                        );

            $res = $this->companion_update( $args );
                            
        } else {
            
            // Результатов нет, создать новую запись

            $data = array();
            $data[] = $new_result;

            $args = array(
                        'post_type' => 'quiz_result',
                        'post_content' => $this->to_xml( $data ),
                        'post_status' => 'publish',
                        'quiz' => $quiz_id,
                        'user' => $user_id,
                        );

            $res = $this->companion_insert( $args );
          
        }
        
        if ( $res && isset( $new_result['snapshot'] ) ) {

            // Если данные о результатах успешно записано и известен ID снимка, то снать со снимка статус черновика
            
            $this->companion_update( array( 'ID' => $new_result['snapshot'], 'post_status' => 'publish' ) );

        }

        return $res;
    }



    //
    // Получить данные о текущих оценках
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
    // Преобразовать массив результатов в xml-запись
    //

    private function to_xml( $arr )
    {
        $xml = new SimpleXMLElement( '<?xml version="1.0" encoding="UTF-8"?><results/>' );

        foreach ( (array) $arr as $item ) {

            $result = $xml->addChild( 'result' );

            foreach ( (array) $item as $key => $value ) {

                if ( in_array( $key, array( 'time', 'user', 'quiz', 'snapshot', 'max' ) ) ) {

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

    private function to_array( $xml )
    {
        $arr = array();

        $xml = new SimpleXMLElement( $xml );

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