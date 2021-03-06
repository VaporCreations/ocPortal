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
function init__ocf_posts()
{
	global $SIGNATURES_CACHE;
	$SIGNATURES_CACHE=array();
}

/**
 * Find whether a member may post in a certain topic.
 *
 * @param  AUTO_LINK 	The forum ID of the forum the topic is in.
 * @param  AUTO_LINK 	The topic ID is in.
 * @param  ?MEMBER		The last poster in the topic (NULL: do not check for double posting).
 * @param  ?MEMBER		The member (NULL: current member).
 * @return boolean		The answer.
 */
function ocf_may_post_in_topic($forum_id,$topic_id,$last_member_id=NULL,$member_id=NULL)
{
	if (is_null($member_id)) $member_id=get_member();

	if (!has_specific_permission($member_id,'submit_lowrange_content','topics',array('forums',$forum_id,'topics',$topic_id))) return false;
	if (is_null($last_member_id)) return true;
	if (($last_member_id==$member_id) && (!is_null($forum_id)))
	{
		if (!has_specific_permission($member_id,'double_post')) return false;
	}

	/*$test=$GLOBALS['FORUM_DB']->query_value_null_ok_full('SELECT id FROM '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_warnings WHERE (p_silence_from_topic='.strval($topic_id).' OR p_silence_from_forum='.strval($forum_id).') AND w_member_id='.strval($member_id));
	if (!is_null($test)) return false;*/

	return true;
}

/**
 * Find whether a member may edit the detailed post.
 *
 * @param  MEMBER			The owner of the post.
 * @param  ?AUTO_LINK 	The forum the post is in (NULL: is a Private Topic).
 * @param  ?MEMBER		The member (NULL: current member).
 * @return boolean		The answer.
 */
function ocf_may_edit_post_by($resource_owner,$forum_id,$member_id=NULL)
{
	if (is_null($member_id)) $member_id=get_member();

	if (is_null($forum_id))
	{
		if (($resource_owner==$member_id) && (has_specific_permission($member_id,'edit_personal_topic_posts'))) return true;
	}

	return has_edit_permission('low',$member_id,$resource_owner,'topics',array('forums',$forum_id));
}

/**
 * Find whether a member may delete the detailed post.
 *
 * @param  MEMBER			The owner of the post.
 * @param  ?AUTO_LINK 	The forum the post is in (NULL: is a Private Topic).
 * @param  ?MEMBER		The member (NULL: current member).
 * @return boolean		The answer.
 */
function ocf_may_delete_post_by($resource_owner,$forum_id,$member_id=NULL)
{
	if (is_null($member_id)) $member_id=get_member();

	if (is_null($forum_id))
	{
		if (($resource_owner!=$member_id) || (!has_specific_permission($member_id,'delete_personal_topic_posts'))) return false;
	}

	return has_delete_permission('low',$member_id,$resource_owner,'topics',array('forums',$forum_id));
}

/**
 * Try and make a spacer post look nicer on OCF than it automatically would.
 *
 * @param  ID_TEXT		Content type.
 * @param  ID_TEXT		Content ID.
 * @return array			A pair: better description (may be NULL), better post (may be NULL).
 */
function ocf_display_spacer_post($linked_type,$linked_id)
{
	$new_description=mixed();
	$new_post=mixed();
	
	if (addon_installed('awards'))
	{
		require_code('content');
		$linked_type=convert_ocportal_type_codes('award_hook',$linked_type,'feedback_type_code');
		if ($linked_type!='')
		{
			require_code('hooks/systems/awards/'.$linked_type);
			$award_ob=object_factory('Hook_awards_'.$linked_type);
			$award_info=$award_ob->info();
			$linked_rows=$GLOBALS['SITE_DB']->query_select($award_info['table'],array('*'),array($award_info['id_field']=>$award_info['id_is_string']?$linked_id:intval($linked_id)),'',1);
			if (array_key_exists(0,$linked_rows))
				$new_post=$award_ob->run($linked_rows[0],'_SEARCH');
			$new_description=do_lang('THIS_IS_COMMENT_TOPIC',get_site_name());
		}
	}

	return array($new_description,$new_post);
}

