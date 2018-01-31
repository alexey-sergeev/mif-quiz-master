<?php

//
// Экранные методы для работы с тестом
// 
//


defined( 'ABSPATH' ) || exit;

// include_once dirname( __FILE__ ) . '/class-core-core.php';
// include_once dirname( __FILE__ ) . '/class-screen-question.php';
include_once dirname( __FILE__ ) . '/class-screen-part.php';
// include_once dirname( __FILE__ ) . '/templates-question.php';


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


        $num = 1;

        foreach ( (array) $this->quiz['parts'] as $a => $part ) {

            foreach ( (array) $part['questions'] as $b => $question ) 
            {

                $this->quiz['parts'][$a]['questions'][$b]['num'] = $num++;

            }

        }





        global $mif_qm_screen_part;
        //
        $num = 1;

        foreach ( (array) $this->quiz['parts'] as $part ) {

            $mif_qm_screen_part = new mif_qm_screen_part( $part );
            // $mif_qm_screen_part->show( array( 'mode' => 'run' ) );
            $mif_qm_screen_part->show();

            // foreach ( (array) $part['questions'] as $question ) 
            // {
            //     if ( ! isset( $question['num'] ) ) $question['num'] = $num++;

            //     // $mif_qm_screen_question->show( 'run' );
    
            // }

        }

        // global $mif_qm_screen_question;
        // //
        // $num = 1;

        // foreach ( (array) $this->quiz['parts'] as $part ) {

        //     foreach ( (array) $part['questions'] as $question ) 
        //     {
        //         if ( ! isset( $question['num'] ) ) $question['num'] = $num++;

        //         $mif_qm_screen_question = new mif_qm_screen_question( $question );
        //         $mif_qm_screen_question->show();
        //         // $mif_qm_screen_question->show( 'run' );
    
        //     }

        // }



        
        
        

    }

}

?>