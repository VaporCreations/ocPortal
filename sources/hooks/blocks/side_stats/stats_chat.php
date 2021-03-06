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
 * @package		chat
 */

class Hook_stats_chat
{

	/**
	 * Standard modular run function.
	 *
	 * @return tempcode	The result of execution.
	 */
	function run()
	{
		if (!addon_installed('chat')) return new ocp_tempcode();

		require_code('chat_stats');
		require_lang('chat');
  
		$bits=new ocp_tempcode();
		if (get_option('chat_show_stats_count_users',true)=='1') $bits->attach(do_template('BLOCK_SIDE_STATS_SUBLINE',array('_GUID'=>'904a46b83a84728243f3fd655705cc04','KEY'=>do_lang_tempcode('COUNT_CHATTERS'),'VALUE'=>integer_format(get_num_chatters()))));
		if (get_option('chat_show_stats_count_rooms',true)=='1') $bits->attach(do_template('BLOCK_SIDE_STATS_SUBLINE',array('_GUID'=>'adf12b729fd23b6fa7115758a64155c6','KEY'=>do_lang_tempcode('ROOMS'),'VALUE'=>integer_format(get_num_chatrooms()))));
		if (get_option('chat_show_stats_count_messages',true)=='1') $bits->attach(do_template('BLOCK_SIDE_STATS_SUBLINE',array('_GUID'=>'0e86e89171ddd8225ac41e14b18ecdb0','KEY'=>do_lang_tempcode('COUNT_CHATPOSTS'),'VALUE'=>integer_format(get_num_chatposts()))));
		if ($bits->is_empty()) return new ocp_tempcode();
		$chat=do_template('BLOCK_SIDE_STATS_SECTION',array('_GUID'=>'4d688c45e01ed34f257fd03100a6be6d','SECTION'=>do_lang_tempcode('SECTION_CHAT'),'CONTENT'=>$bits));
	
		return $chat;
	}

}


