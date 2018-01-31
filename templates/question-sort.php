<?php do_action( 'mif_qm_before_question' );  ?>

<div class="<?php mif_qm_the_question_classes(); ?>">

    <p><br />

    <?php do_action( 'mif_qm_before_question_header' ); ?>
    <?php mif_qm_the_question_header(); ?>
    <?php do_action( 'mif_qm_before_question_question' ); ?>
    <p><?php mif_qm_the_question_question(); ?></p>
    <?php do_action( 'mif_qm_before_answers' ); ?>

    <div class="answers">
    <table class="table table-bordered">
        <?php while ( mif_qm_the_answer() ) : ?>
        <tbody class="border-0">
            <tr>
                <td class="table-active marker"><?php mif_qm_the_answer_marker(); ?></td>
                <td>
                    <?php mif_qm_the_answer_mover(); ?>    
                    <div class="answer"><?php mif_qm_the_answer_caption(); ?></div>
                </td>
            </tr>
        </tbody>
        <?php endwhile; ?>
    </table>
    </div>

    <?php do_action( 'mif_qm_after_answers' );  ?>

</div>

<?php do_action( 'mif_qm_after_question' );  ?>