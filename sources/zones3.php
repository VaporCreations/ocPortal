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
 * @package		core
 */

/**
 * Erase the comcode page cache
 */
function erase_comcode_page_cache()
{
	$GLOBALS['NO_QUERY_LIMIT']=true;

	do
	{
		$rows=$GLOBALS['SITE_DB']->query_select('cached_comcode_pages',array('string_index'),NULL,'',50,NULL,true,array());
		if (is_null($rows)) $rows=array();
		foreach ($rows as $row)
		{
			delete_lang($row['string_index']);
			$GLOBALS['SITE_DB']->query_delete('cached_comcode_pages',array('string_index'=>$row['string_index']));
		}
	}
	while (count($rows)!=0);
	persistant_cache_empty();

	$GLOBALS['NO_QUERY_LIMIT']=false;
}

/**
 * Edit a zone.
 *
 * @param  ID_TEXT		The current name of the zone
 * @param  SHORT_TEXT	The zone title
 * @param  ID_TEXT		The zones default page
 * @param  SHORT_TEXT	The header text
 * @param  ID_TEXT		The theme
 * @param  BINARY			Whether the zone is wide
 * @param  BINARY			Whether the zone requires a session for pages to be used
 * @param  BINARY			Whether the zone in displayed in the menu coded into some themes
 * @param  ID_TEXT		The new name of the zone
 */
function actual_edit_zone($zone,$title,$default_page,$header_text,$theme,$wide,$require_session,$displayed_in_menu,$new_zone)
{
	if ($zone!=$new_zone)
	{
		require_code('type_validation');
		if (!is_alphanumeric($new_zone)) warn_exit(do_lang_tempcode('BAD_CODENAME'));

		if (get_file_base()!=get_custom_file_base()) warn_exit(do_lang_tempcode('SHARED_INSTALL_PROHIBIT'));

		// Check doesn't already exist
		$test=$GLOBALS['SITE_DB']->query_value_null_ok('zones','zone_header_text',array('zone_name'=>$new_zone));
		if (!is_null($test)) warn_exit(do_lang_tempcode('ALREADY_EXISTS',escape_html($new_zone)));

		require_code('abstract_file_manager');
		force_have_afm_details();
		afm_move($zone,$new_zone);
	}

	$_header_text=$GLOBALS['SITE_DB']->query_value('zones','zone_header_text',array('zone_name'=>$zone));
	$_title=$GLOBALS['SITE_DB']->query_value('zones','zone_title',array('zone_name'=>$zone));

	$GLOBALS['SITE_DB']->query_update('zones',array('zone_name'=>$new_zone,'zone_title'=>lang_remap($_title,$title),'zone_default_page'=>$default_page,'zone_header_text'=>lang_remap($_header_text,$header_text),'zone_theme'=>$theme,'zone_wide'=>$wide,'zone_require_session'=>$require_session,'zone_displayed_in_menu'=>$displayed_in_menu),array('zone_name'=>$zone),'',1);

	if ($new_zone!=$zone)
	{
		actual_rename_zone_lite($zone,$new_zone,true);

		$GLOBALS['SITE_DB']->query_update('menu_items',array('i_url'=>$new_zone),array('i_url'=>$zone),'',1);
	}

	// If we're in this zone, update the theme
	global $ZONE;
	if ($ZONE['zone_name']==$zone)
	{
		$ZONE['theme']=$theme;
	}

	decache('side_zone_jump');
	decache('side_stored_menu');
	decache('main_sitemap');
	persistant_cache_delete(array('ZONE',$zone));
	persistant_cache_delete('ALL_ZONES');

	log_it('EDIT_ZONE',$zone);
}

/**
 * Rename a zone in the database.
 *
 * @param  ID_TEXT		The old name of the zone
 * @param  ID_TEXT		The new name of the zone
 * @param  boolean		Whether to assume the main zone row has already been renamed as part of a wider editing operation
 */
