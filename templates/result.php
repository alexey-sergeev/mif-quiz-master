<?php do_action( 'mif_qm_before_quiz' );  ?>

<hr />

<div class="quiz result mb-4">

    <?php do_action( 'mif_qm_before_quiz_menu' ); ?>
    <?php mif_qm_the_quiz_menu(); ?>

    <?php do_action( 'mif_qm_before_result' );  ?>

    <div class="bg-light p-4 text-center">
    
        <h3 class="font-weight-normal"><?php echo __( 'Ваш результат', 'mif-qm') ?></h3>
        
        <div class="p-3 m-3 h2">
            <?php mif_qm_the_result( 'rating' ); ?> <?php echo __( 'из', 'mif-qm') ?> <?php mif_qm_the_result( 'max' ); ?> (<?php mif_qm_the_result( 'percent' ); ?>%)
        </div>

        <div class="row justify-content-center">
            <div class="progress w-50 " style="height: 1.5rem;">
                <div class="progress-bar bg-<?php mif_qm_the_successed_class() ?>" role="progressbar" style="width: <?php mif_qm_the_result( 'percent' ); ?>%" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>

        <div class="m-4 h4">
            <?php mif_qm_the_successed_caption(); ?>
        </div>

        <div class="m-4">
            <?php mif_qm_the_result_button(); ?>
        </div>

    </div>

    <?php do_action( 'mif_qm_after_result' );  ?>

</div>

<?php do_action( 'mif_qm_after_quiz' );  ?>