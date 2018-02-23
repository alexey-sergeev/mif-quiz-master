<?php

//
// Экранные методы главной страницы
// 
//


defined( 'ABSPATH' ) || exit;

include_once dirname( __FILE__ ) . '/qm-core.php';
include_once dirname( __FILE__ ) . '/qm-templates.php';


class mif_qm_screen extends mif_qm_core {

    // Количество элементов на одной странице каталога

    private $quizess_per_page = 20;
    
    function __construct()
    {
        parent::__construct();

        add_filter( 'posts_search', array( $this, 'search_security_fix' ), 10, 2 );
    }



    //
    // Каталог тестов
    //

    public function get_catalog()
    {
        $out = '';

        $page = ( isset( $_REQUEST['page'] ) ) ? (int) $_REQUEST['page'] : 1;

        // Выбрать записи из базы данных
        
        $args = $this->get_catalog_args( $page );
        $quizess = get_posts( $args );
        
        // Показать заголовок-статистику

        // if ( $page == 1 ) $out .= $this->get_catalog_stat( $taxes, $args );

        if ( $page == 1 && count( $quizess ) == 0 ) {

            // Нет тестов по таким криетиям поиска

            $out .= '<div class="col-12 p-3 card"><div class="bg-light p-5 mb-5 text-center">';
            $out .= '<p class="text-secondary mt-4"><i class="fas fa-3x fa-ellipsis-h"></i></p>';
            $out .= '<p class="mb-5">' . __( 'Нужных вам оценочных средств нет, но есть много других.', 'mif-qm' ) . '<br />';
            $out .= __( 'Измените критерии поиска, чтобы что-то найти.', 'mif-qm' ) . '</p>';
            $out .= '</div></div>';
            
        } else {

            // Вывести элементы каталога

            $last_quiz_id = NULL;
            
            foreach ( (array) $quizess as $quiz_data ) {
                
                $out .= $this->get_catalog_item( $quiz_data );
                $last_quiz_id = $quiz_data->ID;
                
            }
                    
        }

        // Выбрать последнюю запись с указанными критериями
        
        $args['order'] = 'ASC';
        $args['posts_per_page'] = 1;
        unset( $args['paged'] );
        
        $quizess = get_posts( $args );
        
        $out .= '<div class="col-12 text-center next-page p-5">';

        // Вывести скрытые поля с данными

        $out .= '<input type="hidden" name="action" value="catalog" />';
        $out .= '<input type="hidden" name="_wpnonce" value="' . wp_create_nonce( 'mif-qm' ) . '" />';     
        $next = $page + 1;
        $out .= '<input type="hidden" name="page" value="' . $next . '" />';

        // Кнопка "Показать еще" - если есть, что показывать
        
        if ( isset( $quizess[0] ) && $quizess[0]->ID != $last_quiz_id ) $out .= '<button class="btn btn-lg">' . __( 'Показать ещё', 'mif-qm' ) . '</button>';
        
        $out .= '<div class="loading" style="display: none"><i class="fas fa-spinner fa-3x fa-spin"></i></div>';
        $out .= '</div>';

        return apply_filters( 'mif_qm_screen_get_catalog', $out, $page );
    }


    
    //
    // Аргументы выбора элементов каталога
    //
    
    public function get_catalog_args( $page = 1 )
    {
    
        $args = array(
            'posts_per_page' => $this->quizess_per_page,
            'post_type' => 'quiz',
            'post_status' => 'publish',
            'order' => 'DESC',
            'orderby' => 'modified',
            'paged' => $page,
            'suppress_filters' => false
        );
    
        if ( isset( $_REQUEST['quiz_search'] ) ) {
            
            $args['s'] = sanitize_text_field( $_REQUEST['quiz_search'] );
    
        }

        // Добавить информацию о категориях
        
        if ( isset( $_REQUEST['cats'] ) ) {
            
            $tax_query = array( 'relation' => 'AND' );
            
            foreach ( (array) $_REQUEST['cats'] as $group ) {
                
                $cats = array_map( 'sanitize_key', (array) $group );
                $cats = array_diff( $cats, array( '' ) );
                
                if ( ! empty( $cats ) ) $tax_query[] = array( 'taxonomy' => 'quiz_category', 'field' => 'slug', 'terms' => $cats );
                
            }
            
            $args['tax_query'] = $tax_query;
            
        }

        return apply_filters( 'mif_qm_screen_get_catalog_args', $args, $page, $tax_query );
    }
    
    



    
    //
    // Статистика выдачи каталога
    //
    
