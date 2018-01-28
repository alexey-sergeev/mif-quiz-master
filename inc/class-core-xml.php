<?php

//
// Преобразовать XML-версию теста в массив
// 
//


defined( 'ABSPATH' ) || exit;

include_once dirname( __FILE__ ) . '/class-core-xml-explode.php';
include_once dirname( __FILE__ ) . '/class-core-xml-implode.php';


class mif_qm_core_xml  {

   
    function to_xml( $quiz_arr )
    {
        $xml = new mif_qm_core_xml_implode();
        return $xml->parse( $quiz_arr );
    }
    
    function to_array( $quiz_xml )
    {
        $xml = new mif_qm_core_xml_explode();
        return $xml->parse( $quiz_xml );
    }

}

?>