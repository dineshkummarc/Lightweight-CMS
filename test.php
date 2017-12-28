<?php


if (!extension_loaded('imagick'))
    echo 'imagick not installed';


error_reporting(E_ALL);
print(Imagick::getVersion());
$im = new Imagick();
print($im->getVersion());
$im->readImage( "./uploads/0YZSQJErNxtcb2Dc.jpg" );
$im->resizeImage(1008, 756,Imagick::FILTER_LANCZOS,1);
$im->writeImage('./uploads/small.jpg');
$im->destroy();

//Try to get ImageMagick "convert" program version number.
exec("convert -version", $out, $rcode);
//Print the return code: 0 if OK, nonzero if error. 
echo "Version return code is $rcode <br>"; 
//Print the output of "convert -version"    
echo alist($out); 
?>

?>