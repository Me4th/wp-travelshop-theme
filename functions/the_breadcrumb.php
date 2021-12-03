<?php

/**
 * @param string $sep
 * @param string $home_title
 * @param object[] $custom_path object{name, url}
 */
function the_breadcrumb($sep = ' › ', $home_title = 'Startseite', $custom_path = array())
{
    global $post;

    if (is_category() || is_archive()) {
         return;
    }


    if(empty($custom_path) === true){
    $path = array();

    // Add Home
    $item = new stdClass();
    $item->name = $home_title;
    $item->url = site_url();
    $path[] = $item;


    // Add Parent Pages
    $ancestors = get_post_ancestors($post);

    $posts = array();
    if (empty($post) === false && $post->post_parent) {
        $posts = get_posts(array('include' => $ancestors, 'order_by' => 'parent', 'post_type' => $post->post_type));
    }

    krsort($posts);

    foreach ($posts as $p) {
        $item = new stdClass();
        $item->name = $p->post_title;
        $item->url = get_permalink($p->ID);
        $path[] = $item;
    }

    // Add the current post
        if(!empty($post->post_title)){
            $item = new stdClass();
            $item->name = $post->post_title;
            $item->url = null;
            $path[] = $item;
        }
    }else{
        $path = $custom_path;
    }
    if(count($path) == 1){
        return;
    }
    ?>
    <section class="breadcrumb-wrapper">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol itemscope itemtype="https://schema.org/BreadcrumbList" class="breadcrumb">
                    <?php

                    $c = 0;
                    foreach ($path as $item) {
                        $c++;
                        if($c == 2) {
                            echo '<li class="bc-separator">'; ?>
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-chevron-left" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="#777" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <polyline points="15 6 9 12 15 18" />
                                </svg>
                            <?php
                            echo '<a itemprop="item" href="' . $item->url . '">' . $item->name . ' anzeigen</a>';
                            echo '</li>';
                        }
                        ?>
                        <li itemprop="itemListElement" itemscope
                            itemtype="https://schema.org/ListItem" class="breadcrumb-item <?php if($c == 1) { echo 'breadcrumb-home'; } ?>">
                            <?php if (empty($item->url) === false){ ?><a itemprop="item"
                                                                         href="<?php echo $item->url; ?>"><?php } ?>
                                <span class="breadcrumb-name" itemprop="name"><?php echo ($c == 1 ? 'Travelshop' : $item->name); ?>
                                    <span class="breadcrumb-sep">
                                        <?php if(count($path) != $c) { ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-chevron-right" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="#777" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <polyline points="9 6 15 12 9 18" />
                                            </svg>
                                        <?php } ?>
                                    </span>
                                </span>
                                <?php if (empty($item->url) === false){ ?></a><?php } ?>
                            <meta itemprop="position" content="<?php echo $c + 1; ?>"/>
                        </li>
                        <?php

                        if (!empty($sep) && $c < count($path)) {
                            echo $sep;
                        }
                    }
                    ?>
                </ol>
            </nav>
        </div>
    </section>
    <?php

}