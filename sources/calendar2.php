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
 * @package		calendar
 */

/**
 * Add a calendar event.
 *
 * @param  AUTO_LINK			The event type
 * @param  SHORT_TEXT		The recurrence code (set to 'none' for no recurrences: blank means infinite and will basically time-out ocPortal)
 * @param  ?integer			The number of recurrences (NULL: none/infinite)
 * @param  BINARY				Whether to segregate the comment-topics/rating/trackbacks per-recurrence
 * @param  SHORT_TEXT		The title of the event
 * @param  LONG_TEXT			The full text describing the event
 * @param  integer			The priority
 * @range  1 5
 * @param  BINARY				Whether it is a public event
 * @param  ?integer			The year the event starts at (NULL: default)
 * @param  ?integer			The month the event starts at (NULL: default)
 * @param  ?integer			The day the event starts at (NULL: default)
 * @param  ?integer			The hour the event starts at (NULL: default)
 * @param  ?integer			The minute the event starts at (NULL: default)
 * @param  ?integer			The year the event ends at (NULL: not a multi day event)
 * @param  ?integer			The month the event ends at (NULL: not a multi day event)
 * @param  ?integer			The day the event ends at (NULL: not a multi day event)
 * @param  ?integer			The hour the event ends at (NULL: not a multi day event)
 * @param  ?integer			The minute the event ends at (NULL: not a multi day event)
 * @param  ?ID_TEXT			The timezone for the event (NULL: current user's timezone)
 * @param  BINARY				Whether the time should be presented in the viewer's own timezone
 * @param  BINARY				Whether the event has been validated
 * @param  BINARY				Whether the event may be rated
 * @param  SHORT_INTEGER	Whether comments are allowed (0=no, 1=yes, 2=review style)
 * @param  BINARY				Whether the event may be trackbacked
 * @param  LONG_TEXT			Hidden notes pertaining to the download
 * @param  ?MEMBER			The event submitter (NULL: current member)
 * @param  integer			The number of views so far
 * @param  ?TIME				The add time (NULL: now)
 * @param  ?TIME				The edit time (NULL: never)
 * @param  ?AUTO_LINK		Force an ID (NULL: don't force an ID)
 * @return AUTO_LINK			The ID of the event
 */
function add_calendar_event($type,$recurrence,$recurrences,$seg_recurrences,$title,$content,$priority,$is_public,$start_year,$start_month,$start_day,$start_hour,$start_minute,$end_year=NULL,$end_month=NULL,$end_day=NULL,$end_hour=NULL,$end_minute=NULL,$timezone=NULL,$do_timezone_conv=1,$validated=1,$allow_rating=1,$allow_comments=1,$allow_trackbacks=1,$notes='',$submitter=NULL,$views=0,$add_date=NULL,$edit_date=NULL,$id=NULL)
{
	if (is_null($submitter)) $submitter=get_member();
	if (is_null($add_date)) $add_date=time();
	if (is_null($timezone)) $timezone=get_users_timezone();

	require_code('comcode_check');

	check_comcode($content,NULL,false,NULL,true);

	if (!addon_installed('unvalidated')) $validated=1;
	$map=array(
		'e_submitter'=>$submitter,
		'e_views'=>$views,
		'e_title'=>insert_lang($title,2),
		'e_content'=>0,
		'e_add_date'=>$add_date,
		'e_edit_date'=>$edit_date,
		'e_recurrence'=>$recurrence,
		'e_recurrences'=>$recurrences,
		'e_seg_recurrences'=>$seg_recurrences,
		'e_start_year'=>$start_year,
		'e_start_month'=>$start_month,
		'e_start_day'=>$start_day,
		'e_start_hour'=>$start_hour,
		'e_start_minute'=>$start_minute,
		'e_end_year'=>$end_year,
		'e_end_month'=>$end_month,
		'e_end_day'=>$end_day,
		'e_end_hour'=>$end_hour,
		'e_end_minute'=>$end_minute,
		'e_timezone'=>$timezone,
		'e_do_timezone_conv'=>$do_timezone_conv,
		'e_is_public'=>$is_public,
		'e_priority'=>$priority,
		'e_type'=>$type,
		'validated'=>$validated,
		'allow_rating'=>$allow_rating,
		'allow_comments'=>$allow_comments,
		'allow_trackbacks'=>$allow_trackbacks,
		'notes'=>$notes
	);
	if (!is_null($id)) $map['id']=$id;
	$id=$GLOBALS['SITE_DB']->query_insert('calendar_events',$map,true);

	require_code('attachments2');
	$GLOBALS['SITE_DB']->query_update('calendar_events',array('e_content'=>insert_lang_comcode_attachments(3,$content,'calendar',strval($id))),array('id'=>$id),'',1);

	require_code('seo2');
	seo_meta_set_for_implicit('event',strval($id),array($title,$content),$content);

	decache('side_calendar');

	if (($is_public==1) && (has_category_access($GLOBALS['FORUM_DRIVER']->get_guest_id(),'calendar',strval($type))))
	{
		$timestamp=mktime($start_hour,$start_minute,0,$start_month,$start_day,$start_year);

		$first_date=get_timezoned_date($timestamp,false,false,false,true);

		$message=do_lang('TWITTER_CALENDAR_EVENT',$content,$first_date);

		twitter_event_update($id,$message,$validated,false);

		facebook_wall_event_update($id,$title,$message,$validated,$type,false);
	}

	log_it('ADD_CALENDAR_EVENT',strval($id),$title);

	return $id;
}

