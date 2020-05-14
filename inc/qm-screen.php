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
    
    // Количество элементов в блоке "Мои тесты"

    private $my_quizess_count = 4;

    

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
        
        if ( isset( $_REQUEST['owner'] ) ) {
            
            $args['author_name'] = sanitize_key( $_REQUEST['owner'] );
    
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

        return apply_filters( 'mif_qm_screen_get_catalog_args', $args, $page );
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
        
        return apply_filters( 'mif_qm_screen_get_catalog_stat', $out, $args );
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
            
            foreach ( (array) $category_group['cats'] as $cat ) {
                
                $out .= '<div>';
                $out .= '<span class="list-item mr-2 p-1 text-secondary rounded"><i class="fas fa-check"></i></span>';
                // $out .= '<a href="#" class="font-weight-bold category" data-group="' . $group . '" data-name="' . $cat['slug'] . ' ">' . $cat['name'] . '</a>';
                // $out .= '<a href="#" class="font-weight-bold category" data-id="' . $cat['id'] . ' ">' . $cat['name'] . '</a>';
                $out .= '<a href="#" class="font-weight-bold category" data-name="' . $cat['slug'] . '">' . $cat['name'] . '</a>';
                // $out .= '<input type="hidden" name="cats[]" value="">';
                $out .= '<input type="hidden" name="cats[' . $group . '][]" value="">';
                $out .= '</div>';
                
            }
            
            $out .= '</div>';

        }

        // Блок галочки, что только свои

        if ( $this->get_count_quiz() ) {

            $out .= '<div class="mb-3">';
            $out .= '<div>';
            $out .= '<span class="list-item mr-2 p-1 text-secondary rounded"><i class="fas fa-check"></i></span>';
            $out .= '<a href="#" class="font-weight-bold category" data-name="' . $this->get_user_token() . '">' . __( 'Я автор', 'mif_qm' ) . '</a>';
            $out .= '<input type="hidden" name="owner" value="">';
            $out .= '</div>';
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
    // Возвращает форму ввода инвайта
    //

    public function get_invite_form() 
    {
        $out = '';

        $invite_code = ( isset( $_REQUEST['invite_code'] ) ) ? sanitize_key( $_REQUEST['invite_code'] ) : '';

        $out .= '<div class="card p-2 invite">';
        $out .= '<form method="POST">';
        $out .= '<div class="card-body">';
        $out .= '<h4 class="h4 text-white t-light">' . __( 'Приглашения', 'mif-qm' ) . '</h4>';
        $out .= '<p>' . __( 'введите код', 'mif-qm' ) . '</p>';
        $out .= '<div class="input-group input-group-lg pl-lg-4 pr-lg-4 pb-4">';
        $out .= '<input type="text" class="form-control" name="invite_code" value="' . $invite_code . '">';
        $out .= '</div>';
        $out .= '<button class="btn btn-lg">' . __( 'Пройти тест', 'mif-qm' ) . '</button>';
        $out .= '</div>';

        $out .= '<input type="hidden" name="action" value="invite" />';
        $out .= '<input type="hidden" name="_wpnonce" value="' . wp_create_nonce( 'mif-qm' ) . '" />';     

        $out .= '</form>';
        $out .= '</div>';

        return apply_filters( 'mif_qm_screen_get_invite_form', $out );
    }
    


    //
    // Возвращает блок "Ваши тесты"
    //

    public function get_you_quizess() 
    {
        $out = '';

        $prefix = '<div class="p-4 mr-4 mark">';
        $suffix = '</div>';
        
        if ( is_user_logged_in() ) {
            
            // Получить список текущих тестов
            
            $process_snapshots = new mif_process_snapshots();
            $current_snapshots = $process_snapshots->get( array( 'user' => get_current_user_id(), 'numberposts' => $this->my_quizess_count ) );
            
            // Получить список завершенных тестов

            $process_results = new mif_qm_process_results();
            $results = $process_results->get_results( array( 'user' => get_current_user_id(), 'numberposts' => $this->my_quizess_count ) );

            $companions = array_slice( array_merge( $current_snapshots, $results ), 0, $this->my_quizess_count ) ;

            if ( empty( $companions ) ) {

                $out .= $prefix;
                $out .= __( 'У вас пока нет пройденных тестов', 'mif-qm' );
                $out .= $suffix;

            } else {

                foreach ( $companions as $companion ) {

                    $quiz_data = get_post( $companion->post_parent );
                   
                    if ( ! $quiz_data ) continue;

                    $link = get_permalink( $quiz_data->ID );

                    $out .= '<div class="media mt-2"><i class="fas fa-chevron-right mt-1"></i><div class="media-body ml-3">';

                    if ( $companion->post_type == 'quiz_snapshot' ) $out .= '<span class="bg-primary text-white p-1 rounded" title="' . __( 'Тест не завершен', 'mif-qm' ) . '"><i class="fas fa-flag-checkered"></i></span> ';

                    $out .= '<a href="' . $link . '" class="font-weight-bold">' . $quiz_data->post_title . '</a>';
                    $out .= '</div></div>';

                }

            }

        } else {

            $out .= $prefix;
            $out .= '<p>' . __( 'Войдите на сайт, чтобы посмотреть свои тесты', 'mif-qm' ) . '</p>';
            $out .= '<p><form action="' . wp_login_url() . '">';
            $out .= '<input type="hidden" name="redirect_to" value="' . get_permalink() . '">';
            $out .= '<button class="btn">' . __( 'Войти', 'mif-qm' ) . '</button></form></p>';
            // $out .= '<a href="#" class="btn btn-large">' . __( 'Войти', 'mif-qm' ) . '</a>';
            $out .= $suffix;

        }

        return apply_filters( 'mif_qm_screen_get_you_quizess', $out );
    }
    
    
    
    //
    // Возвращает ссылку на страницу сайта
    //
    
    public function get_url( $page = '' ) 
    {
        $slug = '';

        switch ( $page ) {

            case 'home':

                $slug = $this->post_name_home;
    
                break;
    
            case 'profile':

                $slug = $this->post_name_profile;
    
                break;
    
            case 'workroom':
    
                $slug = $this->post_name_workroom;
    
                break;
    
            case 'results':
    
                $slug = $this->post_name_results;
    
                break;
    
            case 'help':
    
                $slug = $this->post_name_help;
    
                break;

        }
        
        $out = get_permalink( get_page_by_path( $slug ) );

        return apply_filters( 'mif_qm_screen_get_workroom_url', $out );
    }



    //
    // Исправляет проблему с поиском по содержимому теста
    //

    public function search_security_fix( $search, $obj ) 
    {
        if ( empty( $search ) ) return;
        
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