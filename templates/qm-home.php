<div class="carousel slide clearfix pb-2">
    <div class="carousel-inner">
        <div class="carousel-item active">
            <img class="d-block w-100" src="<?php echo plugins_url('../pictures/header-bg.jpg', __FILE__); ?>">
        </div>
        <div class="carousel-caption d-none d-md-block col-4" style="top: 3rem; bottom: 3rem; right: 3rem; left: auto;">
        
        <div class="card p-2">
            <div class="card-body">
                <h4 class="h4 text-white t-light"><?php echo __( 'Приглашения', 'mif-qm' ) ?></h4>
                <p><?php echo __( 'введите код', 'mif-qm' ) ?>:</p>
                <div class="input-group input-group-lg pl-lg-4 pr-lg-4 pb-4">
                    <input type="text" class="form-control" name="invite">
                </div>
                <button class="btn btn-lg"><?php echo __( 'Пройти тест', 'mif-qm' ) ?></button>

            </div>
        </div>

        </div>
    </div>
</div>

<div class="pt-4 text-center">

    <h2 class="underline h2 t-light mb-5"><?php echo __( 'Ресурсы и инструменты', 'mif-qm' ) ?></h2>

</div>

<div class="row no-gutters pb-4">

    <div class="col-lg-3 col-sm-6 text-center p-4 border-right border-left">
        <a href="<?php mif_qm_the_url( 'profile' ); ?>" class="h3">
            <span class="text-secondary">
                <i class="fas fa-3x fa-user"></i>
            </span>
            <div class="pt-4 t-light"><?php echo __( 'Ваш профиль', 'mif-qm' ) ?></div>
        </a>
        <p class="mt-3"><?php echo __( 'Пройденные вами тесты и полученные результаты', 'mif-qm' ) ?></p>
    </div>

    <div class="col-lg-3 col-sm-6 text-center p-4 border-right">
        <a href="<?php mif_qm_the_url( 'workroom' ); ?>" class="h3">
            <span class="text-secondary">
                <i class="fas fa-3x fa-pencil-alt"></i>
            </span>    
            <div class="pt-4 t-light"><?php echo __( 'Мастерская', 'mif-qm' ) ?></div>
        </a>    
        <p class="mt-3"><?php echo __( 'Создать новый тест или редактировать существующий', 'mif-qm' ) ?></p>
    </div>    

    <div class="col-lg-3 col-sm-6 text-center p-4 border-right">
        <a href="<?php mif_qm_the_url( 'results' ); ?>" class="h3">
            <span class="text-secondary">
                <i class="fas fa-3x fa-rocket"></i>
            </span>
            <div class="pt-4 t-light"><?php echo __( 'Результаты', 'mif-qm' ) ?></div>
        </a>
        <p class="mt-3"><?php echo __( 'Результаты тестирования и статистика сайта', 'mif-qm' ) ?></p>
    </div>

    <div class="col-lg-3 col-sm-6 text-center p-4 border-right">
        <a href="<?php mif_qm_the_url( 'help' ); ?>" class="h3">
            <span class="text-secondary">
                <i class="fas fa-3x fa-life-ring"></i>
            </span>
            <div class="pt-4 t-light"><?php echo __( 'Помощь', 'mif-qm' ) ?></div>
        </a>
        <p class="mt-3"><?php echo __( 'Справочные материалы для авторов и экспертов', 'mif-qm' ) ?></p>
    </div>

</div>


<div class="bg-light p-5 mt-4">
    <div class="bg-white row no-gutters">
        <div class="col-md-6 bg-light pr-md-5 pb-5 pb-md-0">
            <img src="<?php echo plugins_url('../pictures/home-abc.jpg', __FILE__); ?>" class="img-fluid">
        </div>
        <div class="col-md-6 p-4 pl-5">
            <h3 class="h3 underline t-light mb-5"><?php echo __( 'Ваши тесты', 'mif-qm' ) ?></h3>
            <div class="mt-4">

                <?php mif_qm_the_you_quizess(); ?>

            </div>
        </div>
    </div>
</div>
    
<div class="pt-4 text-center">

    <h2 class="underline h2 t-light mb-5"><?php echo __( 'Каталог тестов', 'mif-qm' ) ?></h2>

</div>

<div class="catalog">
<form method="POST">

    <div class="row no-gutters pb-4">

        <div class="col-lg-3 col-sm-12 p-4 bg-light">
            <h3 class="h4 underline t-light mb-5"><?php echo __( 'Параметры поиска', 'mif-qm' ) ?></h3>

            <?php mif_qm_the_category(); ?>

        </div>

        <div class="col-lg-9 col-sm-12r p-4">

            <div class="input-group mb-4 input-group-lg">
                <input type="text" name="quiz_search" class="form-control">
            </div>

            <div class="row">

                <div class="pb-4 col-12 stat">
                    <p class="h4"><?php echo __( 'Все тесты', 'mif-qm' ) ?></p>
                </div>

                <?php mif_qm_the_catalog(); ?>

            </div>

        </div>

    </div>    

</form>
</div>    