/**
 * Edit a calendar event.
 *
 * @param  AUTO_LINK			The ID of the event
 * @param  ?AUTO_LINK		The event type (NULL: default)
 * @param  SHORT_TEXT		The recurrence code
 * @param  ?integer			The number of recurrences (NULL: none/infinite)
 * @param  BINARY				Whether to segregate the comment-topics/rating/trackbacks per-recurrence
 * @param  SHORT_TEXT		The title of the event
 * @param  LONG_TEXT			The full text describing the event
 * @param  integer			The priority
 * @range  1 5
 * @param  BINARY				Whether it is a public event
 * @param  ?integer			The year the event starts at (NULL: default)
 * @param  ?integer			The month the event starts at (NULL: default)
 * @param  ?integer			The day the event starts at (NULL: default)
 * @param  ?integer			The hour the event starts at (NULL: default)
 * @param  ?integer			The minute the event starts at (NULL: default)
 * @param  ?integer			The year the event ends at (NULL: not a multi day event)
 * @param  ?integer			The month the event ends at (NULL: not a multi day event)
 * @param  ?integer			The day the event ends at (NULL: not a multi day event)
 * @param  ?integer			The hour the event ends at (NULL: not a multi day event)
 * @param  ?integer			The minute the event ends at (NULL: not a multi day event)
 * @param  ?ID_TEXT			The timezone for the event (NULL: current user's timezone)
 * @param  BINARY				Whether the time should be presented in the viewer's own timezone
 * @param  SHORT_TEXT		Meta keywords
 * @param  LONG_TEXT			Meta description
 * @param  BINARY				Whether the download has been validated
 * @param  BINARY				Whether the download may be rated
 * @param  SHORT_INTEGER	Whether comments are allowed (0=no, 1=yes, 2=review style)
 * @param  BINARY				Whether the download may be trackbacked
 * @param  LONG_TEXT			Hidden notes pertaining to the download
 */
function edit_calendar_event($id,$type,$recurrence,$recurrences,$seg_recurrences,$title,$content,$priority,$is_public,$start_year,$start_month,$start_day,$start_hour,$start_minute,$end_year,$end_month,$end_day,$end_hour,$end_minute,$timezone,$do_timezone_conv,$meta_keywords,$meta_description,$validated,$allow_rating,$allow_comments,$allow_trackbacks,$notes)
{
	$myrows=$GLOBALS['SITE_DB']->query_select('calendar_events',array('e_title','e_content','e_submitter'),array('id'=>$id),'',1);
	$myrow=$myrows[0];

	require_code('urls2');
	suggest_new_idmoniker_for('calendar','view',strval($id),$title);

	require_code('seo2');
	seo_meta_set_for_explicit('event',strval($id),$meta_keywords,$meta_description);

	require_code('attachments2');
	require_code('attachments3');

	if (!addon_installed('unvalidated')) $validated=1;
	$GLOBALS['SITE_DB']->query_update('calendar_events',array(
		'e_title'=>lang_remap($myrow['e_title'],$title),
		'e_content'=>update_lang_comcode_attachments($myrow['e_content'],$content,'calendar',strval($id),NULL,false,$myrow['e_submitter']),
		'e_edit_date'=>time(),
		'e_recurrence'=>$recurrence,
		'e_recurrences'=>$recurrences,
		'e_seg_recurrences'=>$seg_recurrences,
		'e_start_year'=>$start_year,
		'e_start_month'=>$start_month,
		'e_start_day'=>$start_day,
		'e_start_hour'=>$start_hour,
		'e_start_minute'=>$start_minute,
		'e_end_year'=>$end_year,
		'e_end_month'=>$end_month,
		'e_end_day'=>$end_day,
		'e_end_hour'=>$end_hour,
		'e_end_minute'=>$end_minute,
		'e_timezone'=>$timezone,
		'e_do_timezone_conv'=>$do_timezone_conv,
		'e_is_public'=>$is_public,
		'e_priority'=>$priority,
		'e_type'=>$type,
		'validated'=>$validated,
		'allow_rating'=>$allow_rating,
		'allow_comments'=>$allow_comments,
		'allow_trackbacks'=>$allow_trackbacks,
		'notes'=>$notes
	),array('id'=>$id),'',1);

	decache('side_calendar');

	if (($is_public==1) && (has_category_access($GLOBALS['FORUM_DRIVER']->get_guest_id(),'calendar',strval($type))))
	{
		$timestamp=mktime($start_hour,$start_minute,0,$start_month,$start_day,$start_year);

		$first_date=get_timezoned_date($timestamp,false,false,false,true);

		$message=do_lang('TWITTER_CALENDAR_EVENT',$content,$first_date);

		twitter_event_update($id,$message,$validated,true);

		facebook_wall_event_update($id,$title,$message,$validated,$type,true);
	}
	
	update_spacer_post($allow_comments!=0,'events',strval($id),build_url(array('page'=>'calendar','type'=>'view','id'=>$id),get_module_zone('calendar'),NULL,false,false,true),$title,get_value('comment_forum__calendar'));

	log_it('EDIT_CALENDAR_EVENT',strval($id),$title);
}

