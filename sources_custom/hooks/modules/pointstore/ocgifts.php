<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2011

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		pointstore
 */

class Hook_pointstore_ocgifts
{

	/**
	 * Standard pointstore item initialisation function.
	 */
	function init()
	{
		require_lang('ocgifts');
	}

	/**
	 * Standard pointstore item initialisation function.
	 *
	 * @return array			The "shop fronts"
	 */
	function info()
	{
		$class=str_replace('hook_pointstore_','',strtolower(get_class($this)));

		//if (get_option('is_on_'.$class.'_buy')=='0') return array();

		$next_url=build_url(array('page'=>'_SELF','type'=>'action','id'=>$class),'_SELF');
		return array(do_template('POINTSTORE_'.strtoupper($class),array('NEXT_URL'=>$next_url)));
	}

	/**
	 * Standard interface stage of pointstore item purchase.
	 *
	 * @return tempcode		The UI
	 */
	function action()
	{
		require_code('database_action');
		$class=str_replace('hook_pointstore_','',strtolower(get_class($this)));

		$title=get_page_title('OCGIFTS_TITLE');

		require_code('form_templates');

		$map=NULL;
		$category=either_param('category','');
		if ($category!='')
		{
			$map=array('category'=>$category);
		}

		$max_rows=$GLOBALS['FORUM_DB']->query_value('ocgifts','COUNT(*)',$map);

		$max=get_param_integer('max',20);
		$start=get_param_integer('start',0);
		require_code('templates_results_browser');
		$results_browser=results_browser(do_lang_tempcode('OCGIFTS_TITLE'),get_param('id'),$start,'start',$max,'max',$max_rows,NULL,NULL,true,true);

		$rows=$GLOBALS['FORUM_DB']->query_select('ocgifts g',array('*','(SELECT COUNT(*) FROM '.$GLOBALS['FORUM_DB']->get_table_prefix().'members_gifts m WHERE m.gift_id=g.id) AS popularity'),$map,'ORDER BY popularity DESC',$max,$start);
		$username=get_param('username','');
		$gifts=array();
		foreach($rows as $gift)
		{
			$gift_url=build_url(array('page'=>'pointstore','type'=>'action_done','id'=>'ocgifts','gift'=>$gift['id'],'username'=>$username),'_SEARCH');

			$image_url='';
			if (is_file(get_file_base().'/'.rawurldecode($gift['image'])))
			{
				$image_url=get_base_url().'/'.$gift['image'];
			}

			$gifts[]=array(
				'NAME'=>$gift['name'],
				'PRICE'=>integer_format($gift['price']),
				'POPULARITY'=>integer_format($gift['popularity']),
				'GIFT_URL'=>$gift_url,
				'IMAGE_URL'=>$image_url,
			);
		}
		
		$categories=collapse_1d_complexity('category',$GLOBALS['FORUM_DB']->query_select('ocgifts',array('DISTINCT category'),NULL,'ORDER BY category'));

		return do_template('POINTSTORE_OCGIFTS_GIFTS',array('TITLE'=>$title,'GIFTS'=>$gifts,'RESULTS_BROWSER'=>$results_browser,'CATEGORY'=>$category,'CATEGORIES'=>$categories));
	}

	/**
	 * Standard actualisation stage of pointstore item purchase.
	 *
	 * @return tempcode		The UI
	 */
	function action_done()
	{
		$class=str_replace('hook_pointstore_','',strtolower(get_class($this)));

		$title=get_page_title('OCGIFTS_TITLE');

		require_code('form_templates');
		$fields=new ocp_tempcode();

		$fields->attach(form_input_username(do_lang_tempcode('TO_USERNAME'),do_lang_tempcode('MEMBER_TO_GIVE'),'username',get_param('username',''),false));

		$fields->attach(form_input_text(do_lang_tempcode('GIFT_MESSAGE'),do_lang_tempcode('DESCRIPTION_GIFT_MESSAGE'),'gift_message','',true));

		$fields->attach(form_input_tick(do_lang_tempcode('ANONYMOUS'),do_lang_tempcode('DESCRIPTION_ANONYMOUS'),'anonymous',false));

		$submit_name=do_lang_tempcode('SEND_GIFT');
		$text=paragraph(do_lang_tempcode('CHOOSE_MEMBER'));

		$post_url=build_url(array('page'=>'pointstore','type'=>'action_done2','id'=>'ocgifts','gift'=>get_param('gift',0)),'_SEARCH');

		return do_template('FORM_SCREEN',array('SKIP_VALIDATION'=>true,'STAFF_HELP_URL'=>'','HIDDEN'=>'','TITLE'=>$title,'FIELDS'=>$fields,'TEXT'=>$text,'SUBMIT_NAME'=>$submit_name,'URL'=>$post_url));
	}

