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
 * @package		booking
 */

/**
 * Show bookables ical feed.
 */
function bookables_ical_script()
{
	require_code('calendar_ical');

	require_lang('booking');

	@ini_set('ocproducts.xss_detect','0');

	header('Content-Type: text/calendar');
	header('Content-Disposition: filename="bookables_export.ics"');

	if (function_exists('set_time_limit')) @set_time_limit(0);

	$query='SELECT * FROM '.get_table_prefix().'bookable WHERE enabled=1';
	$filter=get_param('filter','*');
	require_code('ocfiltering');
	$query.=' AND '.ocfilter_to_sqlfragment($filter,'id');
	$events=$GLOBALS['SITE_DB']->query($query);
	echo "BEGIN:VCALENDAR\n";
	echo "VERSION:2.0\n";
	echo "PRODID:-//ocProducts/ocPortal//NONSGML v1.0//EN\n";
	echo "CALSCALE:GREGORIAN\n";
	echo "X-WR-CALNAME:".ical_escape(get_site_name().': '.do_lang('BOOKABLE_EVENTS'))."\n";

	foreach ($events as $event)
	{
		echo "BEGIN:VEVENT\n";

		echo "DTSTAMP:".date('Ymd',time())."T".date('His',$event['add_date'])."\n";
		echo "CREATED:".date('Ymd',time())."T".date('His',$event['add_date'])."\n";
		if (!is_null($event['e_edit_date'])) echo "LAST-MODIFIED:".date('Ymd',time())."T".date('His',$event['edit_date'])."\n";

		echo "SUMMARY:".ical_escape(get_translated_text($event['title']))."\n";
		$description=get_translated_text($event['description']);
		echo "DESCRIPTION:".ical_escape($description)."\n";

		if (!is_guest($event['submitter']))
			echo "ORGANIZER;CN=".ical_escape($GLOBALS['FORUM_DRIVER']->get_username($event['submitter'])).";DIR=".ical_escape($GLOBALS['FORUM_DRIVER']->member_profile_link($event['submitter'])).":MAILTO:".ical_escape($GLOBALS['FORUM_DRIVER']->get_member_email_address($event['submitter']))."\n";
		echo "CATEGORIES:".ical_escape(get_translated_text($event['categorisation']))."\n";
		echo "CLASS:".(($event['price']==0.0)?'PUBLIC':'PRIVATE')."\n";
		echo "STATUS:".(($event['enabled']==1)?'CONFIRMED':'TENTATIVE')."\n";
		echo "UID:".ical_escape(strval($event['id']).'-bookable@'.get_base_url())."\n";
		$_url=build_url(array('page'=>'booking','type'=>'misc','filter'=>$event['id']),get_module_zone('booking'),NULL,false,false,true);
		$url=$_url->evaluate();
		echo "URL:".ical_escape($url)."\n";

		$time=mktime(0,0,0,$event['month'],$event['day'],$event['year']);
		$time2=NULL;
		if ($event['cycle_type']!='none')
		{
			$parts=explode(' ',$event['cycle_type']);
			if (count($parts)==1)
			{
				echo "DTSTART;TZ=".get_site_timezone().":".date('Ymd',$time)."\n";
				$recurrence_code='FREQ='.strtoupper($parts[0]);
				echo "RRULE:".$recurrence_code."\n";
			} else
			{
				for ($i=0;$i<strlen($parts[1]);$i++)
				{
					switch ($parts[0])
					{
						case 'daily':
							$time+=60*60*24;
							if (!is_null($time2)) $time2+=60*60*24;
							break;
						case 'weekly':
							$time+=60*60*24*7;
							if (!is_null($time2)) $time2+=60*60*24*7;
							break;
						case 'monthly':
							$days_in_month=intval(date('D',mktime(0,0,0,intval(date('m',$time))+1,0,intval(date('Y',$time)))));
							$time+=60*60*$days_in_month;
							if (!is_null($time2)) $time2+=60*60*$days_in_month;
							break;
						case 'yearly':
							$days_in_year=intval(date('Y',mktime(0,0,0,0,0,intval(date('Y',$time))+1)));
							$time+=60*60*24*$days_in_year;
							if (!is_null($time2)) $time2+=60*60*24*$days_in_year;
							break;
					}
					if ($parts[1][$i]!='0')
					{
						echo "DTSTART:".date('Ymd',$time)."\n";
						$recurrence_code='FREQ='.strtoupper($parts[0]);
						echo "RRULE:".$recurrence_code.";INTERVAL=".strval(strlen($parts[1])).";COUNT=1\n";
					}
				}
			}
		} else
		{
			echo "DTSTART:".date('Ymd',$time)."\n";

			if ($GLOBALS['FORUM_DRIVER']->is_staff(get_member()))
			{
				$attendees=$GLOBALS['SITE_DB']->query_select('booking',array('*'),array('bookable_id'=>$event['id']),'',5000/*reasonable limit*/);
				if (count($attendees)==5000) $attendees=array();
				foreach ($attendees as $attendee)
				{
					if (!is_guest($event['member_id']))
						echo "ATTENDEE;CN=".ical_escape($GLOBALS['FORUM_DRIVER']->get_username($attendee['member_id'])).";DIR=".ical_escape($GLOBALS['FORUM_DRIVER']->member_profile_link($attendee['member_id'])).":MAILTO:".ical_escape($GLOBALS['FORUM_DRIVER']->get_member_email_address($attendee['member_id']))."\n";
				}
			}
		}

		echo "END:VEVENT\n";
	}
	echo "END:VCALENDAR\n";
	exit();
}

