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
 * @package		catalogues
 */

/**
 * Converts a non-tree catalogue to a tree catalogue.
 *
 * @param  ID_TEXT	Catalogue name
 */
function catalogue_to_tree($catalogue_name)
{
	$new_root=actual_add_catalogue_category($catalogue_name,do_lang('ROOT'),'','',NULL,'');
	$GLOBALS['SITE_DB']->query('UPDATE '.get_table_prefix().'catalogue_categories SET cc_parent_id='.strval($new_root).' WHERE id<>'.strval($new_root).' AND '.db_string_equal_to('c_name',$catalogue_name));
	$GLOBALS['SITE_DB']->query_update('catalogues',array('c_is_tree'=>1),array('c_name'=>$catalogue_name),'',1);
}

/**
 * Converts a non-tree catalogue from a tree catalogue.
 *
 * @param  ID_TEXT	Catalogue name
 */
function catalogue_from_tree($catalogue_name)
{
	$GLOBALS['SITE_DB']->query_update('catalogue_categories',array('cc_parent_id'=>NULL),array('c_name'=>$catalogue_name));
	$GLOBALS['SITE_DB']->query_update('catalogues',array('c_is_tree'=>0),array('c_name'=>$catalogue_name),'',1);
}

/**
 * Farm out the files for catalogue entry fields.
 */
function catalogue_file_script()
{
	// Closed site
	$site_closed=get_option('site_closed');
	if (($site_closed=='1') && (!has_specific_permission(get_member(),'access_closed_site')) && (!$GLOBALS['IS_ACTUALLY_ADMIN']))
	{
		header('Content-Type: text/plain');
		@exit(get_option('closed'));
	}

	$file=filter_naughty(get_param('file',false,true));
	$_full=get_custom_file_base().'/uploads/catalogues/'.rawurldecode($file);
	if (!file_exists($_full)) warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
	$size=filesize($_full);

	$original_filename=filter_naughty(get_param('original_filename',false,true));

	// Send header
	if ((strpos($original_filename,chr(10))!==false) || (strpos($original_filename,chr(13))!==false))
		log_hack_attack_and_exit('HEADER_SPLIT_HACK');
	header('Content-Type: application/octet-stream'.'; authoritative=true;');
	if (get_option('immediate_downloads',true)==='1')
	{
		require_code('mime_types');
		header('Content-Type: '.get_mime_type(get_file_extension($original_filename)).'; authoritative=true;');
		header('Content-Disposition: filename="'.str_replace(chr(13),'',str_replace(chr(10),'',addslashes($original_filename))).'"');
	} else
	{
		if (strstr(ocp_srv('HTTP_USER_AGENT'),'MSIE')!==false)
			header('Content-Disposition: filename="'.str_replace(chr(13),'',str_replace(chr(10),'',addslashes($original_filename))).'"');
		else
			header('Content-Disposition: attachment; filename="'.str_replace(chr(13),'',str_replace(chr(10),'',addslashes($original_filename))).'"');
	}
	header('Accept-Ranges: bytes');
	
	// Default to no resume
	$from=0;
	$new_length=$size;

	@ini_set('zlib.output_compression','Off');

	// They're trying to resume (so update our range)
	$httprange=ocp_srv('HTTP_RANGE');
	if (strlen($httprange)>0)
	{
		$_range=explode('=',ocp_srv('HTTP_RANGE'));
		if (count($_range)==2)
		{
			if (strpos($_range[0],'-')===false) $_range=array_reverse($_range);
			$range=$_range[0];
			if (substr($range,0,1)=='-') $range=strval($size-intval(substr($range,1))-1).$range;
			if (substr($range,-1,1)=='-') $range.=strval($size-1);
			$bits=explode('-',$range);
			if (count($bits)==2)
			{
				list($from,$to)=array_map('intval',$bits);
				if (($to-$from!=0) || ($from==0)) // Workaround to weird behaviour on Chrome
				{
					$new_length=$to-$from+1;

					header('HTTP/1.1 206 Partial Content');
					header('Content-Range: bytes '.$range.'/'.strval($size));
				} else
				{
					$from=0;
				}
			}
		}
	}
	header('Content-Length: '.strval($new_length));
	if (function_exists('set_time_limit')) @set_time_limit(0);
	error_reporting(0);

	// Send actual data
	$myfile=fopen($_full,'rb');
	fseek($myfile,$from);
	/*if ($size==$new_length)		Uses a lot of memory :S
	{
		fpassthru($myfile);
	} else*/
	{
		$i=0;
		flush(); // Works around weird PHP bug that sends data before headers, on some PHP versions
		while ($i<$new_length)
		{
			$content=fread($myfile,min($new_length-$i,1048576));
			echo $content;
			$len=strlen($content);
			if ($len==0) break;
			$i+=$len;
		}
		fclose($myfile);
	}
}

/**
 * Add a catalogue using all the specified values.
 *
 * @param  ID_TEXT			The codename of the catalogue
 * @param  mixed				The title of the catalogue (either language code or string)
 * @param  mixed				A description (either language code or string)
 * @param  SHORT_INTEGER	The display type
 * @param  BINARY				Whether the catalogue uses a tree system (as opposed to mere categories in an index)
 * @param  LONG_TEXT			Hidden notes pertaining to this catalogue
 * @param  integer			How many points a member gets by submitting to this catalogue
 * @param  BINARY				Whether the catalogue is an eCommerce catalogue
 * @param  ID_TEXT			How to send view reports
 * @set    never daily weekly monthly quarterly
 * @return ?AUTO_LINK		The ID of the first new catalogues root category (NULL: no root, as it's not a tree catalogue)
 */
