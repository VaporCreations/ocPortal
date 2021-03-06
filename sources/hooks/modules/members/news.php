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
 * @package		news
 */

class Hook_members_news
{

	/**
	 * Standard modular run function.
	 *
	 * @param  MEMBER		The ID of the member we are getting link hooks for
	 * @return array		List of tuples for results. Each tuple is: type,title,url
	 */
	function run($member_id)
	{
		if (!addon_installed('news')) return array();
		
		$nc_id=$GLOBALS['SITE_DB']->query_value_null_ok('news_categories','id',array('nc_owner'=>$member_id));
		if (!is_null($nc_id))
		{
			require_lang('news');
			$modules=array();
			if (has_actual_page_access(get_member(),'news',get_page_zone('news')))
			{
				$modules[]=array('content',do_lang_tempcode('BLOG_ARCHIVE'),build_url(array('page'=>'news','type'=>'misc','id'=>$nc_id,'blog'=>1),get_module_zone('news')));
			}
			return $modules;
		}
		return array();
	}

}


