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
 * @package		core_html_abstractions
 */

class Hook_addon_registry_core_html_abstractions
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
		return 'Core rendering functionality.';
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

			'sources/hooks/systems/addon_registry/core_html_abstractions.php',
			'HTML_REFRESH.tpl',
			'JS_REFRESH.tpl',
			'META_REFRESH_LINE.tpl',
			'ANCHOR.tpl',
			'HYPERLINK.tpl',
			'HYPERLINK_POPUP_WINDOW.tpl',
			'HYPERLINK_TOOLTIP.tpl',
			'HYPERLINK_BUTTON.tpl',
			'HYPERLINK_EMAIL.tpl',
			'DIV.tpl',
			'PARAGRAPH.tpl',
			'FLOATER.tpl',
			'BASIC_HTML_WRAP.tpl',
			'STYLED_HTML_WRAP.tpl',
			'POOR_XHTML_WRAPPER.tpl',
			'WITH_WHITESPACE.tpl',
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
				'POOR_XHTML_WRAPPER.tpl'=>'poor_xhtml_wrapper',
				'JS_REFRESH.tpl'=>'js_refresh',
				'ANCHOR.tpl'=>'anchor',
				'STYLED_HTML_WRAP.tpl'=>'styled_html_wrap',
				'META_REFRESH_LINE.tpl'=>'meta_refresh_line',
				'HYPERLINK_POPUP_WINDOW.tpl'=>'hyperlink_popup_window',
				'BASIC_HTML_WRAP.tpl'=>'basic_html_wrap',
				'HTML_REFRESH.tpl'=>'html_refresh',
				'FLOATER.tpl'=>'floater',
				'HYPERLINK.tpl'=>'hyperlink',
				'HYPERLINK_BUTTON.tpl'=>'hyperlink_button',
				'HYPERLINK_EMAIL.tpl'=>'hyperlink_email',
				'HYPERLINK_TOOLTIP.tpl'=>'hyperlink_tooltip',
				'PARAGRAPH.tpl'=>'paragraph',
				'DIV.tpl'=>'div',
				'WITH_WHITESPACE.tpl'=>'with_whitespace',
				);
	}

	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__poor_xhtml_wrapper()
	{
		return array(
			lorem_globalise(
				do_lorem_template('POOR_XHTML_WRAPPER',array(
					'CONTENT'=>lorem_phrase_html(),
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
	function tpl_preview__js_refresh()
	{
		return array(
			lorem_globalise(
				do_lorem_template('JS_REFRESH',array(
					'FORM_NAME'=>lorem_word_html(),
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
	function tpl_preview__anchor()
	{
		return array(
			lorem_globalise(
				do_lorem_template('ANCHOR',array(
					'NAME'=>lorem_word(),
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
	function tpl_preview__styled_html_wrap()
	{
		return array(
			lorem_globalise(
				do_lorem_template('STYLED_HTML_WRAP',array(
					'TITLE'=>lorem_phrase(),
					'CONTENT'=>lorem_phrase_html(),
					'NOFOLLOW'=>lorem_word(),
					'UNINSTALL'=>lorem_word(),
					'UNINSTALL_SCREEN'=>lorem_word(),
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
	function tpl_preview__meta_refresh_line()
	{
		return array(
			lorem_globalise(
				do_lorem_template('META_REFRESH_LINE',array(
					'URL'=>placeholder_url(),
					'TIME'=>placeholder_date_raw(),
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
	function tpl_preview__hyperlink_popup_window()
	{
		return array(
			lorem_globalise(
				do_lorem_template('HYPERLINK_POPUP_WINDOW',array(
					'TITLE'=>lorem_phrase(),
					'CAPTION'=>lorem_phrase(),
					'URL'=>placeholder_url(),
					'WIDTH'=>placeholder_number(),
					'HEIGHT'=>placeholder_number(),
					'REL'=>lorem_phrase(),
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
	function tpl_preview__basic_html_wrap()
	{
		return array(
			lorem_globalise(
				do_lorem_template('BASIC_HTML_WRAP',array(
					'TITLE'=>lorem_phrase(),
					'CONTENT'=>lorem_phrase(),
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
	function tpl_preview__html_refresh()
	{
		return array(
			lorem_globalise(
				do_lorem_template('HTML_REFRESH',array(
					'URL'=>placeholder_url(),
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
	function tpl_preview__floater()
	{
		return array(
			lorem_globalise(
				do_lorem_template('FLOATER',array(
					'FLOAT'=>'left',
					'CONTENT'=>lorem_phrase(),
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
	function tpl_preview__hyperlink_email()
	{
		return array(
			lorem_globalise(
				do_lorem_template('HYPERLINK_EMAIL',array(
					'VALUE'=>lorem_phrase(),
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
	function tpl_preview__hyperlink_tooltip()
	{
		return array(
			lorem_globalise(
				do_lorem_template('HYPERLINK_TOOLTIP',array(
					'TOOLTIP'=>lorem_phrase(),
					'CAPTION'=>lorem_phrase(),
					'URL'=>placeholder_url(),
					'NEW_WINDOW'=>lorem_phrase(),
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
	function tpl_preview__paragraph()
	{
		return array(
			lorem_globalise(
				do_lorem_template('PARAGRAPH',array(
					'TEXT'=>lorem_sentence_html(),
					'CLASS'=>lorem_phrase(),
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
	function tpl_preview__div()
	{
		return array(
			lorem_globalise(
				do_lorem_template('DIV',array(
					'TEMPCODE'=>lorem_phrase(),
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
	function tpl_preview__with_whitespace()
	{
		return array(
			lorem_globalise(
				do_lorem_template('WITH_WHITESPACE',array(
					'CONTENT'=>lorem_phrase(),
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
	function tpl_preview__hyperlink()
	{
		return array(
			lorem_globalise(
				do_lorem_template('HYPERLINK',array(
					'REL'=>NULL,
					'POST_DATA'=>NULL,
					'ACCESSKEY'=>NULL,
					'NEW_WINDOW'=>false,
					'TITLE'=>lorem_phrase(),
					'URL'=>placeholder_url(),
					'CAPTION'=>lorem_word(),
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
	function tpl_preview__hyperlink_button()
	{
		return array(
			lorem_globalise(
				do_lorem_template('HYPERLINK_BUTTON',array(
					'REL'=>NULL,
					'POST_DATA'=>lorem_word(),
					'ACCESSKEY'=>NULL,
					'NEW_WINDOW'=>false,
					'TITLE'=>lorem_phrase(),
					'URL'=>placeholder_url(),
					'CAPTION'=>lorem_word(),
					)
			),NULL,'',true),
		);
	}
}