function actual_add_catalogue($name,$title,$description,$display_type,$is_tree,$notes,$submit_points,$ecommerce=0,$send_view_reports='never')
{
	require_code('type_validation');
	if (!is_alphanumeric($name)) warn_exit(do_lang_tempcode('BAD_CODENAME'));

	// Check doesn't already exist
	$test=$GLOBALS['SITE_DB']->query_value_null_ok('catalogues','c_name',array('c_name'=>$name));
	if (!is_null($test)) warn_exit(do_lang_tempcode('ALREADY_EXISTS',escape_html($name)));

	// Create
	if (!is_integer($description)) $description=insert_lang_comcode($description,2);
	if (!is_integer($title)) $title=insert_lang($title,1);
	$GLOBALS['SITE_DB']->query_insert('catalogues',array('c_name'=>$name,'c_title'=>$title,'c_send_view_reports'=>$send_view_reports,'c_ecommerce'=>$ecommerce,'c_description'=>$description,'c_display_type'=>$display_type,'c_is_tree'=>$is_tree,'c_notes'=>$notes,'c_add_date'=>time(),'c_submit_points'=>$submit_points));

	if ($is_tree==1)
	{
		// Create root node
		$root_title=($display_type==1)?do_lang('_HOME',get_translated_text($title)):get_translated_text($title);
		$category=$GLOBALS['SITE_DB']->query_insert('catalogue_categories',array('cc_move_days_lower'=>30,'cc_move_days_higher'=>60,'cc_move_target'=>NULL,'rep_image'=>'','c_name'=>$name,'cc_title'=>insert_lang($root_title,1),'cc_description'=>insert_lang_comcode('',3),'cc_notes'=>'','cc_add_date'=>time(),'cc_parent_id'=>NULL),true);
	} else $category=NULL;

	log_it('ADD_CATALOGUE',$name);
	
	return $category;
}

/**
 * Add a field to the specified catalogue, without disturbing any other data in that catalogue.
 *
 * @param  ID_TEXT		The codename of the catalogue the field is for
 * @param  mixed			The name of the field (either language code or string)
 * @param  mixed			A description (either language code or string)
 * @param  ID_TEXT		The type of the field
 * @param  integer		The field order (the field order determines what order the fields are displayed within an entry)
 * @param  BINARY			Whether this field defines the catalogue order
 * @param  BINARY			Whether this is a visible field
 * @param  BINARY			Whether the field is usable as a search key
 * @param  LONG_TEXT		The default value for the field
 * @param  BINARY			Whether this field is required
 * @param  BINARY			Whether the field is to be shown in category views (not applicable for the list display type)
 * @param  BINARY			Whether the field is to be shown in search views (not applicable for the list display type)
 * @param  ?AUTO_LINK	Force this ID (NULL: auto-increment as normal)
 * @return AUTO_LINK		Field ID
 */
function actual_add_catalogue_field($c_name,$name,$description,$type,$order,$defines_order,$visible,$searchable,$default,$required,$put_in_category=1,$put_in_search=1,$id=NULL)
{
	if (!is_integer($description)) $description=insert_lang($description,2);
	if (!is_integer($name)) $name=insert_lang($name,2);
	$map=array('c_name'=>$c_name,'cf_name'=>$name,'cf_description'=>$description,'cf_type'=>$type,'cf_order'=>$order,'cf_defines_order'=>$defines_order,'cf_visible'=>$visible,'cf_searchable'=>$searchable,'cf_default'=>$default,'cf_required'=>$required,'cf_put_in_category'=>$put_in_category,'cf_put_in_search'=>$put_in_search);
	if (!is_null($id)) $map['id']=$id;
	$cf_id=$GLOBALS['SITE_DB']->query_insert('catalogue_fields',$map,true);
	if (!is_null($id)) $cf_id=$id;

	require_code('fields');

	$ob=get_fields_hook($type);
	
	if (function_exists('set_time_limit')) @set_time_limit(0);

	// Now add field values for all pre-existing entries (in the ideal world, there would be none yet)
	$start=0;
	do
	{
		$entries=collapse_1d_complexity('id',$GLOBALS['SITE_DB']->query_select('catalogue_entries',array('id'),array('c_name'=>$c_name),'',300,$start));
		foreach ($entries as $entry)
		{
			$_default=mixed();
			
			list($raw_type,$_default,$_type)=$ob->get_field_value_row_bits($map+array('id'=>$cf_id),$required==1,$default);

			if (strpos($raw_type,'trans')!==false) $_default=intval($_default);

			if ($_type=='float')
			{
				$map=array('cf_id'=>$cf_id,'ce_id'=>$entry,'cv_value'=>((is_null($_default)) || ($_default==''))?NULL:floatval($_default));
			}
			elseif ($_type=='integer')
			{
				$map=array('cf_id'=>$cf_id,'ce_id'=>$entry,'cv_value'=>((is_null($_default)) || ($_default==''))?NULL:intval($_default));
			} else
			{
				$map=array('cf_id'=>$cf_id,'ce_id'=>$entry,'cv_value'=>$_default);
			}
			$GLOBALS['SITE_DB']->query_insert('catalogue_efv_'.$_type,$map);
		}
		
		$start+=300;
	}
	while (array_key_exists(0,$entries));
	
	return $cf_id;
}

