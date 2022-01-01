<?php
use Pressmind\HelperFunctions;
use Pressmind\Travelshop\PriceHandler;
use Pressmind\Travelshop\Template;

/**
 * @var array $args
 */

?>
<div class="mobile-bar">

    <div class="container">
        <span class="mobile-bar-title"><?php echo $args['headline'] ?></span>
        <p>
            <?php echo Template::render(APPLICATION_PATH.'/template-parts/micro-templates/duration.php', [
                    'duration' => $args['cheapest_price']->duration]);
            ?>
            -
            <?php
            echo Template::render(APPLICATION_PATH.'/template-parts/micro-templates/price.php',  [
                'cheapest_price' => $args['cheapest_price'],
            ]);
            ?>
            <?php if (($discount = PriceHandler::getDiscount($args['cheapest_price'])) !== false) {
                echo Template::render(APPLICATION_PATH.'/template-parts/micro-templates/discount.php', [
                    'cheapest_price' => $args['cheapest_price'],
                    'discount' => $discount,
                ]);
                ?>
            <?php } ?>
        </p>
        <div class="mobile-bar-cta">
            <div class="mobile-bar-date">
                        <span class="date">
                            <?php echo Template::render(APPLICATION_PATH.'/template-parts/micro-templates/transport-icon.php', ['transport_type' => $args['cheapest_price']->transport_type]);?>
                            <a class="show-dates" data-modal="true" data-modal-id="<?php echo $args['id_modal_price_box'];?>">
                                 <?php echo Template::render(APPLICATION_PATH.'/template-parts/micro-templates/travel-date-range.php', [
                                     'date_departure' => $args['cheapest_price']->date_departure,
                                     'date_arrival' => $args['cheapest_price']->date_arrival
                                 ]);?>
                            </a>
                        </span>
            </div>
            <?php echo Template::render(APPLICATION_PATH.'/template-parts/micro-templates/booking-button.php', [
                'cheapest_price' => $args['cheapest_price'],
                'url' => $args['url']
            ]);?>
        </div>
    </div>
</div>
