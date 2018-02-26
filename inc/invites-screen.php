<?php

//
// Экранные методы для работы с приглашениями
// 
//


defined( 'ABSPATH' ) || exit;

include_once dirname( __FILE__ ) . '/invites-core.php';
include_once dirname( __FILE__ ) . '/invites-templates.php';


class mif_qm_invites_screen extends mif_qm_invites_core { 

    private $quiz_id;

    // Массив приглашений

    private $invites = array();
    
    function __construct( $quiz_id )
    {
        parent::__construct();
        $this->quiz_id = $quiz_id;
    }



    //
    // Вывести окно списков приглашений
    //

    public function the_invites()
    {
        // Смотреть приглашения может только эксперт и выше

        if ( ! ( $this->access_level( $this->quiz_id ) > 1 ) ) return;

        // Получить список пользователей

        // $this->invites = $this->get( $this->quiz_id );

        // Подключить шаблон из темы оформления или локальный

        if ( $template = locate_template( 'invites.php' ) ) {
           
            load_template( $template, false );

        } else {

            load_template( dirname( __FILE__ ) . '/../templates/invites.php', false );

        }    

    }


    //
    // Форма добавления инвайтов
    //

    public function get_add_form()
    {
        $out = '';

        $out .= '<div class="mt-5 text-center"><h4>' . __( 'Новые приглашения', 'mif-qm' ) . '</h4></div>';

        $out .= '<form method="POST">';
        $out .= '<div>';
        $out .= '<p class="text-center">' . __( 'Укажите список обучающихся для подготовки приглашений', 'mif-qm' ) . '</p>';

        $out .= '<textarea name="members-list" style="height: 20rem;"></textarea>';
        $out .= '<div class="text-center m-4"><button class="btn">' . __( 'Создать приглашения', 'mif-qm' ) . '</button></div>';


        $out .= '<br />';
        $out .= '</div>';


        $out .= '<input type="hidden" name="action" value="invites" />';
        $out .= '<input type="hidden" name="_wpnonce" value="' . wp_create_nonce( 'mif-qm' ) . '" />';
        $out .= '<input type="hidden" name="quiz_id" value="' . $this->quiz_id . '" />';

        $out .= '</form>';

        return apply_filters( 'mif_qm_invites_screen_get_add_form', $out );
    }


    //
    // Список приглашений
    //

    public function get_invites()
    {
        $out = '';
        
        $invites = $this->get_list( $this->quiz_id );
        
        $out .= '<div class="mt-5 text-center"><h3>' . __( 'Приглашения', 'mif-qm' ) . '</h3></div>';

        $out .= '<div class="row no-gutters mt-5 border-bottom">';
        $out .= '<div class="col-1 text-center bg-light p-2 font-weight-bold">№</div>';
        $out .= '<div class="col-8 text-center bg-light p-2 font-weight-bold">' . __( 'Ф. И. О.', 'mif-qm' ) . '</div>';
        $out .= '<div class="col-3 text-center bg-light p-2 font-weight-bold">' . __( 'Код', 'mif-qm' ) . '</div>';
        $out .= '</div>';
        
        if ( count( $invites ) > 0 ) {
   
            $n = 1;
            
            foreach ( (array) $invites as $invite ) {
                
                $out .= '<div class="row no-gutters border-bottom">';
                
                $out .= '<div class="col-1 text-center p-1">' . $n . '</div>';
                $out .= '<div class="col-8 p-1">' . $invite['fullname'] . '</div>';
                $out .= '<div class="col-3 p-1">' . $invite['invite_code'] . '</div>';
                
                $out .= '</div>';
                
                $n++;    
                
            }
            
        } else {
            
            $out .= '<div class="row no-gutters border-bottom">';
            $out .= '<div class="col-12 p-1 pl-3 alert-warning">' . __( 'Приглашения отсутствуют', 'mif-qm' ) . '</div>';
            $out .= '</div>';

        }

        return apply_filters( 'mif_qm_invites_screen_get_add_form', $out );
    }

}


?>
