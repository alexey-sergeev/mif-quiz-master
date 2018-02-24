<?php

//
// Методы страницы мастерской
// 
//


defined( 'ABSPATH' ) || exit;




class mif_qm_workroom extends mif_qm_screen { 

    
    function __construct()
    {
        parent::__construct();


    }



    // 
    // Показывает страницу мастерской
    // 

    public function the_workroom()
    {
        $out = '';

        $out .= '<div class="p-5 m-5 text-center">';

        if ( current_user_can( 'edit_posts' )  ) {

            $out .= '<form action="' . admin_url( 'post-new.php' ) . '">';
            $out .= '<input type="hidden" name="post_type" value="quiz">';
            $out .= '<button href="#" class="btn btn-lg">' . __( 'Создать новый тест', 'mif-qm' ) . '</button>';
            $out .= '</form>';

        } else {

            $out .= __( 'Создавать тесты могут только авторы сайта', 'mif-qm' );

        }



        $out .= '</div>';
        $out .= '<p><br />';

        echo $out;
    }

    


}


?>