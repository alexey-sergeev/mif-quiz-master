<?php

//
// Экранные методы для работы с тестом
// 
//


defined( 'ABSPATH' ) || exit;

// include_once dirname( __FILE__ ) . '/class-core-core.php';
include_once dirname( __FILE__ ) . '/class-screen-question.php';
include_once dirname( __FILE__ ) . '/templates-question.php';


class mif_qm_screen_quiz extends mif_qm_screen_core {

    private $quiz = array();
    
    function __construct( $quiz )
    {

        parent::__construct();
        
        // Привести XML в array, если надо
        
        if ( ! is_array( $quiz ) ) {
            
            $xml = new mif_qm_core_xml();
            $quiz = $xml->to_array( $quiz );
            
        }
    
        $this->quiz = apply_filters( 'mif_qm_screen_core_quiz',  $quiz );
    
    }


    function show()
    {
        if ( empty( $this->quiz ) ) return;

        global $mif_qm_screen_question;
        //

        foreach ( (array) $this->quiz['parts'] as $part ) {

            foreach ( (array) $part['questions'] as $question ) 
            {
    
                $mif_qm_screen_question = new mif_qm_screen_question( $question );
                $mif_qm_screen_question->show();
    
            }

        }



        
        
        

    }

}

?>