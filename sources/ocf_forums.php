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

/**
 * Standard code module initialisation function.
 */
function init__ocf_forums()
{
	global $USER_ACCESS_CACHE;
	$USER_ACCESS_CACHE=array();

	global $CATEGORY_TITLES;
	$CATEGORY_TITLES=NULL;
	
	global $FORUM_TREE_SECURE_CACHE;
	$FORUM_TREE_SECURE_CACHE=mixed();
	
	global $ALL_FORUMS_STRUCT;
	$ALL_FORUMS_STRUCT=NULL;
}

/**
 * Organise a list of forum rows into a tree structure.
 *
 * @param  array			The list of all forum rows (be aware that this will get modified for performance reasons).
 * @param  AUTO_LINK		The forum row that we are taking as the root of our current recursion.
 * @return array			The child list of $forum_id.
 */
function ocf_organise_into_tree(&$all_forums,$forum_id)
{
	$children=array();
	$all_forums_copy=$all_forums;
	foreach ($all_forums_copy as $i=>$forum)
	{
		if ($forum['f_parent_forum']==$forum_id)
		{
			$forum['children']=ocf_organise_into_tree($all_forums,$forum['id']);
			$children[$forum['id']]=$forum;
			unset ($all_forums[$i]);
		}
	}
	return $children;
}

/**
 * Gets a list of subordinate forums of a certain forum.
 *
 * @param  AUTO_LINK		The ID of the forum we are finding subordinate forums of.
 * @param  ?string		The field name to use in the OR list (NULL: do not make an OR list, return an array).
 * @param  ?array			The forum tree structure (NULL: unknown, it will be found using ocf_organise_into_tree).
 * @param  boolean		Whether to ignore permissions in this.
 * @return mixed			The list (is either a true list, or an OR list).
 */
