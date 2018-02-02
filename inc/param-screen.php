<?php

//
// Экранные методы для работы с параемтрами
// 
//


defined( 'ABSPATH' ) || exit;

include_once dirname( __FILE__ ) . '/param-interpretation.php';


class mif_qm_param_screen {

    private $param = array();
    private $mode = 'quiz';


    function __construct( $param = array(), $mode = 'quiz' )
    {

        $this->param = $param;
        $this->mode = $mode;

    }


    // 
    // Получить экранную форму
    // 

    public function get_show()
    {
        
        $out = '';
        
        $title = ( $this->mode == 'part' ) ? __( 'Параметры раздела', 'mif-qm' ) : __( 'Параметры теста', 'mif-qm' );
        
        $out .= '<div class="bg-light p-3">';
        $out .= '<h4 class="pt-2">' . $title . '</h4>';
        // $out .= '<div class="bs-callout bs-callout-warning">';
        // $out .= '<h4 class="pt-1 pb-1">' . $title . '</h4>';

        $out .= '<hr />';
        $out .= '<div>';
        
        foreach ( (array) $this->param as $key => $value ) {
            
            if ( $key == 'settings' ) continue;

            $param_interpretation = new mif_qm_param_interpretation( $key, $value, $this->mode );

            if ( $param_interpretation->get_description() == '' ) continue;

            $out .= '<div class="p-1">';
            $out .= '' . $param_interpretation->get_description() . ': ';
            $out .= '<span class="bg-secondary text-light rounded p-1 pl-2 pr-2">' . $param_interpretation->get_interpretation( '</span>, <span class="bg-secondary text-light rounded p-1 pl-2 pr-2">' ) . '</span>';
            // $out .= '<span class="bg-light text-secondary rounded p-1 pl-2 pr-2">' . $param_interpretation->get_interpretation( '</span>, <span class="bg-light text-secondary rounded p-1 pl-2 pr-2">' ) . '</span>';
            $out .= '</div>';
            
        }
        
        if ( isset( $this->param['settings'] ) ) {
            
            $param_interpretation = new mif_qm_param_interpretation( 'settings', $this->param['settings'], $this->mode );
            
            $out .= '<div class="p-1">';
            $out .= $param_interpretation->get_interpretation( '</div><div class="p-1">' );
            $out .= '</div>';

        }

        $out .= '</div>';
        $out .= '</div>';

        return $out;
    }  

}

?>