/**
 * Show bookings ical feed (NB: the event type for this is admins only, unless edited).
 */
function bookings_ical_script()
{
	if (get_param('pass')!=md5('booking_salt_'.$GLOBALS['SITE_INFO']['admin_password'])) access_denied('I_ERROR');
	
	require_code('calendar_ical');

	@ini_set('ocproducts.xss_detect','0');

	header('Content-Type: text/calendar');
	header('Content-Disposition: filename="bookings_export.ics"');

	if (function_exists('set_time_limit')) @set_time_limit(0);

	$time=get_param_integer('from',time());

	$id=get_param_integer('id');
	$where='bookable_id='.strval($id).' AND (year>'.date('Y',$time).' OR (date='.date('Y',$time).' AND month>'.date('m',$time).') OR (date='.date('Y',$time).' AND month='.date('m',$time).' AND day='.date('d',$time).'))';
	$bookings=$GLOBALS['SITE_DB']->query('SELECT * FROM '.get_table_prefix().'booking WHERE '.$where.' ORDER BY booked_at DESC',10000/*reasonable limit*/);
	echo "BEGIN:VCALENDAR\n";
	echo "VERSION:2.0\n";
	echo "PRODID:-//ocProducts/ocPortal//NONSGML v1.0//EN\n";
	echo "CALSCALE:GREGORIAN\n";
	$categories=array();
	$_categories=$GLOBALS['SITE_DB']->query_select('bookable',array('*'));
	foreach ($_categories as $category)
	{
		$categories[$category['id']]=get_translated_text($category['title']);
	}
	if ((is_null($filter)) || (!array_key_exists($filter,$categories)))
	{
		echo "X-WR-CALNAME:".ical_escape(get_site_name().': '.do_lang('BOOKINGS'))."\n";
	} else
	{
		echo "X-WR-CALNAME:".ical_escape(get_site_name().': '.do_lang('BOOKINGS').': '.$categories[$filter])."\n";
	}

	foreach ($bookings as $booking)
	{
		echo "BEGIN:VEVENT\n";

		echo "DTSTAMP:".date('Ymd',time())."T".date('His',$booking['booked_at'])."\n";
		echo "CREATED:".date('Ymd',time())."T".date('His',$booking['booked_at'])."\n";

		echo "SUMMARY:".ical_escape($booking['code_allocation'])."\n";
		$description=$booking['notes'];
		$supplements=$GLOBALS['SITE_DB']->query_select('booking_supplement a JOIN bookable_supplement b ON a.supplement_id=b.id',array('quantity','notes','title'),array(
			'booking_id'=>$booking['id'],
		));
		foreach ($supplements as $supplement)
		{
			$description.="\n\n+ ".get_translated_text($supplement['title']).'x'.integer_format($supplement['quantity']);
			if ($supplement['notes']!='') $description.=' ('.$supplement['notes'].')';
		}
		echo "DESCRIPTION:".ical_escape($description)."\n";

		if (!is_guest($booking['member_id']))
			echo "ORGANIZER;CN=".ical_escape($GLOBALS['FORUM_DRIVER']->get_username($booking['member_id'])).";DIR=".ical_escape($GLOBALS['FORUM_DRIVER']->member_profile_link($booking['member_id'])).":MAILTO:".ical_escape($GLOBALS['FORUM_DRIVER']->get_member_email_address($booking['member_id']))."\n";
		echo "CATEGORIES:".ical_escape($categories[$booking['e_type']])."\n";
		echo "CLASS:PRIVATE\n";
		echo "STATUS:".(($booking['paid_at']!==NULL)?'CONFIRMED':'TENTATIVE')."\n";
		echo "UID:".ical_escape(strval($booking['id']).'-booking@'.get_base_url())."\n";
		$_url=build_url(array('page'=>'cms_booking','type'=>'_ed','id'=>$booking['id']),get_module_zone('cms_booking'),NULL,false,false,true);
		$url=$_url->evaluate();
		echo "URL:".ical_escape($url)."\n";
		$_url=build_url(array('page'=>'cms_booking','type'=>'_edit_for_member','id'=>$booking['submitter']),get_module_zone('cms_booking'),NULL,false,false,true);
		$url=$_url->evaluate();
		echo "URL:".ical_escape($url)."\n";

		$time=mktime(0,0,0,$booking['month'],$booking['day'],$booking['year']);
		echo "DTSTART:".date('Ymd',$time)."\n";

		echo "END:VEVENT\n";
	}
	echo "END:VCALENDAR\n";
	exit();
}