    public function get_catalog_stat()
    {
        $out = '';
        
        // Посчитать количество
        
        $args = $this->get_catalog_args();
        $args['posts_per_page'] = -1;
        unset( $args['paged'] );

        if ( isset( $args['tax_query'][0] ) || $args['s'] ) {
            
            // Если есть какие-то учточнения - считать

            $quizess = get_posts( $args );
            $count = count( $quizess );

        } else {

            // Без уточнений - не считать

            $count = -1;

        }

        // $out .= '<div class="mb-4 col-12 stat">';
        // $out .= '<p class="h4">';


        // if ( count( $taxes ) == 0 ) {
            
        //     // Параметры не указаны - не считать
            
        //     $count = -1;
            
        // } elseif ( count( $taxes ) == 1 ) {
            
        //     // Один параметр - взять из данных таксономий

        //     $cat = get_term_by( 'slug', array_shift( $taxes ), 'quiz_category' );
        //     $count = $cat->count;

        // } else {

        //     // Несколько параметров - считать

        //     $args['posts_per_page'] = -1;
        //     unset( $args['paged'] );

        //     $quizess = get_posts( $args );
        //     $count = count( $quizess );

        // }
            
        // Сформировать строку

        if ( $count == -1 ) {

            $out .= __( 'Все тесты', 'mif_qm' );

        } elseif ( $count == 0 ) {
            
            $out .= __( 'Ничего не найдено', 'mif_qm' );
            
        } elseif ( $count == 1 ) {
            
            $out .= __( 'Найден один тест', 'mif_qm' );
            
        } elseif ( $count > 200 ) {
            
            $out .= __( 'Найдено тестов', 'mif_qm' ) . ': ' . $count;

        } else {
            
            $n_suffix = $count % 10;
            
            switch ( $n_suffix ) {
                
                case 1:

                    $out .= sprintf( __( 'Найден %s тест', 'mif-qm' ), $count );
                
                break;
                
                case 2: case 3: case 4:

                    $out .= sprintf( __( 'Найдено %s теста', 'mif-qm' ), $count );
                    
                break;
                
                default:
                    
                    $out .= sprintf( __( 'Найдено %s тестов', 'mif-qm' ), $count );

            }
            
        }
        
        // $out .= '</p>';
        // $out .= '</div>';
        
        return apply_filters( 'mif_qm_screen_get_catalog_stat', $out, $taxes, $args );
    }

        
    
    
    
    //
    // Элемент каталога тестов
    //
    
    public function get_catalog_item( $data )
    {
        if ( empty( $data ) ) return '';

        $link = get_permalink( $data->ID );
        $title = $data->post_title;
        $excerpt = $data->post_excerpt;

        $out = '';

        $out .= '<div class="card p-3 col-4"><div class="bg-light h-100">';
        
        if ( has_post_thumbnail( $data->ID ) ) {

            $out .= '<a href="' . $link . '">' . get_the_post_thumbnail( $data->ID ) . '</a>';

        }
        
        $out .= '<div class="card-block p-3">';
        $out .= '<a href="' . $link . '"><h4 class="h5 card-title">' . $title . '</h4></a>';
        $out .= '<p class="card-text">';
        if ( $excerpt ) $out .= $excerpt;
        $out .= ' <a href="' . $link . '" class=""><i class="fas fa-arrow-right"></i></a></p>';
        $out .= '</div>';
        $out .= '</div></div>';
        
        return apply_filters( 'mif_qm_screen_get_catalog_item', $out, $data );
    }
    
    
    
