<div class="blog-sidebar d-flex flex-column gap-3">

    <div class="card border-0 p-0">
        <div class="teaser-body p-0">
            <?php echo get_search_form(); ?>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h4>Kategorien</h4>

            <div class="d-flex flex-column gap-2">
                <?php
                $categories = get_categories();
                $current_cat_ID = get_query_var('cat');

                foreach ( $categories as $category ) {
                    $category_link = get_category_link( $category );
                    $category_id = $category->term_id;
                    $category_children = false;

                    foreach ( $categories as $sub_category ) {
                        if ( $sub_category->category_parent == $category_id ) {
                            $category_children = true;
                        }
                    }

                    if ( $category->category_parent == 0 ) {
                    ?>
                    <div class="d-flex flex-column gap-2">
                        <a class="d-flex justify-content-between align-items-center text-decoration-none category-link" href="<?php echo $category_link; ?>" title='<?php echo $category->name; ?>' class='<?php echo $category->slug; ?>'>

                            <?php if ( $current_cat_ID === $category->term_id ) { ?><strong><?php } ?><?php echo $category->name; ?><?php if ( $current_cat_ID === $category->term_id ) { ?></strong><?php } ?>

                            <span class="badge badge-primary badge-pill">
                                <?php echo $category->count; ?>
                            </span>

                        </a>

                        <?php
                        if ( $category_children ) {
                            ?>
                            <div class="d-flex flex-column gap-2 pl-3">
                                <?php
                                foreach ( $categories as $category ) {
                                    $category_link = get_category_link( $category );
                                    if ( $category->category_parent == $category_id ) {
                                        ?>
                                        <a class="d-flex justify-content-between align-items-center text-decoration-none category-link" href="<?php echo $category_link; ?>" title='<?php echo $category->name; ?>' class='<?php echo $category->slug; ?>'>
                                            <?php if ( $current_cat_ID === $category->term_id ) { ?><strong><?php } ?><?php echo $category->name; ?><?php if ( $current_cat_ID === $category->term_id ) { ?></strong><?php } ?>
                                            <span class="badge badge-primary badge-pill">
                                                <?php echo $category->count; ?>
                                            </span>
                                        </a>
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                    }
                }
                ?>
            </div>

        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h4>Stichworte</h4>

            <div class="d-flex flex-row flex-wrap gap-1">

                <?php

                $tags = get_tags();
                if ( isset(get_queried_object()->term_id) ) {
                    $current_tag_ID = get_queried_object()->term_id;
                } else {
                    $current_tag_ID = null;
                }


                foreach ( $tags as $tag ) {

                    $tag_link = get_tag_link( $tag->term_id );
                    ?>
                        <a class="badge badge-pill <?php if ( $current_tag_ID && $current_tag_ID === $tag->term_id ) { ?> badge-primary <?php } else { ?> badge-secondary <?php } ?>" href="<?php echo $tag_link; ?>" title='<?php echo $tag->name; ?>' class='<?php echo $tag->slug; ?>'>
                            <?php echo $tag->name; ?>
                        </a>
                    <?php
                }
                ?>
            </div>

        </div>
    </div>

</div>
