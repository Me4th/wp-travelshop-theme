<?php
use Pressmind\Travelshop\PriceHandler;
/**
 * <code>
 * $args = [
 *  'id_media_object' => 12345
 *  'id_object_type' => 123
 *  'headline' => ''
 *  'subline' => ''
 *  'travel_type' => ''
 *  'destination' => ''
 *  'image' => []
 *  'cheapest_price' => {}
 *  'departure_date_count' => 12
 *  'possible_durations' => []
 *  'dates_per_month' => []
 *  'class' => ''
 * ];
 * </code>
 * @var array $args
 *
 */

/**
 * DON'T USE WordPress Stuff here
 */

// only this search params are transmitted, price range (pm-pr), date range (pm-dr), duration range (pm-du), housingoption occupancy (pm-ho)
$allowedParams = ['pm-pr', 'pm-dr', 'pm-du', 'pm-ho'];
$filteredParams = array_filter($_GET,
    function ($key) use ($allowedParams) {
        return in_array($key, $allowedParams);
    },
    ARRAY_FILTER_USE_KEY
);

if (empty($filteredParams) === false) {
    $query_string = http_build_query($filteredParams);
    $args['url'] .= '?' . $query_string;
}
?>
<section class="wishlist-item">
    <div class="wishlist-item-image">
        <a href="<?php echo $args['url'] ; ?>">
            <?php if (!empty($args['image']['url'])) { ?>
                <img src="<?php echo $args['image']['url']; ?>"
                     alt="<?php echo $args['headline']; ?>"
                     title="<?php echo $args['image']['copyright']; ?>"
                />
            <?php }else{ ?>
                <img src="/placeholder.svg?wh=250x170" class="card-img-top"/>
            <?php } ?>
        </a>
    </div>
    <div class="wishlist-item-data">
        <span class="name">
            <a href="<?php echo $args['url'] ; ?>"><?php echo $args['headline']; ?></a>
        </span>
        <span class="price">
            <div data-pm-id="<?php echo $args['id_media_object']; ?>" class="remove-from-wishlist">entfernen</div>
            <a href="<?php echo $args['url'] ; ?>"><?php echo ' ab <strong>'.PriceHandler::format($args['cheapest_price']->price_total).'</strong>' ?></a>
        </span>
    </div>
</section>