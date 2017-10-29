<?php
header("Content-Type: image/png");
$im = @imagecreate(1, 1)
    or die("Cannot Initialize new GD image stream");
$background_color = imagecolorallocate($im, 247, 247, 247);
imagepng($im);
imagedestroy($im);
?>
