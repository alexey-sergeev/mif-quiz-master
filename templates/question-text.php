<?php do_action( 'mif_qm_before_question' );  ?>

<div class="<?php mif_qm_the_question_classes(); ?>">

    <p><br />

    <?php do_action( 'mif_qm_before_question_header' ); ?>
    <?php mif_qm_the_question_header(); ?>
    <?php do_action( 'mif_qm_before_question_question' ); ?>
    <p><?php mif_qm_the_question_question(); ?></p>
    <?php do_action( 'mif_qm_before_answers' ); ?>

    <div class="answers form-group">
        <?php while ( mif_qm_the_answer() ) : ?>
        <div class="form-group <?php mif_qm_the_answer_classes(); ?>">
            <p><?php mif_qm_the_answer_handmake(); ?></p>
        </div>
        <?php endwhile; ?>
    </div>

    <?php do_action( 'mif_qm_after_answers' );  ?>

</div>

<?php do_action( 'mif_qm_after_question' );  ?>