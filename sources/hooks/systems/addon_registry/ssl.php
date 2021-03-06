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
 * @package		ssl
 */

class Hook_addon_registry_ssl
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
		return 'Choose which pages of your website run over HTTPS (SSL). Requires an SSL certificate to be installed on the webserver, and a dedicated IP address.';
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

			'sources/hooks/systems/addon_registry/ssl.php',
			'SSL_CONFIGURATION_SCREEN.tpl',
			'SSL_CONFIGURATION_ENTRY.tpl',
			'adminzone/pages/modules/admin_ssl.php',
			'sources/hooks/systems/do_next_menus/ssl.php',
			'themes/default/images/pagepics/ssl.png',
			'themes/default/images/bigicons/ssl.png',
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
		'SSL_CONFIGURATION_ENTRY.tpl'=>'administrative__ssl_configuration_screen',
		'SSL_CONFIGURATION_SCREEN.tpl'=>'administrative__ssl_configuration_screen',
		);
	}

	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array                 Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__administrative__ssl_configuration_screen()
	{
		require_lang('security');
		$content = new ocp_tempcode();
		$k = 0;
		foreach (placeholder_array() as $k=>$v)
		{
			foreach (placeholder_array() as $k=>$v2)
			{
				$k++;
				$content->attach(do_lorem_template('SSL_CONFIGURATION_ENTRY',array('TICKED'=>lorem_word(),'PAGE'=>$v,'ZONE'=>$v2)));
			}
		}

	   return array(
	         lorem_globalise(do_lorem_template('SSL_CONFIGURATION_SCREEN',array(
						'URL'=>placeholder_url(),
						'TITLE'=>lorem_title(),
						'CONTENT'=>$content,
							)
	         ),NULL,'',true),
	   );
	}
}