/**
 * Edit a catalogue.
 *
 * @param  ID_TEXT			The current name of the catalogue
 * @param  ID_TEXT			The new name of the catalogue
 * @param  SHORT_TEXT		The human readable name/title of the catalogue
 * @param  LONG_TEXT			The description
 * @param  SHORT_INTEGER	The display type
 * @param  LONG_TEXT			Admin notes
 * @param  integer			How many points are given to a member that submits to the catalogue
 * @param  BINARY				Whether the catalogue is an eCommerce catalogue
 * @param  ID_TEXT			How to send view reports
 * @set    never daily weekly monthly quarterly
 */
function actual_edit_catalogue($old_name,$name,$title,$description,$display_type,$notes,$submit_points,$ecommerce,$send_view_reports)
{
	if ($old_name!=$name)
	{
		// Check doesn't already exist
		$test=$GLOBALS['SITE_DB']->query_value_null_ok('catalogues','c_name',array('c_name'=>$name));
		if (!is_null($test)) warn_exit(do_lang_tempcode('ALREADY_EXISTS',escape_html($name)));

		require_code('type_validation');
		if (!is_alphanumeric($name)) warn_exit(do_lang_tempcode('BAD_CODENAME'));
	}

	$rows=$GLOBALS['SITE_DB']->query_select('catalogues',array('c_description','c_title'),array('c_name'=>$old_name),'',1);
	if (!array_key_exists(0,$rows))
	{
		warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
	}
	$myrow=$rows[0];
	$_title=$myrow['c_title'];
	$_description=$myrow['c_description'];

	// Edit
	$GLOBALS['SITE_DB']->query_update('catalogues',array('c_send_view_reports'=>$send_view_reports,'c_display_type'=>$display_type,'c_ecommerce'=>$ecommerce,'c_name'=>$name,'c_title'=>lang_remap($_title,$title),'c_description'=>lang_remap_comcode($_description,$description),'c_notes'=>$notes,'c_add_date'=>time(),'c_submit_points'=>$submit_points),array('c_name'=>$old_name),'',1);

	// If we're renaming, then we better change a load of references
	if ($name!=$old_name)
	{
		$GLOBALS['SITE_DB']->query_update('catalogue_categories',array('c_name'=>$name),array('c_name'=>$old_name));
		$GLOBALS['SITE_DB']->query_update('catalogue_fields',array('c_name'=>$name),array('c_name'=>$old_name));
		$GLOBALS['SITE_DB']->query_update('catalogue_entries',array('c_name'=>$name),array('c_name'=>$old_name));

		$types=$GLOBALS['SITE_DB']->query_select('award_types',array('id'),array('a_content_type'=>'catalogue'));
		foreach ($types as $type)
		{
			$GLOBALS['SITE_DB']->query_update('award_archive',array('content_id'=>$name),array('content_id'=>$old_name,'a_type_id'=>$type['id']));
		}
	}

	decache('main_cc_embed');
	decache('main_recent_cc_entries');

	log_it('EDIT_CATALOGUE',$name);
}

/**
 * Delete a catalogue.
 *
 * @param  ID_TEXT		The name of the catalogue
 */
function actual_delete_catalogue($name)
{
	// Delete lang
	$rows=$GLOBALS['SITE_DB']->query_select('catalogues',array('c_description','c_title'),array('c_name'=>$name),'',1);
	if (!array_key_exists(0,$rows))
	{
		warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
	}
	$myrow=$rows[0];

	// Delete anything involved (ha ha destruction!)
	if (function_exists('set_time_limit')) @set_time_limit(0);
	$start=0;
	do
	{
		$entries=collapse_1d_complexity('id',$GLOBALS['SITE_DB']->query_select('catalogue_entries',array('id'),array('c_name'=>$name),'',500,$start));
		foreach ($entries as $entry)
		{
			actual_delete_catalogue_entry($entry);
		}
		$start+=500;
	}
	while (count($entries)==500);
	$start=0;
	do
	{
		$categories=collapse_1d_complexity('id',$GLOBALS['SITE_DB']->query_select('catalogue_categories',array('id'),array('c_name'=>$name),'',30,$start));
		foreach ($categories as $category)
		{
			actual_delete_catalogue_category($category,true);
		}
		$start+=30;
	}
	while (array_key_exists(0,$categories));
	$fields=collapse_1d_complexity('id',$GLOBALS['SITE_DB']->query_select('catalogue_fields',array('id'),array('c_name'=>$name)));
	foreach ($fields as $field)
	{
		actual_delete_catalogue_field($field);
	}
	$GLOBALS['SITE_DB']->query_delete('catalogues',array('c_name'=>$name),'',1);
	delete_lang($myrow['c_title']);
	delete_lang($myrow['c_description']);
	$GLOBALS['SITE_DB']->query_delete('group_category_access',array('module_the_name'=>'catalogues_catalogue','category_name'=>$name));
	$GLOBALS['SITE_DB']->query_delete('gsp',array('module_the_name'=>'catalogues_catalogue','category_name'=>$name));

	log_it('DELETE_CATALOGUE',$name);
}

/**
 * Edit a catalogue field.
 *
 * @param  AUTO_LINK		The ID of the field
 * @param  ID_TEXT		The name of the catalogue
 * @param  SHORT_TEXT	The name of the field
 * @param  LONG_TEXT		Description for the field
 * @param  integer		The field order (the field order determines what order the fields are displayed within an entry)
 * @param  BINARY			Whether the field defines entry ordering
 * @param  BINARY			Whether the field is visible when an entry is viewed
 * @param  BINARY			Whether the field is usable as a search key
 * @param  LONG_TEXT		The default value for the field
 * @param  BINARY			Whether the field is required
 * @param  BINARY			Whether the field is to be shown in category views (not applicable for the list display type)
 * @param  BINARY			Whether the field is to be shown in search views (not applicable for the list display type)
 * @param  ?ID_TEXT		The field type (NULL: do not change)
 */
