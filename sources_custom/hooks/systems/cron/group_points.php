<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2011

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		group_points
 */

class Hook_cron_group_points
{

	/**
	 * Standard modular run function for CRON hooks. Searches for tasks to perform.
	 */
	function run()
	{
		require_code('points');

		$time=time();
		$last_time=intval(get_value('last_group_points'));
		if ($last_time>time()-24*60*60*27) return; // Only once within a month
	
		if (date('j')!='1') return; // Only on first day
		
		require_code('points');
		require_code('points2');

		$groups=$GLOBALS['FORUM_DRIVER']->get_usergroup_list(false,true,true);
		$group_points=get_group_points();

		$fields=new ocp_tempcode();

		foreach ($groups as $group_id=>$group_name)
		{
			if (isset($group_points[$group_id]))
			{
				$points=$group_points[$group_id];
				if ($points['p_points_per_month']!=0)
				{
					$members=$GLOBALS['FORUM_DRIVER']->member_group_query(array($group_id));
					foreach ($members as $member_row)
					{
						$member_id=$GLOBALS['FORUM_DRIVER']->pname_id($member_row);
						system_gift_transfer('Being in the '.$group_name.' usergroup',$points['p_points_per_month'],$member_id);
					}
				}
			}
		}
		
		set_value('last_group_points',strval($time));
	}
	
}