/**
 * Delete a calendar event.
 *
 * @param  AUTO_LINK		The ID of the event
 */
function delete_calendar_event($id)
{
	$myrows=$GLOBALS['SITE_DB']->query_select('calendar_events',array('e_title','e_content'),array('id'=>$id),'',1);
	$myrow=$myrows[0];
	$e_title=get_translated_text($myrow['e_title']);

	$GLOBALS['SITE_DB']->query_delete('calendar_events',array('id'=>$id),'',1);

	$GLOBALS['SITE_DB']->query_delete('calendar_jobs',array('j_event_id'=>$id));
	$GLOBALS['SITE_DB']->query_delete('calendar_reminders',array('e_id'=>$id));

	require_code('seo2');
	seo_meta_erase_storage('event',strval($id));

	$GLOBALS['SITE_DB']->query_delete('rating',array('rating_for_type'=>'events','rating_for_id'=>$id));
	$GLOBALS['SITE_DB']->query_delete('trackbacks',array('trackback_for_type'=>'events','trackback_for_id'=>$id));

	delete_lang($myrow['e_title']);
	require_code('attachments2');
	require_code('attachments3');
	if (!is_null($myrow['e_content']))
		delete_lang_comcode_attachments($myrow['e_content'],'e_content',strval($id));

	decache('side_calendar');

	log_it('DELETE_CALENDAR_EVENT',strval($id),$e_title);
}

/**
 * Update new event to twitter
 *
 * @param  AUTO_LINK		The ID of the event
 * @param  SHORT_TEXT	Event description
 * @param  BINARY			Whether the event has been validated	
 * @param  boolean		Current process indication. If it is update, need to check it's old "validated" state
 * @return boolean		Returns the success status of function
 */
function twitter_event_update($id,$message,$validated,$update=true)
{
	if (!addon_installed('twitter')) return false;

	if (!($validated==1) || !(has_specific_permission($GLOBALS['FORUM_DRIVER']->get_guest_id(),'view_calendar')))
	{
		return false;
	}
	
	if($update)
	{
		$validated_old=$GLOBALS['SITE_DB']->query_value('calendar_events','validated',array('id'=>$id));

		if($validated_old==1) return false; // skip twitter add if the event was already validated.
	}

	// username and password
	$username=get_option('twitter_login');
	$password=get_option('twitter_password');

	// Checking twitter requirements
	
	if(is_null($username)) return false;
	if($username=='') return false;

	$url = 'http://twitter.com/statuses/update.xml';

	if(strlen($message)>249)
	{
		$_more_url=build_url(array('page'=>'calendar','type'=>'view','id'=>$id),'site',NULL,false,false,true);
		$more_url=$_more_url->evaluate();
		$url_length=strlen($more_url);
		$message=substr($message,0,245-$url_length);
		$message.= "...".urlencode($more_url);
	}

	require_code('files');
	$ret=http_download_file($url,NULL,false,false,'ocPortal',array('status'=>$message),NULL,NULL,NULL,NULL,NULL,NULL,array($username,$password));
	return !is_null($ret);
}

/**
 * Update new event to facebook wall
 *
 * @param  AUTO_LINK		The ID of the event
 * @param  SHORT_TEXT	Event title
 * @param  SHORT_TEXT	Event description
 * @param  BINARY			Whether the event has been validated	
 * @param  AUTO_LINK		Event type	
 * @param  boolean		Current process indication. If it is update, need to check it's old "validated" state
 * @return boolean		Returns the success status of function
 */
