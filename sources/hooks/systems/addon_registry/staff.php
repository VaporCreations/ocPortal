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
 * @package		staff
 */

class Hook_addon_registry_staff
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
		return 'Choose and display a selection of staff from the super-administator/super-moderator usergroups. This is useful for multi-site networks, where members with privileges may not be considered staff on all websites on the network.';
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

			'sources/hooks/systems/config_default/is_on_staff_filter.php',
			'sources/hooks/systems/config_default/is_on_sync_staff.php',
			'sources/hooks/systems/config_default/staff_text.php',
			'sources/hooks/systems/addon_registry/staff.php',
			'sources/hooks/systems/do_next_menus/staff.php',
			'STAFF_SCREEN.tpl',
			'STAFF_EDIT_WRAPPER.tpl',
			'STAFF_ADMIN_SCREEN.tpl',
			'adminzone/pages/modules/admin_staff.php',
			'site/pages/modules/staff.php',
			'lang/EN/staff.ini',
			'themes/default/images/pagepics/staff.png',
			'themes/default/images/bigicons/staff.png',
			'sources/hooks/systems/ocf_cpf_filter/staff_filter.php',
		);
	}


	/**
	* Get mapping between template names and the method of this class that can render a preview of them
	*
	* @return array                 The mapping
	*/
	function tpl_previews()
	{
	   return array(
		'STAFF_SCREEN.tpl'=>'staff_screen',
		'STAFF_EDIT_WRAPPER.tpl'=>'administrative__staff_admin_screen',
		'STAFF_ADMIN_SCREEN.tpl'=>'administrative__staff_admin_screen',
		);
	}

	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__administrative__staff_admin_screen()
	{
		$available = new ocp_tempcode();
		foreach (placeholder_array() as $k=>$v)
		{
			$available->attach(do_lorem_template('STAFF_EDIT_WRAPPER',array('FORM'=>placeholder_form(),'NAME'=>lorem_word())));
		}

		return array(
			lorem_globalise(
				do_lorem_template('STAFF_ADMIN_SCREEN',array(
					'TITLE'=>lorem_title(),
					'TEXT'=>lorem_sentence_html(),
					'FORUM_STAFF'=>$available,
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
	function tpl_preview__staff_screen()
	{
		return array(
			lorem_globalise(
				do_lorem_template('STAFF_SCREEN',array(
					'TITLE'=>lorem_title(),
					'REAL_NAME'=>lorem_phrase(),
					'ROLE'=>lorem_phrase(),
					'ADDRESS'=>lorem_phrase(),
					'NAME'=>lorem_word(),
					'MEMBER_ID'=>placeholder_id(),
					'PROFILE_URL'=>placeholder_url(),
					'ALL_LINK'=>placeholder_url(),
						)
                        ),NULL,'',true),
                  );
	}
}