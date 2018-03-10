<?php

//
// Экранные методы для работы с параемтрами
// 
//


defined( 'ABSPATH' ) || exit;

include_once dirname( __FILE__ ) . '/param-interpretation.php';


class mif_qm_param_screen extends mif_qm_param_core {

    private $param = array();
    private $mode = 'quiz';


    function __construct( $param = array(), $mode = 'quiz' )
    {
        parent::__construct();

        $this->param = $param;
        $this->mode = $mode;

    }


    // 
    // Получить экранную форму
    // 

    public function get_show( $quiz = array() )
    {
        
        $out = '';
        
        $out .= '<div class="bg-light p-3 mb-3">';
        
        if ( $this->mode == 'quiz' ) {
            
            $out .= '<h3 class="pt-2 text-center">' . __( 'Параметры теста', 'mif-qm' ) . '</h3>';
            $out .= $this->get_stat_panel( $quiz );
            
        } else {
            
            $out .= '<h4 class="pt-2">' . __( 'Параметры раздела', 'mif-qm' ) . '</h4>';

        }

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
        
        $yes = ': <span class="bg-secondary text-light rounded p-1 pl-2 pr-2">' . __( 'да', 'mif-qm' ) . '</span>';

        if ( isset( $this->param['settings'] ) ) {
            
            $param_interpretation = new mif_qm_param_interpretation( 'settings', $this->param['settings'], $this->mode );
            
            $out .= '<div class="p-1">';
            $out .= $param_interpretation->get_interpretation( $yes . '</div><div class="p-1">' );
            $out .= $yes . '</div>';

        }

        $out .= '</div>';
        $out .= '</div>';

        return $out;
    }  



    // 
    // Возвращает панель с пояснениями на странице начала теста
    // 
    
    public function get_stat_panel( $quiz = array() )
    {
        $arr = array();
        
        $attempt = $this->get_clean( 'attempt', $quiz );
        $success = $this->get_clean( 'success', $quiz, 'quiz', true );
        
        $time = $this->get_clean( 'time', $quiz, 'quiz', true );
        $time = ( $time['value'] == 0 ) ? $time = __( 'нет', 'mif-qm') : $time['description']; 
        
        $number = $this->get_question_count( $quiz );
        $rating = $this->get_max_rating( $quiz );
        
        $arr[] = '<span title="' . __( 'Количество попыток', 'mif-qm') . '"><i class="fas fa-2x fa-flag-checkered mr-2" aria-hidden="true"></i>' . $attempt . '</span>';
        $arr[] = '<span title="' . __( 'Количество вопросов', 'mif-qm') . '"><i class="fas fa-2x mr-2 fa-list-ul"></i>' . $number . '</span>';
        $arr[] = '<span title="' . __( 'Максимальный балл', 'mif-qm') . '"><i class="fas fa-2x fa-graduation-cap mr-2" aria-hidden="true"></i></i>' . $rating . '</span>';
        $arr[] = '<span title="' . __( 'Порог положительной оценки', 'mif-qm') . '"><i class="fas fa-2x fa-tachometer-alt mr-2" aria-hidden="true"></i>' . $success['description'] . '</span>';
        // !!! $arr[] = '<span title="' . __( 'Ограничение времени', 'mif-qm') . '"><i class="far fa-2x fa-clock mr-2" aria-hidden="true"></i>' . $time . '</span>';
        
        $class = ' class="p-2 m-2 bg-secondary text-light rounded"';

        $out = '';
        $out .= '<div class="row justify-content-center">';
        $out .= '<div' . $class . '>' . implode( '</div><div' . $class . '>', $arr ) . '</div>';
        $out .= '</div>';
        
        return apply_filters( 'mif_qm_param_screen_get_stat_panel', $out, $arr, $quiz );
    }

}

?>