function actual_edit_catalogue_field($id,$c_name,$name,$description,$order,$defines_order,$visible,$searchable,$default,$required,$put_in_category=1,$put_in_search=1,$type=NULL) // You cannot edit a field type
{
	$rows=$GLOBALS['SITE_DB']->query_select('catalogue_fields',array('cf_description','cf_name'),array('id'=>$id));
	if (!array_key_exists(0,$rows))
	{
		warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
	}
	$myrow=$rows[0];
	$_name=$myrow['cf_name'];
	$_description=$myrow['cf_description'];

	$map=array('c_name'=>$c_name,'cf_name'=>lang_remap($_name,$name),'cf_description'=>lang_remap($_description,$description),'cf_order'=>$order,'cf_defines_order'=>$defines_order,'cf_visible'=>$visible,'cf_searchable'=>$searchable,'cf_default'=>$default,'cf_required'=>$required,'cf_put_in_category'=>$put_in_category,'cf_put_in_search'=>$put_in_search);
	if (!is_null($type)) $map['cf_type']=$type;

	$GLOBALS['SITE_DB']->query_update('catalogue_fields',$map,array('id'=>$id),'',1);
}

/**
 * Delete a catalogue field.
 *
 * @param  AUTO_LINK		The ID of the field
 */
function actual_delete_catalogue_field($id)
{
	$rows=$GLOBALS['SITE_DB']->query_select('catalogue_fields',array('cf_name','cf_description','cf_type'),array('id'=>$id));
	if (!array_key_exists(0,$rows))
	{
		warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
	}
	$myrow=$rows[0];
	delete_lang($myrow['cf_name']);
	delete_lang($myrow['cf_description']);

	$GLOBALS['SITE_DB']->query_delete('catalogue_fields',array('id'=>$id),'',1);
}

/**
 * Add a catalogue category
 *
 * @param  ID_TEXT		The codename of the catalogue the category is in
 * @param  mixed			The title of this category (either language code or string)
 * @param  mixed			A description (either language code or string)
 * @param  LONG_TEXT		Hidden notes pertaining to this category
 * @param  ?AUTO_LINK	The ID of this categories parent (NULL: a root category, or not a tree catalogue)
 * @param  URLPATH		The representative image for the category (blank: none)
 * @param  integer		The number of days before expiry (lower limit)
 * @param  integer		The number of days before expiry (higher limit)
 * @param  ?AUTO_LINK	The expiry category (NULL: do not expire)
 * @param  ?TIME			The add time (NULL: now)
 * @param  ?AUTO_LINK	Force an ID (NULL: don't force an ID)
 * @return AUTO_LINK		The ID of the new category
 */
function actual_add_catalogue_category($catalogue_name,$title,$description,$notes,$parent_id,$rep_image='',$move_days_lower=30,$move_days_higher=60,$move_target=NULL,$add_date=NULL,$id=NULL)
{
	if (is_null($add_date)) $add_date=time();
	if (!is_integer($description)) $description=insert_lang_comcode($description,3);
	if (!is_integer($title)) $title=insert_lang($title,2);
	$map=array('cc_move_days_lower'=>$move_days_lower,'cc_move_days_higher'=>$move_days_higher,'cc_move_target'=>$move_target,'rep_image'=>$rep_image,'cc_add_date'=>$add_date,'c_name'=>$catalogue_name,'cc_title'=>$title,'cc_description'=>$description,'cc_notes'=>$notes,'cc_parent_id'=>$parent_id);
	if (!is_null($id)) $map['id']=$id;
	$id=$GLOBALS['SITE_DB']->query_insert('catalogue_categories',$map,true);

	calculate_category_child_count_cache($parent_id);

	log_it('ADD_CATALOGUE_CATEGORY',strval($id),$title);

	require_code('seo2');
	if (!is_numeric($title)) seo_meta_set_for_implicit('catalogue_category',strval($id),array($title,$description),$title);

	store_in_catalogue_cat_treecache($id,$parent_id);

	return $id;
}

/**
 * Re-build the efficient catalogue category tree structure ancestry cache.
 */
function rebuild_catalogue_cat_treecache()
{
	if (function_exists('set_time_limit')) @set_time_limit(0);

	$GLOBALS['SITE_DB']->query_delete('catalogue_cat_treecache');
	$GLOBALS['SITE_DB']->query_delete('catalogue_childcountcache');

	$GLOBALS['NO_QUERY_LIMIT']=true;

	$max=1000;
	$start=0;
	do
	{
		$rows=$GLOBALS['SITE_DB']->query_select('catalogue_categories',array('id','cc_parent_id'),NULL,'',$max,$start);

		foreach ($rows as $row)
		{
			store_in_catalogue_cat_treecache($row['id'],$row['cc_parent_id'],false);
			calculate_category_child_count_cache($row['id'],false);
		}

		$start+=$max;
	}
	while (count($rows)!=0);
}

/**
 * Update the treecache for a catalogue category node.
 *
 * @param  AUTO_LINK		The ID of the category
 * @param  ?AUTO_LINK	The ID of the parent category (NULL: no parent)
 * @param  boolean		Whether to delete any possible pre-existing records for the category first
 */
