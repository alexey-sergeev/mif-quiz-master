<?php

//
// Интерпретация параметров для дальнейшей обработки
// 
//


defined( 'ABSPATH' ) || exit;



class mif_qm_param_interpretation {

    private $settings_map = array();
    private $params_map = array();
    private $key = '';
    private $value = '';
    private $mode = '';
    
    
    //
    // ключ, значение и режим
    //
    
    function __construct( $key = '', $value = '', $mode = 'quiz' )
    {
        
        $param_core = new mif_qm_param_core();
        
        $this->settings_map = $param_core->get_settings_map();
        $this->params_map = $param_core->get_params_map( $mode );
        
        $this->key = $key;
        $this->value = $value;
        $this->mode = $mode;
        
    }
    
    
    
    //
    // Получить описание параметра
    //
    
    public function get_description()
    {
        return $this->params_map[$this->key]['description'];
    }
    
    
   
    //
    // Получить интерпретацию значения параметра
    //      $separator - разделитель, если значение представлено массивом
    //                   если не указано, то возвращается массив
    //
    
    public function get_interpretation( $separator = false )
    {
        $arr = $this->get_clean_value();
        
        $out = ( isset( $arr['description'] ) ) ? $arr['description'] : $arr['value'];

        if ( is_array( $out ) && $separator ) $out = implode( $separator, $out );
        
        return $out;
    }

    
    
   
    //
    // Получить "чистое" значение с единицей измерения
    //
    
    public function get_clean_value()
    {

        $arr = array();

        switch ( $this->key ) {

            case 'attempt': case 'number':

                // Взять просто число
                
                $arr['value'] = (int) $this->value;
                                
            break;
                
            case 'rating':

                // Взять число, уточнить, если 0
                
                $arr['value'] = (int) $this->value;
                if ( $arr['value'] === 0 ) $arr['value'] = __( 'сумма по вопросам', 'mif-qm' );
                                
            break;
                
            case 'success':
                
                // Взять число и уточнить, если это процент

                $arr['value'] = (int) $this->value;

                if ( preg_match( '/^[\d]+[\s]?[%]$/', $this->value ) ) {
                    
                    $arr['unit'] = 'percent';
                    $arr['description'] = $arr['value'] . '%';

                }

            break;

            case 'time':

                // Взять число (секунды) и уточнить, если это минуты или часы

                $arr['value'] = (int) $this->value;
                $arr['unit'] = 'second';
                $arr['description'] = $arr['value'] . ' ' . __( 'сек.', 'mif-qm' );
                $arr['second'] = $arr['value'];

                if ( preg_match( '/^[\d]+[\s]?[m]$/', $this->value ) ) {
                    
                    $arr['unit'] = 'minute';
                    $arr['description'] = $arr['value'] . ' ' . __( 'мин.', 'mif-qm' );
                    $arr['second'] = $arr['value'] * 60;
                    
                } 
                
                if ( preg_match( '/^[\d]+[\s]?[h]$/', $this->value ) ) {
                    
                    $arr['unit'] = 'hours';
                    $arr['description'] = $arr['value'] . ' ' . __( 'час.', 'mif-qm' );
                    $arr['second'] = $arr['value'] * 3600;

                } 
   
            break;

            // case 'competences':

            //     // Взять массив компетенций и уточнить - из какого они стандарта

            //     foreach ( (array) $this->value as $item ) {
                    
            //         $pattern = '/\(.*\)/';
            //         $arr['value'][] = trim( preg_replace( $pattern, '', $item ) );
            //         preg_match( $pattern, $item, $res );
            //         $arr['description'][] = ' ' . trim( $res[0] );

            //     }

            // break;

            case 'settings':

                // Взять массив и подготовить описание

                $arr['value'] = $this->value;
                $arr['description'] = array();

                foreach ( (array) $this->value as $item ) {

                    if ( is_array( $this->settings_map[$item]['description'] ) ) {

                        $description = ( $this->settings_map[$item]['description'][$this->mode] ) ? $this->settings_map[$item]['description'][$this->mode] : '';
                        
                    } else {
                        
                        $description = ( $this->settings_map[$item]['description'] ) ? $this->settings_map[$item]['description'] : '';

                    }

                    $arr['description'][] = $description;

                }

            break;

            case 'competences':

                // Взять массив, добавтиь запятые между кометенциями

                $arr['value'] = $this->value;
                
                foreach ( (array) $arr['value'] as $key => $value ) {

                    $arr['value'][$key] = preg_replace( '/ /', ', ', $arr['value'][$key] );
                    $arr['value'][$key] = preg_replace( '/, \(/', ' (', $arr['value'][$key] );

                }

            break;    

            case 'tags':

                // Взять массив

                $arr['value'] = $this->value;

            break;    

        }


        return $arr;
    }

}

?>