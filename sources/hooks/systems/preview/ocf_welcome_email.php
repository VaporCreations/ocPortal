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
 * @package		welcome_emails
 */

class Hook_Preview_ocf_welcome_email
{

	/**
	 * Find whether this preview hook applies.
	 *
	 * @return array			A pair: The preview, the updated post Comcode
	 */
	function applies()
	{
		$member_id=get_param_integer('id',get_member());
		
		$applies=(get_param('page','')=='admin_ocf_welcome_emails');
		if ($applies)
		{
			require_lang('ocf');
			require_code('mail');

			$subject_tag=post_param('subject');
			$message_raw=do_template('NEWSLETTER_DEFAULT',array('CONTENT'=>post_param('text'),'LANG'=>get_site_default_lang()));

			$to=$GLOBALS['FORUM_DRIVER']->get_member_email_address(get_member());
			if ($to=='' ) $to=get_option('staff_address');
			mail_wrap($subject_tag,$message_raw->evaluate(get_site_default_lang()),array($to),$GLOBALS['FORUM_DRIVER']->get_username(get_member()),'','',3,NULL,false,get_member(),true);
		}
		return array($applies,NULL);
	}

	/**
	 * Standard modular run function for preview hooks.
	 *
	 * @return array			A pair: The preview, the updated post Comcode
	 */
	function run()
	{
		$preview=new ocp_tempcode();
		$preview->attach(comcode_to_tempcode(post_param('text'),get_member()));
		
		return array($preview,NULL);
	}

}


