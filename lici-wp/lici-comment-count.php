<?
function calculateTextBox($font_size, $font_angle, $font_file, $text) {
    $box = imagettfbbox($font_size, $font_angle, $font_file, $text);

    $min_x = min(array($box[0], $box[2], $box[4], $box[6]));
    $max_x = max(array($box[0], $box[2], $box[4], $box[6]));
    $min_y = min(array($box[1], $box[3], $box[5], $box[7]));
    $max_y = max(array($box[1], $box[3], $box[5], $box[7]));

    return array(
        'left' => ($min_x >= -1) ? -abs($min_x + 1) : abs($min_x + 2),
        'top' => abs($min_y),
        'width' => $max_x - $min_x,
        'height' => $max_y - $min_y,
        'box' => $box
    );
}

 function RGB2Hex2RGB($c)
 {
     if(!$c) 
         return false;
 
     $c = trim($c);
     $out = false;
 
     if(eregi("^[0-9ABCDEFabcdef\#]+$", $c))
     {
         $c = str_replace('#','', $c);
         $l = strlen($c);
         if($l == 3)
         {
             unset($out);
             $out[0] = $out['r'] = $out['red'] = hexdec(substr($c, 0,1));
             $out[1] = $out['g'] = $out['green'] = hexdec(substr($c, 1,1));
             $out[2] = $out['b'] = $out['blue'] = hexdec(substr($c, 2,1));
         }
         elseif($l == 6)
         {
             unset($out);
             $out[0] = $out['r'] = $out['red'] = hexdec(substr($c, 0,2));
             $out[1] = $out['g'] = $out['green'] = hexdec(substr($c, 2,2));
             $out[2] = $out['b'] = $out['blue'] = hexdec(substr($c, 4,2));
         }
         else 
             $out = false;
     }
     elseif (eregi("^[0-9]+(,| |.)+[0-9]+(,| |.)+[0-9]+$", $c))
     {
         if(eregi(",", $c))
         $e = explode(",",$c);
         else if(eregi(" ", $c))
         $e = explode(" ",$c);
         else if(eregi(".", $c))
         $e = explode(".",$c);
         else return false;
 
         if(count($e) != 3) 
             return false;
 
         $out = '#';
         for($i = 0; $i<3; $i++)
         $e[$i] = dechex(($e[$i] <= 0)?0:(($e[$i] >= 255)?255:$e[$i]));
 
         for($i = 0; $i<3; $i++)
             $out .= ((strlen($e[$i]) < 2)?'0':'').$e[$i];
 
         $out = strtoupper($out);
     }
     else 
         $out = false;
 
     return $out;
 }

$cid = intval($_GET['id']);
$lid = intval($_GET['lilogin']);

$root = "../../..";
if (file_exists($root.'/wp-load.php')) {
	require_once($root.'/wp-load.php');
} else {
	require_once($root.'/wp-config.php');
}

$options_table = $wpdb->prefix . "lici_options";
$comments = get_comments_number($cid);
$login = $wpdb->get_row("SELECT * FROM $options_table WHERE `id`='$lid' LIMIT 1;");

$color = $login->fontcolor;
$font = "./fonts/".$login->font;
$size = $login->fontsize;

$box = calculateTextBox($size, 0, $font, $comments);

//print_r($box); 

$im=imagecreatetruecolor($box['width'],$box['height']);
imagealphablending($im,false);
$col=imagecolorallocatealpha($im,255,255,255,127);
//$col=imagecolorallocate($im,255,255,255);
imagefilledrectangle($im,0,0,$box['width'],$box['height'],$col);
imagealphablending($im,true);

if ($im) {
	
	$co = RGB2Hex2RGB("#".$color);
	$color_a = imagecolorallocatealpha($im, $co['r'], $co['g'],$co['b'], 0);
	imagettftext($im, $size, 0, -1, $box['height'], $color_a, "fonts/vera.ttf", $comments);
	
  	header("Content-Type: image/png;");
 	imagealphablending($im,false);
 	imagesavealpha($im,true);
 	imagepng($im);
} else {
	print "Can't create image";
}

?>