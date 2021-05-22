<?php
/**
 * @var \Pressmind\Search $search
 */

return;
// get the min and max duration, based on the current search

// TODO #152888
// missing function
//$search1 = $search;
//$search1->removeCondition('DurationRange');

$dRangeFilter = new Pressmind\Search\Filter\Duration($search1);
$dRange = $dRangeFilter->getResult();

if(empty($dRange->min) || empty($dRange->max)){
    return;
}

if (isset($_GET['pm-du']) === true && preg_match('/^([0-9]+)\-([0-9]+)$/', $_GET['pm-du'], $m) > 0) {
    $duration_from = $m[1];
    $duration_to = $m[2];
}else{
    $duration_from = $dRange->min;
    $duration_to = $dRange->max;
}



?>
<div class="list-filter-box list-filter-box-price">
    <div class="list-filter-box-title">
        <strong>Dauer</strong>
    </div>
    <div class="list-filter-box-body">

        <input type="text" class="js-range-slider" name="pm-du" value=""
               data-type="double"
               data-min="<?php echo $dRange->min; ?>"
               data-max="<?php echo $dRange->max; ?>"
               data-from="<?php echo $duration_from; ?>"
               data-to="<?php echo $duration_to; ?>"
               data-grid="false"
               data-prefix=""
               data-step="1"
               data-input-values-separator="-"
               data-disable="<?php
               // disable the picker if there is no plausible step to pick
               echo ($dRange->min == $dRange->max || ($dRange->max - $dRange->min) == 1 ) ? 'true' : 'false';
               ?>"
        />

    </div>
</div>