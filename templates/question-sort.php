<?php do_action( 'mif_qm_before_question' );  ?>

<div class="<?php mif_qm_the_question_classes(); ?>" id="<?php mif_qm_the_question_id(); ?>">

    <p><br />

    <?php do_action( 'mif_qm_before_question_header' ); ?>
    <?php mif_qm_the_question_header(); ?>
    <?php do_action( 'mif_qm_before_question_question' ); ?>
    <p><?php mif_qm_the_question_question(); ?></p>
    <?php do_action( 'mif_qm_before_answers' ); ?>

    <div class="answers">
    <table class="table table-bordered qm-sortable">
        <?php while ( mif_qm_the_answer() ) : ?>
        <tbody class="border-0">
            <tr>
                <td class="table-active marker p-0 pl-1">
                    <div class="p-3 pr-4">
                        <?php mif_qm_the_answer_marker(); ?>
                    </div>
                </td>
                <td class="p-0">
                    <div class="qm-draggable p-3 pl-4">
                        <?php mif_qm_the_answer_linker(); ?>    
                        <?php mif_qm_the_answer_mover(); ?>    
                        <div class="answer"><?php mif_qm_the_answer_caption(); ?></div>
                    </div>
                </td>
            </tr>
        </tbody>
        <?php endwhile; ?>
    </table>
    </div>

    <?php do_action( 'mif_qm_after_answers' );  ?>

</div>

<?php do_action( 'mif_qm_after_question' );  ?>