function store_in_catalogue_cat_treecache($id,$parent_id,$cleanup_first=true)
{
	if ($cleanup_first)
	{
		$GLOBALS['SITE_DB']->query_delete('catalogue_cat_treecache',array('cc_id'=>$id));
	}
	
	// Self reference
	$GLOBALS['SITE_DB']->query_insert('catalogue_cat_treecache',array(
		'cc_id'=>$id,
		'cc_ancestor_id'=>$id,
	));

	// Stored recursed referenced towards root
	while (!is_null($parent_id))
	{
		$GLOBALS['SITE_DB']->query_insert('catalogue_cat_treecache',array(
			'cc_id'=>$id,
			'cc_ancestor_id'=>$parent_id,
		));
		$parent_id=$GLOBALS['SITE_DB']->query_value_null_ok('catalogue_categories','cc_parent_id',array('id'=>$parent_id));
	}
}

/**
 * Update cache for a categories child counts.
 *
 * @param  ?AUTO_LINK	The ID of the category (NULL: skip, called by some code that didn't realise it didn't impact a tree parent)
 * @param  boolean		Whether to recurse up the tree to force recalculations on other categories (recommended, unless you are doing a complete rebuild)
 */
function calculate_category_child_count_cache($cat_id,$recursive_updates=true)
{
	if (is_null($cat_id)) return;
	
	$GLOBALS['SITE_DB']->query_delete('catalogue_childcountcache',array(
		'cc_id'=>$cat_id,
	),'',1);

	$catalogue_name=$GLOBALS['SITE_DB']->query_value_null_ok('catalogue_categories','c_name',array('id'=>$cat_id));

	$num_rec_children=$GLOBALS['SITE_DB']->query_value('catalogue_cat_treecache','COUNT(*)',array('cc_ancestor_id'=>$cat_id))-1;
	$num_rec_entries=$GLOBALS['SITE_DB']->query_value('catalogue_cat_treecache t JOIN '.get_table_prefix().'catalogue_entries e ON e.cc_id=t.cc_id','COUNT(*)',array('t.cc_ancestor_id'=>$cat_id,'c_name'=>$catalogue_name/*important, else custom field cats could be included*/));

	$GLOBALS['SITE_DB']->query_insert('catalogue_childcountcache',array(
		'cc_id'=>$cat_id,
		'c_num_rec_children'=>$num_rec_children,
		'c_num_rec_entries'=>$num_rec_entries,
	));

	if ($recursive_updates)
	{
		$parent_id=$GLOBALS['SITE_DB']->query_value_null_ok('catalogue_categories','cc_parent_id',array('id'=>$cat_id));
		if (!is_null($parent_id))
			calculate_category_child_count_cache($parent_id);
	}
}

/**
 * Edit a catalogue category.
 *
 * @param  AUTO_LINK		The ID of the category
 * @param  SHORT_TEXT	The title of the category
 * @param  LONG_TEXT		Description for the category
 * @param  LONG_TEXT		Admin notes
 * @param  ?AUTO_LINK	The ID of the parent category (NULL: no parent)
 * @param  SHORT_TEXT	Meta keywords for the category
 * @param  LONG_TEXT		Meta description for the category
 * @param  URLPATH		The representative image for the category (blank: none)
 * @param  integer		The number of days before expiry (lower limit)
 * @param  integer		The number of days before expiry (higher limit)
 * @param  ?AUTO_LINK	The expiry category (NULL: do not expire)
 */
function actual_edit_catalogue_category($id,$title,$description,$notes,$parent_id,$meta_keywords,$meta_description,$rep_image,$move_days_lower,$move_days_higher,$move_target)
{
	$under_category_id=$parent_id;
	while ((!is_null($under_category_id)) && ($under_category_id!=INTEGER_MAGIC_NULL))
	{
		if ($id==$under_category_id) warn_exit(do_lang_tempcode('OWN_PARENT_ERROR'));
		$under_category_id=$GLOBALS['SITE_DB']->query_value('catalogue_categories','cc_parent_id',array('id'=>$under_category_id));
	}

	$rows=$GLOBALS['SITE_DB']->query_select('catalogue_categories',array('cc_description','cc_title'),array('id'=>$id),'',1);
	if (!array_key_exists(0,$rows))
	{
		warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
	}
	$myrow=$rows[0];
	$_title=$myrow['cc_title'];
	$_description=$myrow['cc_description'];
	
	store_in_catalogue_cat_treecache($id,$parent_id);

	$map=array('cc_move_days_lower'=>$move_days_lower,'cc_move_days_higher'=>$move_days_higher,'cc_move_target'=>$move_target,'cc_title'=>lang_remap($_title,$title),'cc_description'=>lang_remap_comcode($_description,$description),'cc_notes'=>$notes,'cc_parent_id'=>$parent_id);

	if (!is_null($rep_image))
	{
		$map['rep_image']=$rep_image;
		require_code('files2');
		delete_upload('uploads/grepimages','catalogue_categories','rep_image','id',$id,$rep_image);
	}

	$old_parent_id=$GLOBALS['SITE_DB']->query_value('catalogue_categories','cc_parent_id',array('id'=>$id));

	$GLOBALS['SITE_DB']->query_update('catalogue_categories',$map,array('id'=>$id),'',1);

	require_code('urls2');
	suggest_new_idmoniker_for('catalogues','category',strval($id),$title);

	require_code('seo2');
	seo_meta_set_for_explicit('catalogue_category',strval($id),$meta_keywords,$meta_description);
	
	if ($old_parent_id!==$parent_id)
	{
		calculate_category_child_count_cache($old_parent_id);
		calculate_category_child_count_cache($parent_id);
	}

	log_it('EDIT_CATALOGUE_CATEGORY',strval($id),$title);
}

