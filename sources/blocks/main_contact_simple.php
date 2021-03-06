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
 * @package		staff_messaging
 */

class Block_main_contact_simple
{

	/**
	 * Standard modular info function.
	 *
	 * @return ?array	Map of module info (NULL: module is disabled).
	 */
	function info()
	{
		$info=array();
		$info['author']='Chris Graham';
		$info['organisation']='ocProducts';
		$info['hacked_by']=NULL;
		$info['hack_version']=NULL;
		$info['version']=2;
		$info['locked']=false;
		$info['parameters']=array('param','title','private','email_optional');
		return $info;
	}

	/**
	 * Standard modular run function.
	 *
	 * @param  array		A map of parameters.
	 * @return tempcode	The result of execution.
	 */
	function run($map)
	{
		require_lang('messaging');
		require_code('feedback');

		$to=array_key_exists('param',$map)?$map['param']:get_option('staff_address');

		$post=post_param('post','');
		if ((post_param_integer('_comment_form_post',0)==1) && ($post!=''))
		{
			if (addon_installed('captcha'))
			{
				if (get_option('captcha_on_feedback')=='1')
				{
					require_code('captcha');
					enforce_captcha();
				}
			}

			$message=new ocp_tempcode();/*Used to be written out here*/ attach_message(do_lang_tempcode('MESSAGE_SENT'),'inform');

			require_code('mail');

			$email_from=trim(post_param('email',$GLOBALS['FORUM_DRIVER']->get_member_email_address(get_member())));
			mail_wrap(post_param('title'),$post,array($to),NULL,$email_from,$GLOBALS['FORUM_DRIVER']->get_username(get_member()),3,NULL,false,get_member());

			if ($email_from!='')
			{
				mail_wrap(do_lang('YOUR_MESSAGE_WAS_SENT_SUBJECT',post_param('title')),do_lang('YOUR_MESSAGE_WAS_SENT_BODY',$post),array($email_from),NULL,'','',3,NULL,false,get_member());
			}
		} else
		{
			$message=new ocp_tempcode();
		}

		$box_title=array_key_exists('title',$map)?$map['title']:do_lang('CONTACT_US');
		$private=(array_key_exists('private',$map)) && ($map['private']=='1');

		$em=$GLOBALS['FORUM_DRIVER']->get_emoticon_chooser();
		require_javascript('javascript_editing');
		$comcode_help=build_url(array('page'=>'userguide_comcode'),get_comcode_zone('userguide_comcode',false));
		require_javascript('javascript_validation');
		$comment_url=get_self_url();
		$email_optional=array_key_exists('email_optional',$map)?(intval($map['email_optional'])==1):true;

		if (addon_installed('captcha'))
		{
			require_code('captcha');
			$use_captcha=((get_option('captcha_on_feedback')=='1') && (use_captcha()));
			if ($use_captcha)
			{
				generate_captcha();
			}
		} else $use_captcha=false;

		$comment_details=do_template('COMMENTS',array('JOIN_BITS'=>'','FIRST_POST_URL'=>'','FIRST_POST'=>'','USE_CAPTCHA'=>$use_captcha,'EMAIL_OPTIONAL'=>$email_optional,'POST_WARNING'=>'','COMMENT_TEXT'=>'','GET_EMAIL'=>!$private,'GET_TITLE'=>!$private,'EM'=>$em,'DISPLAY'=>'block','TITLE'=>$box_title,'COMMENT_URL'=>$comment_url));

		$out=do_template('BLOCK_MAIN_CONTACT_SIMPLE',array('_GUID'=>'298a357f442f440c6b42e58d6717e57c','EMAIL_OPTIONAL'=>true,'COMMENT_DETAILS'=>$comment_details,'MESSAGE'=>$message));

		return $out;
	}

}


