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

use Pressmind\HelperFunctions;
use Pressmind\Search\CheapestPrice;
use Pressmind\ORM\Object\Itinerary\Variant;
use Pressmind\ORM\Object\MediaObject;

/**
 * @var array $args
 */

/**
 * @var Custom\MediaType\Reise $moc
 */
$moc = $args['moc'];

/**
 * @var Pressmind\ORM\Object\MediaObject $mo
 */
$mo = $args['mo'];


// check if the itinerary steps are set for this object, if not return
if (empty($mo->getItinerarySteps())) {
    return;
}

?>

<section class="travelshop-itinerary">

    <?php 
    foreach ($mo->getItinerarySteps() as $key => $step) { 
        foreach ($step->sections as $section) { ?>

            <div class="travelshop-itinerary-step">

                <h3>
                    <?php echo $section->content->headline; ?>
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus travelshop-itinerary-step-open" width="30" height="30" viewBox="0 0 24 24" stroke-width="2.5" stroke="#06f" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                </h3>

                <div class="travelshop-itinerary-step-more">

                    <div class="travelshop-itinerary-step-desc">

                        <?php echo $section->content->description; ?>

                    </div>
                    
                    <div class="travelshop-itinerary-step-gallery it-gallery-<?php echo $key; ?>">
                        <?php foreach($step->document_media_objects as $picture) { ?>
                            <a href="<?php echo $picture->getUri('detail'); ?>" data-lightbox="itinerary-step-<?php echo $step->id; ?>">
                              <img src="<?php echo $picture->getUri('teaser'); ?>" alt="<?php echo $picture->alt; ?>" />
                              <div class="travelshop-itinerary-step-gallery-image-copyright">
                                <?php echo $picture->copyright; ?>
                              </div>
                            </a>
                        <?php }
                        ?>
                    </div>

                    <div class="travelshop-itinerary-step-catering">

                        Hier kommt die Verpflegung hin

                    </div>

                </div>

            </div>

    <?php } 
    }?>

</section>