/**
 * Delete a catalogue category.
 *
 * @param  AUTO_LINK		The ID of the category
 * @param  boolean		Whether we're deleting everything under the category; if FALSE we will actively reassign child categories to be directly under the root
 */
function actual_delete_catalogue_category($id,$deleting_all=false)
{
	// Info about our category
	$rows=$GLOBALS['SITE_DB']->query_select('catalogue_categories c LEFT JOIN '.$GLOBALS['SITE_DB']->get_table_prefix().'catalogues x ON c.c_name=x.c_name',array('c_is_tree','c.c_name','cc_description','cc_title','cc_parent_id'),array('id'=>$id),'',1);
	if (!array_key_exists(0,$rows))
	{
		warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
	}
	$myrow=$rows[0];

	// If we aren't deleting the entire catalogue, make sure we don't delete the root category
	if ((!$deleting_all) && ($myrow['c_is_tree']==1))
	{
		$root_category=$GLOBALS['SITE_DB']->query_value('catalogue_categories','MIN(id)',array('c_name'=>$myrow['c_name'],'cc_parent_id'=>NULL));
		if ($id==$root_category) warn_exit(do_lang_tempcode('CATALOGUE_NO_DELETE_ROOT'));
	}

	$GLOBALS['SITE_DB']->query_delete('catalogue_cat_treecache',array('cc_id'=>$id));
	$GLOBALS['SITE_DB']->query_delete('catalogue_childcountcache',array('cc_id'=>$id));

	require_code('files2');
	delete_upload('uploads/grepimages','catalogue_categories','rep_image','id',$id);

	if (!$deleting_all) // If not deleting the whole catalogue
	{
		if (function_exists('set_time_limit')) @set_time_limit(0);

		// If we're in a tree
		if ($myrow['c_is_tree']==1)
		{
			$GLOBALS['SITE_DB']->query_update('catalogue_categories',array('cc_parent_id'=>$myrow['cc_parent_id']),array('cc_parent_id'=>$id));
			$GLOBALS['SITE_DB']->query_update('catalogue_entries',array('cc_id'=>$myrow['cc_parent_id']),array('cc_id'=>$id));
		} else // If we're not in a tree catalogue we can't move them, we have to delete
		{
			if (function_exists('set_time_limit')) @set_time_limit(0);
			
			$GLOBALS['SITE_DB']->query_delete('catalogue_categories',array('cc_parent_id'=>$id)); // Does nothing, in theory
			$start=0;
			do
			{
				$entries=$GLOBALS['SITE_DB']->query_select('catalogue_entries',array('id'),array('cc_id'=>$id),'',500,$start);
				foreach ($entries as $entry)
				{
					actual_delete_catalogue_entry($entry['id']);
				}
				$start+=500;
			}
			while (count($entries)==500);
		}

		$GLOBALS['SITE_DB']->query_update('catalogue_categories',array('cc_move_target'=>NULL),array('cc_move_target'=>$id));
	}

	require_code('seo2');
	seo_meta_erase_storage('catalogue_category',strval($id));

	log_it('DELETE_CATALOGUE_CATEGORY',strval($id),get_translated_text($myrow['cc_title']));

	// Delete lang
	delete_lang($myrow['cc_title']);
	delete_lang($myrow['cc_description']);

	/*$entries=collapse_1d_complexity('id',$GLOBALS['SITE_DB']->query_select('catalogue_entries',array('id'),array('cc_id'=>$id)));
	foreach ($entries as $entry)
	{
		actual_delete_catalogue_entry($entry);
	}*/

	$old_parent_id=$GLOBALS['SITE_DB']->query_value('catalogue_categories','cc_parent_id',array('id'=>$id));

	$GLOBALS['SITE_DB']->query_delete('catalogue_categories',array('id'=>$id),'',1);

	$GLOBALS['SITE_DB']->query_delete('group_category_access',array('module_the_name'=>'catalogues_category','category_name'=>strval($id)));
	$GLOBALS['SITE_DB']->query_delete('gsp',array('module_the_name'=>'catalogues_category','category_name'=>strval($id)));

	calculate_category_child_count_cache($old_parent_id);
}

/**
 * Adds an entry to the specified catalogue.
 *
 * @param  AUTO_LINK			The ID of the category that the entry is in
 * @param  BINARY				Whether the entry has been validated
 * @param  LONG_TEXT			Hidden notes pertaining to the entry
 * @param  BINARY				Whether the entry may be rated
 * @param  SHORT_INTEGER	Whether comments are allowed (0=no, 1=yes, 2=review style)
 * @param  BINARY				Whether the entry may be trackbacked
 * @param  array				A map of field IDs, to values, that defines the entries settings
 * @param  ?TIME				The time the entry was added (NULL: now)
 * @param  ?MEMBER			The entries submitter (NULL: current user)
 * @param  ?TIME				The edit time (NULL: never)
 * @param  integer			The number of views
 * @param  ?AUTO_LINK		Force an ID (NULL: don't force an ID)
 * @return AUTO_LINK			The ID of the newly added entry
 */
