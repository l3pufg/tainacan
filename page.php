<?php get_header();

$_menu_args = [ 'container_class' => 'container', 'container' => false, 'walker' => new wp_bootstrap_navwalker(),
    'menu_class' => 'navbar navbar-inverse menu-ibram' ];
?>

    <header class="custom-header" style="<?php echo home_header_bg($socialdb_logo)?>">
        <div class="menu-transp-cover"></div>
        <?php get_template_part("partials/header/main"); ?>
    </header>

<?php wp_nav_menu($_menu_args); ?>

    <div id="primary" class="tainacan-page-area col-md-12">

        <div class="col-md-8 no-padding center">
            <header class="page-header col-md-12 no-padding">
                <h1 class="page-title"> <?php the_title();  ?> </h1>
            </header>

            <?php if( has_post_thumbnail() ): ?>
                <div class="page-thumb" style="background: url(<?php echo get_the_post_thumbnail_url(); ?>)"> </div>
            <?php endif;?>

            <div id="primary" class="tainacan-content-area">
                <main id="main" class="col-md-12" role="main">
                    <?php if (have_posts()):
                        while ( have_posts() ) : the_post(); ?>

                            <div class="single-post-wrapper">
                                <?php the_content(); ?>
                            </div>

                            <?php
                        endwhile;
                    else:
                        get_template_part( 'partials/content/content', 'none' );
                    endif;
                    ?>
                </main>
            </div>
        </div>
    </div>

<?php get_footer(); ?>