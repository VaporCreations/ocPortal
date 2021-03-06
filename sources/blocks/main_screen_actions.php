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
 * @package		recommend
 */

class Block_main_screen_actions
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
		$info['parameters']=array('title');
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
		//if (count($_POST)!=0) return new ocp_tempcode();		Can't do that, breaks previewing it

		require_lang('recommend');
		
		$_map=array('page'=>'recommend','from'=>get_self_url(true));
		if (array_key_exists('title',$map)) $_map['title']=$map['title'];
		$recommend_url=build_url($_map,'_SEARCH');
		
		return do_template('BLOCK_MAIN_SCREEN_ACTIONS',array(
			'PRINT_URL'=>get_self_url(true,false,array('wide_print'=>1,'max'=>1000)),
			'RECOMMEND_URL'=>$recommend_url,
			'EASY_SELF_URL'=>str_replace("'",'',urlencode(get_self_url(true))),
			'TITLE'=>array_key_exists('title',$map)?$map['title']:'',
		));
	}

}


