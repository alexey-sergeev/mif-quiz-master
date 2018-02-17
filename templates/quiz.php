<?php do_action( 'mif_qm_before_quiz' );  ?>

<hr />

<div class="quiz">
    <form method="post" enctype="multipart/form-data">

    <?php do_action( 'mif_qm_before_quiz_menu' ); ?>
    <?php mif_qm_the_quiz_menu(); ?>

    <?php do_action( 'mif_qm_before_quiz_header' ); ?>
    <div class="mt-3 mb-3">
    <?php mif_qm_the_quiz_header(); ?>
    </div>
    
    <?php do_action( 'mif_qm_before_quiz_navigation' ); ?>
    <?php mif_qm_the_quiz_navigation(); ?>
    
    <?php do_action( 'mif_qm_before_quiz_param' ); ?>
    <?php mif_qm_the_quiz_param(); ?>
    
    <?php do_action( 'mif_qm_before_quiz_content' ); ?>
    <div class="quiz-content">
    <?php mif_qm_the_parts(); ?>
    </div>

    <?php do_action( 'mif_qm_before_quiz_next_button' ); ?>
    <div class="next-button">
    <?php mif_qm_the_next_button(); ?>
    </div>

    <?php do_action( 'mif_qm_after_questions' );  ?>
    </form>
</div>

<?php do_action( 'mif_qm_after_quiz' );  ?>