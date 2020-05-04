<?php do_action( 'mif_qm_before_quiz' );  ?>

<hr />

<div class="quiz mb-4 mt-4">
    <form method="post" enctype="multipart/form-data">

    <?php do_action( 'mif_qm_before_quiz_menu' ); ?>
    <?php mif_qm_the_quiz_menu(); ?>

    <?php do_action( 'mif_qm_before_quiz_header' ); ?>

    <div class="mt-3 mb-3 quiz-header">
        <?php mif_qm_the_quiz_header(); ?>
    </div>
    
    <div class="row navigation">

        <div class="col-2"></div>
        
        <div class="col-8">
            <?php do_action( 'mif_qm_before_quiz_navigation' ); ?>
            <?php mif_qm_the_quiz_navigation(); ?>
        </div>
        
        <div class="col-2 text-right">
            <?php mif_qm_the_timer(); ?>
        </div>

    </div>

    <?php do_action( 'mif_qm_before_quiz_param' ); ?>

    <?php mif_qm_the_quiz_param(); ?>

    <div class="quiz-body">

    <?php do_action( 'mif_qm_before_quiz_content' ); ?>

    <div class="quiz-content">
        <?php mif_qm_the_parts(); ?>
    </div>

    <?php do_action( 'mif_qm_before_quiz_next_button' ); ?>

    <div class="next-button">
        <?php mif_qm_the_next_button(); ?>
    </div>

    <?php do_action( 'mif_qm_after_questions' );  ?>

    </div>

    <?php do_action( 'mif_qm_before_timeout' );  ?>

    <?php mif_qm_the_timeout(); ?>

    </form>
</div>

<?php do_action( 'mif_qm_after_quiz' );  ?>
