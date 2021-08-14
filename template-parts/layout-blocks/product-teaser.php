<?php
/**
 * <code>
 *  $args = (
 *          [headline] => Reise-Empfehlungen
 *          [text] => Travel is the movement of people between relatively distant geographical locations, and can involve travel by foot, bicycle, automobile, train, boat, bus, airplane, or other means, with or without luggage, and can be one way or round trip.
 *          [view] => Teaser1
 *          [search] => Array
 *                  (
 *                       [pm-ot] => 607
 *                       [pm-view] => Teaser1
 *                       [pm-vi] => 10
 *                       [pm-l] => 0,4
 *                       [pm-o] => price-desc
 *                  )
 *      )
 * </code>
 * @var array $args
 */

$search = BuildSearch::fromRequest(isset($args['search']) ? $args['search'] : [], 'pm', true, 4);
$products = $search->getResults();
// if no items where found, we avoid output like headline or intro text...
if(count($products) == 0){
    return;
}
?>
<section class="content-block content-block-travel-cols">
    <div class="row">
        <?php if(!empty($args['headline']) || !empty($args['intro'])){ ?>
        <div class="col-12">
            <?php if(!empty($args['headline'])){ ?>
            <h2 class="mt-0">
                <?php echo $args['headline'];?>
            </h2>
            <?php } ?>
            <?php if(!empty($args['text'])){ ?>
            <p>
                <?php echo $args['text'];?>
            </p>
            <?php } ?>
        </div>
        <?php } ?>

        <?php

       // Example 1: Using the the shortcode ts-list for displaying the product teasers
       //echo do_shortcode( '[ts-list view="Teaser1" pm-ot="607" pm-l="1,4" pm-o="RAND"]');
       // Note: the View-Template "Teaser1" can be found in travelshop/template-parts/pm-views/{OBJECT_TYPE_NAME}_{VIEWNAME}.php


       // Example 2: Using the pressmind® web-core SDK
       //$conditions = array();
       //$conditions[] = Pressmind\Search\Condition\ObjectType::create(TS_TOUR_PRODUCTS);
       //$search = new Pressmind\Search($conditions, ['start' => 0, 'length' => 4], ['' => 'RAND()']);
       //$products = $search->getResults();
       //foreach ($products as $product) {
       //    echo  $product->render('Teaser1', TS_LANGUAGE_CODE);
       //}

       // Example 3: Use a $args['search] list (or $_GET)
       // $search = BuildSearch::fromRequest(isset($args['search']) ? $args['search'] : [], 'pm', true, 4);
       // $products = $search->getResults();

        $view = 'Teaser1';
        if(!empty($args['view']) && preg_match('/^[0-9A-Za-z\_]+$/', $args['view']) !== false){
            $view = $args['view'];
        }
        foreach ($products as $product) {
            echo  $product->render($view, TS_LANGUAGE_CODE);
        }

       ?>
    </div>
</section>