function actual_rename_zone_lite($zone,$new_zone,$dont_bother_with_main_row=false)
{
	if (!$dont_bother_with_main_row)
	{
		$GLOBALS['SITE_DB']->query_update('zones',array('zone_name'=>$new_zone),array('zone_name'=>$zone),'',1);
		$GLOBALS['SITE_DB']->query_update('group_zone_access',array('zone_name'=>$new_zone),array('zone_name'=>$zone));
		$GLOBALS['SITE_DB']->query_update('member_zone_access',array('zone_name'=>$new_zone),array('zone_name'=>$zone));
	} else
	{
		$GLOBALS['SITE_DB']->query_delete('zones',array('zone_name'=>$zone),'',1);
		$GLOBALS['SITE_DB']->query_delete('group_zone_access',array('zone_name'=>$zone));
		$GLOBALS['SITE_DB']->query_delete('member_zone_access',array('zone_name'=>$zone));
	}
	$GLOBALS['SITE_DB']->query_update('group_page_access',array('zone_name'=>$new_zone),array('zone_name'=>$zone));
	$GLOBALS['SITE_DB']->query_update('member_page_access',array('zone_name'=>$new_zone),array('zone_name'=>$zone));
	$GLOBALS['SITE_DB']->query_update('comcode_pages',array('the_zone'=>$new_zone),array('the_zone'=>$zone),'',NULL,NULL,false,true); // May fail because the table might not exist when this is called
	if (addon_installed('redirects_editor'))
	{
		$GLOBALS['SITE_DB']->query_update('redirects',array('r_from_zone'=>$new_zone),array('r_from_zone'=>$zone));
		$GLOBALS['SITE_DB']->query_update('redirects',array('r_to_zone'=>$new_zone),array('r_to_zone'=>$zone));
	}

	// Copy logo theme images if needed
	require_code('themes2');
	$themes=find_all_themes();
	foreach (array_keys($themes) as $theme)
	{
		$zone_logo_img=find_theme_image('logo/'.$zone.'-logo',true,true,$theme);
		$zone_logo_img_new=find_theme_image('logo/'.$new_zone.'-logo',true,true,$theme);
		if (($zone_logo_img!='') && ($zone_logo_img_new==''))
		{
			$GLOBALS['SITE_DB']->query_delete('theme_images',array('id'=>'logo/'.$new_zone.'-logo','theme'=>$theme,'lang'=>get_site_default_lang()),'',1);
			$GLOBALS['SITE_DB']->query_insert('theme_images',array('id'=>'logo/'.$new_zone.'-logo','theme'=>$theme,'path'=>$zone_logo_img,'lang'=>get_site_default_lang()));
		}
	}

	global $ALL_ZONES,$ALL_ZONES_TITLED;
	$ALL_ZONES=NULL;
	$ALL_ZONES_TITLED=NULL;
}

/**
 * Delete a zone.
 *
 * @param  ID_TEXT		The name of the zone
 */
function actual_delete_zone($zone)
{
	if (get_file_base()!=get_custom_file_base()) warn_exit(do_lang_tempcode('SHARED_INSTALL_PROHIBIT'));

	require_code('abstract_file_manager');
	force_have_afm_details();
	
	if (function_exists('set_time_limit')) @set_time_limit(0);
	disable_php_memory_limit();

	$pages=find_all_pages_wrap($zone,false,false,FIND_ALL_PAGES__ALL);
	$bad=array();
	foreach (array_keys($pages) as $page)
	{
		if ((substr($page,0,6)!='panel_') && ($page!='start')) $bad[]=$page;
	}
	if ($bad!=array())
	{
		require_lang('zones');
		warn_exit(do_lang_tempcode('DELETE_ZONE_ERROR','<kbd>'.implode('</kbd>, <kbd>',$bad).'</kbd>'));
	}

	actual_delete_zone_lite($zone);

	if (file_exists(get_custom_file_base().'/'.filter_naughty($zone))) afm_delete_directory(filter_naughty($zone),true);
}

/**
 * Delete a zones database stuff.
 *
 * @param  ID_TEXT		The name of the zone
 */
function actual_delete_zone_lite($zone)
{
	$zone_header_text=$GLOBALS['SITE_DB']->query_value_null_ok('zones','zone_header_text',array('zone_name'=>$zone));
	if (is_null($zone_header_text)) return;
	$zone_title=$GLOBALS['SITE_DB']->query_value('zones','zone_title',array('zone_name'=>$zone));
	delete_lang($zone_header_text);
	delete_lang($zone_title);

	$GLOBALS['SITE_DB']->query_delete('zones',array('zone_name'=>$zone),'',1);
	$GLOBALS['SITE_DB']->query_delete('group_zone_access',array('zone_name'=>$zone));
	$GLOBALS['SITE_DB']->query_delete('group_page_access',array('zone_name'=>$zone));
	$GLOBALS['SITE_DB']->query_delete('comcode_pages',array('the_zone'=>$zone),'',NULL,NULL,true); // May fail because the table might not exist when this is called
	if (addon_installed('redirects_editor'))
	{
		$GLOBALS['SITE_DB']->query_delete('redirects',array('r_from_zone'=>$zone));
		$GLOBALS['SITE_DB']->query_delete('redirects',array('r_to_zone'=>$zone));
	}
	$GLOBALS['SITE_DB']->query_delete('menu_items',array('i_url'=>$zone.':'));

	log_it('DELETE_ZONE',$zone);
	decache('side_zone_jump');
	decache('side_stored_menu');
	decache('main_sitemap');
	persistant_cache_delete(array('ZONE',$zone));
	persistant_cache_delete('ALL_ZONES');

	global $ALL_ZONES,$ALL_ZONES_TITLED;
	$ALL_ZONES=NULL;
	$ALL_ZONES_TITLED=NULL;
}

