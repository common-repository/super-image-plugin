<?php
/*
Plugin Name: Super Image Plugin
Plugin URI: http://www.pkphp.com/2008/06/08/wordpress-image-super-tools/
Description: A image super tools include : image water mark, image in content to auto down!
Author: askie
Version: 2.5
Author URI: http://www.pkphp.com/

Licence:
Provided under the GNU General Public Licence v3.0
http://www.gnu.org/licenses/gpl-3.0.txt

USAGE:
See http://www.pkphp.com/2008/06/08/wordpress-image-super-tools/
*/

//global
$im_path=(ABSPATH . 'wp-content/plugins/super-image-plugin');
$im_FontPath=$im_path."/fonts";
$im_photo=dirname(__FILE__)."/photo.jpg";

//preview
if ($_GET["cm"]=="preview") 
{
	IM_mark($im_photo,"preview");
	exit();
}


function IM_mark($file,$op="")
{
	global $im_path;
	
	if (file_exists($file.".txt")) 
	{
		return true;
	}
	
	$im_pos=get_option("im_pos");
	$im_pos=is_array($im_pos)?$im_pos:array($im_pos);
	$im_pos=$im_pos[array_rand($im_pos)];
	$im_pos=$im_pos==""?3:$im_pos;
	
	$im_config=array(
		"im_type"		=>get_option("im_type"),
		"im_backup"		=>get_option("im_backup"),
		"im_pos"		=>$im_pos,
		"im_font"		=>$im_path."/fonts/".get_option("im_font"),
		"im_text"		=>get_option("im_text"),
		"im_size"		=>get_option("im_size"),
		"im_color"		=>get_option("im_color"),
		"im_transp"		=>get_option("im_transp"),
		"im_scolor"		=>get_option("im_scolor"),
		"im_offsetx"	=>get_option("im_offsetx"),
		"im_offsety"	=>get_option("im_offsety"),
		"im_notsizex"	=>get_option("im_notsizex"),
		"im_notsizey"	=>get_option("im_notsizey"),
		"im_markfile"	=>$im_path."/mark.png",
		
	);

	//是否拷贝副本
	if ($im_config["im_backup"]==1 and $_GET["cm"]<>"preview") 
	{
		if (file_exists($file.".bak")) 
		{
			@unlink($file.".bak");
		}
		copy($file,$file.".bak");
	}
	
	include_once($im_path."/ImageShuiYin.php");
	$markImage=new ImageShuiYin();
	$markImage->src_image_name=$file;
	$markImage->wm_image_transition=$im_config["im_transp"];
	$markImage->wm_image_pos=$markImage->wm_text_pos=$im_config["im_pos"];
	$markImage->wm_offset_x=$im_config["im_offsetx"];
	$markImage->wm_offset_y=$im_config["im_offsety"];
	$markImage->src_img_mini_x=$im_config["im_notsizex"];
	$markImage->src_img_mini_y=$im_config["im_notsizey"];
	
	if ($im_config["im_type"]==0) 
	{
		$markImage->wm_image_name=$im_config["im_markfile"];
	}
	else 
	{
		$markImage->wm_text				=$im_config["im_text"];
		$markImage->wm_text_font		=$im_config["im_font"];
		$markImage->wm_text_color		=$im_config["im_color"];
		$markImage->wm_text_shadowcolor	=$im_config["im_scolor"];
		$markImage->wm_text_size		=$im_config["im_size"];
	}
	
	//根据$op判断如何操作
	$fileinfo=pathinfo($file);
	switch ($op)
	{
		case "new":
			$markImage->save_file=$fileinfo["dirname"]."/".$fileinfo["filename"]."_new.".$fileinfo["extension"];
			break;
			
		case "overwrite":
			$markImage->save_file=$file;
			break;	
		
		case "preview":
			$markImage->create();
			return ;	
	}
	
	if (get_option("im_enable")==1) 
	{
		$markImage->create();
		
		//写入一个记录文件
		if (!file_exists($file.".txt")) 
		{
			$fp=fopen($file.".txt","w");
			fwrite($fp,date("Y-m-d H:i:s"));
			fclose($fp);
		}
	}
}
//一般设定
function IM_generalsetting()
{
	if (date("Y-m-d")<>get_option("im_vesionsupdatetime")) 
	{
		IM_versionCheck();
	}
	if ($_GET['cm']=="dirmark") 
	{
		IM_dirmark();
		return ;
	}
	if ($_GET['cm']=="contentimagedown") 
	{
		echo IM_contenimagedownset();
		return ;
	}
	global $im_FontPath;
	if ($_POST['flag']=="general") 
     {
		foreach ($_POST as $key=>$value) 
		{
			if (strstr($key,"im_")==$key) 
			{
				update_option($key, $value);
			}
		}
		IM_addPkphplink();
		echo '<div class="updated"><p>General setting saved!</p></div>';
     }
     
     $img_pos=get_option('im_pos');
     if (!is_array($img_pos)) 
     {
     	$img_pos=array($img_pos);
     	update_option('im_pos', $img_pos);
     }
?>	
	<div class="wrap">
	<h3><a href="<?=$_SERVER["PHP_SELF"]?>?page=imagesupertools.php"><font color="Red">GeneralSetting</font></a> | <a href="<?=$_SERVER["PHP_SELF"]?>?page=imagesupertools.php&cm=dirmark">MarkImagesInDirectory</a> | <a href="<?=$_SERVER["PHP_SELF"]?>?page=imagesupertools.php&cm=contentimagedown">ContentImageDown</a></h3>
<style type="text/css">
<!--
.aatable {
	border: 1px solid #CCCCCC;
	margin: 0px;
}
.tdblack{
	background-color: #000000;
	line-height: 1px;
	height: 1px;
}
-->
</style>
<table width="100%" border="0" cellpadding="0">
<tr>
<td valign="top">
		<form name="updateoption" method="post">
		<input type="hidden" name="flag" value="general">		
		<table width="100%">
			<tr>
           		<td nowrap>Enable ImageMark ?</td>
           		<td>
           		  <input type="radio" name="im_enable" value="0" <?=get_option('im_enable')==0?" checked=\"checked\"":""?>>No 
				  <input type="radio" name="im_enable" value="1" <?=get_option('im_enable')==1?" checked=\"checked\"":""?>>Yes
				 </td>
			</tr>
			<tr><td colspan="2" width="100%" class="tdblack"></td></tr>
			<tr>
           		<td nowrap>Support this plugin?</td>
           		<td>
           		  <input type="radio" name="im_support" value="0" <?=get_option('im_support')==0?" checked=\"checked\"":""?>>No 
				  <input type="radio" name="im_support" value="1" <?=get_option('im_support')==1?" checked=\"checked\"":""?>>Yes(Give a link to me!)
				</td>
			</tr>
			<tr><td colspan="2" width="100%" class="tdblack"></td></tr>
			<tr>
           		<td nowrap>Backup source image ?</td>
           		<td>
           		  <input type="radio" name="im_backup" value="0" <?=get_option('im_backup')==0?" checked=\"checked\"":""?>>No 
				  <input type="radio" name="im_backup" value="1" <?=get_option('im_backup')==1?" checked=\"checked\"":""?>>Yes 
				  (Yes to backup imagefile as *.bak file before Mark!)
				 </td>
			</tr>
			<tr><td colspan="2" width="100%" class="tdblack"></td></tr>
			<tr>
           		<td nowrap>Position:</td>
           		<td>
           		  <table border="1">
					<tbody><tr>
						<td><input name="im_pos[]" value="1" type="checkbox" <?=in_array(1,$img_pos)?" checked=\"checked\"":""?>></td>
						<td><input name="im_pos[]" value="5" type="checkbox" <?=in_array(5,$img_pos)?" checked=\"checked\"":""?>></td>
						<td><input name="im_pos[]" value="2" type="checkbox" <?=in_array(2,$img_pos)?" checked=\"checked\"":""?>></td>
					</tr>
	    			<tr>
						<td><input name="im_pos[]" value="8" type="checkbox" <?=in_array(8,$img_pos)?" checked=\"checked\"":""?>></td>
						<td><input name="im_pos[]" value="0" type="checkbox" <?=in_array(0,$img_pos)?" checked=\"checked\"":""?>></td>
						<td><input name="im_pos[]" value="6" type="checkbox" <?=in_array(6,$img_pos)?" checked=\"checked\"":""?>></td>
	
					</tr>
	    			<tr>
						<td><input name="im_pos[]" value="4" type="checkbox" <?=in_array(4,$img_pos)?" checked=\"checked\"":""?>></td>
						<td><input name="im_pos[]" value="7" type="checkbox" <?=in_array(7,$img_pos)?" checked=\"checked\"":""?>></td>
						<td><input name="im_pos[]" value="3" type="checkbox" <?=in_array(3,$img_pos)?" checked=\"checked\"":""?>></td>
					</tr>
	    			</tbody></table>
	    			Multiple will be chosen randomly position watermark!
	    			</td>
			</tr>
			<tr><td colspan="2" width="100%" class="tdblack"></td></tr>	
			<tr>
           		<td nowrap>Offset</td>
           		<td>
           		 	<table border="0">
						<tbody><tr><td>x</td><td><input name="im_offsetx" value="<?=get_option('im_offsetx')?>" size="4" type="text"> px</td></tr>
		
						<tr><td>y</td><td><input name="im_offsety" value="<?=get_option('im_offsety')?>" size="4" type="text"> px</td></tr>
					</tbody></table>
				 </td>
			</tr>
			<tr><td colspan="2" width="100%" class="tdblack"></td></tr>	
			<tr>
           		<td nowrap>Image size not to mark</td>
           		<td>
           		 	width <= <input name="im_notsizex" value="<?=get_option('im_notsizex')?>" size="3" type="text">px &nbsp;&nbsp;&nbsp;<font color="Red"><b>OR</b></font>&nbsp;&nbsp;&nbsp;
           		 	height <= <input name="im_notsizey" value="<?=get_option('im_notsizey')?>" size="3" type="text">px
				</td>
			</tr>
			<tr><td colspan="2" width="100%" class="tdblack"></td></tr>	
			<tr>
           		<td nowrap>Type of ImageMark ?</td>
           		<td>
           		  <table border="0">
		    	<tbody><tr>
			  		<td><input name="im_type" value="0" type="radio" <?=get_option('im_type')==0?" checked=\"checked\"":""?>></td>
			  		<td>
			  			Use file <i>mark.png</i> as watermark<br>
						<img src="../wp-content/plugins/super-image-plugin/mark.png"><br>
						<font color="Red">Please change mark.png to your site's logo!</font>
			  		</td>
				</tr>
				<tr><td colspan="2"><hr width="100%"></td></tr>
				<tr>
			  		<td valign="top"><input name="im_type" value="1" type="radio" <?=get_option('im_type')==1?" checked=\"checked\"":""?>></td>
			  		<td>Use text as watermark<br>
					  <table border="0">
						<tbody><tr><td>Font:</td><td>
						<select name="im_font" size="1">
						<?php
						$im_FontListe=array();
						if ($handle = opendir($im_FontPath)) 
						{
						   while (false !== ($file = readdir($handle))) 
						   {
						      if(preg_match("/ttf$/i", $file)) 
						      {
						        array_push($im_FontListe, $file);
						      }
						   }
						   closedir($handle);
						   sort($im_FontListe,SORT_STRING);
						}
						$im_font=get_option("im_font");
						foreach ($im_FontListe as $value) 
						{
			                print "<option";
			                if($im_font==$value) {
			                  echo ' selected="selected"';
							}
			                print ">$value</option>";
			            }
						?>
						</select><br>
						中文用户请点击<a href="http://svn.wp-plugins.org/super-image-plugin/tags/1.9/fonts/chinese.ttf">这里下载中文字体文件</a>，下载后请将chinese.ttf文件存入插件目录内的fonts目录下。
						当你要在图片上输出汉字时，请务必选择chinese.ttf字体。你也可以将其他的汉字字体ttf文件存入fonts目录。
</td></tr>

            			<tr><td>Size:</td><td><input name="im_size" value="<?=get_option('im_size')?>" size="4" maxlength="2" type="text"> px</td></tr>
						<tr><td>Color:</td><td><input name="im_color" value="<?=get_option('im_color')?>" size="6" maxlength="7" type="text"> (#cccccc)</td></tr>
						<tr><td valign="top">Text:</td><td><textarea name="im_text" cols="20" rows="5"><?=get_option('im_text')?></textarea></td></tr>
				    <tr><td>Opaque:</td><td><input name="im_transp" value="<?=get_option('im_transp')?>" size="3" maxlength="3" type="text"> % </td></tr>

				    <tr><td>Shadow:</td><td colspan="3"><input name="im_scolor" value="<?=get_option('im_scolor')?>" size="6" maxlength="7" type="text"> (#cccccc but # for no shadow)</td></tr>
					  </tbody></table>
					</td>
				</tr>
			  </tbody></table>
			  </td>
			
		</tr>
		</table>
	<p><div class="submit"><input type="submit" name="update_rp" value="<?php _e('Save!', 'update_rp') ?>"  style="font-weight:bold;" /></div></p>
	</form> 		
</td>
<td valign="top" width="200">
<?IM_sidebar();?>
</td>
</tr>
</table>	
</div>
<?php }
//文章内容下载图片
function IM_contenimagedown($post_ID)
{
	if (get_option("im_downimgenable")<>1) return ; 
	
	//$post=get_post($post_ID);
	global $wpdb;
	$post=$wpdb->get_row("SELECT * FROM {$wpdb->posts} WHERE ID='{$post_ID}'");
	
	$images=IM_GetImgUrlFromHtml($post->post_content);
	//获取文本中存在的图片
	$newimages=array();
	foreach ($images as $key=>$url) 
	{
		$newimages[$key]=IM_downloadimage($url);
	}
	$post->post_content=str_replace($images,$newimages,$post->post_content);
	
	//获取链接中存在的图片
	$linkImages=IM_GetImgUrlFromLink($post->post_content);
	$newlinkimages=array();
	foreach ($linkImages as $key=>$url) 
	{
		$newlinkimages[$key]=IM_downloadimage($url);
	}
	$post->post_content=str_replace($linkImages,$newlinkimages,$post->post_content);
	$postary=add_magic_quotes(array("content"=>$post->post_content));
	
	$wpdb->query("UPDATE `wp_posts` SET `post_content` = '{$postary["content"]}' WHERE `ID` = '{$post_ID}';");
	
	//合并两次替换的数据
	//print_r($images);print_r($linkImages);
	$allimages=array();
	$siteurl=get_option("siteurl");
	foreach ($images as $key=>$oldimg) 
	{
		if (strstr($oldimg,$siteurl)) continue;
		$allimages[$oldimg]=$newimages[$key];
	}
	
	foreach ($linkImages as $key=>$oldimg) 
	{
		if (strstr($oldimg,$siteurl)) continue;
		$allimages[$oldimg]=$newlinkimages[$key];
	}
	
	//存入数据库
	$IMdata=get_post_meta($post_ID,"IM_data");
	if (count($allimages)==0) return $allimages;
	
	if ($IMdata=="") 
	{
		add_post_meta($post_ID,"IM_data",$allimages,true);
	}
	else 
	{
		foreach ($IMdata[0] as $old=>$new) 
		{
			$allimages[$old]=$new;
		}
		update_post_meta($post_ID,"IM_data",$allimages);
	}
	return $allimages;
}
//将远程图片网址返回文章内容中
function IM_restore()
{
	global $wpdb;
	$data=get_post_meta_by_key("IM_data");
//	print_r($data);exit();
	foreach ($data as $postid=>$imdata) 
	{
		$old=$new=array();
		foreach ($imdata as $oldurl=>$newurl) 
		{
			if (strstr($oldurl,get_option("siteurl"))==$oldurl) 
			{
				continue;
			}
			$old[]=$oldurl;
			$new[]=$newurl;
		}
		
		$p=$wpdb->get_row("SELECT * FROM {$wpdb->posts} WHERE ID='{$postid}'");
		$p->post_content=str_replace($new,$old,$p->post_content);
		$updatecontent=$wpdb->query("UPDATE {$wpdb->posts} SET `post_content` = '{$p->post_content}' WHERE `ID` ='{$postid}' LIMIT 1 ;");
		
		if ($updatecontent==1) 
		{
			//删除文件
			foreach ($new as $newlink) 
			{
				$newlink=str_replace(get_option("siteurl"),"..",$newlink);
				@unlink($newlink);
			}
			$wpdb->query("DELETE FROM `{$wpdb->postmeta}` WHERE (`meta_key` = 'IM_contentdowned' OR `meta_key` = 'IM_data') OR `post_id`='{$postid}';");
		}
	}
	
	return count($data);
}
function get_post_meta_by_key($key) 
{
	global $wpdb;
	
	$results=$wpdb->get_results("SELECT * FROM {$wpdb->postmeta} WHERE meta_key='{$key}'");
	$z=array();
	foreach ($results as $a) 
	{
		$z[$a->post_id]=unserialize($a->meta_value);
	}
	return $z;
}
//文章内容图片下载设定
function IM_contenimagedownset()
{
	if ($_GET["subcm"]=="restore") 
	{
		//update_option("im_downimgenable",0);
		$deleten=IM_restore();
		echo '<div class="updated"><p>'.$deleten.' Posts Restored!</p></div>';
	}
	if ($_POST["flag"]=="savesetting") 
	{
		foreach ($_POST as $key=>$value) 
		{
			if (strstr($key,"im_")==$key) 
			{
				update_option($key, $value);
			}
		}
		echo '<div class="updated"><p>Setting saved!</p></div>';
	}
?>
<div class="wrap">
<style type="text/css">
<!--
.aatable {
	border: 1px solid #CCCCCC;
	margin: 0px;
}
.tdblack{
	background-color: #000000;
	line-height: 1px;
	height: 1px;
}
-->
</style>
<h3><a href="<?=$_SERVER["PHP_SELF"]?>?page=imagesupertools.php">GeneralSetting</a> | <a href="<?=$_SERVER["PHP_SELF"]?>?page=imagesupertools.php&cm=dirmark">MarkImagesInDirectory</a> | <a href="<?=$_SERVER["PHP_SELF"]?>?page=imagesupertools.php&cm=contentimagedown"><font color="Red">ContentImageDown</font></a></h3>
<table width="100%" border="1" cellpadding="0">
<tr>
<td valign="top" width="600">
		<form name="search" method="post" action="<?=$_SERVER["PHP_SELF"]?>?page=imagesupertools.php&cm=contentimagedown">
		<input type="hidden" name="flag" value="savesetting">	
		<table width="100%">
		<tr><td colspan="2" width="100%" class="tdblack"></td></tr>
		<tr>
       		<td width="300">Enable auto download all remote images inluded in content <br>
       		when publish a post or page?<br>
       		All the downloaded images will be marked!</td>
       		<td nowrap>
       		  <input type="radio" name="im_downimgenable" value="0" <?=get_option('im_downimgenable')==0?" checked=\"checked\"":""?>>No 
			  <input type="radio" name="im_downimgenable" value="1" <?=get_option('im_downimgenable')==1?" checked=\"checked\"":""?>>Yes
			 </td>
		</tr>
		<tr><td colspan="2" width="100%" class="tdblack"></td></tr>
		<tr>
       		<td width="300">Local images on your server inluded in post's content <br>
       		to add mark again when publish a post or page?<br>
			If set Yes will add mark to local image again!
       		</td>
       		<td nowrap>
       		  <input type="radio" name="im_contentlocalimgmarkagain" value="0" <?=get_option('im_contentlocalimgmarkagain')==0?" checked=\"checked\"":""?>>No 
			  <input type="radio" name="im_contentlocalimgmarkagain" value="1" <?=get_option('im_contentlocalimgmarkagain')==1?" checked=\"checked\"":""?>>Yes
			</td>
		</tr>
		<tr><td colspan="2" width="100%" class="tdblack"></td></tr>
		<tr>
       		<td width="300">When begin to mark localimage in content?
       		</td>
       		<td nowrap>
       		  <input type="text" size="20" name="im_markstarttime" value="<?=get_option('im_markstarttime')?>"><br> 
			  Format: <font color="Blue"><b><?=date("Y-m-d H:i:s")?></b></font> (server time)
			</td>
		</tr>
		<tr><td colspan="2" width="100%" class="tdblack"></td></tr>
		<tr>
       		<td width="300">Down any link is image in content?
       		</td>
       		<td nowrap>
       			<input type="radio" name="im_downlinkimage" value="0" <?=get_option('im_downlinkimage')==0?" checked=\"checked\"":""?>>No 
			    <input type="radio" name="im_downlinkimage" value="1" <?=get_option('im_downlinkimage')==1?" checked=\"checked\"":""?>>Yes
			</td>
		</tr>
		<tr><td colspan="2" width="100%" class="tdblack"></td></tr>
		<tr>
       		<td width="300">Restore remote images link to post content:
       		</td>
       		<td nowrap>
       			<a href="<?=$_SERVER["PHP_SELF"]?>?page=imagesupertools.php&cm=contentimagedown&subcm=restore">Click Here to Restore</a>
			</td>
		</tr>
		<tr><td colspan="2" width="100%" class="tdblack"></td></tr>
		</table>	
		<p><div class="submit"><input type="submit" name="update_rp" value="<?php _e('Save!', 'update_rp') ?>"  style="font-weight:bold;" /></div></p> 
		</form>
</td>
<td valign="top" width="200">			
<?IM_sidebar();?>
</td>
</tr>
</table>	
</div>
<?php
}
//批处理文件夹图片加入水印
function IM_dirmark()
{
	if ($_POST["flag"]=="domarkimages") 
	{
		if (isset($_POST["domarkimages"])) 
		{
			$images=IM_images2mark($_POST["img"]);
			echo '<div class="updated"><p>The checked images have been marked!</p></div>';
		}
		if (isset($_POST["dodeleteimages"])) 
		{
			$images=IM_images2delete($_POST["img"]);
			echo '<div class="updated"><p>The checked images have been deleted!</p></div>';
		}
		if (isset($_POST["imagesback2bak"])) 
		{
			$images=IM_imagesback2bak($_POST["img"]);
			echo '<div class="updated"><p>The checked images have back to .bak file!</p></div>';
		}
	}
	if ($_GET["cm"]=="dirmark") 
	{
		$searchpath=str_replace("\\","/",$_GET["searchdir"]==""?dirname(__FILE__):$_GET["searchdir"]);
		$images	=IM_searchimgindir($searchpath);
		$dirs	=IM_searchdir($searchpath);
	}
	
?>
<div class="wrap">
<script>
function checkboxselect(itemname,checkstatus)
 {
	 if(!itemname) return;
	 
	 if(!itemname.length)
	 {
	 	itemname.checked=checkstatus;
	 }
	 else
	 {
	 	for(var i=0; i<itemname.length; ++i)
		 {
		 itemname[i].checked=checkstatus;
		 }
	 }
	 
	 var sel = document.getElementsByName("all_sel");
	 for(var i=0; i<sel.length; ++i)
	 {
	 	sel[i].checked = checkstatus;
	 }
 }
 function Allcheckboxselect(itemname)
 {
     var n=0;
	 var sel = document.getElementsByName("all_sel");
		
	 if(!itemname) return;
	 if(!itemname.length)
	 {
	 	sel.checked=itemname.checked;
	 }
	 else
	 {
	 	for(var i=0;i<itemname.length;++i)
		 {
		 	if(itemname[i].checked==true)
			{
				n=n+1;
			}
		 }
	 }

	 if(n==itemname.length)
		 for(var i=0; i< sel.length; ++i)
		 {
			sel[i].checked = true;
		 }
	 else
		for(var i=0; i< sel.length; ++i)
		 {
			sel[i].checked = false;
		 }
 }
</script>
<h3><a href="<?=$_SERVER["PHP_SELF"]?>?page=imagesupertools.php">GeneralSetting</a> | <a href="<?=$_SERVER["PHP_SELF"]?>?page=imagesupertools.php&cm=dirmark"><font color="Red">MarkImagesInDirectory</font></a> | <a href="<?=$_SERVER["PHP_SELF"]?>?page=imagesupertools.php&cm=contentimagedown">ContentImageDown</a></h3>
<style type="text/css">
<!--
.aatable {
	border: 1px solid #CCCCCC;
	margin: 0px;
}
.tdblack {
	background-color: #000000;
	line-height: 1px;
	height: 1px;
}
-->
</style>
<table width="100%" border="0" cellpadding="3">
<tr>
<td valign="top" width="600">		
		<form name="search" method="GET" action="<?=$_SERVER["PHP_SELF"]?>">
		<input type="hidden" name="page" value="imagesupertools.php">
		<input type="hidden" name="cm" value="dirmark">
		<input type="hidden" name="flag" value="dirimagesearch">
		<table width="100%">
			<tr>
           		<td nowrap align="right"><b>Search:</b></td>
           		<td nowrap>
           		  <input type="text" name="searchdir" value="<?=$_POST["searchdir"]==""?(str_replace("\\","/",$_GET["searchdir"]==""?dirname(__FILE__):$_GET["searchdir"])):$_POST["searchdir"]?>" size="50"> 
           		  <input type="submit" name="dosearch" value="search images"  style="font-weight:bold;" > 
				 </td>
			</tr>
			<tr><td colspan="2" width="100%" class="tdblack"></td></tr>
			<tr>
           		<td nowrap align="right"><b>Filter:</b></td>
           		<td nowrap>
           		  List about <input type="text" name="modifytime" value="<?=$_POST["modifytime"]==""?($_GET["modifytime"]==""?60:$_GET["modifytime"]):$_POST["modifytime"]?>" size="3"> seconds do not modified images. 
				 </td>
			</tr>
			<tr><td colspan="2" width="100%" class="tdblack"></td></tr>
			<tr>
				<td nowrap align="right"><b>SubDir:</b></td>
           		<td>
           		<?
           		foreach ((array)$dirs as $dirname=>$dirpath) 
           		{
           			$subdir[]="<a href='{$_SERVER["PHP_SELF"]}?page=imagesupertools.php&cm=dirmark&flag=dirimagesearch&searchdir=".urlencode($dirpath)."'>$dirname</a>";
           		}
           		echo implode(" | ",(array)$subdir);
           		?>
           		</td>
			</tr>
			<tr><td colspan="2" width="100%" class="tdblack"></td></tr>
		</table>	
		</form>
		<form name="search" method="post" action="<?=$_SERVER["PHP_SELF"]?>?page=imagesupertools.php&cm=dirmark">
		<input type="hidden" name="flag" value="domarkimages">
		<input type="hidden" name="modifytime" value="<?=$_POST["modifytime"]==""?$_GET["modifytime"]==""?60:$_GET["modifytime"]:$_POST["modifytime"]?>">
		<input type="hidden" name="searchdir" value="<?=$_POST["searchdir"]==""?str_replace("\\\\","/",$_GET["searchdir"]):$_POST["searchdir"]?>">
		<table width="700" border="1">
		<tr>
       		<td nowrap width="1%" bgcolor="Black"><input type="checkbox" class="radio" name=all_sel value="" onClick="checkboxselect(document.getElementsByName('img[]'), checked);"></td>
       		<td  width="97%" bgcolor="Black" align="center"><font>Image files</font></td>
       		<td nowrap width="1%" bgcolor="Black" align="center"><font>Backup</font></td>
       		<td nowrap width="1%" bgcolor="Black" align="center"><font>Modify time</font></td>
		</tr>
		<tr><td colspan="4" width="100%" class="tdblack"></td></tr>
		<?
		foreach ((array)$images as $key=>$var) 
		{
		?>	
		<tr>
       		<td nowrap width="1%"><input type="checkbox" name="img[]" value="<?=base64_encode($var)?>" onClick="Allcheckboxselect(document.getElementsByName('img[]'));"></td>
       		<td  width="98%"><a href="<?=str_replace(str_replace("\\","/",ABSPATH),get_option("siteurl")."/",$var)?>" target="_blank" title="<?=$var?>"><?=strlen($var)>80?"...".substr($var,strlen($var)-80):$var?></a></td>
       		<td nowrap width="1%" align="center"><?=file_exists($var.".bak")?"Y":"N";?></td>
       		<td nowrap width="1%"><?=date ("Y-m-d H:i:s", filemtime($var));?></td>
		</tr>
		<tr><td colspan="4" width="100%" class="tdblack"></td></tr>
		<?}?>	
		</table>
		<input type="submit" name="domarkimages" value="Mark Images"  style="font-weight:bold;" <?=get_option("im_enable")==1?"":" disabled"?>> 
		<input type="submit" name="imagesback2bak" value="Images back to last"  style="font-weight:bold;" >
		<input type="submit" name="dodeleteimages" value="Delete Images"  onclick="javascript:confm('209','Delete the seleted Images?');return false;" style="font-weight:bold;" > 	
		</form>
</td>
<td valign="top" width="200">			
<?IM_sidebar();?>
</td>
</tr>
</table>	
</div>
<?php
}
//sidebar
function IM_sidebar()
{
	?>
	<b>Preview</b><br>
	<p align="center" style="vertical-align: middle;">
    <img src="<?=$_SERVER['REQUEST_URI']."&cm=preview"?>">
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
		<input type="hidden" name="cmd" value="_s-xclick">
		<input type="image" src="https://www.paypal.com/zh_XC/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
		<img alt="" border="0" src="https://www.paypal.com/zh_XC/i/scr/pixel.gif" width="1" height="1"><font size="3">to support this plugins!</font>
		<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----">
	</form>
	</p>
	<hr>
	<b>About</b><br>
	<p align="left">
	<?=IM_showversionstring()?>
	<img src="<?=IM_getpluginUrl()?>/i.png"> <a href="http://www.pkphp.com/2008/06/08/wordpress-image-super-tools/" target="_blank">SuperImageTools homepage</a><br>
	<img src="<?=IM_getpluginUrl()?>/i.png"> Askie's home page <a href="http://www.pkphp.com/" target="_blank">PKPHP.com</a>!
	</p>
	<?
}
//处理选择的图片加入水印
function IM_images2mark($images)
{
	foreach ((array)$images as $file) 
	{
		IM_mark(base64_decode($file),"overwrite");
	}
	return ;
}
//删除图片
function IM_images2delete($images)
{
	foreach ((array)$images as $file) 
	{
		unlink(base64_decode($file));
	}
	return ;
}
//删除图片
function IM_imagesback2bak($images)
{
	foreach ((array)$images as $file) 
	{
		$file=base64_decode($file);
		if (file_exists($file.".bak")) 
		{
			unlink($file);
			rename($file.".bak",$file);
		}
		
	}
	return ;
}
//遍历目录搜索图片
function IM_searchimgindir($dir)
{
    $imageArray=array();
	if ($handle = opendir($dir)) 
    {
	     while (false !== ($file = readdir($handle))) 
	     {
	           if ($file != "." && $file != "..") 
	           {           
		             $child_dir = str_replace("//","/",$dir."/".$file);
		             if(is_dir($child_dir)) 
	                 {
	                     $imageArray=array_merge(IM_searchimgindir($child_dir),$imageArray);
	                 }
		             else 
		             {
		             	if ($file=="mark.png" or $file=="photo.jpg" or $file=="i.png") continue; 
		             	
		             	$x=str_replace("//","/",str_replace("\\\\","/",$child_dir));
		             	$p=pathinfo($x);
		             	if ($_POST["modifytime"]+filemtime($x)>=time()) continue;
		             	
		             	if (strtolower($p["extension"])=="jpg" or strtolower($p["extension"])=="jpeg" or strtolower($p["extension"])=="gif" or strtolower($p["extension"])=="png") 
		             	{
		             		$imageArray[]=$x;
		             	}
		             }
	           }
	     }
	     closedir($handle); 
    }
    return $imageArray;
}
//遍历目录搜索目录
function IM_searchdir($dir)
{
    $imageArray=array();
    $p=pathinfo($dir);
    $imageArray[".."]=$p["dirname"];
    
	if ($handle = opendir($dir)) 
    {
	     while (false !== ($file = readdir($handle))) 
	     {
	           if ($file != "." && $file != "..") 
	           {           
		             $child_dir = str_replace("//","/",$dir."/".$file);
		             if(is_dir($child_dir)) 
	                 {
	                    $imageArray[$file]=$child_dir; 
	                 }
	           }
	     }
	     closedir($handle); 
    }
    asort($imageArray);
    return $imageArray;
}
//获取文本中的图片地址
function IM_GetImgUrlFromHtml($text)
{
	preg_match_all("'<img[^>]*?>'si",$text,$n);
	$oldimgsrc=array();
	foreach ($n as $m)
	{
		foreach ($m as $x)
		{
			preg_match('#src[[:space:]]*=[[:space:]]*[\'|"]?([[:alnum:]:@/._-]+[?]?[^\'|"]*)"?#ie',$x,$y);
			$oldimgsrc[]=$y[1];
		}
	}
	
	return $oldimgsrc;
}
//获取文本链接中的图片地址
function IM_GetImgUrlFromLink($text)
{
	if (get_option("im_downlinkimage")!=1) 
	{
		return array();
	}
	
	preg_match_all("'<a[^>]*?>'si",$text,$n);
	$oldimgsrc=array();
	foreach ($n as $m)
	{
		foreach ($m as $x)
		{
			preg_match('#href[[:space:]]*=[[:space:]]*[\'|"]?([[:alnum:]:@/._-]+[?]?[^\'|"]*)"?#ie',$x,$y);
			$oldimgsrc[]=$y[1];
		}
	}
	//过滤出来图片
	$returnImg=array();
	foreach ($oldimgsrc as $s) 
	{
		$p=pathinfo($s);
		if (in_array(strtolower($p["extension"]),array("jpg","png","gif","jpeg"))) 
		{
			$returnImg[]=$s;
		}
	}
	return $returnImg;
}
//下载远程图片
function IM_downloadimage($url)
{
	if (strstr(strtolower($url),"http://")<>strtolower($url)) 
	{
		return $url;
	}
	
	if (get_option('im_contentlocalimgmarkagain')==1 and strstr(strtolower($url),get_option("siteurl"))==strtolower($url)) 
	{
		$localImage = str_replace(get_option("siteurl"),ABSPATH,$url);
		$localImage = str_replace("\\","/",$localImage);
		$localImage = str_replace("//","/",$localImage);
		$mtime = @filemtime($localImage);
		$starttime = strtotime(get_option("im_markstarttime"));
		
		if ($starttime<$mtime and get_option("im_enable")==1) 
		{
			IM_mark($localImage,$op="overwrite");
		}
		return $url;
	}
	elseif (strstr(strtolower($url),get_option("siteurl"))==strtolower($url)) 
	{
		return $url;
	}
	
	if ( ! ( ( $uploads = wp_upload_dir() ) && false === $uploads['error'] ) )
	{
		return $url;
	}
	
	$fileinfo=pathinfo($url);
	//判断文件是否已经下载过，如果下载过则停止下载
	$fileinfo['basename'] = IM_isfilename($fileinfo['basename'])?substr(md5($url),0,4)."_".$fileinfo['basename']:substr(md5($url),0,5).".".$fileinfo["extension"];
	$filename = $fileinfo['basename'];
	
	if (file_exists($uploads['path']."/".$filename)) 
	{
		return $uploads["url"]."/".$filename;
	}
	
	$tmpfile=IM_savefile($url,$uploads['path']);
	
	if (file_exists($tmpfile)==false) 
	{
		return $url;
	}
	$tmpfileinfo=IM_image_info($tmpfile);
	
	//判断是否是图片文件，否则删除
	if ($tmpfileinfo==false or $tmpfileinfo["type"]=="" or in_array($tmpfileinfo["type"],array("gif","jpg","png"))==false) 
	{
		@unlink($tmpfile);
		return $url;
	}
	
	//把下载的临时文件重命名
	if (rename($tmpfile,$uploads['path']."/".$filename)) 
	{
		if (get_option("im_enable")==1) 
		{
			IM_mark($uploads['path']."/".$filename,$op="overwrite");
		}
		// Construct the attachment array
		global $post;
		$post_ID=$post->ID;
		$attachment = array(
			'post_mime_type' => "image/".$tmpfileinfo["type"],
			'guid' => $uploads["url"]."/".$filename,
			'post_parent' => $post_ID,
			'post_title' => $filename,
			'post_content' => $filename,
			'post_author'	=>1,
		);
		
		// Save the data
		$file=$uploads['path']."/".$filename;
		$id = wp_insert_attachment($attachment, $file, $post_ID);
		if (!is_wp_error($id)) 
		{
			@include_once("./wp-admin/includes/image.php");
			@include_once("./includes/image.php");
			wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );
		}
		return $uploads["url"]."/".$filename;	
	}
	else 
	{
		@unlink($tmpfile);
		return $url;
	}
	
}
//下载文件
function IM_savefile($url,$path,$filename="")
{
	$content=@file_get_contents($url);
	if ($filename=="") 
	{
		$filename=IM_randname();
	}
	$filename=$path."/".$filename;
	
	$fp=fopen($filename,"w+");
	if (fwrite($fp,$content)) 
	{
		fclose($fp);
		return $filename;
	}
	else 
	{
		fclose($fp);
		return false;
	}
}
//是否合法文件名
function IM_isfilename($name)
{
	$sErrorStr = Array("\\", "/", ":", "*", "?", "\"", "<", ">", "|","&","%");
	foreach ($sErrorStr as $var) 
	{
		if (strpos($name,$var)) 
		{
			return false;
		}
	}
	if(preg_match("/[".chr(0xa1)."-".chr(0xff)."]+$U/", $name)) 
	{
	    return false;
	}
	return true;
}
//显示当前发布版本信息
function IM_showversionstring()
{
	$v=get_option("im_vesionstring");
	if ($v<>"") 
	{ 
		return '<div style="border: 1px dotted #FF6600; background-color: #FFEFDF; padding: 2px; margin-bottom: 5px; margin-top: -5px;">'.$v.'</div>';	
	}
}
function IM_addPkphplink()
{
	global $wpdb;
	
	if (get_option("im_support")<>1) 
	{
		return ;
	}
	
	$r=$wpdb->get_results("SELECT * FROM $wpdb->links WHERE link_name='PKPHP.COM' LIMIT 0 , 1");
	if (count($r)==0) 
	{
		$link_url="http://www.pkphp.com";
		$link_name="PK with PHP!";
		$link_target="_blank";
		$link_description="PK with PHP!";
		$link_owner = 1;
		$link_category = array(get_option('default_link_category'));
		$wpdb->query("INSERT INTO $wpdb->links (link_url, link_name, link_image, link_target, link_description, link_visible, link_owner, link_rating, link_rel, link_notes, link_rss) VALUES('$link_url','$link_name', 'NULL', '$link_target', '$link_description', 'Y', '$link_owner', '0', 'NULL', 'NULL', 'NULL')");	
		$link_id = (int) $wpdb->insert_id;
		wp_set_link_cats($link_id, $link_category);
	}
}
//检查版本
function IM_versionCheck()
{
	//$v=@file_get_contents("http://www.pkphp.com/versioncheck.php");
	if ($v<>"") 
	{
		update_option("im_vesionstring", $v);
		update_option("im_vesionsupdatetime", date("Y-m-d"));
	}
	
	IM_addPkphplink();
}
//获取插件URL
function IM_getpluginUrl()
{
	$path = dirname(__FILE__);
	$path = str_replace("\\","/",$path);
	$path = trailingslashit(get_bloginfo('wpurl')) . trailingslashit(substr($path,strpos($path,"wp-content/")));
	return $path;
}
//产生一个随机文件名
function IM_randname($extension="")
{
	$name= substr(md5(time().chr(rand(1,150))),rand(0,25),6);
	if ($extension) 
	{
		return $name.".".$extension;
	}
	else 
	{
		return $name;
	}
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
function IM_image_info($file = null, $out = null) {

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
//初始化
function IM_init()
{
	$im_config=array(
		"im_type"		=>0,
		"im_backup"		=>1,
		"im_pos"		=>array(0),
		"im_font"		=>"arial.ttf",
		"im_text"		=>"PKPHP.com",
		"im_size"		=>12,
		"im_color"		=>"#cccccc",
		"im_transp"		=>"50",
		"im_scolor"		=>"#000000",
		"im_offsetx"	=>1,
		"im_offsety"	=>1,
		"im_notsizex"	=>150,
		"im_notsizey"	=>150,	
		"im_support"	=>1,
		"im_contentlocalimgmarkagain"	=>1,
		"im_markstarttime"	=>date("Y-m-d H:i:s"),
	);
	foreach ($im_config as $key=>$var) 
	{
		if (get_option($key)=="") 
		{
			$var=$key=="im_support"?1:$var;
			update_option($key,$var);
		}
	}
}
//添加菜单
function IM_admin_menu() 
{
	if (function_exists('add_options_page')) 
	{ 
		add_options_page('ImageSuperTools', 'ImageSuperTools', 8, basename(__FILE__), 'IM_generalsetting');
	}
}

if (function_exists(add_action)) 
{
	add_action('init', 'IM_init');
	add_action('admin_menu', 'IM_admin_menu');
	add_action('edit_post', 'IM_contenimagedown');
	add_action('edit_page', 'IM_contenimagedown');
}
//通过文章内容来激活下载图片
function IM_downimgfromcontentfilter($content)
{
	global $post,$wpdb;
	
	if(is_object($post))
	{
		$downed=get_post_meta($post->ID,"IM_contentdowned",true);
		if ($downed=="") 
		{
			$IMdata=(array)IM_contenimagedown($post->ID);
			$oldimgs=$newimgs=array();
			foreach ($IMdata as $old=>$new) 
			{
				if (strstr($old,get_option("siteurl"))==$old or $old==$new) 
				{
					continue;
				}
				$oldimgs[]=$old;
				$newimgs[]=$new;
			}
			$content=str_replace($oldimgs,$newimgs,$post->post_content);
			add_post_meta($post->ID,"IM_contentdowned","1",true);
		}
	}
	return $content;
}
if (get_option("im_downimgenable")==1)
{
	add_filter("the_content","IM_downimgfromcontentfilter");
}
?>