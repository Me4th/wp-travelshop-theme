<?php
use Pressmind\HelperFunctions;
/**
 * <code>
 * $args['duration']
 * $args['suffix']
 * </code>
 * @var array $args
 */

if($args['duration'] == 1){
    echo 'Tagesfahrt';
}else{
    echo $args['duration']. ' Tage'.(!empty($args['suffix']) ? $args['suffix'] : '');
}