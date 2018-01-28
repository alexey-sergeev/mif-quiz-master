<?php do_action( 'mif_qm_before_question' );  ?>

<div class="<?php mif_qm_the_question_classes(); ?>">

    <?php do_action( 'mif_qm_before_title' );  ?>

    <div class="title"><?php mif_qm_the_question_title(); ?><div>

    <?php do_action( 'mif_qm_after_title' );  ?>
    <?php do_action( 'mif_qm_before_answers' );  ?>

    <div class="answers">
            
        <?php while ( mif_qm_the_answer() ) : ?>

        <div class="<?php mif_qm_the_answer_classes(); ?>">
        
            <?php mif_qm_the_answer_handmake(); ?>
            
        </div>

        <?php endwhile; ?>

    <div>

    <?php do_action( 'mif_qm_after_answers' );  ?>

</div>

<?php do_action( 'mif_qm_after_question' );  ?>