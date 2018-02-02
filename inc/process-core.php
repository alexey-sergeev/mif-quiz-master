<?php

//
// Ядро процесса обработки
// 
//


defined( 'ABSPATH' ) || exit;



class mif_qm_process_core {


    function __construct()
    {
        // parent::__construct();
    }


    public function get_action()
    {

        $action = isset( $_REQUEST['action'] ) ? sanitize_key( $_REQUEST['action'] ) : 'view';

        return $action;
    }



}


?>