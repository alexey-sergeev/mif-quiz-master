<?php do_action( 'mif_qm_before_quiz' );  ?>

<hr />

<div class="quiz result mt-4 mb-4">

    <?php do_action( 'mif_qm_before_quiz_menu' ); ?>
    <?php mif_qm_the_quiz_menu(); ?>

    <?php do_action( 'mif_qm_before_result' );  ?>

    <div class="bg-light p-4 text-center">
    
        <h3 class="font-weight-normal"><?php echo __( 'Ваш результат', 'mif-qm'); ?></h3>
        
        <?php do_action( 'mif_qm_before_result_panel' );  ?>
        
        <?php mif_qm_the_result_panel(); ?>

        <?php do_action( 'mif_qm_before_result_button' );  ?>

        <div class="m-4">
            <?php mif_qm_the_result_button(); ?>
        </div>

        <?php do_action( 'mif_qm_before_result_link' );  ?>

        <div class="m-4">
            <?php mif_qm_the_result_link(); ?>
        </div>

    </div>

    <?php do_action( 'mif_qm_after_result' );  ?>

</div>

<?php do_action( 'mif_qm_after_quiz' );  ?>