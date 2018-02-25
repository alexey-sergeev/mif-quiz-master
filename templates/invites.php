<?php do_action( 'mif_qm_before_quiz' );  ?>

<hr />

<div class="quiz invites mb-4 mt-4">

    <?php do_action( 'mif_qm_before_quiz_menu' ); ?>
    <?php mif_qm_the_quiz_menu(); ?>

    <?php do_action( 'mif_qm_before_invites' );  ?>

    <div>
        <?php // mif_qm_the_access_mode_panel(); ?>
    </div>

123
    <?php do_action( 'mif_qm_after_invites' );  ?>

</div>

<?php do_action( 'mif_qm_after_quiz' );  ?>