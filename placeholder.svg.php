<?php
// Usage: <img src="placeholder.svg.php?wh=400x400&fill=bada55&color=000000&font=Georgia&size=20&text=missing%20picture" />
$wh = explode('x', !empty($_GET['wh']) ? $_GET['wh'] : '100x100');
$wh[0] = (int)$wh[0];
$wh[1] = (int)$wh[1];
$fill = isset($_GET['fill']) ? $_GET['fill']: '666666';
$color = isset($_GET['color']) ? $_GET['color']: 'FFFFFF';
$size = isset($_GET['size']) ? (int)$_GET['size']: 6;
$fontfamily = isset($_GET['font']) ? $_GET['font']: 'Arial';
$text = isset($_GET['text']) ? urldecode($_GET['text']) : $wh[0].'x'.$wh[1];
header('Content-Type: image/svg+xml');
?>
<svg viewBox="0 0 <?php echo $wh[0] ?> <?php echo $wh[1] ?>" xmlns="http://www.w3.org/2000/svg">
    <g>
        <title>placeholder</title>
        <rect id="svg_1" width="<?php echo $wh[0] ?>" height="<?php echo $wh[1] ?>" y="0" x="0" fill="#<?php echo $fill ?>"/>
        <text x="50%" y="50%" text-anchor="middle" alignment-baseline="middle" font-size="<?php echo $size ?>" dominant-baseline="middle" font-family="<?php echo $fontfamily ?>" fill="#<?php echo $color ?>"><?php echo $text; ?></text>
    </g>
</svg>