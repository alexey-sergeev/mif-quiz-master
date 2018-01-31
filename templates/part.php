<?php do_action( 'mif_qm_before_part' );  ?>

<div class="part">

    <?php do_action( 'mif_qm_before_part_header' ); ?>
    <?php mif_qm_the_part_header(); ?>
    
    <?php do_action( 'mif_qm_before_part_param' ); ?>
    <?php mif_qm_the_part_param(); ?>
    
    <?php do_action( 'mif_qm_before_questions' ); ?>
    <div class="questions">

        <?php mif_qm_the_questions(); ?>

    </div>
    <?php do_action( 'mif_qm_after_questions' );  ?>

</div>

<?php do_action( 'mif_qm_after_part' );  ?>