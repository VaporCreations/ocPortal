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

class Hook_Profiles_Tabs_galleries
{

	/**
	 * Find whether this hook is active.
	 *
	 * @param  MEMBER			The ID of the member who is being viewed
	 * @param  MEMBER			The ID of the member who is doing the viewing
	 * @return boolean		Whether this hook is active
	 */
	function is_active($member_id_of,$member_id_viewing)
	{
		return has_specific_permission($member_id_of,'have_personal_category','cms_galleries') && !is_null($GLOBALS['SITE_DB']->query_value_null_ok('galleries','is_member_synched',array('is_member_synched'=>1)));
	}

	/**
	 * Standard modular render function for profile tab hooks.
	 *
	 * @param  MEMBER			The ID of the member who is being viewed
	 * @param  MEMBER			The ID of the member who is doing the viewing
	 * @return array			A triple: The tab title, the tab contents, the suggested tab order
	 */
	function render_tab($member_id_of,$member_id_viewing)
	{
		$title=do_lang_tempcode('GALLERIES');

		$order=30;

		$galleries=new ocp_tempcode();
		require_lang('galleries');
		require_code('galleries');
		$rows=$GLOBALS['SITE_DB']->query('SELECT * FROM '.get_table_prefix().'galleries WHERE name LIKE \''.db_encode_like('member\_'.strval($member_id_of).'\_%').'\'');
		foreach ($rows as $i=>$row)
		{
			$galleries->attach(do_template('GALLERY_SUBGALLERY_WRAP',array('CONTENT'=>show_gallery_box($row,'root',false,get_module_zone('galleries')))));
			$this->attach_gallery_subgalleries($row['name'],$galleries);
		}

		$content=do_template('OCF_MEMBER_PROFILE_GALLERIES',array('MEMBER_ID'=>strval($member_id_of),'GALLERIES'=>$galleries));

		return array($title,$content,$order);
	}
	
	/**
	 * Show subgalleries belonging to member.
	 *
	 * @param  ID_TEXT		Gallery name
	 * @param  tempcode		The output goes in here (passed by reference)
	 */
	function attach_gallery_subgalleries($gallery_name,&$galleries)
	{
		$rows=$GLOBALS['SITE_DB']->query_select('galleries',array('*'),array('parent_id'=>$gallery_name),'ORDER BY add_date DESC');
		foreach ($rows as $i=>$row)
		{
			$galleries->attach(do_template('GALLERY_SUBGALLERY_WRAP',array('CONTENT'=>show_gallery_box($row,'root',false,get_module_zone('galleries')))));
			$this->attach_gallery_subgalleries($row['name'],$galleries);
		}
	}

}


