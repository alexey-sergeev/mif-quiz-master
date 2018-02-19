<?php do_action( 'mif_qm_before_quiz' );  ?>

<hr />

<div class="quiz members mb-4 mt-4">

    <?php do_action( 'mif_qm_before_quiz_menu' ); ?>
    <?php mif_qm_the_quiz_menu(); ?>

    <?php do_action( 'mif_qm_before_members' );  ?>

    <div>
        <?php mif_qm_the_access_mode_panel(); ?>
    </div>

    <div>
        <?php mif_qm_the_members_part( 'master' ); ?>
        <?php mif_qm_the_members_part( 'tutor' ); ?>
        <?php mif_qm_the_members_part( 'assistant' ); ?>
        <?php mif_qm_the_members_part( 'expert' ); ?>
        <?php mif_qm_the_members_part( 'student' ); ?>
    </div>
    

    <?php do_action( 'mif_qm_after_members' );  ?>

</div>

<?php do_action( 'mif_qm_after_quiz' );  ?>