<?php

//
// Преобразовать XML-версию теста в массив
// 
//


defined( 'ABSPATH' ) || exit;

include_once dirname( __FILE__ ) . '/xml-explode.php';
include_once dirname( __FILE__ ) . '/xml-implode.php';


class mif_qm_xml_core  {

   
    function to_xml( $quiz_arr )
    {
        $xml = new mif_qm_xml_implode();
        return $xml->parse( $quiz_arr );
    }
    
    function to_array( $quiz_xml )
    {
        $xml = new mif_qm_xml_explode();
        return $xml->parse( $quiz_xml );
    }
    
    function get_formatted_xml( $xml )
    {
        $xml_implode = new mif_qm_xml_implode();
        return $xml_implode->get_formatted_xml( $xml );
    }

}

?>