	/**
	 * Standard actualisation stage of pointstore item purchase.
	 *
	 * @return tempcode		The UI
	 */
	function action_done2()
	{
		$class=str_replace('hook_pointstore_','',strtolower(get_class($this)));

		$title=get_page_title('OCGIFTS_TITLE');

		$gift_id=get_param_integer('gift');
		$member_id=get_member();
		$to_member=post_param('username','');
		$gift_message=post_param('gift_message','');

		$member_row=$GLOBALS['FORUM_DB']->query_select('f_members',array('*'),array('m_username'=>$to_member),'',1);
		if(isset($member_row[0]['id']) && $member_row[0]['id']>0) 
		{
			$to_member_id=$member_row[0]['id'];		
			$anonymous=post_param_integer('anonymous',0);

			$gift_row=$GLOBALS['FORUM_DB']->query_select('ocgifts',array('*'),array('id'=>$gift_id) );

			if(isset($gift_row[0]['id']) && $gift_row[0]['id']>0) 
			{
				//check available points and charge
				$available_points=available_points($member_id);


				if($gift_row[0]['price']>$available_points) warn_exit(do_lang_tempcode('CANT_AFFORD'));
				require_code('points2');

				//get gift points
				charge_member($member_id,$gift_row[0]['price'],do_lang('GIFT_PURCHASING') . ' - ' .strval($gift_row[0]['price']).' point(-s).');

				$gift_row_id=$GLOBALS['SITE_DB']->query_insert('members_gifts',array('to_user_id'=>$to_member_id,'from_user_id'=>$member_id,'gift_id'=>$gift_id,'add_time'=>time(),'is_anonymous'=>$anonymous,'topic_id'=>NULL,'gift_message'=>$gift_message),true);
			}

			require_code('ocf_topics');
			require_code('ocf_forums');
			require_code('ocf_topics_action');
			require_code('ocf_topics_action2');
			require_code('ocf_posts_action');

			if(isset($gift_row[0]['id']) && $gift_row[0]['id']>0) 
			{
				if($anonymous==0)
				{
					$subject=do_lang('GOT_GIFT');
					$gift_pt_topic_post=do_lang('GIFT_EXPLANATION1',$GLOBALS['FORUM_DRIVER']->get_username($member_id),$gift_row[0]['name']) . '.  '."\n\n".'[img]'.get_base_url().'/'.$gift_row[0]['image'].'[/img]'."\n\n".$gift_message;

					$topic_id=ocf_make_topic(NULL,$subject,'',1,1,0,0,0,$member_id,$to_member_id,true,0,NULL,'');

					$post_id=ocf_make_post($topic_id,$subject,$gift_pt_topic_post,0,true,1,0,NULL,NULL,NULL,$member_id,NULL,NULL,NULL,true,true,NULL,true,$subject,0,NULL,false,true,true);

					sent_pt_notification($post_id,$subject,$topic_id,$to_member_id);

					$GLOBALS['SITE_DB']->query_update('members_gifts',array('topic_id'=>$topic_id),array('id'=>$gift_row_id) );

				}
				else // If it's anonymous, send via e-mail
				{
					$gift_pt_topic_post=do_lang('GIFT_EXPLANATION2',$gift_row[0]['name'],NULL,NULL,get_lang($to_member_id))."\n\n".'[img]'.get_base_url().'/'.$gift_row[0]['image'].'[/img]'."\n\n".$gift_message;

					require_code('mail');

					$message=$gift_pt_topic_post;
					$email_address=$GLOBALS['FORUM_DRIVER']->get_member_email_address($to_member_id);
					$member_name=$GLOBALS['FORUM_DRIVER']->get_username($to_member_id);

					mail_wrap(do_lang('GOT_GIFT',NULL,NULL,NULL,get_lang($to_member_id)),$message,array($email_address),$member_name,'','',3,NULL,false,NULL,false,false);
				}
			}
		}


		else 
		{
			warn_exit(do_lang_tempcode('NO_MEMBER_SELECTED'));
		}
		

		// Show message
		$result=do_lang_tempcode('GIFT_CONGRATULATIONS');

		$url=build_url(array('page'=>'_SELF','type'=>'misc'),'_SELF');
		return redirect_screen($title,$url,$result);
	}

}


