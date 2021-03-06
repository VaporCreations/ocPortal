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

class Block_main_sitemap
{
	
	/**
	 * Standard modular info function.
	 *
	 * @return ?array	Map of module info (NULL: module is disabled).
	 */
	function info()
	{
		$info=array();
		$info['author']='Chris Graham'; 
		$info['organisation']='ocProducts';
		$info['hacked_by']=NULL; 
		$info['hack_version']=NULL;
		$info['version']=2;
		$info['locked']=false;
		$info['parameters']=array('skip');
		return $info;
	}
	
	/**
	 * Standard modular cache function.
	 *
	 * @return ?array	Map of cache details (cache_on and ttl) (NULL: module is disabled).
	 */
	function cacheing_environment()
	{
		$info=array();
		$info['cache_on']=array('block_main_sitemap__cache_on');
		$info['ttl']=600;
		return $info;
	}
	
	/**
	 * Standard modular run function.
	 *
	 * @param  array		A map of parameters.
	 * @return tempcode	The result of execution.
	 */
	function run($map)
	{
		require_all_lang();
		require_code('zones2');
		
		$skip_pages=array_key_exists('skip',$map)?explode(',',$map['skip']):array();

		$comcode_page_rows=$GLOBALS['SITE_DB']->query_select('comcode_pages',array('*'));

		$_zones=array();
		$zones=find_all_zones(false,true);

		$GLOBALS['MEMORY_OVER_SPEED']=true;
		$low_memory=((ini_get('memory_limit')!='-1') && (ini_get('memory_limit')!='0') && (ini_get('memory_limit')!='') && (intval(preg_replace('#M$#','',ini_get('memory_limit')))<26)) || (get_option('has_low_memory_limit')==='1');

		// Reorder a bit
		$zones2=array();
		foreach (array('','site') as $zone_match)
		{
			foreach ($zones as $i=>$zone)
			{
				if ($zone[0]==$zone_match)
				{
					$zones2[]=$zone;
					unset($zones[$i]);
				}
			}
		}
		$zones2=array_merge($zones2,$zones);

		foreach ($zones2 as $z)
		{
			list($zone,$zone_title,,$zone_default_page)=$z;
			if (has_zone_access(get_member(),$zone))
			{
				$_pages=array();
				$pages=find_all_pages_wrap($zone);
				if (isset($pages[$zone_default_page])) // Put default page first
				{
					$default=$pages[$zone_default_page];
					$pages=array($zone_default_page=>$default)+$pages;
				}
				foreach ($pages as $page=>$page_type)
				{
					if (is_integer($page)) $page=strval($page);
					if (substr($page,0,6)=='panel_') continue;
					if (substr($page,0,1)=='_') continue;
					if (in_array($page,$skip_pages)) continue;
					if ($page=='404') continue;
					if (substr($page,-10)=='_tree_made') continue;
					if ($page=='sitemap') continue;
					if (($page=='forums') && (substr($page_type,0,7)=='modules') && ((get_forum_type()=='ocf') || (get_forum_type()=='none'))) continue;
					if (($page=='join') && (substr($page_type,0,7)=='modules') && (!is_guest())) continue;

					if (has_page_access(get_member(),$page,$zone))
					{
						$_entrypoints=array();
						$__entrypoints=$low_memory?array(NULL):extract_module_functions_page($zone,$page,array('get_entry_points'));
						if (!is_null($__entrypoints[0]))
						{
							$entrypoints=is_array($__entrypoints[0])?call_user_func_array($__entrypoints[0][0],$__entrypoints[0][1]):((strpos($__entrypoints[0],'::')!==false)?NULL:eval($__entrypoints[0])); // The strpos thing is a little hack that allows it to work for base-class derived modules
							if (is_null($entrypoints))
							{
								$path=zone_black_magic_filterer($zone.(($zone=='')?'':'/').'pages/'.$page_type.'/'.$page.'.php',true);
								if (($low_memory) && (!defined('HIPHOP_PHP')) && (strpos(file_get_contents(get_file_base().'/'.$path),' extends standard_aed_module')!==false)) // Hackerish code when we have a memory limit. It's unfortunate, we'd rather execute in full
								{
									$new_code=str_replace(',parent::get_entry_points()','',str_replace('parent::get_entry_points(),','',$__entrypoints[0]));
									if (strpos($new_code,'parent::')!==false) continue;
									$entrypoints=eval($new_code);
								} else
								{
									require_code($path);
									if (class_exists('Mx_'.filter_naughty_harsh($page)))
									{
										$object=object_factory('Mx_'.filter_naughty_harsh($page));
									} else
									{
										$object=object_factory('Module_'.filter_naughty_harsh($page));
									}
									$entrypoints=$object->get_entry_points();
								}
							}
						} else $entrypoints=array('!');
						if (!is_array($entrypoints)) $entrypoints=array('!');
						if ($entrypoints==array('!'))
						{
							$url=build_url(array('page'=>$page),$zone,NULL,false,false,true);
							$title=ucwords(str_replace('_',' ',$page));
							if (substr($page_type,0,7)=='comcode')
							{
								foreach ($comcode_page_rows as $page_row)
								{
									if (($page_row['p_validated']==0) && ($page_row['the_page']==$page) && ($page_row['the_zone']==$zone))
									{
										continue 2;
									}
								}

								$path=zone_black_magic_filterer(((strpos($page_type,'_custom')!==false)?get_custom_file_base():get_file_base()).'/'.filter_naughty($zone).'/pages/'.filter_naughty($page_type).'/'.$page.'.txt');
								if (!is_file($path))
									$path=zone_black_magic_filterer(get_file_base().'/'.filter_naughty($zone).'/pages/'.filter_naughty($page_type).'/'.$page.'.txt');
								$page_contents=file_get_contents($path);
								$matches=array();
								if (preg_match('#\[title[^\]]*\]#',$page_contents,$matches)!=0)
								{
									$start=strpos($page_contents,$matches[0])+strlen($matches[0]);
									$end=strpos($page_contents,'[/title]',$start);
									$matches=array();
									$title_portion=str_replace('{$SITE_NAME}',get_site_name(),substr($page_contents,$start,$end-$start));
									if (preg_match('#\{\!([\w:]+)\}#',$title_portion,$matches)!=0)
									{
										$title_portion=str_replace($matches[0],do_lang($matches[1]),$title_portion);
									}
									if (preg_match('#^[^\[\{\&]*$#',$title_portion,$matches)!=0)
									{
										$title=$matches[0];
									} elseif (!$low_memory)
									{
										$_title=comcode_to_tempcode($title_portion,NULL,true);
										$title=strip_tags(@html_entity_decode($_title->evaluate(),ENT_QUOTES,get_charset()));
									}
								}
							}
							elseif (substr($page_type,0,4)=='html')
							{
								$path=zone_black_magic_filterer(((strpos($page_type,'_custom')!==false)?get_custom_file_base():get_file_base()).'/'.filter_naughty($zone).'/pages/'.filter_naughty($page_type).'/'.$page.'.htm');
								$page_contents=file_get_contents($path);
								$matches=array();
								if (preg_match('#\<title[^\>]*\>#',$page_contents,$matches)!=0)
								{
									$start=strpos($page_contents,$matches[0])+strlen($matches[0]);
									$end=strpos($page_contents,'</title>',$start);
									$title=strip_tags(@html_entity_decode(substr($page_contents,$start,$end-$start),ENT_QUOTES,get_charset()));
								}
							}
							$temp=do_template('BLOCK_MAIN_SITEMAP_NEST',array('_GUID'=>'92e657f8b9a3642df053f54e724e66f6','URL'=>$url,'NAME'=>$title,'CHILDREN'=>array()));
							$_pages[$title]=$temp->evaluate(); // FUDGEFUDGE
						} elseif (count($entrypoints)!=0)
						{
							foreach ($entrypoints as $entrypoint=>$title)
							{
								if ((($entrypoint=='concede') || ($entrypoint=='invisible') || ($entrypoint=='logout')) && (is_guest())) continue;

								if ($entrypoint=='!')
								{
									$url=build_url(array('page'=>$page),$zone,NULL,false,false,true);
								} else
								{
									$url=build_url(array('page'=>$page,'type'=>$entrypoint),$zone,NULL,false,false,true);
								}
								$_entrypoints[$title]=do_template('BLOCK_MAIN_SITEMAP_NEST',array('_GUID'=>'ae2ed2549644a8e699e0938b3ab98ddb','URL'=>$url,'NAME'=>do_lang_tempcode($title),'CHILDREN'=>array()));
							}
							//ksort($_entrypoints);
							$title=do_lang('MODULE_TRANS_NAME_'.$page,NULL,NULL,NULL,NULL,false);
							if (is_null($title)) $title=ucwords(str_replace('_',' ',preg_replace('#^ocf\_#','',preg_replace('#^'.str_replace('#','\#',preg_quote($zone)).'_#','',preg_replace('#^'.str_replace('#','\#',preg_quote(str_replace('zone','',$zone))).'_#','',$page)))));
							if (count($_entrypoints)==1)
							{
								$temp_keys=array_keys($_entrypoints);
								$temp=$_entrypoints[$temp_keys[0]];
							} else
							{
								$temp=do_template('BLOCK_MAIN_SITEMAP_NEST',array('_GUID'=>'dfc5cc7db0301acd938d3b2e3fceaab8','URL'=>new ocp_tempcode(),'NAME'=>$title,'CHILDREN'=>$_entrypoints));
							}
							$_pages[$title]=$temp->evaluate(); // FUDGEFUDGE
						}
					}
				}
				$url=new ocp_tempcode();
				if ($_pages!=array())
				{
					$keys=array_keys($_pages);
					$first=$_pages[$keys[0]];
					ksort($_pages);
					$_pages=array($keys[0]=>$first)+$_pages;
				}
				$temp=do_template('BLOCK_MAIN_SITEMAP_NEST',array('_GUID'=>'38abb0a0e5bec968b28b4791320dd0dc','URL'=>$url,'NAME'=>$zone_title,'CHILDREN'=>$_pages));
				$_zones[]=$temp->evaluate(); // FUDGEFUDGE
			}
		}

		// To avoid running out of memory
		$out=do_template('BLOCK_MAIN_SITEMAP',array('_GUID'=>'d0807b30925e47d10cdb2c36231436ab','CHILDREN'=>$_zones));
		$e=$out->evaluate();
		$explode=explode('__keep__',$e); // the URLs are build without keep and the templates tack it on the end
		if (strpos($e,'__keep__')!==false)
		{
			$out=new ocp_tempcode();
			foreach ($explode as $i=>$bit)
			{
				if ($i!=0) $out->attach(symbol_tempcode('KEEP',NULL,array(ENTITY_ESCAPED)));
				if ($GLOBALS['XSS_DETECT']) ocp_mark_as_escaped($bit);
				$out->attach($bit);
			}
		}
		$e=$out->evaluate();
		if (strpos($e,'__keep1__')!==false)
		{
			$explode=explode('__keep1__',$e);
			$out=new ocp_tempcode();
			foreach ($explode as $i=>$bit)
			{
				if ($i!=0) $out->attach(symbol_tempcode('KEEP',array('1'),array(ENTITY_ESCAPED)));
				if ($GLOBALS['XSS_DETECT']) ocp_mark_as_escaped($bit);
				$out->attach($bit);
			}
		}

		return $out;
	}

}

/**
 * Find the cache signature for the block.
 *
 * @param  array	The block parameters.
 * @return array	The cache signature.
 */
function block_main_sitemap__cache_on($map)
{
	return array($GLOBALS['FORUM_DRIVER']->get_members_groups(get_member(),false,true),array_key_exists('skip',$map)?explode(',',$map['skip']):array());
}


