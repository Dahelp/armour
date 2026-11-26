<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
$dir=sys_get_temp_dir().'/product-photo-'.bin2hex(random_bytes(6));
mkdir($dir);
try {
    $source=imagecreatetruecolor(80,40);
    imagefill($source,0,0,imagecolorallocate($source,25,90,180));
    foreach(['jpeg','png','gif','webp'] as $format) {
        $input=$dir.'/source.'.$format;
        $write='image'.$format;$write($source,$input);
        $output=$dir.'/result-'.$format.'.webp';
        \app\models\AppModel::resize($input,$output,60,60,'webp');
        $info=getimagesize($output);
        if($info[0]!==60 || $info[1]!==30 || $info['mime']!=='image/webp') throw new RuntimeException('Invalid converted '.$format.' photo');
        $result=imagecreatefromwebp($output);
        $pixel=imagecolorat($result,20,10);
        if(($pixel & 255)<140) throw new RuntimeException('Lost '.$format.' image content');
        imagedestroy($result);
    }
    imagedestroy($source);
    echo "Product photo resize tests passed.\n";
} finally {
    foreach(glob($dir.'/*') as $file) unlink($file);
    rmdir($dir);
}
