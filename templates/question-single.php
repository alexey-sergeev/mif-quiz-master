<?php do_action( 'mif_qm_before_question' );  ?>

<div class="<?php mif_qm_the_question_classes(); ?>">

    <?php do_action( 'mif_qm_before_title' );  ?>

    <div class="title"><?php mif_qm_the_question_title(); ?><div>

    <?php do_action( 'mif_qm_after_title' );  ?>
    <?php do_action( 'mif_qm_before_answers' );  ?>

    <div class="answers">
        <table>
            
            <?php while ( mif_qm_the_answer() ) : ?>

            <tr class="<?php mif_qm_the_answer_classes(); ?>">
                <td class="mark"><?php mif_qm_the_answer_mark(); ?></td>
                <td class="answer"><?php mif_qm_the_answer_answer(); ?></td>
            </tr>  

            <?php endwhile; ?>

        </table>
    <div>

    <?php do_action( 'mif_qm_after_answers' );  ?>

</div>

<?php do_action( 'mif_qm_after_question' );  ?>