<?php do_action( 'mif_qm_before_quiz' );  ?>

<hr />

<div class="quiz">

    <?php do_action( 'mif_qm_before_quiz_menu' ); ?>
    <?php if ( mif_qm_user_can( 'edit-quiz' ) ) : ?>
    <div class="btn-group mt-3 mb-3" role="group" aria-label="Basic example">
    <a class="btn btn-outline-light pt-2<?php mif_qm_the_menu_class( 'view', 'bg-light' ) ?>" href="?action=view"><i class="fa fa-2x fa-circle-o<?php mif_qm_the_menu_class( 'view', 'text-secondary', false ) ?>" aria-hidden="true"></i><br /><small><?php echo __( 'Обзор', 'mif-qm' ); ?></small></a>
    <a class="btn btn-outline-light pt-2<?php mif_qm_the_menu_class( 'run', 'bg-light' ) ?>" href="?action=run"><i class="fa fa-2x fa-play<?php mif_qm_the_menu_class( 'run', 'text-secondary', false ) ?>" aria-hidden="true"></i><br /><small><?php echo __( 'Запуск', 'mif-qm' ); ?></small></a>
    <a class="btn btn-outline-light pt-2<?php mif_qm_the_menu_class( 'edit', 'bg-light' ) ?>" href="<?php mif_qm_the_edit_post_link(); ?>"><i class="fa fa-2x fa-pencil-square<?php mif_qm_the_menu_class( 'edit', 'text-secondary', false ) ?>" aria-hidden="true"></i><br /><small><?php echo __( 'Правка', 'mif-qm' ); ?></small></a>
    <a class="btn btn-outline-light pt-2<?php mif_qm_the_menu_class( 'result', 'bg-light' ) ?>" href="?action=result"><i class="fa fa-2x fa-check-square<?php mif_qm_the_menu_class( 'result', 'text-secondary', false ) ?>" aria-hidden="true"></i><br /><small><?php echo __( 'Результаты', 'mif-qm' ); ?></small></a>
    </div>
    <?php endif; ?>

    <?php do_action( 'mif_qm_before_quiz_header' ); ?>
    <div class="mt-3 mb-3">
    <?php mif_qm_the_quiz_header(); ?>
    </div>
    
    <?php do_action( 'mif_qm_before_quiz_param' ); ?>
    <?php mif_qm_the_quiz_param(); ?>
    
    <?php do_action( 'mif_qm_before_parts' ); ?>
    <div class="parts">

        <?php mif_qm_the_parts(); ?>

    </div>
    <?php do_action( 'mif_qm_after_questions' );  ?>

</div>

<?php do_action( 'mif_qm_after_quiz' );  ?>