<?
/*
+--------------------------------------------------------------------------
| 生成加水印的图片类 (支持水印为图片或者文字)
+--------------------------------------------------------------------------
| 使用方法:
| $img = new Gimage();
| $img->wm_text = "www.pkphp.com";
| $img->wm_text_font = "./STXINWEI.TTF";
| $img->create("./mouse.jpg");
| 就可以了，其中
| mouse.jpg是你要在其上添加水印的图片名称，注意包含路径名
| STXINWEI.TTF是字体文件的路径名＋文件名
| 这就是一个简单的测试。如果要调整更复杂的显示效果，只要修改一下类中的属性就可以了，例如把字体放大就可以
| $img->wm_text_size = 20;
| 增加水印图片就可以
| $img->wm_image_name="文件名";
+--------------------------------------------------------------------------
*/
Class ImageShuiYin{
	//============================================================
	//基本参数设置
	//============================================================
	var $src_image_name = ""; //输入图片的文件名(必须包含路径名)
	var $jpeg_quality = 100; //jpeg图片质量
	var $save_file = ''; //输出文件名
	
	//============================================================
	//图片水印设置
	//============================================================
	var $wm_image_name = ""; //水印图片的文件名(必须包含路径名)
	var $wm_image_pos = 3; //水印图片放置的位置
	// 0 = middle
	// 1 = top left
	// 2 = top right
	// 3 = bottom right
	// 4 = bottom left
	// 5 = top middle
	// 6 = middle right
	// 7 = bottom middle
	// 8 = middle left
	//other = 3
	var $wm_image_transition = 100; //水印图片与原图片的融合度 (1=100)
	//============================================================
	//文字水印设置
	//============================================================
	var $wm_text = ""; //水印文字(支持中英文以及带有\r\n的跨行文字)
	var $wm_text_size = 20; //水印文字大小
	var $wm_text_angle = 0; //水印文字角度,这个值尽量不要更改
	var $wm_text_pos = 3; //水印文字放置位置
	var $wm_text_font = "./public/shuiyin/chinese.ttf"; //水印文字的字体
	var $wm_text_color = "#000000"; //水印字体的颜色值
	var $wm_text_shadowcolor = "#eeeeee"; //水印字体的颜色值
	//============================================================
	//水印偏距设置
	//============================================================	
	var $wm_offset_x = 0; //x向偏距
	var $wm_offset_y = 0; //y向偏距
	//============================================================
	//原图片最小多少才打上水印
	//============================================================	
	var $src_img_mini_x = 50; //x向最小
	var $src_img_mini_y = 50; //y向最小
	
function create($filename="")
	{
		if ($filename) $this->src_image_name = trim($filename);
		$src_image_type = $this->get_type($this->src_image_name);
		$src_image = $this->createImage($src_image_type,$this->src_image_name);
		
		if (!$src_image) return;
		$src_image_w=ImageSX($src_image);
		$src_image_h=ImageSY($src_image);
		
		if (!($src_image_w <= $this->src_img_mini_x or $src_image_h <= $this->src_img_mini_y)) 
		{
			if ($this->wm_image_name)
			{
				 $this->wm_image_name = trim($this->wm_image_name);
				 $wm_image_type = $this->get_type($this->wm_image_name);
				 $wm_image = $this->createImage("",$this->wm_image_name);
				 $wm_image_w=ImageSX($wm_image);
				 $wm_image_h=ImageSY($wm_image);
				 $temp_wm_image = $this->getPos($src_image_w,$src_image_h,$this->wm_image_pos,$wm_image);
				 $wm_image_x = $temp_wm_image["dest_x"];
				 $wm_image_y = $temp_wm_image["dest_y"];
	//			 imageCopyMerge($src_image, $wm_image,$wm_image_x,$wm_image_y,0,0,$wm_image_w,$wm_image_h,$this->wm_image_transition);
				 imagecopy($src_image, $wm_image,$wm_image_x,$wm_image_y,0,0,$wm_image_w,$wm_image_h);
			}
			if ($this->wm_text)
			{
	//			 $this->wm_text = $this->wm_text;
				 $temp_wm_text = $this->getPos($src_image_w,$src_image_h,$this->wm_text_pos);
				 $wm_image_x = $temp_wm_text["dest_x"];
				 $wm_image_y = $temp_wm_text["dest_y"];
				 $wm_image =$this->create_frome_text();
				 $wm_image_w=ImageSX($wm_image);
				 $wm_image_h=ImageSY($wm_image);
				 imagecopy($src_image, $wm_image,$wm_image_x,$wm_image_y,0,0,$wm_image_w,$wm_image_h);
			}
		}
		
		
		if ($this->save_file)
		{
			$savainfo=pathinfo($this->save_file); 
			switch ($savainfo["extension"])
			 {
				 case 'gif':$src_img=ImagePNG($src_image, $this->save_file); break;
				 case 'jpeg':$src_img=ImageJPEG($src_image, $this->save_file, $this->jpeg_quality); break;
				 case 'png':$src_img=ImagePNG($src_image, $this->save_file); break;
				 default:$src_img=ImageJPEG($src_image, $this->save_file, $this->jpeg_quality); break;
			 }
		}
		else
		{
			 if ($src_image_type = "jpg") $src_image_type="jpeg";
			 header("Content-type: image/{$src_image_type}");
			 switch ($src_image_type)
			 {
				 case 'gif':$src_img=ImagePNG($src_image); break;
				 case 'jpg':$src_img=ImageJPEG($src_image, "", $this->jpeg_quality);break;
				 case 'png':$src_img=ImagePNG($src_image);break;
				 default:$src_img=ImageJPEG($src_image, "", $this->jpeg_quality);break;
			 }
		}
		imagedestroy($src_image);
	}
/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
/*
createImage 根据文件名和类型创建图片
内部函数
$type: 图片的类型，包括gif,jpg,png
$img_name: 图片文件名，包括路径名，例如 " ./mouse.jpg"
*/
	function createImage($type,$img_name)
	{
		 if (!$type)
		 {
		 	$type = strtolower($this->get_type($img_name));
		 }
		 switch ($type)
		 {
			 case 'gif':
				 if (function_exists('imagecreatefromgif'))
				 $tmp_img=@ImageCreateFromGIF($img_name);
			 	break;
			 case 'jpg':
				 $tmp_img=ImageCreateFromJPEG($img_name);
				 break;
			case 'jpeg':
				 $tmp_img=ImageCreateFromJPEG($img_name);
				 break;	 
			 case 'png':
				 $tmp_img=ImageCreateFromPNG($img_name);
				 break;
			 default:
				 $tmp_img=ImageCreateFromString($img_name);
				 break;
		 }
		 return $tmp_img;
	}
/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
getPos 根据源图像的长、宽，位置代码，水印图片id来生成把水印放置到源图像中的位置
内部函数
$sourcefile_width: 源图像的宽
$sourcefile_height: 原图像的高
$pos: 位置代码
// 0 = middle
// 1 = top left
// 2 = top right
// 3 = bottom right
// 4 = bottom left
// 5 = top middle
// 6 = middle right
// 7 = bottom middle
// 8 = middle left
$wm_image: 水印图片ID
*/
function getPos($sourcefile_width,$sourcefile_height,$pos,$wm_image="")
	{
		 if ($wm_image)
		 {
			 $insertfile_width = ImageSx($wm_image);
			 $insertfile_height = ImageSy($wm_image);
		 }else 
		 {
			$lineCount = explode("\n",str_replace("\r","",$this->wm_text));
			$fontSize = imagettfbbox($this->wm_text_size,$this->wm_text_angle,$this->wm_text_font,$this->wm_text);
			$insertfile_width  = abs($fontSize[2]) + abs($fontSize[0]);
			$insertfile_height = abs($fontSize[7]) + abs($fontSize[1]);
		 }
		 
		 //设置偏距
		 $insertfile_width = $insertfile_width+2*$this->wm_offset_x;
		 $insertfile_height = $insertfile_height+2*$this->wm_offset_y;
			 
		 switch ($pos)
		 {
			 case 0:
				 $dest_x = ( $sourcefile_width / 2 ) - ( $insertfile_width / 2 );
				 $dest_y = ( $sourcefile_height / 2 ) - ( $insertfile_height / 2 );
			 break;
			 case 1:
				 $dest_x = 0;
				 if ($this->wm_text)
				 {
				 	$dest_y = $insertfile_height;
				 }else
				 {
				 	$dest_y = 0;
				 }
				 break;
			 case 2:
				 $dest_x = $sourcefile_width - $insertfile_width;
				 if ($this->wm_text){
				 $dest_y = $insertfile_height;
				 }else{
				 $dest_y = 0;
				 }
				 break;
			 case 3:
				 $dest_x = $sourcefile_width - $insertfile_width;
				 $dest_y = $sourcefile_height - $insertfile_height;
				 break;
			 case 4:
				 $dest_x = 0;
				 $dest_y = $sourcefile_height - $insertfile_height;
				 break;
			 case 5:
				 $dest_x = ( ( $sourcefile_width - $insertfile_width ) / 2 );
				 if ($this->wm_text){
				 $dest_y = $insertfile_height;
				 }else{
				 $dest_y = 0;
				 }
				 break;
			 case 6:
				 $dest_x = $sourcefile_width - $insertfile_width;
				 $dest_y = ( $sourcefile_height / 2 ) - ( $insertfile_height / 2 );
				 break;
			 case 7:
				 $dest_x = ( ( $sourcefile_width - $insertfile_width ) / 2 );
				 $dest_y = $sourcefile_height - $insertfile_height;
				 break;
			 case 8:
				 $dest_x = 0;
				 $dest_y = ( $sourcefile_height / 2 ) - ( $insertfile_height / 2 );
				 break;
			 default:
				 $dest_x = $sourcefile_width - $insertfile_width;
				 $dest_y = $sourcefile_height - $insertfile_height;
				 break;
		 }
		 return array("dest_x"=>$dest_x,"dest_y"=>$dest_y);
	}
/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
get_type 获得图片的格式，包括jpg,png,gif
内部函数
$img_name： 图片文件名，可以包括路径名
*/
function get_type($img_name)//获取图像文件类型
{
	$imageinfo=$this->image_info($img_name);
	if (in_array($imageinfo["type"],array("gif","jpg","png"))) 
	{
		return $imageinfo["type"];
	}
	return "string";
}
/**
 * mixed IM_image_info( file $file [, string $out] )
 *
 * Returns information about $file.
 *
 * If the second argument is supplied, a string representing that information will be returned.
 *
 * Valid values for the second argument are IMAGE_WIDTH, 'width', IMAGE_HEIGHT, 'height', IMAGE_TYPE, 'type',
 * IMAGE_ATTR, 'attr', IMAGE_BITS, 'bits', IMAGE_CHANNELS, 'channels', IMAGE_MIME, and 'mime'.
 *
 * If only the first argument is supplied an array containing all the information is returned,
 * which will look like the following:
 *
 *    [width] => int (width),
 *    [height] => int (height),
 *    [type] => string (type),
 *    [attr] => string (attributes formatted for IMG tags),
 *    [bits] => int (bits),
 *    [channels] => int (channels),
 *    [mime] => string (mime-type)
 *
 * Returns false if $file is not a file, no arguments are supplied, $file is not an image, or otherwise fails.
 *
 **/
function image_info($file = null, $out = null) 
{
   // If $file is not supplied or is not a file, warn the user and return false.
   if (is_null($file) || !is_file($file)) 
   {
       return false;
   }

   // Defines the keys we want instead of 0, 1, 2, 3, 'bits', 'channels', and 'mime'.
   $redefine_keys = array(
       'width',
       'height',
       'type',
       'attr',
       'bits',
       'channels',
       'mime',
   );

   // If $out is supplied, but is not a valid key, nullify it.
   if (!is_null($out) && !in_array($out, $redefine_keys)) $out = null;

   // Assign usefull values for the third index.
   $types = array(
       1 => 'GIF',
       2 => 'JPG',
       3 => 'PNG',
       4 => 'SWF',
       5 => 'PSD',
       6 => 'BMP',
       7 => 'TIFF(intel byte order)',
       8 => 'TIFF(motorola byte order)',
       9 => 'JPC',
       10 => 'JP2',
       11 => 'JPX',
       12 => 'JB2',
       13 => 'SWC',
       14 => 'IFF',
       15 => 'WBMP',
       16 => 'XBM'
   );
   $temp = array();
   $data = array();

   // Get the image info using getimagesize().
   // If $temp fails to populate, warn the user and return false.
   if (!$temp = getimagesize($file)) {
       return false;
   }

   // Get the values returned by getimagesize()
   $temp = array_values($temp);

   // Make an array using values from $redefine_keys as keys and values from $temp as values.
   foreach ($temp AS $k => $v) {
       $data[$redefine_keys[$k]] = $v;
   }

   // Make 'type' usefull.
   $data['type'] = strtolower($types[$data['type']]);

   // Return the desired information.
   return !is_null($out) ? $data[$out] : $data;    
}
//使用文字创建图片	
function create_frome_text()
{
  	if(preg_match("/([a-f0-9][a-f0-9])([a-f0-9][a-f0-9])([a-f0-9][a-f0-9])/i", $this->wm_text_color, $color))
	{
		$red   = hexdec($color[1]);
		$green = hexdec($color[2]);
		$blue  = hexdec($color[3]);
	}
	else
	{
		$red  = 255;
		$green = 255;
		$blue  = 255;
	}
	unset($color);
	if(preg_match("/([a-f0-9][a-f0-9])([a-f0-9][a-f0-9])([a-f0-9][a-f0-9])/i", $this->wm_text_shadowcolor, $color))
	{
		$sred   = hexdec($color[1]);
		$sgreen = hexdec($color[2]);
		$sblue  = hexdec($color[3]);
	}
	else
	{
		$sred   = 0;
		$sgreen = 0;
		$sblue  = 0;
	}
	
	$fontSize = imagettfbbox($this->wm_text_size,$this->wm_text_angle,$this->wm_text_font,$this->wm_text);
	$insertfile_width  = abs($fontSize[2]) + abs($fontSize[0]) + 2*$this->wm_offset_x;
	$insertfile_height = abs($fontSize[7]) + abs($fontSize[1]) + 2*$this->wm_offset_y;
	
	$imageLogo = ImageCreateTrueColor($insertfile_width, $insertfile_height);
	ImageSaveAlpha($imageLogo, true);
	ImageAlphaBlending($imageLogo, false);
	$bgLogo = imagecolorallocatealpha($imageLogo, 255, 255, 255, 127);
	imagefill($imageLogo, 0, 0, $bgLogo);
	$mmTransp=127-($this->wm_image_transition*1.27);
	
	//shadow
	if ($this->wm_text_shadowcolor<>"#") 
	{
		$sschriftLogo=imagecolorallocatealpha($imageLogo, $sred, $sgreen, $sblue, $mmTransp);
	  	imagettftext($imageLogo, $this->wm_text_size, $this->wm_text_angle, 1, abs($fontSize[5])+1, $sschriftLogo, $this->wm_text_font, $this->wm_text);
	  	imagettftext($imageLogo, $this->wm_text_size, $this->wm_text_angle, 1, abs($fontSize[5])+1, $sschriftLogo, $this->wm_text_font, $this->wm_text);
	}
	//write text
	$schriftLogo=imagecolorallocatealpha($imageLogo, $red, $green, $blue, $mmTransp);
  	imagettftext($imageLogo, $this->wm_text_size, $this->wm_text_angle, 0, abs($fontSize[5]), $schriftLogo, $this->wm_text_font, $this->wm_text);

  	return $imageLogo;
}	
}
?>