function facebook_wall_event_update($id,$title,$message,$validated,$type,$update=true)
{
	if (!addon_installed('facebook')) return false;

	if (!($validated==1) || !(has_specific_permission($GLOBALS['FORUM_DRIVER']->get_guest_id(),'view_calendar')))
	{
		return false;
	}
	
	$appapikey	=	get_option('facebook_api');
	$appsecret	=	get_option('facebook_secret_code');
	$uid		=	get_option('facebook_uid');
	if(($appapikey=='') || ($appsecret=='') || ($uid=='')) return false;
	if (version_compare(PHP_VERSION, '5.0.0', '<')) return false;

	if($update)
	{
		$validated_old=$GLOBALS['SITE_DB']->query_value('calendar_events','validated',array('id'=>$id));

		if($validated_old==1) return false; // skip twitter add if the event was already validated.
	}
	
	require_code('mail');

	require_code('facebook_publish');
	if (!function_exists('publish_to_FB')) return false; // Just for code quality checker
	
	$categorisation	=	do_lang('FACEBOOK_WALL_HEADING_EVENT',get_option('site_name'));

	//$message	=	comcode_to_clean_text($message);

	$view_url=build_url(array('page'=>'calendar','type'=>'view','id'=>$id),'site',NULL,false,false,true);

	publish_to_FB($title,$categorisation,$view_url->evaluate());
}

/**
 * Add a calendar event type.
 *
 * @param  SHORT_TEXT		The title of the event type
 * @param  ID_TEXT			The theme image code
 * @param  URLPATH			URL to external feed to associate with this event type
 * @return AUTO_LINK			The ID of the event type
 */
function add_event_type($title,$logo,$external_feed='')
{
	$id=$GLOBALS['SITE_DB']->query_insert('calendar_types',array(
		't_title'=>insert_lang($title,2),
		't_logo'=>$logo,
		't_external_feed'=>$external_feed,
	),true);

	log_it('ADD_EVENT_TYPE',strval($id),$title);
	return $id;
}

/**
 * Edit a calendar event type.
 *
 * @param  AUTO_LINK			The ID of the event type
 * @param  SHORT_TEXT		The title of the event type
 * @param  ID_TEXT			The theme image code
 * @param  URLPATH			URL to external feed to associate with this event type
 */
function edit_event_type($id,$title,$logo,$external_feed)
{
	$myrows=$GLOBALS['SITE_DB']->query_select('calendar_types',array('t_title','t_logo'),array('id'=>$id),'',1);
	if (!array_key_exists(0,$myrows)) warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
	$myrow=$myrows[0];

	require_code('urls2');
	suggest_new_idmoniker_for('calendar','misc',strval($id),$title);

	$GLOBALS['SITE_DB']->query_update('calendar_types',array(
		't_title'=>lang_remap($myrow['t_title'],$title),
		't_logo'=>$logo,
		't_external_feed'=>$external_feed,
	),array('id'=>$id),'',1);

	require_code('themes2');
	tidy_theme_img_code($logo,$myrow['t_logo'],'calendar_types','t_logo');

	log_it('EDIT_EVENT_TYPE',strval($id),$title);
}

/**
 * Delete a calendar event type.
 *
 * @param  AUTO_LINK		The ID of the event type
 */
function delete_event_type($id)
{
	$myrows=$GLOBALS['SITE_DB']->query_select('calendar_types',array('t_title','t_logo'),array('id'=>$id),'',1);
	if (!array_key_exists(0,$myrows)) warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
	$myrow=$myrows[0];

	$lowest=$GLOBALS['SITE_DB']->query_value_null_ok_full('SELECT MIN(id) FROM '.get_table_prefix().'calendar_types WHERE id<>'.strval($id).' AND id<>'.strval(db_get_first_id()));
	if (is_null($lowest)) warn_exit(do_lang_tempcode('NO_DELETE_LAST_CATEGORY'));
	$GLOBALS['SITE_DB']->query_update('calendar_events',array('e_type'=>$lowest),array('e_type'=>$id));

	$GLOBALS['SITE_DB']->query_delete('calendar_types',array('id'=>$id),'',1);
	$GLOBALS['SITE_DB']->query_delete('calendar_interests',array('t_type'=>$id));

	log_it('DELETE_EVENT_TYPE',strval($id),get_translated_text($myrow['t_title']));
	delete_lang($myrow['t_title']);

	$GLOBALS['SITE_DB']->query_delete('group_category_access',array('module_the_name'=>'calendar','category_name'=>strval($id)));

	require_code('themes2');
	tidy_theme_img_code(NULL,$myrow['t_logo'],'calendar_types','t_logo');
}


