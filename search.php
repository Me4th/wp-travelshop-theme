<?php
get_header();
?>

    <!-- BREADCRUMB: START -->
<?php the_breadcrumb(null);?>
    <!-- BREADCRUMB: END -->

<?php

global $wp_query;

?>
    <main>

        <div class="content-main content-main--posts">

            <div class="container">

                <div class="content-block content-block-blog--header">
                    <div class="row">
                        <div class="col-12">
                                <h1 >
                                    <?php
                                    $found_posts = $wp_query->found_posts;
                                    ?>

                                    <?php echo $found_posts; ?> <?php if ( $wp_query->found_posts == 1 ) { echo "Beitrag"; } else { echo "Beiträge"; }?> für "<?php echo get_search_query(); ?>" gefunden
                                </h1>
                        </div>
                    </div>
                </div>


                <div class="content-block content-block-blog">
                    <div class="row">
                        <div class="col-12 col-md-9">
                            <div class="posts-list">
                                <?php
                                // -- wp query, all posts
                                global $wp;

                                $count = get_option('posts_per_page', 10);
                                $paged = get_query_var('paged') ? get_query_var('paged') : 1;
                                $offset = ($paged - 1) * $count;

                                $get_permalink = home_url();

                                // get searchquery
                                $search_query = get_search_query();

                                $count_posts = $wp_query->found_posts;
                                ?>

                                <?php if ( have_posts() ): ?>
                                <?php while ( have_posts() ) : the_post(); ?>

                                    <?php get_template_part( 'template-parts/wp-views/blog-list-entry' );  ?>

                                <?php endwhile; else: ?>

                                    Keine Beiträge gefunden.

                                <?php endif; ?>
                            </div>

                            <?php if ( $count_posts > $count ) { ?>
                                <div class="posts-pagination">
                                    <nav>
                                        <ul class="pagination">

                                            <?php
                                            $prev_page = $paged - 1;
                                            $prev_page_str = '/?s=' . $search_query;

                                            if ( $prev_page < 1 ) {
                                                $prev_page = 1;
                                            }

                                            if ( $prev_page >= 1 ) {
                                                $prev_page_str = '/page/' . $prev_page . '/?s=' . $search_query;
                                            }
                                            ?>

                                            <li  class="page-item <?php if ( $paged == 1 ) { echo 'disabled'; } ?>">
                                                <a href="<?php echo $get_permalink . $prev_page_str; ?>" class="page-link">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-chevron-left" width="16" height="16" viewBox="0 2 24 24" stroke-width="2" stroke="#607D8B" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                        <polyline points="15 6 9 12 15 18"></polyline>
                                                    </svg>
                                                </a>
                                            </li>

                                            <?php

                                            $pager_active_class = '';

                                            for ( $i = 1; $i <= $wp_query->max_num_pages; $i++ ) {
                                                if ( $i == $paged ) {
                                                    $pager_active_class = 'active';
                                                } else {
                                                    $pager_active_class = '';
                                                }
                                                ?>
                                                <li class="page-item <?php echo $pager_active_class; ?>">
                                                    <a href="<?php echo $get_permalink; ?>/page/<?php echo $i; ?>/?s=<?php echo $search_query; ?>" class="page-link "><?php echo $i; ?></a>

                                                </li>
                                                <?php
                                            }

                                            ?>

                                            <?php if ( $wp_query->max_num_pages > 1 ) { ?>
                                                <?php
                                                $next_page = $paged + 1;

                                                if ( $next_page > $wp_query->max_num_pages ) {
                                                    $next_page = $wp_query->max_num_pages;
                                                }
                                                ?>
                                                <li  class="page-item <?php if ( $paged == $wp_query->max_num_pages ) { echo 'disabled'; } ?>">
                                                    <a href="<?php echo $get_permalink; ?>/page/<?php echo $next_page; ?>/?s=<?php echo $search_query; ?>" class="page-link">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-chevron-right" width="16" height="16" viewBox="0 2 24 24" stroke-width="2" stroke="#607D8B" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                            <polyline points="9 6 15 12 9 18"></polyline>
                                                        </svg>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </nav>
                                </div>
                            <?php } ?>

                        </div>
                        <div class="col-12 col-md-3">
                            <?php get_sidebar(); ?>
                        </div>
                    </div>
                </div>

            </div><!-- .container -->
        </div>

    </main>
<?php
get_footer();