function actual_add_catalogue_entry($category_id,$validated,$notes,$allow_rating,$allow_comments,$allow_trackbacks,$map,$time=NULL,$submitter=NULL,$edit_date=NULL,$views=0,$id=NULL)
{
	if (is_null($time)) $time=time();
	if (is_null($submitter)) $submitter=get_member();

	$catalogue_name=$GLOBALS['SITE_DB']->query_value('catalogue_categories','c_name',array('id'=>$category_id));
	$fields=collapse_2d_complexity('id','cf_type',$GLOBALS['SITE_DB']->query_select('catalogue_fields',array('id','cf_type'),array('c_name'=>$catalogue_name)));

	require_code('comcode_check');

	if (!addon_installed('unvalidated')) $validated=1;
	$imap=array('c_name'=>$catalogue_name,'ce_edit_date'=>$edit_date,'cc_id'=>$category_id,'ce_last_moved'=>time(),'ce_submitter'=>$submitter,'ce_add_date'=>$time,'ce_views'=>$views,'ce_views_prior'=>$views,'ce_validated'=>$validated,'notes'=>$notes,'allow_rating'=>$allow_rating,'allow_comments'=>$allow_comments,'allow_trackbacks'=>$allow_trackbacks);
	if (!is_null($id)) $imap['id']=$id;
	$val=mixed();
	foreach ($map as $field_id=>$val)
	{
		$type=$fields[$field_id];

		$ob=get_fields_hook($type);
		list($raw_type)=$ob->get_field_value_row_bits($fields[$field_id]);

		if (strpos($raw_type,'_trans')!==false)
			check_comcode($val);
	}
	$id=$GLOBALS['SITE_DB']->query_insert('catalogue_entries',$imap,true);
	require_code('fields');
	$title=NULL;
	foreach ($map as $field_id=>$val)
	{
		if ($val==STRING_MAGIC_NULL) $val='';

		if (is_null($title)) $title=$val;

		$type=$fields[$field_id];

		$ob=get_fields_hook($type);
		list($raw_type,,$sup_table_name)=$ob->get_field_value_row_bits($fields[$field_id]);

		if (strpos($raw_type,'_trans')!==false)
		{
			if ($type=='posting_field')
			{
				require_code('attachments2');
				$val=insert_lang_comcode_attachments(3,$val,'catalogue_entry',strval($id));
			} else
			{
				$val=insert_lang_comcode($val,3);
			}
		}

		if ($sup_table_name=='float')
		{
			$map=array('cf_id'=>$field_id,'ce_id'=>$id,'cv_value'=>((is_null($val)) || ($val==''))?NULL:floatval($val));
		}
		elseif ($sup_table_name=='integer')
		{
			$map=array('cf_id'=>$field_id,'ce_id'=>$id,'cv_value'=>((is_null($val)) || ($val==''))?NULL:intval($val));
		} else
		{
			$map=array('cf_id'=>$field_id,'ce_id'=>$id,'cv_value'=>$val);
		}
		$GLOBALS['SITE_DB']->query_insert('catalogue_efv_'.$sup_table_name,$map);
	}

	require_code('seo2');
	seo_meta_set_for_implicit('catalogue_entry',strval($id),$map,'');

	calculate_category_child_count_cache($category_id);

	if ($catalogue_name[0]!='_')
	{
		log_it('ADD_CATALOGUE_ENTRY',strval($id),$title);
	}

	decache('main_cc_embed');
	decache('main_recent_cc_entries');

	return $id;
}

/**
 * Edit the specified catalogue entry
 *
 * @param  AUTO_LINK			The ID of the entry being edited
 * @param  AUTO_LINK			The ID of the category that the entry is in
 * @param  BINARY				Whether the entry has been validated
 * @param  LONG_TEXT			Hidden notes pertaining to the entry
 * @param  BINARY				Whether the entry may be rated
 * @param  SHORT_INTEGER	Whether comments are allowed (0=no, 1=yes, 2=review style)
 * @param  BINARY				Whether the entry may be trackbacked
 * @param  array				A map of field IDs, to values, that defines the entries settings
 * @param  ?SHORT_TEXT		Meta keywords for this resource (NULL: do not edit)
 * @param  ?LONG_TEXT		Meta description for this resource (NULL: do not edit)
 */