function ocf_get_all_subordinate_forums($forum_id,$create_or_list=NULL,$tree=NULL,$ignore_permissions=false)
{
	if (is_null($forum_id))
	{
		if (is_null($create_or_list)) return array($forum_id); else return '('.$create_or_list.' IS NULL)';
	}

	if (is_null($tree))
	{
		global $ALL_FORUMS_STRUCT;
		if (is_null($ALL_FORUMS_STRUCT))
		{
			$huge_forums=$GLOBALS['FORUM_DB']->query_value('f_forums','COUNT(*)')>100;
			if ($huge_forums)
			{
				$all_descendant=$GLOBALS['FORUM_DB']->query('SELECT id,f_parent_forum FROM '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_forums WHERE id='.strval($forum_id).' OR f_parent_forum='.strval($forum_id),300);
				if (count($all_descendant)==300) // Too many
				{
					if (is_null($create_or_list)) return array($forum_id); else return '('.$create_or_list.'='.strval($forum_id).')';
				}
				$tree=ocf_organise_into_tree($all_descendant,$forum_id);
			} else
			{
				$ALL_FORUMS_STRUCT=$GLOBALS['FORUM_DB']->query_select('f_forums');
				$all_forum_struct_copy=$ALL_FORUMS_STRUCT;
				$tree=ocf_organise_into_tree($all_forum_struct_copy,$forum_id);
			}
		} else
		{
			$all_forum_struct_copy=$ALL_FORUMS_STRUCT;
			$tree=ocf_organise_into_tree($all_forum_struct_copy,$forum_id);
		}
	}

//	$subordinates=$direct_subordinates;
	$subordinates=array();
	foreach ($tree as $subordinate)
	{
		$subordinates=$subordinates+ocf_get_all_subordinate_forums($subordinate['id'],NULL,$subordinate['children'],$ignore_permissions);
	}
	load_up_all_module_category_permissions(get_member(),'forums');
	if (($ignore_permissions) || (has_category_access(get_member(),'forums',strval($forum_id))))
		$subordinates[$forum_id]=$forum_id;

	if (!is_null($create_or_list))
	{
		$or_list='';
		foreach ($subordinates as $subordinate)
		{
			if ($or_list!='') $or_list.=' OR ';
			$or_list.=$create_or_list.'='.strval($subordinate);
		}
		if ($or_list=='') return $or_list;
		return '('.$or_list.')';
	}

	return $subordinates;
}

/*function ocf_is_up_to_date_on_forum($forum_id,$member_id=NULL)
{
	$_last_topic=$GLOBALS['FORUM_DB']->query_select('f_forums',array('f_cache_last_time','f_cache_last_topic_id'),array('id'=>$forum_id));
	if (!array_key_exists(0,$_last_topic)) return false; // Data error, but let's just trip past
	$topic_last_time=$_last_topic[0]['f_cache_last_time'];
	$topic_id=$_last_topic[0]['f_cache_last_topic_id'];
	return ocf_has_read_topic($topic_id,$topic_last_time,$member_id);
}*/

/**
 * Find whether a member is tracking a certain forum.
 *
 * @param  AUTO_LINK		The ID of the forum.
 * @param  ?MEMBER		The member ID (NULL: current member).
 * @return boolean		The answer.
 */
function ocf_is_tracking_forum($forum_id,$member_id=NULL)
{
	if (is_null($member_id)) $member_id=get_member();
	$test=$GLOBALS['FORUM_DB']->query_value_null_ok('f_forum_tracking','r_member_id',array('r_member_id'=>$member_id,'r_forum_id'=>$forum_id));
	return !is_null($test);
}

/**
 * Find whether a member may track a certain forum.
 *
 * @param  AUTO_LINK		The ID of the forum.
 * @param  ?MEMBER		The member ID (NULL: current member).
 * @return boolean		The answer.
 */
function ocf_may_track_forum($forum_id,$member_id=NULL)
{
	if (is_null($member_id)) $member_id=get_member();

	if (!has_category_access($member_id,'forums',strval($forum_id))) return false;

	if (!has_specific_permission($member_id,'may_track_forums')) return false;

	return true;
}

/**
 * Find whether a member may moderate a certain forum.
 *
 * @param  AUTO_LINK		The ID of the forum.
 * @param  ?MEMBER		The member ID (NULL: current member).
 * @return boolean		The answer.
 */
function ocf_may_moderate_forum($forum_id,$member_id=NULL)
{
	if (is_null($member_id)) $member_id=get_member();

	if (is_null($forum_id)) return has_specific_permission($member_id,'moderate_personal_topic');

	return has_specific_permission($member_id,'edit_midrange_content','topics',array('forums',$forum_id));
}

/**
 * Get an OR list of a forums parents, suited for selection from the f_topics table.
 *
 * @param  AUTO_LINK		The ID of the forum.
 * @param  ?AUTO_LINK	The ID of the parent forum (-1: get it from the DB) (NULL: there is no parent, as it is the root forum).
 * @return string			The OR list.
 */
function ocf_get_forum_parent_or_list($forum_id,$parent_id=-1)
{
	if (is_null($forum_id)) return '';

	if ($parent_id==-1) $parent_id=$GLOBALS['FORUM_DB']->query_value('f_forums','f_parent_forum',array('id'=>$forum_id));

	$from_below=ocf_get_forum_parent_or_list($parent_id);
	$term='t_forum_id='.strval((integer)$forum_id);

	return $term.(($from_below!='')?(' OR '.$from_below):'');
}

/**
 * Get a forum navigation tree (a horizontal thing that works backwards along the tree path ['bread crumb trail'], not a full tree).
 *
 * @param  mixed			The ID of the forum we are at in our path (NULL: end of recursion) (false: no forum ID available, this_name and parent_forum must not be NULL).
 * @param  ?string		The name of the given forum (NULL: find it from the DB).
 * @param  ?AUTO_LINK	The parent forum of the given forum (NULL: find it from the DB).
 * @param  boolean		Whether this is being called as the recursion start of deriving the navigation tree (top level call).
 * @return tempcode		The navigation tree.
 */
function ocf_forum_breadcrumbs($end_point_forum,$this_name=NULL,$parent_forum=NULL,$start=true)
{
	if (is_null($end_point_forum))
	{
		return new ocp_tempcode();
	}

	if (is_null($this_name))
	{
		$_forum_details=$GLOBALS['FORUM_DB']->query_select('f_forums',array('f_name','f_parent_forum'),array('id'=>$end_point_forum),'',1);
		if (!array_key_exists(0,$_forum_details)) return new ocp_tempcode();//warn_exit(do_lang_tempcode('_MISSING_RESOURCE','forum#'.strval($end_point_forum)));
		$forum_details=$_forum_details[0];
		$this_name=escape_html($forum_details['f_name']);
		$parent_forum=$forum_details['f_parent_forum'];
	} else $this_name=escape_html($this_name);
	if (((!$start) || (has_specific_permission(get_member(),'open_virtual_roots'))) && (is_integer($end_point_forum)))
	{
		$map=array('page'=>'forumview');
		if ($end_point_forum!=db_get_first_id()) $map['id']=$end_point_forum;
		$test=get_param_integer('kfs'.strval($end_point_forum),-1);
		if (($test!=-1) && ($test!=0)) $map['kfs'.strval($end_point_forum)]=$test;
		if ($start) $map['keep_forum_root']=$end_point_forum;
		$_this_name=hyperlink(build_url($map,get_module_zone('forumview')),$this_name,false,false,$start?do_lang_tempcode('VIRTUAL_ROOT'):do_lang_tempcode('GO_BACKWARDS_TO',@html_entity_decode($this_name,ENT_QUOTES,get_charset())),NULL,NULL,'up');
	} else
	{
		$_this_name=make_string_tempcode('<span>'.$this_name.'</span>');
	}
	if ($end_point_forum!==get_param_integer('keep_forum_root',db_get_first_id()))
	{
		$out=ocf_forum_breadcrumbs($parent_forum,NULL,NULL,false);
		if (!$out->is_empty()) $out->attach(do_template('BREADCRUMB_ESCAPED'));
	} else $out=new ocp_tempcode();
	$out->attach($_this_name);

	return $out;
}
