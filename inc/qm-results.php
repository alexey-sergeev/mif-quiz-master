<?php

//
// Методы страницы результатов
// 
//


defined( 'ABSPATH' ) || exit;




class mif_qm_results extends mif_qm_screen { 

    
    function __construct()
    {
        parent::__construct();


    }



    // 
    // Показывает страницу результатов
    // 

    public function the_results()
    {
        $out = '';

        if ( current_user_can( 'edit_posts' ) ) {

            // Получить список результатов по моим тестам

            $process_results = new mif_qm_process_results();
            $results = $process_results->get_results( array( 'author' => get_current_user_id() ) );

            // Посчитать, сколько результатов по каким тестам
            
            $arr = array();

            foreach ( $results as $result ) {
                
                if ( isset( $arr[$result->post_parent] ) ) {
                    
                    $arr[$result->post_parent]++;
                    
                } else {
                    
                    $arr[$result->post_parent] = 1;
                    
                }
                
            }
            
            arsort( $arr );
            
            $out .= '<div class="row no-gutters">';
            $out .= '<div class="col-1 bg-light text-center font-weight-bold p-2">№</div>';
            $out .= '<div class="col-6 bg-light text-center font-weight-bold p-2">' . __( 'Тест', 'mif-qm' ) . '</div>';
            $out .= '<div class="col-2 bg-light text-center font-weight-bold p-2">' . __( 'Количество', 'mif-qm' ) . '</div>';
            $out .= '<div class="col-2 bg-light text-center font-weight-bold p-2">' . __( 'Ссылка', 'mif-qm' ) . '</div>';
            $out .= '</div>';
            
            $n = 1;

            foreach ( $arr as $key => $item ) {
                
                $out .= '<div class="row no-gutters">';
                
                $quiz_data = get_post( $key );
                $out .= '<div class="col-1 text-center p-2">' . $n . '</div>';
                $out .= '<div class="col-6 p-2">' . $quiz_data->post_title . '</div>';
                $out .= '<div class="col-2 text-center p-2">' . $item . '</div>';
                $out .= '<div class="col-2 text-center p-2"><a href="' . get_permalink( $key ) . '?action=result">' . __( 'смотреть', 'mif-qm' ) . '</a></div>';

                $out .= '</div>';
                $n++;

            }
            
        } else {

            $out .= '<div class="p-5 m-5 text-center">' . __( 'Результаты тестов могут смотреть только авторы и эксперты сайта', 'mif-qm' ) . '</div>';

        }

        // p($results);

        echo $out;
    }

    


}


?>