    //
    // Категории тестов
    //
    
    public function get_category()
    {
        $out = '';

        $arr = $this->get_category_arr();

        foreach ( $arr as $category_group ) {

            $group = $category_group['slug'];

            $out .= '<div class="mb-3">';
            
            foreach ( $category_group['cats'] as $cat ) {
                
                $out .= '<div>';
                $out .= '<span class="list-item mr-2 p-1 text-secondary rounded"><i class="fas fa-check"></i></span>';
                // $out .= '<a href="#" class="font-weight-bold category" data-group="' . $group . '" data-name="' . $cat['slug'] . ' ">' . $cat['name'] . '</a>';
                // $out .= '<a href="#" class="font-weight-bold category" data-id="' . $cat['id'] . ' ">' . $cat['name'] . '</a>';
                $out .= '<a href="#" class="font-weight-bold category" data-name="' . $cat['slug'] . ' ">' . $cat['name'] . '</a>';
                // $out .= '<input type="hidden" name="cats[]" value="">';
                $out .= '<input type="hidden" name="cats[' . $group . '][]" value="">';
                $out .= '</div>';
                
            }
            
            $out .= '</div>';

        }

        // p($categories);
        // p($arr);

        return apply_filters( 'mif_qm_screen_get_category', $out );
    }
    
    
    
    //
    // Получить массив категорий
    //
    
    public function get_category_arr()
    {
        $out = '';

        $args = array(
            'taxonomy' => 'quiz_category',
            'hide_empty' => 0,
        );

        $categories = get_categories( $args );

        // Сортировать по описаниям с сохранением сортировки по алфавиту

        $index_tree = array();
        foreach ( (array) $categories as $key => $category ) $index_tree[ (int) $category->description ][] = $key;
        ksort( $index_tree );

        $index = array();
        foreach ( $index_tree as $item ) $index = array_merge( $index, $item );

        // Построить основу массива категорий (базовые категории)

        $arr = array();

        foreach ( $index as $key ) {
            
            $category = $categories[$key];

            // Пропустить категории, которые дочерние
            
            if ( $category->parent ) continue;
            
            // Пропустить категории, которые исключены

            if ( $category->description == -1 ) continue;

            $arr[$category->term_id] = array(  'name' => $category->name, 'slug' => $category->slug, 'id' => $category->term_id );

        }

        // Добавить в массив подкатегории

        foreach ( $index as $key ) {
            
            $category = $categories[$key];

            // Пропустить категории, которые не дочерние
            
            if ( ! $category->parent ) continue;
            
            // Пропустить категории, для которых в дочерних не элемент не определене (исключенные категории - -1)
            
            if ( ! isset( $arr[$category->parent] ) ) continue;
            
            // Пропустить категории без тестов

            if ( $category->count == 0 ) continue;

            $arr[$category->parent]['cats'][] = array( 'name' => $category->name, 'slug' => $category->slug, 'id' => $category->term_id );

        }

        // p($categories);
        // p($arr);

        return apply_filters( 'mif_qm_screen_get_category_arr', $arr );
    }



    //
    // Исправляет проблему с поиском по содержимому теста
    //

    public function search_security_fix( $search, $obj ) 
    {
        global $wpdb;

        $q = $obj->query_vars;

        $search = '';
        $searchand = '';
        $n = '%';

		foreach ( $q['search_terms'] as $term ) {

            $like_op  = 'LIKE';
            $andor_op = 'OR';

			$like = $n . $wpdb->esc_like( $term ) . $n;
			$search .= $wpdb->prepare( "{$searchand}(({$wpdb->posts}.post_title $like_op %s) $andor_op ({$wpdb->posts}.post_excerpt $like_op %s))", $like, $like, $like );
			$searchand = ' AND ';

        }

		if ( ! empty( $search ) ) {
			$search = " AND ({$search}) ";
			if ( ! is_user_logged_in() ) {
				$search .= " AND ({$wpdb->posts}.post_password = '') ";
			}
		}

        return $search;
    }
    

}

?>