<?php do_action( 'mif_qm_before_quiz' );  ?>

<div class="quiz">

    <?php do_action( 'mif_qm_before_quiz_header' ); ?>
    <?php mif_qm_the_quiz_header(); ?>
    
    <?php do_action( 'mif_qm_before_quiz_param' ); ?>
    <?php mif_qm_the_quiz_param(); ?>
    
    <?php do_action( 'mif_qm_before_parts' ); ?>
    <div class="parts">

        <?php mif_qm_the_parts(); ?>

    </div>
    <?php do_action( 'mif_qm_after_questions' );  ?>

</div>

<?php do_action( 'mif_qm_after_quiz' );  ?>