<?php
/**
 * THIS FILE IS A DIRTY DRAFT!!!
 * it's possible to add two types of itineraries in pressmind:
 *
 * 1. the very complex one: booking package specific itineraries
 *    which has a dependencies to the travel duration and dates
 *    and can contain different variants and travel bricks.
 *
 * or
 *
 * 2. the easy one: media object specific itineraries
 *    which has  -- NO -- dependencies to travel duration or dates
 *
 * THIS FILE handles type 2, the "easy one"
 */

/**
 * @var array $args
 */

if (empty($args['media_object']->getItinerarySteps())) {
    return;
}

$valid = false;
foreach ($args['media_object']->getItinerarySteps() as $key => $step) {
    /**
     * @var $step \Pressmind\ORM\Object\Itinerary\Step
     */
    foreach ($step->sections as $section) {
        if(!empty(strip_tags((string)$section->content->headline))){
            $valid = true;
        }
    }
}

if(!$valid){
    return;
}


?>
<div class='detail-info-section detail-info-section--itinerary'>
    <div class="accordion-group">
        <div class="accordion-header">
            <h2 class="h3">Reiseverlauf</h2>

            <button class="accordion-toggle-all btn btn-sm btn-link btn-link-light py-0 px-0" data-toggle="open">
                <span class="txt-open">Alle öffnen</span><span class="txt-close">Alle schließen</span>
            </button>
        </div>
        <section class="accordion-wrapper accordion-wrapper-boxed">
            <?php
            foreach ($args['media_object']->getItinerarySteps() as $key => $step) {
                foreach ($step->sections as $section) {
                    if(empty(strip_tags((string)$section->content->headline))){
                        continue;
                    }
                    ?>
                    <div class="accordion-item">
                        <button class="accordion-toggle" type="button">
                            <h3 class="accordion-toggle--title h5">
                                <?php echo $section->content->headline; ?>
                            </h3>
                            <svg><use xmlns:xlink="http://www.w3.org/1999/xlink" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/phosphor-sprite.svg#caret-down"></use></svg>
                        </button>

                        <div class="accordion-content">
                            <div class="accordion-content--inner">
                                <div class="accordion-block accordion-block-text">
                                    <?php echo remove_empty_paragraphs($section->content->description); ?>
                                </div>

                                <div class="accordion-block accordion-block-gallery" data-gallery="true" id="accordion-block-gallery__<?php echo $key; ?>">
                                    <div class="accordion-block-gallery--inner">
                                        <?php foreach($step->document_media_objects as $picture) { ?>
                                            <div class="accordion-gallery-item">
                                                <a href="<?php echo $picture->getUri('detail_gallery'); ?>" data-lightbox="accordion-gallery-<?php echo $step->id; ?>">
                                                    <div class="zoom-indicator">
                                                        <svg><use xmlns:xlink="http://www.w3.org/1999/xlink" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/phosphor-sprite.svg#magnifying-glass-plus"></use></svg>
                                                    </div>
                                                    <div class="accordion-gallery-item--image">
                                                        <img src="<?php echo $picture->getUri('teaser'); ?>" alt="<?php echo $picture->alt; ?>" loading="lazy" />
                                                    </div>
                                                    <div class="accordion-gallery-item--copyright">
                                                        <?php echo $picture->copyright; ?>
                                                    </div>
                                                </a>
                                            </div>
                                        <?php }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php }
            }?>
        </section>
    </div>
</div>