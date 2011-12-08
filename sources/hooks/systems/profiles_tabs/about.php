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
 * @package		core_ocf
 */

class Hook_Profiles_Tabs_about
{

	/**
	 * Find whether this hook is active.
	 *
	 * @param  MEMBER			The ID of the member who is being viewed
	 * @param  MEMBER			The ID of the member who is doing the viewing
	 * @return boolean		Whether this hook is active
	 */
	function is_active($member_id_of,$member_id_viewing)
	{
		return true;
	}

	/**
	 * Standard modular render function for profile tab hooks.
	 *
	 * @param  MEMBER			The ID of the member who is being viewed
	 * @param  MEMBER			The ID of the member who is doing the viewing
	 * @return array			A triple: The tab title, the tab contents, the suggested tab order
	 */
	function render_tab($member_id_of,$member_id_viewing)
	{
		$title=do_lang_tempcode('PROFILE');

		$order=10;

		$photo_url=$GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id_of,'m_photo_url');
		if (($photo_url!='') && (addon_installed('ocf_member_photos')) && (has_specific_permission($member_id_viewing,'view_member_photos')))
		{
			require_code('images');
			$photo_thumb_url=$GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id_of,'m_photo_thumb_url');
			$photo_thumb_url=ensure_thumbnail($photo_url,$photo_thumb_url,(strpos($photo_url,'uploads/photos')!==false)?'photos':'ocf_photos','f_members',$member_id_of,'m_photo_thumb_url');
			if (url_is_local($photo_url))
			{
				$photo_url=get_complex_base_url($photo_url).'/'.$photo_url;
			}
			if (url_is_local($photo_thumb_url))
			{
				$photo_thumb_url=get_complex_base_url($photo_thumb_url).'/'.$photo_thumb_url;
			}
		} else
		{
			$photo_url='';
			$photo_thumb_url='';
		}

		$avatar_url=$GLOBALS['FORUM_DRIVER']->get_member_avatar_url($member_id_of);
		$username=$GLOBALS['FORUM_DRIVER']->get_username($member_id_of);

		// Things staff can do with this user
		$modules=array();
		if ((has_specific_permission($member_id_viewing,'warn_member')) && (has_actual_page_access($member_id_viewing,'warnings')) && (addon_installed('ocf_warnings')))
		{
			$redir_url=get_self_url(true);
			$modules[]=array('usage',do_lang_tempcode('WARN_MEMBER'),build_url(array('page'=>'warnings','type'=>'ad','id'=>$member_id_of,'redirect'=>$redir_url),get_module_zone('warnings')));
			$modules[]=array('usage',do_lang_tempcode('PUNITIVE_HISTORY'),build_url(array('page'=>'warnings','type'=>'history','id'=>$member_id_of),get_module_zone('warnings')));
		}
		if ((has_specific_permission($member_id_viewing,'view_content_history')) && (has_actual_page_access($member_id_viewing,'admin_ocf_history')))
			$modules[]=(!addon_installed('ocf_forum'))?NULL:array('usage',do_lang_tempcode('POST_HISTORY'),build_url(array('page'=>'admin_ocf_history','member_id'=>$member_id_of),'adminzone'));
		if (has_actual_page_access($member_id_viewing,'admin_lookup'))
		{
			require_lang('submitban');
			$modules[]=array('usage',do_lang_tempcode('INVESTIGATE_USER'),build_url(array('page'=>'admin_lookup','param'=>$member_id_of),'adminzone'));
		}
		if (has_actual_page_access($member_id_viewing,'admin_security'))
		{
			require_lang('security');
			$modules[]=array('usage',do_lang_tempcode('SECURITY_LOGGING'),build_url(array('page'=>'admin_security','member_id'=>$member_id_of),'adminzone'));
		}
		if (addon_installed('actionlog'))
		{
			if (has_actual_page_access($member_id_viewing,'admin_actionlog'))
			{
				require_lang('submitban');
				$modules[]=array('usage',do_lang_tempcode('VIEW_ACTION_LOGS'),build_url(array('page'=>'admin_actionlog','type'=>'list','id'=>$member_id_of),'adminzone'));
			}
		}
		if ((has_actual_page_access($member_id_viewing,'search')) && (addon_installed('ocf_forum')) && (addon_installed('search')))
			$modules[]=array('content',do_lang_tempcode('SEARCH_POSTS'),build_url(array('page'=>'search','type'=>'misc','id'=>'ocf_posts','author'=>$username,'sort'=>'add_date','direction'=>'DESC','content'=>''),get_module_zone('search')),'search');
		if ((has_actual_page_access($member_id_viewing,'search')) && (addon_installed('search')))
			$modules[]=array('content',do_lang_tempcode('SEARCH'),build_url(array('page'=>'search','type'=>'misc','author'=>$username),get_module_zone('search')),'search');
		if (addon_installed('authors'))
		{
			$author=$GLOBALS['SITE_DB']->query_value_null_ok_full('SELECT author FROM '.get_table_prefix().'authors WHERE (forum_handle='.strval($member_id_viewing).') OR (forum_handle IS NULL AND '.db_string_equal_to('author',$username).')');
			if ((has_actual_page_access($member_id_viewing,'authors')) && (!is_null($author)))
			{
				$modules[]=array('content',do_lang_tempcode('AUTHOR'),build_url(array('page'=>'authors','type'=>'misc','id'=>$author),get_module_zone('authors')));
			}
		}
		require_code('ocf_members2');
		if ((!is_guest()) && (ocf_may_whisper($member_id_of)) && (has_actual_page_access($member_id_viewing,'topics')) && (ocf_may_make_personal_topic()))
		{
			$modules[]=(!addon_installed('ocf_forum'))?NULL:array('contact',do_lang_tempcode('ADD_PERSONAL_TOPIC'),build_url(array('page'=>'topics','type'=>'new_pt','id'=>$member_id_of),get_module_zone('topics')),'reply');
		}
		$extra_sections=array();
		$info_details=array();
		$hooks=find_all_hooks('modules','members');
		foreach (array_keys($hooks) as $hook)
		{
			require_code('hooks/modules/members/'.filter_naughty_harsh($hook));
			$object=object_factory('Hook_members_'.filter_naughty_harsh($hook),true);
			if (is_null($object)) continue;
			if (method_exists($object,'run'))
			{
				$hook_result=$object->run($member_id_of);
				$modules=array_merge($modules,$hook_result);
			}
			if (method_exists($object,'get_info_details'))
			{
				$hook_result=$object->get_info_details($member_id_of);
				$info_details=array_merge($info_details,$hook_result);
			}
			if (method_exists($object,'get_sections'))
			{
				$hook_result=$object->get_sections($member_id_of);
				$extra_sections=array_merge($extra_sections,$hook_result);
			}
		}
		if ((($GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id_of,'m_allow_emails')==1) || (get_option('allow_email_disable')=='0')) && (!is_guest($member_id_of)) && (has_actual_page_access($member_id_viewing,'contactmember')))
		{
			$redirect=get_self_url(true);
			$modules[]=array('contact',do_lang_tempcode('_EMAIL_MEMBER'),build_url(array('page'=>'contactmember','redirect'=>$redirect,'id'=>$member_id_of),get_module_zone('contactmember')),'reply');
		}
		require_lang('menus');
		$sections=array('contact'=>do_lang_tempcode('CONTACT'),'profile'=>do_lang_tempcode('EDIT_PROFILE'),'views'=>do_lang_tempcode('ACCOUNT'),'usage'=>do_lang_tempcode('USAGE'),'content'=>do_lang_tempcode('CONTENT'));
		$actions=array();
		global $M_SORT_KEY;
		$M_SORT_KEY=mixed();
		$M_SORT_KEY=1;
		@uasort($modules,'multi_sort'); /* @ is to stop PHP bug warning about altered array contents when Tempcode copies are evaluated internally */
		foreach ($sections as $section_code=>$section_title)
		{
			$links=new ocp_tempcode();
			foreach ($modules as $module)
			{
				if (count($module)==3)
				{
					list($_section_code,$lang,$url)=$module;
					$rel=NULL;
				} else
				{
					list($_section_code,$lang,$url,$rel)=$module;
				}
				if ($section_code==$_section_code)
					$links->attach(do_template('OCF_MEMBER_ACTION',array('_GUID'=>'67b2a640a368c6f53f1b1fa10f922fd0','ID'=>strval($member_id_of),'URL'=>$url,'LANG'=>$lang,'REL'=>$rel)));
			}
			$actions[$section_code]=$links;
		}

		// Custom fields
		$_custom_fields=ocf_get_all_custom_fields_match_member($member_id_of,(($member_id_viewing!=$member_id_of) && (!has_specific_permission($member_id_viewing,'view_any_profile_field')))?1:NULL,(($member_id_viewing==$member_id_of) && (!has_specific_permission($member_id_viewing,'view_any_profile_field')))?1:NULL);
		$custom_fields=array();
		require_code('encryption');
		$value=mixed();
		foreach ($_custom_fields as $name=>$_value)
		{
			$value=$_value['RAW'];
			$rendered_value=$_value['RENDERED'];

			$encrypted_value='';
			if (is_data_encrypted($value))
			{
				$encrypted_value=remove_magic_encryption_marker($value);
			}
			elseif ((is_string($value)) && (substr($value,0,7)=='http://'))
			{
				$_value=hyperlink($value,$value,true,true);
				$value=$_value->evaluate();
			} elseif (is_integer($value))
			{
				$value=escape_html(integer_format($value));
			} else
			{
				if (!is_object($value)) $value=escape_html($value);
			}

			if (((!is_object($value)) && ($value!='')) || ((is_object($value)) && (!$value->is_empty())))
			{
				$custom_fields[]=array('NAME'=>$name,'RAW_VALUE'=>$value,'VALUE'=>$rendered_value,'ENCRYPTED_VALUE'=>$encrypted_value);
				if ($name==do_lang('KEYWORDS')) $GLOBALS['SEO_KEYWORDS']=is_object($value)?$value->evaluate():$value;
				if ($name==do_lang('DESCRIPTION')) $GLOBALS['SEO_DESCRIPTION']=is_object($value)?$value->evaluate():$value;
			}
		}

		// Birthday
		$dob='';
		if ($GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id_of,'m_reveal_age')==1)
		{
			$day=$GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id_of,'m_dob_day');
			$month=$GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id_of,'m_dob_month');
			$year=$GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id_of,'m_dob_year');
			if (!is_null($day))
			{
				if (@strftime('%Y',@mktime(0,0,0,1,1,1963))!='1963') $dob=strval($year).'-'.str_pad(strval($month),2,'0',STR_PAD_LEFT).'-'.str_pad(strval($day),2,'0',STR_PAD_LEFT); else $dob=get_timezoned_date(mktime(12,0,0,$month,$day,$year),false,true,true);
			}
		}

		// Find forum with most posts
		$forums=$GLOBALS['FORUM_DB']->query('SELECT id,f_name FROM '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_forums WHERE f_cache_num_posts>0');
		$best_yet_forum=0; // Initialise to integer type
		$best_yet_forum=NULL;
		$most_active_forum=NULL;
		$_best_yet_forum=$GLOBALS['FORUM_DB']->query_select('f_posts',array('COUNT(*) as cnt','p_cache_forum_id'),array('p_poster'=>$member_id_of),'GROUP BY p_cache_forum_id');
		$_best_yet_forum=collapse_2d_complexity('p_cache_forum_id','cnt',$_best_yet_forum);
		foreach ($forums as $forum)
		{
			if (((array_key_exists($forum['id'],$_best_yet_forum)) && ((is_null($best_yet_forum)) || ($_best_yet_forum[$forum['id']]>$best_yet_forum))))
			{
				$most_active_forum=has_category_access($member_id_viewing,'forums',strval($forum['id']))?protect_from_escaping(escape_html($forum['f_name'])):do_lang_tempcode('PROTECTED_FORUM');
				$best_yet_forum=$_best_yet_forum[$forum['id']];
			}
		}
		$post_count=$GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id_of,'m_cache_num_posts');
		$best_post_fraction=($post_count==0)?do_lang_tempcode('NA_EM'):make_string_tempcode(integer_format(100*$best_yet_forum/$post_count));
		$most_active_forum=is_null($best_yet_forum)?new ocp_tempcode():do_lang_tempcode('_MOST_ACTIVE_FORUM',$most_active_forum,make_string_tempcode(integer_format($best_yet_forum)),array($best_post_fraction));
		$time_for_them_raw=tz_time(time(),get_users_timezone($member_id_of));
		$time_for_them=get_timezoned_time(time(),true,$member_id_of);

		$banned=($GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id_of,'m_is_perm_banned')==1)?do_lang_tempcode('YES'):do_lang_tempcode('NO');

		$last_submit_time=$GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id_of,'m_last_submit_time');
		$submit_days_ago=intval(floor(floatval(time()-$last_submit_time)/60.0/60.0/24.0));

		require_code('ocf_groups');
		$primary_group_id=ocf_get_member_primary_group($member_id_of);
		$primary_group=ocf_get_group_link($primary_group_id);

		$signature=get_translated_tempcode($GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id_of,'m_signature'),$GLOBALS['FORUM_DB']);

		$last_visit_time=$GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id_of,'m_last_visit_time');
		if (member_is_online($member_id_of))
		{
			$online_now=do_lang_tempcode('YES');
		} else
		{
			$minutes_ago=intval(floor((floatval(time()-$last_visit_time)/60.0)));
			$hours_ago=intval(floor((floatval(time()-$last_visit_time)/60.0/60.0)));
			$days_ago=intval(floor((floatval(time()-$last_visit_time)/60.0/60.0/24.0)));
			$months_ago=intval(floor((floatval(time()-$last_visit_time)/60.0/60.0/24.0/31.0)));
			if ($minutes_ago<180)
				$online_now=do_lang_tempcode('_ONLINE_NOW_NO_MINUTES',integer_format($minutes_ago));
			elseif ($hours_ago<72)
				$online_now=do_lang_tempcode('_ONLINE_NOW_NO_HOURS',integer_format($hours_ago));
			elseif ($days_ago<93)
				$online_now=do_lang_tempcode('_ONLINE_NOW_NO_DAYS',integer_format($days_ago));
			else
				$online_now=do_lang_tempcode('_ONLINE_NOW_NO_MONTHS',integer_format($months_ago));
		}

		$join_time=$GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id_of,'m_join_time');
		$days_joined=intval(round((time()-$join_time)/60/60/24));
		$total_posts=$GLOBALS['FORUM_DB']->query_value('f_posts','COUNT(*)');
		$join_date=($join_time==0)?'':get_timezoned_date($join_time,false);
		$count_posts=do_lang_tempcode('_COUNT_POSTS',integer_format($post_count),float_format(floatval($post_count)/floatval(($days_joined==0)?1:$days_joined)),array(float_format(floatval(100*$post_count)/floatval(($total_posts==0)?1:$total_posts))));

		$a=($avatar_url=='')?0:ocf_get_member_best_group_property($member_id_of,'max_avatar_width');
		$b=($photo_thumb_url=='')?0:intval(get_option('thumb_width'));
		$right_margin=(max($a,$b)==0)?'auto':(strval(max($a,$b)+6).'px');

		breadcrumb_set_parents(array(array('_SELF:_SELF:misc',do_lang_tempcode('MEMBERS'))));

		if (has_specific_permission($member_id_viewing,'see_ip'))
		{
			$ip_address=$GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id_of,'m_ip_address');
		} else $ip_address='';

		$secondary_groups=ocf_get_members_groups($member_id_of,true);
		unset($secondary_groups[$primary_group_id]);
		if (count($secondary_groups)>0)
		{
			$_secondary_groups=array();
			$all_groups=$GLOBALS['FORUM_DRIVER']->get_usergroup_list(true,false,false,array_keys($secondary_groups),$member_id_of);
			foreach (array_keys($secondary_groups) as $key)
			{
				$_secondary_groups[$key]=$all_groups[$key];
			}
			$secondary_groups=$_secondary_groups;
		}

		if (addon_installed('points'))
		{
			require_code('points');
			$count_points=integer_format(total_points($member_id_of));
		} else
		{
			$count_points='';
		}

		$user_agent=NULL;
		$operating_system=NULL;
		if ((has_specific_permission($member_id_viewing,'show_user_browsing')) && (addon_installed('stats')))
		{
			$last_stats=$GLOBALS['SITE_DB']->query_select('stats',array('browser','operating_system'),array('the_user'=>$member_id_of),'ORDER BY date_and_time DESC',1);
			if (array_key_exists(0,$last_stats))
			{
				$user_agent=$last_stats[0]['browser'];
				$operating_system=$last_stats[0]['operating_system'];
			}
		}

		/*if ((get_option('allow_member_integration')!='off') && (get_option('allow_member_integration')!='hidden'))
		{
			$remote=$GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id_of,'m_password_compat_scheme')=='remote';
		} else */$remote=NULL;

		$_on_probation=$GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id_of,'m_on_probation_until');
		if ((is_null($_on_probation)) || ($_on_probation<=time())) $on_probation=NULL; else $on_probation=strval($_on_probation);

		$GLOBALS['META_DATA']+=array(
			'created'=>date('Y-m-d',$join_time),
			'creator'=>$username,
			'publisher'=>'', // blank means same as creator
			'modified'=>'',
			'type'=>'Member',
			'title'=>'',
			'identifier'=>'_SEARCH:members:view:'.strval($member_id_of),
			'description'=>'',
			'image'=>$avatar_url,
		);

		// Look up member's clubs
		$clubs=array();
		if (addon_installed('ocf_clubs'))
		{
			$club_ids=$GLOBALS['FORUM_DRIVER']->get_members_groups($member_id_of,true);
			$club_rows=list_to_map('id',$GLOBALS['FORUM_DB']->query_select('f_groups',array('*'),array('g_is_private_club'=>1),'',200));
			if (count($club_rows)==200) $club_rows=NULL;
			foreach ($club_ids as $club_id)
			{
				if (is_null($club_rows))
				{
					$club_rows=list_to_map('id',$GLOBALS['FORUM_DB']->query_select('f_groups',array('*'),array('g_is_private_club'=>1,'id'=>$club_id),'',200));
					if (!array_key_exists($club_id,$club_rows)) continue;
					$club_row=$club_rows[$club_id];
					$club_rows=NULL;
				} else
				{
					if (!array_key_exists($club_id,$club_rows)) continue;
					$club_row=$club_rows[$club_id];
				}

				$club_name=get_translated_text($club_row['g_name']);
				$club_forum=$GLOBALS['FORUM_DB']->query_value_null_ok('f_forums f LEFT JOIN '.$GLOBALS['FORUM_DB']->get_table_prefix().'translate t ON t.id=f.f_description','f.id',array('text_original'=>do_lang('FORUM_FOR_CLUB',$club_name)));

				$clubs[]=array(
					'CLUB_NAME'=>$club_name,
					'CLUB_ID'=>strval($club_row['id']),
					'CLUB_FORUM'=>is_null($club_forum)?'':strval($club_forum),
				);
			}
		}

		$content=do_template('OCF_MEMBER_PROFILE_ABOUT',
			array('_GUID'=>'fodfjdsfjsdljfdls',
					'CLUBS'=>$clubs,
					'REMOTE'=>$remote,
					'RIGHT_MARGIN'=>$right_margin,
					'AVATAR_WIDTH'=>strval($a).'px',
					'PHOTO_WIDTH'=>strval($b).'px',
					'MOST_ACTIVE_FORUM'=>$most_active_forum,
					'TIME_FOR_THEM'=>$time_for_them,
					'TIME_FOR_THEM_RAW'=>strval($time_for_them_raw),
					'SUBMIT_DAYS_AGO'=>integer_format($submit_days_ago),
					'SUBMIT_TIME_RAW'=>strval($last_submit_time),
					'LAST_VISIT_TIME_RAW'=>strval($last_visit_time),
					'ONLINE_NOW'=>$online_now,
					'BANNED'=>$banned,
					'USER_AGENT'=>$user_agent,
					'OPERATING_SYSTEM'=>$operating_system,
					'DOB'=>$dob,
					'IP_ADDRESS'=>$ip_address,
					'COUNT_POSTS'=>$count_posts,
					'COUNT_POINTS'=>$count_points,
					'PRIMARY_GROUP'=>$primary_group,
					'PRIMARY_GROUP_ID'=>strval($primary_group_id),
					'PHOTO_URL'=>$photo_url,
					'PHOTO_THUMB_URL'=>$photo_thumb_url,
					'EMAIL_ADDRESS'=>$GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id_of,'m_email_address'),
					'AVATAR_URL'=>$avatar_url,
					'SIGNATURE'=>$signature,
					'JOIN_DATE'=>$join_date,
					'JOIN_DATE_RAW'=>strval($join_time),
					'CUSTOM_FIELDS'=>$custom_fields,
					'ACTIONS_contact'=>$actions['contact'],
					'ACTIONS_profile'=>$actions['profile'],
					'ACTIONS_views'=>$actions['views'],
					'ACTIONS_usage'=>$actions['usage'],
					'ACTIONS_content'=>$actions['content'],
					'USERNAME'=>$username,
					'MEMBER_ID'=>strval($member_id_of),
					'SECONDARY_GROUPS'=>$secondary_groups,
					'VIEW_PROFILES'=>has_specific_permission($member_id_viewing,'view_profiles'),
					'ON_PROBATION'=>$on_probation,
					'EXTRA_INFO_DETAILS'=>$info_details,
					'EXTRA_SECTIONS'=>$extra_sections,
			)
		);

		return array($title,$content,$order);
	}

}

