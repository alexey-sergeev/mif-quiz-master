<?php do_action( 'mif_qm_before_quiz' );  ?>

<hr />

<div class="quiz start mb-4">

    <?php do_action( 'mif_qm_before_quiz_menu' ); ?>
    <?php mif_qm_the_quiz_menu(); ?>

    <?php do_action( 'mif_qm_before_start' );  ?>

    <div class="bg-light p-4 text-center">
        
        <?php do_action( 'mif_qm_before_start_panel' );  ?>

        <div class="pb-4 row justify-content-center">
            <?php mif_qm_the_start_panel(); ?>
        </div>

        <?php do_action( 'mif_qm_before_start_button' );  ?>

        <div class="m-4">
            <?php mif_qm_the_start_button(); ?>
        </div>

        <div class="m-4">
            <?php mif_qm_the_result_link(); ?>
        </div>

    </div>
    <?php do_action( 'mif_qm_after_start' );  ?>

</div>

<?php do_action( 'mif_qm_after_quiz' );  ?>