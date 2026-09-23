<?php
require 'vendor/autoload.php';
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

$manager = new ImageManager(new Driver());
$image = $manager->read('public/images/favicon.png');
$encoded = $image->toWebp(80);
$encoded->save('public/images/test.webp');
echo "Saved successfully.";
