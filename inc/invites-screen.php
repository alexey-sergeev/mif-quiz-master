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


}


?>
