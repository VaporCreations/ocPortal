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
 * @package		unit_testing
 */

/**
 * ocPortal test case class (unit testing).
 */
class bash_parser_test_set extends ocp_test_case
{
	function testValidCode()
	{
		require_code('files2');
		$php_path=find_php_path();
		$contents=get_directory_contents(get_file_base());
		foreach ($contents as $c)
		{
			if ((substr($c,-4)=='.php') && (basename($c)!='errorlog.php') && (basename($c)!='phpstub.php') && (basename($c)!='permissioncheckslog.php'))
			{
				$message=shell_exec($php_path.' -l '.$c);
				$this->assertTrue(strpos($message,'No syntax errors detected')!==false,$message.' ('.$c.')');
			}
		}
	}
}
