<?php do_action( 'mif_qm_before_question' );  ?>

<div class="<?php mif_qm_the_question_classes(); ?>">

    <p><br />

    <?php do_action( 'mif_qm_before_header' ); ?>
    <?php mif_qm_the_question_header(); ?>
    <?php do_action( 'mif_qm_before_question' ); ?>
    <p><?php mif_qm_the_question_question(); ?></p>
    <?php do_action( 'mif_qm_before_answers' ); ?>

    <div class="answers">
    <table class="table table-hover">
        <?php while ( mif_qm_the_answer() ) : ?>
        <tbody class="border-0"><tr class="<?php mif_qm_the_answer_classes(); ?>"><td>
            <div class="form-check">
                <label class="form-check-label col-12 row no-gutters">
                    <?php mif_qm_the_answer_marker(); ?>
                    <div class="answer"><?php mif_qm_the_answer_answer(); ?></div>
                </label>
            </div>
        </td></tr></tbody>
        <?php endwhile; ?>
    </table>
    </div>

    <?php do_action( 'mif_qm_after_answers' );  ?>

</div>

<?php do_action( 'mif_qm_after_question' );  ?>