<?php

//
// Методы страницы профиля
// 
//


defined( 'ABSPATH' ) || exit;




class mif_qm_profile extends mif_qm_screen { 

    
    function __construct()
    {
        parent::__construct();


    }



    // 
    // Показывает страницу профиля
    // 

    public function the_profile()
    {
        $out = '';

        if ( is_user_logged_in() ) {

            // Получить список результатов по тестам, которые проходил пользователь

            $process_results = new mif_qm_process_results();
            $results = $process_results->get_results( array( 'user' => get_current_user_id() ) );

            // Вывести на экран

            $out .= '<div class="row no-gutters">';
            $out .= '<div class="col-1 bg-light text-center font-weight-bold p-2">№</div>';
            $out .= '<div class="col-6 bg-light text-center font-weight-bold p-2">' . __( 'Тест', 'mif-qm' ) . '</div>';
            $out .= '<div class="col-2 bg-light text-center font-weight-bold p-2">' . __( 'Баллы', 'mif-qm' ) . '</div>';
            $out .= '<div class="col-2 bg-light text-center font-weight-bold p-2">' . __( 'Ссылка', 'mif-qm' ) . '</div>';
            $out .= '</div>';

            $n = 1;

            foreach ( $results as $result_data ) {
                
                $result = $process_results->to_array( $result_data->post_content );
                
                $rating = '';
                $class = '';

                foreach ( $result as $item ) {
                    
                    if ( isset( $item['current'] ) && $item['current'] == 'yes' ) {
                        
                        $rating = $item['rating'] . ' / ' . $item['max'] . ' (' . $item['percent'] . ')';
                        $class = ( $item['success'] == 'yes' ) ? ' alert-success' : ' alert-danger';

                    }

                }

                $out .= '<div class="row no-gutters">';
                
                $quiz_data = get_post( $result_data->post_parent );
                $out .= '<div class="col-1 text-center p-2">' . $n . '</div>';
                $out .= '<div class="col-6 p-2">' . $quiz_data->post_title . '</div>';
                $out .= '<div class="col-2 text-center p-2' . $class . '">' . $rating . '</div>';
                $out .= '<div class="col-2 text-center p-2"><a href="' . get_permalink( $result_data->post_parent ) . '?action=result">' . __( 'смотреть', 'mif-qm' ) . '</a></div>';

                $out .= '</div>';
                $n++;




// p($result);

                // $process_screen = new mif_qm_process_screen();
                // $out .= $process_screen->get_result_list( $result );
                
            }
            
            
        //     arsort( $arr );
            
        //     $out .= '<div class="row no-gutters">';
        //     $out .= '<div class="col-1 bg-light text-center font-weight-bold p-2">№</div>';
        //     $out .= '<div class="col-6 bg-light text-center font-weight-bold p-2">' . __( 'Тест', 'mif-qm' ) . '</div>';
        //     $out .= '<div class="col-2 bg-light text-center font-weight-bold p-2">' . __( 'Балл', 'mif-qm' ) . '</div>';
        //     $out .= '<div class="col-2 bg-light text-center font-weight-bold p-2">' . __( 'Ссылка', 'mif-qm' ) . '</div>';
        //     $out .= '</div>';
            
        //     $n = 1;

        //     foreach ( $arr as $key => $item ) {
                
        //         $out .= '<div class="row no-gutters">';
                
        //         $quiz_data = get_post( $key );
        //         $out .= '<div class="col-1 text-center p-2">' . $n . '</div>';
        //         $out .= '<div class="col-6 p-2">' . $quiz_data->post_title . '</div>';
        //         $out .= '<div class="col-2 text-center p-2">' . $item . '</div>';
        //         $out .= '<div class="col-2 text-center p-2"><a href="' . get_permalink( $key ) . '?action=result">' . __( 'смотреть', 'mif-qm' ) . '</a></div>';

        //         $out .= '</div>';
        //         $n++;

        //     }
            
        } else {

            $out .= '<div class="p-5 m-5 text-center">' . __( 'Только зарегистрированные пользователи могут смотреть свой профиль', 'mif-qm' ) . '</div>';

        }

        // p($results);

        echo $out;
    }

    


}


?>