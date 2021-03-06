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
 * @package		jwplayer
 */

class Hook_addon_registry_jwplayer
{

	/**
	 * Get a list of file permissions to set
	 *
	 * @return array			File permissions to set
	 */
	function get_chmod_array()
	{
		return array();
	}
	
	/**
	 * Get the version of ocPortal this addon is for
	 *
	 * @return float			Version number
	 */
	function get_version()
	{
		return ocp_version_number();
	}

	/**
	 * Get the description of the addon
	 *
	 * @return string			Description of the addon
	 */
	function get_description()
	{
		return 'An integrated version of the jwplayer video player (provided under ocProducts\' redistribution license). Handles FLV files, h.264 files (mp4), and WebM files.';
	}

	/**
	 * Get a mapping of dependency types
	 *
	 * @return array			File permissions to set
	 */
	function get_dependencies()
	{
		return array(
			'requires'=>array(),
			'recommends'=>array(),
			'conflicts_with'=>array(),
			'previously_in_addon'=>array('core'),
		);
	}

	/**
	 * Get a list of files that belong to this addon
	 *
	 * @return array			List of files
	 */
	function get_file_list()
	{
		return array(

			'sources/hooks/systems/addon_registry/jwplayer.php',
			'data/flvplayer.swf',
			'ATTACHMENT_FLV.tpl',
			'COMCODE_FLV.tpl',
			'GALLERY_VIDEO_FLV.tpl',
			'JAVASCRIPT_JWPLAYER.tpl',
		);
	}

	/**
	* Get mapping between template names and the method of this class that can render a preview of them
	*
	* @return array			The mapping
	*/
	function tpl_previews()
	{
		return array(
				'COMCODE_FLV.tpl'=>'comcode_flv',
				'ATTACHMENT_FLV.tpl'=>'attachment_flv',
				'GALLERY_VIDEO_FLV.tpl'=>'gallery_video_flv',
				);
	}

	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__comcode_flv()
	{
		return array(
			lorem_globalise(
				do_lorem_template('COMCODE_FLV',array(
					'URL'=>placeholder_url(),
					'WIDTH'=>placeholder_number(),
					'HEIGHT'=>placeholder_number(),
						)
			),NULL,'',true),
		);
	}

	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__attachment_flv()
	{
		return array(
			lorem_globalise(
				do_lorem_template('ATTACHMENT_FLV',array(
							'SCRIPT'=>placeholder_javascript(),
							'ID'=>placeholder_id(),
							'A_WIDTH'=>placeholder_number(),
							'A_HEIGHT'=>placeholder_number(),
							'A_DESCRIPTION'=>lorem_paragraph_html(),
							'SUP_PARAMS'=>placeholder_blank(),
							'FORUM_DB_BIN'=>placeholder_blank(),
							'MIME_TYPE'=>lorem_word(),
						)
			),NULL,'',true),
		);
	}

	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__gallery_video_flv()
	{
		return array(
			lorem_globalise(
				do_lorem_template('GALLERY_VIDEO_FLV',array(
					'URL'=>placeholder_url(),
					'THUMB_URL'=>placeholder_url(),
					'WIDTH'=>placeholder_number(),
					'HEIGHT'=>placeholder_number(),
					'LENGTH'=>placeholder_number()
						)
			),NULL,'',true),
		);
	}

}
