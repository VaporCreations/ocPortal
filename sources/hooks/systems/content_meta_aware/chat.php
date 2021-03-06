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

class Hook_content_meta_aware_chat
{

	/**
	 * Standard modular info function for content_meta_aware hooks. Allows progmattic identification of ocPortal entity model (along with db_meta table contents).
	 *
	 * @return ?array	Map of award content-type info (NULL: disabled).
	 */
	function info()
	{
		return array(
			'table'=>'chat_rooms',
			'id_field'=>'id',
			'id_field_numeric'=>true,
			'parent_category_field'=>NULL,
			'parent_category_meta_aware_type'=>NULL,
			'title_field'=>'room_name',
			'title_field_dereference'=>false,

			'is_category'=>true,
			'is_entry'=>false,
			'seo_type_code'=>NULL,
			'feedback_type_code'=>NULL,
			'permissions_type_code'=>NULL, // NULL if has no permissions
			'view_pagelink_pattern'=>'_SEARCH:chat:room:_WILD',
			'edit_pagelink_pattern'=>'_SEARCH:cms_chat:room:_WILD',
			'view_category_pagelink_pattern'=>'_SEARCH:chat:room:_WILD',
			'support_url_monikers'=>true,
			'search_hook'=>NULL,
			'submitter_field'=>NULL,
			'add_time_field'=>NULL,
			'edit_time_field'=>NULL,
			'validated_field'=>NULL,
			
			'addon_name'=>'chat',
			
			'module'=>'chat',
		);
	}
	
}
