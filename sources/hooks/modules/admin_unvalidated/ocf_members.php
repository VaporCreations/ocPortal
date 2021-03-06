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
 * @package		core_ocf
 */

class Hook_unvalidated_ocf_members
{

	/**
	 * Standard modular info function.
	 *
	 * @return ?array	Map of module info (NULL: module is disabled).
	 */
	function info()
	{
		if (get_forum_type()!='ocf') return NULL;
	
		require_lang('ocf');
	
		$info=array();
		$info['db_table']='f_members';
		$info['db_identifier']='id';
		$info['db_validated']='m_validated';
		$info['db_title']='m_username';
		$info['db_title_dereference']=false;
		$info['db_add_date']='m_join_time';
		$info['db_edit_date']='m_join_time';
		$info['edit_module']='members';
		$info['edit_type']='view';
		$info['edit_identifier']='id';
		$info['title']=do_lang_tempcode('MEMBERS');
		$info['db']=$GLOBALS['FORUM_DB'];
	
		return $info;
	}

}