function actual_edit_catalogue_entry($id,$category_id,$validated,$notes,$allow_rating,$allow_comments,$allow_trackbacks,$map,$meta_keywords='',$meta_description='')
{
	$catalogue_name=$GLOBALS['SITE_DB']->query_value('catalogue_categories','c_name',array('id'=>$category_id));
	$fields=collapse_2d_complexity('id','cf_type',$GLOBALS['SITE_DB']->query_select('catalogue_fields',array('id','cf_type'),array('c_name'=>$catalogue_name)));

	$original_submitter=$GLOBALS['SITE_DB']->query_value('catalogue_entries','ce_submitter',array('id'=>$id));

	$old_category_id=$GLOBALS['SITE_DB']->query_value('catalogue_entries','cc_id',array('id'=>$id));

	if (!addon_installed('unvalidated')) $validated=1;
	$GLOBALS['SITE_DB']->query_update('catalogue_entries',array('ce_edit_date'=>time(),'cc_id'=>$category_id,'ce_validated'=>$validated,'notes'=>$notes,'allow_rating'=>$allow_rating,'allow_comments'=>$allow_comments,'allow_trackbacks'=>$allow_trackbacks),array('id'=>$id),'',1);
	require_code('fields');
	$title=NULL;
	foreach ($map as $field_id=>$val)
	{
		if (is_null($title)) $title=$val;
		
		$type=$fields[$field_id];

		$ob=get_fields_hook($type);
		list(,,$sup_table_name)=$ob->get_field_value_row_bits($fields[$field_id]);

		if (substr($sup_table_name,-6)=='_trans')
		{
			$_val=$GLOBALS['SITE_DB']->query_value_null_ok('catalogue_efv_'.$sup_table_name,'cv_value',array('cf_id'=>$field_id,'ce_id'=>$id));
			if (is_null($_val))
			{
				$_val=insert_lang_comcode($val,3);
			} else
			{
				if ($type=='posting_field')
				{
					require_code('attachments2');
					require_code('attachments3');
					$_val=update_lang_comcode_attachments($_val,$val,'catalogue_entry',strval($id),NULL,false,$original_submitter);
				} else
				{
					$_val=lang_remap_comcode($_val,$val);
				}
			}

			$GLOBALS['SITE_DB']->query_update('catalogue_efv_'.$sup_table_name,array('cv_value'=>$_val),array('cf_id'=>$field_id,'ce_id'=>$id),'',1);
		} else
		{
			if ($sup_table_name=='float')
			{
				$map=array('cv_value'=>((is_null($val)) || ($val==''))?NULL:floatval($val));
			}
			elseif ($sup_table_name=='integer')
			{
				$map=array('cv_value'=>((is_null($val)) || ($val==''))?NULL:intval($val));
			} else
			{
				$map=array('cv_value'=>$val);
			}
			$GLOBALS['SITE_DB']->query_update('catalogue_efv_'.$sup_table_name,$map,array('cf_id'=>$field_id,'ce_id'=>$id),'',1);
		}
	}

	require_code('urls2');
	suggest_new_idmoniker_for('catalogues','entry',strval($id),strip_comcode($title));

	require_code('seo2');
	seo_meta_set_for_explicit('catalogue_entry',strval($id),$meta_keywords,$meta_description);

	if ($category_id!=$old_category_id)
	{
		calculate_category_child_count_cache($category_id);
		calculate_category_child_count_cache($old_category_id);
	}

	decache('main_cc_embed');
	decache('main_recent_cc_entries');

	if ($catalogue_name[0]!='_')
		log_it('EDIT_CATALOGUE_ENTRY',strval($id),$title);

	update_spacer_post($allow_comments!=0,'catalogues',strval($id),build_url(array('page'=>'catalogues','type'=>'entry','id'=>$id),get_module_zone('catalogues'),NULL,false,false,true),$title,get_value('comment_forum__catalogues__'.$catalogue_name));
}

/**
 * Delete a catalogue entry.
 *
 * @param  AUTO_LINK		The ID of the entry to delete
 */
function actual_delete_catalogue_entry($id)
{
	$old_category_id=$GLOBALS['SITE_DB']->query_value('catalogue_entries','cc_id',array('id'=>$id));

	$catalogue_name=$GLOBALS['SITE_DB']->query_value('catalogue_entries','c_name',array('id'=>$id));

	require_code('fields');
	require_code('catalogues');
	$fields=$GLOBALS['SITE_DB']->query_select('catalogue_fields',array('*'),array('c_name'=>$catalogue_name));
	$title=NULL;
	foreach ($fields as $field)
	{
		$object=get_fields_hook($field['cf_type']);
		list(,,$storage_type)=$object->get_field_value_row_bits($field);
		$value=_get_catalogue_entry_field($field['id'],$id,$storage_type);
		if (method_exists($object,'cleanup'))
		{
			$object->cleanup($value);
		}
		if (is_null($title))
		{
			if (($storage_type=='long_trans') || ($storage_type=='short_trans'))
			{
				$title=get_translated_text(intval($value));
			} else
			{
				$title=$value;
			}
		}
	}

	$lang1=$GLOBALS['SITE_DB']->query_select('catalogue_efv_long_trans',array('cv_value'),array('ce_id'=>$id));
	$lang2=$GLOBALS['SITE_DB']->query_select('catalogue_efv_short_trans',array('cv_value'),array('ce_id'=>$id));
	$lang=array_merge($lang1,$lang2);
	foreach ($lang as $lang_to_delete)
	{
		if (true) // Always do this just in case it is for attachments
		{
			require_code('attachments2');
			require_code('attachments3');
			delete_lang_comcode_attachments($lang_to_delete['cv_value'],'catalogue_entry',strval($id));
		} else
		{
			delete_lang($lang_to_delete['cv_value']);
		}
	}

	$GLOBALS['SITE_DB']->query_delete('catalogue_efv_long_trans',array('ce_id'=>$id));
	$GLOBALS['SITE_DB']->query_delete('catalogue_efv_short_trans',array('ce_id'=>$id));
	$GLOBALS['SITE_DB']->query_delete('catalogue_efv_long',array('ce_id'=>$id));
	$GLOBALS['SITE_DB']->query_delete('catalogue_efv_short',array('ce_id'=>$id));
	$GLOBALS['SITE_DB']->query_delete('catalogue_efv_float',array('ce_id'=>$id));
	$GLOBALS['SITE_DB']->query_delete('catalogue_efv_integer',array('ce_id'=>$id));

	$GLOBALS['SITE_DB']->query_delete('catalogue_entries',array('id'=>$id),'',1);
	$GLOBALS['SITE_DB']->query_delete('trackbacks',array('trackback_for_type'=>'catalogues','trackback_for_id'=>$id));
	$GLOBALS['SITE_DB']->query_delete('rating',array('rating_for_type'=>'catalogues','rating_for_id'=>$id));

	require_code('seo2');
	seo_meta_erase_storage('catalogue_entry',strval($id));

	calculate_category_child_count_cache($old_category_id);

	decache('main_recent_cc_entries');
	decache('main_cc_embed');

	if ($catalogue_name[0]!='_')
		log_it('DELETE_CATALOGUE_ENTRY',strval($id),$title);
}


