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

function unit_testing_run()
{
	@ini_set('ocproducts.xss_detect','0');
	@ini_set('ocproducts.type_strictness','0'); // TODO: Fix simpletest to be type strict, then remove this line
	
	global $SCREEN_TEMPLATE_CALLED;
	$SCREEN_TEMPLATE_CALLED='';

	header('Content-Type: text/html');
	
	require_code('_tests/simpletest/unit_tester.php');
	require_code('_tests/simpletest/web_tester.php');
	require_code('_tests/simpletest/mock_objects.php');
	require_code('_tests/simpletest/collector.php');
	require_code('_tests/ocptest/ocp_test_case.php');

	$id=get_param('id',NULL);
	if (!is_null($id))
	{
		if ($id=='!')
		{
			testset_do_header('Running all test sets');

			$sets=find_testsets();
			foreach ($sets as $set)
			{
				run_testset($set);
			}
			
			testset_do_footer();
			
			return;
		}

		testset_do_header('Running test set: '.escape_html($id));
		run_testset($id);
		testset_do_footer();
		return;
	}

	testset_do_header('Choose a test set');

	$sets=find_testsets();
	echo '<ul>';
	echo '<li><em><a href="?id=!">All</a></em></li>'.chr(10);
	foreach ($sets as $set)
	{
		echo '<li><a href="?id='.escape_html($set).'">'.escape_html($set).'</a></li>'.chr(10);
	}
	echo '</ul>';
	
	testset_do_footer();
}

function find_testsets($dir='')
{
	$tests=array();
	$dh=opendir(get_file_base().'/_tests/tests'.$dir);
	while (($file=readdir($dh)))
	{
		if ((is_dir(get_file_base().'/_tests/tests'.$dir.'/'.$file)) && (substr($file,0,1)!='.'))
		{
			$tests=array_merge($tests,find_testsets($dir.'/'.$file));
		} else
		{
			if (substr($file,-4)=='.php') $tests[]=substr($dir.'/'.basename($file,'.php'),1);
		}
	}
	return $tests;
}

function run_testset($testset)
{
	require_code('_tests/tests/'.filter_naughty($testset).'.php');

   $loader = new SimpleFileLoader();
   $suite = $loader->createSuiteFromClasses(
           $testset,
           array(basename($testset).'_test_set'));
   /*$result = */$suite->run(new DefaultReporter());
}

function testset_do_header($title)
{
	echo <<<END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" lang="EN">
	<head>
		<title>{$title}</title>
		<link rel="icon" href="http://ocportal.com/favicon.ico" type="image/x-icon" />
		
		<style type="text/css">
END;
@print(preg_replace('#/\*\s*\*/\s*#','',str_replace('url(\'\')','none',str_replace('url("")','none',preg_replace('#\{\$[^\}]*\}#','',file_get_contents(get_file_base().'/themes/default/css/global.css'))))));
echo <<<END
			.main_page_title { text-decoration: underline; display: block; background: url('../themes/default/images/bigicons/ocp-logo.png') top left no-repeat; min-height: 42px; padding: 3px 0 0 60px; }
			a[target="_blank"], a[onclick$="window.open"] { padding-right: 0; }
		</style>
	</head>
	<body class="re_body"><div class="global_middle">
		<h1 class="main_page_title">{$title}</h1>
END;
}

function testset_do_footer()
{
	echo <<<END
		<hr />
		<p>ocPortal test set tool, based on SimpleTest.</p>
	</div></body>
</html>
END;
}

