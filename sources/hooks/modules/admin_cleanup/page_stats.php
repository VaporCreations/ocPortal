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
 * @package		stats
 */

class Hook_page_stats
{

	/**
	 * Standard modular info function.
	 *
	 * @return ?array	Map of module info (NULL: module is disabled).
	 */
	function info()
	{
		require_lang('stats');
		
		$info=array();
		$info['title']=do_lang_tempcode('PAGE_STATS_DELETE');
		$num_records=$GLOBALS['SITE_DB']->query_value('stats','COUNT(*)');
		$info['description']=do_lang_tempcode('DESCRIPTION_PAGE_STATS_DELETE',integer_format($num_records),integer_format(intval(get_option('stats_store_time'))));
		$info['type']='optimise';

		return $info;
	}
	
	/**
	 * Standard modular run function.
	 *
	 * @return tempcode	Results
	 */
	function run()
	{
		require_lang('stats');
		require_lang('dates');
		
		$delete_older_than=post_param_integer('delete_older_than',NULL);
		if (is_null($delete_older_than))
		{
			@ob_end_clean();

			$delete_older_than=intval(get_option('stats_store_time'));
			
			require_code('form_templates');
			$fields=form_input_integer(do_lang_tempcode('DPLU_DAYS'),do_lang_tempcode('DESCRIPTION_DELETE_DAYS'),'delete_older_than',$delete_older_than,true);
			$post_url=get_self_url(false,false,NULL,false,true);
			$submit_name=do_lang_tempcode('DELETE');
			$hidden=build_keep_post_fields();

			$title=get_page_title('PAGE_STATS_DELETE');
			$tpl=do_template('FORM_SCREEN',array('SKIP_VALIDATION'=>true,'HIDDEN'=>$hidden,'TITLE'=>$title,'TEXT'=>do_lang_tempcode('DELETE_DATA_AVAILABLE'),'URL'=>$post_url,'FIELDS'=>$fields,'SUBMIT_NAME'=>$submit_name));
			$echo=globalise($tpl,NULL,'',true);
			$echo->evaluate_echo();
			exit();
		}

		if (function_exists('set_time_limit')) @set_time_limit(0);

		// Write install.php file
		$template=get_custom_file_base().'/data_custom/modules/admin_cleanup/page_stats.php.pre';
		if (!file_exists($template)) $template=get_file_base().'/data/modules/admin_cleanup/page_stats.php.pre';
		$_install_php_file=file_get_contents($template);
		$install_php_file=ocp_tempnam('ps');
		$tmpfile=fopen($install_php_file,'wb');
		fwrite($tmpfile,substr($_install_php_file,0,strpos($_install_php_file,'{!!DB!!}')));

		// Get old data
		do
		{
			$or_list='';

			$data=$GLOBALS['SITE_DB']->query('SELECT * FROM '.get_table_prefix().'stats WHERE date_and_time<'.strval(time()-60*60*24*$delete_older_than),500);
			foreach ($data as $d)
			{
				$list='';
				foreach ($d as $name=>$value)
				{
					if (is_null($value)) continue;
					if ($list!='') $list.=',';
					$list.="'".(is_string($name)?$name:strval($name))."'=>";
					if (!is_integer($value)) $list.="'".str_replace('\'','\\\'',$value)."'"; else $list.=strval($value);
				}
				fwrite($tmpfile,"	\$GLOBALS['SITE_DB']->query_insert('stats',array($list));\n");
				
				if ($or_list!='') $or_list.=' OR ';
				$or_list.='id='.strval($d['id']);
			}
			
			if ($or_list!='')
				$GLOBALS['SITE_DB']->query('DELETE FROM '.get_table_prefix().'stats WHERE '.$or_list);
		}
		while ($data!=array());

		fwrite($tmpfile,substr($_install_php_file,strpos($_install_php_file,'{!!DB!!}')+8));

		// Make tar
		require_code('tar');
		$file='stats-leading-to-'.date('Y-m-d',servertime_to_usertime(time()-60*60*24*$delete_older_than));
		$stats_backup_url=get_custom_base_url().'/exports/backups/'.$file.'.tar';
		$myfile=tar_open(get_custom_file_base().'/exports/backups/'.$file.'.tar','wb');
		tar_add_file($myfile,$file.'.php',$install_php_file,0664,time(),true);
		tar_close($myfile);
		fclose($tmpfile);

		$GLOBALS['SITE_DB']->query('DELETE FROM '.get_table_prefix().'stats WHERE date_and_time<'.strval(time()-60*60*24*$delete_older_than));

		return do_template('CLEANUP_PAGE_STATS',array('_GUID'=>'1df213eee7c5c6b97168e5a34e92d3b0','STATS_BACKUP_URL'=>$stats_backup_url));
	}

}