/**
 * The do-next manager for after content management.
 *
 * @param  tempcode		The title (output of get_page_title)
 * @param  ?ID_TEXT		The name of the page just handled (NULL: none)
 * @param  ID_TEXT		The name of the zone just handled (blank: none/welcome-zone)
 * @param  tempcode		The text to show (blank: default)
 * @return tempcode		The UI
 */
function site_tree_do_next_manager($title,$page,$zone,$completion_text)
{
	if ($completion_text->is_empty()) $completion_text=do_lang_tempcode('SUCCESS');

	require_code('templates_donext');
	$special=array(
		/*	 type							  page	 params													 zone	  */
		array('pagewizard',array('admin_sitetree',array('type'=>'pagewizard','zone'=>$zone),get_module_zone('admin_sitetree')),do_lang_tempcode('PAGE_WIZARD')),
		array('comcode_page_edit',array('cms_comcode_pages',array('type'=>'misc'),get_module_zone('cms_comcode_pages')),do_lang_tempcode('COMCODE_PAGE_EDIT')),
	);
	if (addon_installed('redirects_editor'))
	{
		require_lang('redirects');
		$special[]=array('redirect',array('admin_redirects',array('type'=>'misc'),get_module_zone('admin_redirects')),do_lang_tempcode('REDIRECTS'));
	}
	if (!has_js())
	{
		$special=array_merge($special,array(
			array('delete',array('admin_sitetree',array('type'=>'delete'),get_module_zone('admin_sitetree')),do_lang_tempcode('DELETE_PAGES')),
			array('move',array('admin_sitetree',array('type'=>'move'),get_module_zone('admin_sitetree')),do_lang_tempcode('MOVE_PAGES')),
		));
	} else
	{
		$special=array_merge($special,array(
			array('sitetree',array('admin_sitetree',array('type'=>'site_tree'),get_module_zone('admin_sitetree')),do_lang_tempcode('SITE_TREE_EDITOR')),
		));
	}
	return do_next_manager($title,$completion_text,
					$special,
					do_lang('PAGES'),
					/*		TYPED-ORDERED LIST OF 'LINKS'		*/
					/*	 page	 params				  zone	  */
					NULL,								 // Add one
					is_null($page)?NULL:array('_SELF',array('type'=>'_ed','page_link'=>$zone.':'.$page),'_SELF'), // Edit this
					NULL,																						// Edit one
					is_null($page)?NULL:array($page,array(),$zone),		 // View this
					NULL,				// View archive
					NULL,						// Add to category
					NULL,							 // Add one category
					NULL,							 // Edit one category
					NULL,  // Edit this category
					NULL						// View this category
	);
}

/**
 * Get a list of zones.
 *
 * @param  ?ID_TEXT		The zone in the list to select by default (NULL: use first)
 * @param  ?array			A list of zone to not put into the list (NULL: none to skip)
 * @param  ?array			A reordering (NULL: no reordering)
 * @return tempcode		The list
 */
function nice_get_zones($sel=NULL,$no_go=NULL,$reorder=NULL)
{
	if (is_null($no_go)) $no_go=array();

	if (($sel==='site') && (get_option('collapse_user_zones')=='1')) $sel='';

	$zones=find_all_zones(false,true);
	$content=new ocp_tempcode();
	if (!is_null($reorder))
	{
		$_zones_a=array();
		$_zones_b=array();
		foreach ($zones as $_zone)
		{
			list($zone,$title,)=$_zone;
			if (in_array($zone,$reorder)) $_zones_a[]=$_zone; else $_zones_b[]=$_zone;
		}
		$zones=array_merge($_zones_a,$_zones_b);
	}
	foreach ($zones as $_zone)
	{
		list($zone,$title,)=$_zone;
		if ((has_zone_access(get_member(),$zone)) && (!in_array($zone,$no_go)))
		{
			$content->attach(form_input_list_entry($zone,((!is_null($sel)) && ($zone==$sel)),$title));
		}
	}
	return $content;
}

/**
 * Get a zone chooser interface.
 *
 * @param  boolean		Whether the zone chooser will be shown inline to something else (as opposed to providing it's own borderings)
 * @param  ?array			A list of zone to not put into the list (NULL: none to skip)
 * @param  ?array			A reordering (NULL: no reordering)
 * @return tempcode		The zone chooser
 */
function get_zone_chooser($inline=false,$no_go=NULL,$reorder=NULL)
{
	$content=nice_get_zones(get_zone_name(),$no_go,$reorder);

	$content=do_template('ZONE_CHOOSE'.($inline?'_INLINE':''),array('CONTENT'=>$content));
	return $content;
}

