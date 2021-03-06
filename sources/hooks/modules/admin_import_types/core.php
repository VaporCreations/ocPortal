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
 * @package		import
 */

class Hook_admin_import_types_core
{

	/**
	 * Standard modular run function.
	 *
	 * @return array		Results
	 */
	function run()
	{
		$ret=array(
			'attachments'=>'ATTACHMENTS',
			'attachment_references'=>'ATTACHMENT_REFERENCES',
			'feedback'=>'_FEEDBACK',
			'permissions'=>'PERMISSIONS',
			'themes'=>'THEMES',
			'useronline_tracking'=>'USER_ONLINE_TRACKING',
			'zones'=>'ZONES',
			'ocf_emoticons'=>'EMOTICONS',
			'ocf_members'=>'MEMBERS',
			'ocf_member_files'=>'MEMBER_FILES',
			'ocf_groups'=>'USERGROUPS',
			'ocf_privileges'=>'PRIVILEGES',
			'config'=>'CONFIGURATION',
			'logs'=>'LOGS',
			'pages'=>'COMCODE_PAGES',
			'rss'=>'_RSS',
			'blocks'=>'_BLOCK_LABEL',
		);
		if (addon_installed('ocf_cpfs'))
			$ret['ocf_custom_profile_fields']='CUSTOM_PROFILE_FIELDS';
		if (addon_installed('ocf_warnings'))
			$ret['ocf_warnings']='WARNINGS';
		if (addon_installed('custom_comcode'))
			$ret['custom_comcode']='CUSTOM_COMCODE';
		if (addon_installed('authors'))
			$ret['authors']='AUTHORS';
		if (addon_installed('welcome_emails'))
			$ret['ocf_welcome_emails']='WELCOME_EMAILS';
		if (addon_installed('securitylogging'))
			$ret['ip_bans']='BANNED_ADDRESSES';
		if (addon_installed('redirects_editor'))
			$ret['redirects']='REDIRECTS';
		return $ret;
	}

}


