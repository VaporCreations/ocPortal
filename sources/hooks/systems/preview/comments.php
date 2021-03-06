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
 * @package		core_feedback_features
 */

class Hook_Preview_comments
{

	/**
	 * Find whether this preview hook applies.
	 *
	 * @return array			Triplet: Whether it applies, the attachment ID type, whether the forum DB is used [optional]
	 */
	function applies()
	{
		$applies=((get_param('page')!='topicview') && (post_param_integer('_comment_form_post',0)==1) && (is_null(post_param('hidFileID_file0',NULL))) && (is_null(post_param('file0',NULL))));
		return array($applies,NULL,false);
	}

	/**
	 * Standard modular run function for preview hooks.
	 *
	 * @return array			A pair: The preview, the updated post Comcode
	 */
	function run()
	{
		// Find review, if there is one
		$individual_review_ratings=array();
		$review_rating=post_param('review_rating','');
		if ($review_rating!='')
		{
			$individual_review_ratings['']=array(
				'REVIEW_TITLE'=>'',
				'REVIEW_RATING'=>$review_rating,
			);
		}

		$highlight=false;
		$datetime_raw=time();
		$datetime=get_timezoned_date(time());
		$poster_link=$GLOBALS['FORUM_DRIVER']->member_profile_hyperlink(get_member());
		$poster_name=$GLOBALS['FORUM_DRIVER']->get_username(get_member());
		$title=post_param('title','');
		$post=comcode_to_tempcode(post_param('post'));
		$tpl=do_template('POST',array('INDIVIDUAL_REVIEW_RATINGS'=>$individual_review_ratings,'HIGHLIGHT'=>$highlight,'TITLE'=>$title,'TIME_RAW'=>strval($datetime_raw),'TIME'=>$datetime,'POSTER_LINK'=>$poster_link,'POSTER_NAME'=>$poster_name,'POST'=>$post));
		return array($tpl,NULL);
	}

}
