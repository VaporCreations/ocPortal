<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2011

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		galleries
 */

class Block_main_image_fader
{

	/**
	 * Standard modular info function.
	 *
	 * @return ?array	Map of module info (NULL: module is disabled).
	 */
	function info()
	{
		$info=array();
		$info['author']='Chris Graham';
		$info['organisation']='ocProducts';
		$info['hacked_by']=NULL;
		$info['hack_version']=NULL;
		$info['version']=2;
		$info['locked']=false;
		$info['parameters']=array('param','time','zone','order');
		return $info;
	}
	
	/**
	 * Standard modular run function.
	 *
	 * @param  array		A map of parameters.
	 * @return tempcode	The result of execution.
	 */
	function run($map)
	{
		$cat=array_key_exists('param',$map)?$map['param']:'';
		if ($cat=='') $cat='root';
		$mill=array_key_exists('time',$map)?intval($map['time']):8000; // milliseconds between animations
		$zone=array_key_exists('zone',$map)?$map['zone']:get_module_zone('galleries');
		$order=array_key_exists('order',$map)?$map['order']:'';

		require_code('ocfiltering');
		$cat_select=ocfilter_to_sqlfragment($cat,'cat','galleries','parent_id','cat','name',false,false);

		$images=array();
		$images_full=array();
		$image_rows=$GLOBALS['SITE_DB']->query('SELECT id,thumb_url,url FROM '.get_table_prefix().'images WHERE '.$cat_select,100/*reasonable amount*/);
		$video_rows=$GLOBALS['SITE_DB']->query('SELECT id,thumb_url,thumb_url AS url FROM '.get_table_prefix().'videos WHERE '.$cat_select,100/*reasonable amount*/);
		$all_rows=array();
		if ($order!='')
		{
			foreach (explode(',',$order) as $o)
			{
				$num=substr($o,1);
				
				if (is_numeric($num))
				{
					switch (substr($o,0,1))
					{
						case 'i':
							foreach ($image_rows as $i=>$row)
							{
								if ($row['id']==intval($num))
								{
									$all_rows[]=$row;
									unset($image_rows[$i]);
								}
							}
							break;
						case 'v':
							foreach ($video_rows as $i=>$row)
							{
								if ($row['id']==intval($num))
								{
									$all_rows[]=$row;
									unset($video_rows[$i]);
								}
							}
							break;
					}
				}
			}
		}
		$all_rows=array_merge($all_rows,$image_rows,$video_rows);
		require_code('images');
		foreach ($all_rows as $row)
		{
			$url=$row['thumb_url'];
			if (url_is_local($url)) $url=get_custom_base_url().'/'.$url;
			$images[]=$url;

			$full_url=$row['url'];
			if (url_is_local($full_url)) $full_url=get_custom_base_url().'/'.$full_url;
			$images_full[]=$full_url;
		}

		if (count($images)==0) return do_template('INLINE_WIP_MESSAGE',array('MESSAGE'=>do_lang_tempcode('NO_ENTRIES')));

		$nice_cat=str_replace('*','',$cat);
		if (preg_match('#^[\w\_]+$#',$nice_cat)==0) $nice_cat='root';
		$gallery_url=build_url(array('page'=>'galleries','type'=>'misc','id'=>$nice_cat),$zone);

		return do_template('BLOCK_MAIN_IMAGE_FADER',array(
			'GALLERY_URL'=>$gallery_url,
			'RAND'=>uniqid(''),
			'PREVIOUS_URL'=>$images[count($images)-1],
			'PREVIOUS_URL_FULL'=>$images[count($images_full)-1],
			'FIRST_URL'=>$images[0],
			'FIRST_URL_FULL'=>$images_full[0],
			'NEXT_URL'=>isset($images[1])?$images[1]:'',
			'NEXT_URL_FULL'=>isset($images_full[1])?$images_full[1]:'',
			'IMAGES'=>$images,
			'IMAGES_FULL'=>$images_full,
			'MILL'=>strval($mill),
		));
	}

}
