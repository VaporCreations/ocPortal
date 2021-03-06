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

class Hook_symbol_POINTS_FROM_USERGROUPS
{

	/**
	 * Standard modular run function for symbol hooks. Searches for tasks to perform.
    *
    * @param  array		Symbol parameters
    * @return string		Result
	 */
	function run($param)
	{
		require_code('points');
		$member=isset($param[0])?intval($param[0]):get_member();
		$value=strval(total_points($member)-non_overrided__total_points($member));
		return $value;
	}

}
