<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2011

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 */

class Hook_members_octhanks
{

	/**
	 * Standard modular run function.
	 *
	 * @param  MEMBER		The ID of the member we are getting link hooks for
	 * @return array		List of tuples for results. Each tuple is: type,title,url
	 */
	function get_info_details($member_id)
	{
		$topics_opened=$GLOBALS['FORUM_DB']->query_value('f_topics','COUNT(*)',array('t_cache_first_member_id'=>$member_id));
		$num_replies=$GLOBALS['FORUM_DB']->query_value('f_posts','COUNT(DISTINCT p_topic_id)',array('p_poster'=>$member_id))-$topics_opened;
		return array('Forum contributions'=>$GLOBALS['FORUM_DRIVER']->get_username($member_id).' has opened '.integer_format($topics_opened).' '.(($topics_opened==1)?'topic':'topics').' and replied to '.integer_format($num_replies).' '.(($num_replies==1)?'topic':'topics').' by other people.');
	}

}


