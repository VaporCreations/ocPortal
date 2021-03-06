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
 * @package		core
 */

if (!function_exists('critical_error'))
{
	/**
	 * Exit with a nicely formatted critical error.
	 *
	 * @param  string			The error message code
	 * @param  ?string		Relayed additional details (NULL: nothing relayed)
	 * @param  boolean		Whether to actually exit
	 */
	function critical_error($code,$relay=NULL,$exit=true)
	{
		error_reporting(0);

		if (!headers_sent())
		{
			if ((function_exists('browser_matches')) && ((is_null($relay)) || (strpos($relay,'Allowed memory')===false)))
				if ((!browser_matches('ie')) && (strpos(ocp_srv('SERVER_SOFTWARE'),'IIS')===false)) header('HTTP/1.0 500 Internal server error');
		}

		$error='Unknown critical error type: this should not happen, so please report this to ocProducts.';
	
		switch ($code)
		{
			case 'MISSING_SOURCE':
				$error='A source-code ('.$relay.') file is missing.';
				break;
			case 'PASSON':
				$error=$relay;
				break;
			case 'MEMBER_BANNED':
				$error='The member you are masquerading as has been banned. We cannot finish initialising the virtualised environment for this reason.';
				break;
			case 'BANNED':
				$error='The IP address you are accessing this website from ('.get_ip_address().') has been banished from this website. If you believe this is a mistake, contact the staff to have it resolved (typically, postmaster@'.get_domain().' will be able to reach them).</div>'.chr(10).'<div>If you are yourself staff, you should be able to unban yourself by editing the <kbd>usersubmitban_ip</kbd> table in a database administation tool, by removing rows that qualify against yourself. This error is raised to a critical error to reduce the chance of this IP address being able to further consume server resources.';
				break;
	/*		case 'PHP':
				$error='<p>This is a PHP error.</div>'.chr(10).'<div style="padding-left: 50px">'.$relay;
				break;
	*/
			case 'TEST':
				$error='This is a test error.';
				break;
			case 'BUSY':
				$error='This is a less-critical error that has been elevated for quick dismissal due to high server load.</div>'.chr(10).'<div style="padding-left: 50px">'.$relay;
				break;
			case 'EMERGENCY':
				$error='This is an error that has been elevated to critical error status because it occured during the primary error mechanism reporting system itself (possibly due to it occuring within the standard output framework). It may be masking a secondary error that occured before this, but was never output - if so, it is likely strongly related to this one, thus fixing this will fix the other.</div>'.chr(10).'<div style="padding-left: 50px">'.$relay;
				break;
			case 'RELAY':
				$error='This is a relayed critical error, which means that this less-critical error has occured during startup, and thus halted startup.</div>'.chr(10).'<div style="padding-left: 50px">'.$relay;
				break;
			case 'FILE_DOS':
				$error='This website was prompted to download a file ('.htmlentities($relay).') which seemingly has a never-ending chain of redirections. Because this could be a denial of service attack, execution has been terminated.';
				break;
			case 'DATABASE_FAIL':
				$error='The website\'s first database query (checking the page request is not from a banned IP address) has failed. This almost always means that the database is not set up correctly, which in turns means that either backend database configuration has changed (perhaps the database has been emptied), or the configuration file (info.php) has been incorrectly altered (perhaps to point to an empty database), or you have moved servers and not updated your info.php settings properly or placed your database. It could also mean that the <kbd>'.get_table_prefix().'usersubmitban_ip</kbd> table or <kbd>'.get_table_prefix().'config</kbd> table alone is missing or corrupt, but this is unlikely. As this is an error due to the website\'s environment being externally altered by unknown means, the website cannot continue to function or solve the problem itself.';
				break;
			case 'INFO.PHP':
				$install_url='install.php';
				if (!file_exists($install_url)) $install_url='../install.php';
				if (file_exists($install_url))
				{
					$likely='ocPortal files have been placed, yet installation not completed. To install ocPortal, <a href="'.$install_url.'">run the installer</a>.';
				} else
				{
					$likely='ocPortal files have been placed by direct copying from a non-standard source that included neither a configuration file nor installation script, or info.php has become corrupt after installation. The installer (install.php) is not present: it is advised that you replace info.php from backup, or if you have not yet installed, use an official ocProducts installation package.';
				}
				$error='The top-level configuration file (info.php) is either not-present or empty. This file is created upon installation, and the likely cause of this error is that '.$likely;
				break;
			case 'INFO.PHP_CORRUPTED':
				$error='The top-level configuration file (info.php) appears to be corrupt. Perhaps it was incorrectly uploaded, or a typo was made. It must be valid PHP code.';
				break;
			case 'CRIT_LANG':
				$error='The most basic critical error language file (lang/'.fallback_lang().'/critical_error.ini) is missing. It is likely that other files are also, for whatever reason, missing from this ocPortal installation.';
				break;
		}

		$edit_url='config_editor.php';
		if (!file_exists($edit_url)) $edit_url='../'.$edit_url;
		if (isset($GLOBALS['SITE_INFO']['base_url'])) $edit_url=$GLOBALS['SITE_INFO']['base_url'].'/config_editor.php';

		$extra='';

		if ((function_exists('debug_backtrace')) && (strpos($error,'Allowed memory')===false) && ((is_null($relay)) || (strpos($relay,'Stack trace')===false)) && (function_exists('ocp_srv')) && (((ocp_srv('REMOTE_ADDR')==ocp_srv('SERVER_ADDR')) && (ocp_srv('HTTP_X_FORWARDED_FOR')=='')) || (preg_match('#^localhost(\.|\:|$)#',ocp_srv('HTTP_HOST'))!=0) && (function_exists('get_base_url')) && (substr(get_base_url(),0,16)=='http://localhost')))
		{
			$_trace=debug_backtrace();
			$extra='<div class="medborder medborder_box"><h2>Stack trace&hellip;</h2>';
			foreach ($_trace as $stage)
			{
				$traces='';
				foreach ($stage as $key=>$value)
				{
					if ((is_object($value) && (is_a($value,'ocp_tempcode'))) || (is_array($value) && (strlen(serialize($value))>500)))
					{
						$_value=gettype($value);
					} else
					{
						if (strpos($error,'Allowed memory')!==false) // Actually we don't call this code path any more, as stack trace is useless (comes from the catch_fatal_errors function)
						{
							$_value=gettype($value);
							switch ($_value)
							{
								case 'integer':
									$_value=strval($value);
									break;
								case 'string':
									$_value=$value;
									break;
							}
						} else
						{
							@ob_start();
							if (function_exists('var_export'))
								/*var_dump*/var_export($value);
							$_value=ob_get_contents();
							ob_end_clean();
						}
					}

					global $SITE_INFO;
					if ((isset($SITE_INFO['db_site_password'])) && (strlen($SITE_INFO['db_site_password'])>4))
						$_value=str_replace($SITE_INFO['db_site_password'],'(password removed)',$_value);
					if ((isset($SITE_INFO['db_forums_password'])) && (strlen($SITE_INFO['db_forums_password'])>4))
						$_value=str_replace($SITE_INFO['db_forums_password'],'(password removed)',$_value);

					$traces.=ucfirst($key).' -> '.htmlentities($_value).'<br />'.chr(10);
				}
				$extra.='<p>'.$traces.'</p>'.chr(10);
			}
			$extra.='</div>';
		}

		$headers_sent=headers_sent();
		if (!$headers_sent)
		{
			@header('Content-type: text/html');
			echo <<<END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="EN" lang="EN">
<head>
	<title>Critical error</title>
	<style type="text/css">
END;
if (strpos($error,'Allowed memory')===false)
{
	$file_contents=file_get_contents($GLOBALS['FILE_BASE'].'/themes/default/css/global.css');
} else
{
	$file_contents=''; // Can't load files if dying due to memory limit
}
$css=((preg_replace('#/\*\s*\*/\s*#','',str_replace('url(\'\')','none',str_replace('url("")','none',preg_replace('#\{\$[^\}]*\}#','',$file_contents))))));
echo htmlentities($css);
echo <<<END
		.main_page_title { text-decoration: underline; display: block; min-height: 42px; padding: 3px 0 0 0; }
		a[target="_blank"], a[onclick$="window.open"] { padding-right: 0; }
	</style>
</head>
<body><div class="global_middle">
END;
		}
		echo '<h1 class="main_page_title">Critical error &ndash; bailing out</h1>'.chr(10).'<div class="red_alert">'.$error.'</div>'.chr(10);
		flush();
		if ((strpos($_SERVER['PHP_SELF'],'upgrader.php')!==false) && (strpos($error,'Allowed memory')===false))
		{
			require_code('upgrade');
			echo '<div class="medborder medborder_box"><h2>Integrity check</h2><p><strong>If you think this problem could be due to corruption caused by a failed upgrade (e.g. time-out during extraction), check the following integrity check&hellip;</strong></p>',run_integrity_check(true),'</div><br />';
		}
		flush();
		echo $extra,chr(10);
		echo '<p>Details here are intended only for the website/system-administrator, not for regular website users.<br />&raquo; <strong>If you are a regular website user, please let the website staff deal with this problem.</strong></p>'.chr(10).'<p class="associated_details">Depending on the error, and only if the website installation finished, you may need to <a href="#" onclick="if (!window.confirm(\'Are you staff on this site?\')) return false; this.href=\''.htmlentities($edit_url).'\';">edit the installation options</a> (the <kbd>info.php</kbd> file).</p>'.chr(10).'<p class="associated_details">ocProducts maintains full documentation for all procedures and tools. These may be found on the <a href="http://ocportal.com">ocPortal website</a>. If you are unable to easily solve this problem, we may be contacted from our website and can help resolve it for you.</p>'.chr(10).'<hr />'.chr(10).'<p style="font-size: 0.8em"><a href="http://ocportal.com/">ocPortal</a> is a <abbr title="Content Management System">CMS</abbr> for building websites, developed by ocProducts.</p>'.chr(10);
		echo '</div></body>'.chr(10).'</html>';
		$GLOBALS['SCREEN_TEMPLATE_CALLED']='';
		if ($exit) exit();
	}
}


