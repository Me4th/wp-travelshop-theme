<?php
/**
 * This file builds scaffolder templates for the pressmind web-core sdk installer. it's for theme-development only
 */
$prefix = 'Reise';
$files = [];
$files[] = [$prefix.'_Detail1.php', 'scaffolder/Detail1.txt'];
$files[] = [$prefix.'_Teaser1.php', 'scaffolder/Teaser1.txt'];
$files[] = [$prefix.'_Teaser2.php', 'scaffolder/Teaser2.txt'];
$files[] = [$prefix.'_Teaser3.php', 'scaffolder/Teaser3.txt'];
$files[] = [$prefix.'_Teaser4.php', 'scaffolder/Teaser4.txt'];
$files[] = [$prefix.'_Teaser5.php', 'scaffolder/Teaser5.txt'];
$files[] = [$prefix.'_Teaser6.php', 'scaffolder/Teaser6.txt'];

foreach($files as $file){
    $c = file_get_contents($file[0]);
    $c = str_replace(['Custom\MediaType\Reise'], ['Custom\MediaType\###CLASSNAME###'], $c);
    file_put_contents($file[1], $c);
}
