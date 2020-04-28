<?php do_action( 'mif_qm_before_quiz' );  ?>

<hr />

<div class="quiz result mb-4 mt-4">

    <?php do_action( 'mif_qm_before_quiz_menu' ); ?>
    <?php mif_qm_the_quiz_menu(); ?>

    <?php do_action( 'mif_qm_before_result_list' );  ?>

    <div>
        <?php mif_qm_the_result_list(); ?>
    </div>

    <?php do_action( 'mif_qm_after_result_list' );  ?>

</div>

<?php do_action( 'mif_qm_after_quiz' );  ?>