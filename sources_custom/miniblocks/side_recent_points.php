<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2011

 See text/EN/licence.txt for full licencing information.

*/

$max=array_key_exists('max',$map)?intval($map['max']):10;

$gifts=$GLOBALS['SITE_DB']->query('SELECT * FROM '.get_table_prefix().'gifts g LEFT JOIN '.get_table_prefix().'translate t ON t.id=g.reason WHERE gift_from<>'.strval($GLOBALS['FORUM_DRIVER']->get_guest_id()).' ORDER BY g.id DESC',$max);
echo '<div class="wide_table_wrap"><table class="solidborder wide_table">';

echo '<tr>';
//echo '<th>From</th>';
echo '<th>To</th>';
echo '<th>&times; pt</th>';
echo '<th>For</th>';
echo '</tr>';

require_code('templates_interfaces');

foreach ($gifts as $gift)
{
	$amount=$gift['amount'];
	$from_name=$GLOBALS['FORUM_DRIVER']->get_username($gift['gift_from']);
	if (is_null($from_name)) $from_name='(Deleted)';
	$to_name=$GLOBALS['FORUM_DRIVER']->get_username($gift['gift_to']);
	if (is_null($to_name)) $from_name='(Deleted)';
	$from_url=build_url(array('page'=>'points','type'=>'member','id'=>$gift['gift_from']),get_module_zone('points'));
	$to_url=build_url(array('page'=>'points','type'=>'member','id'=>$gift['gift_to']),get_module_zone('points'));
	$reason=$gift['text_original'];

	if (is_null($from_name)) continue;
	if (is_null($to_name)) continue;
	if ($amount<=0) continue;
	
	$from_link=hyperlink($from_url,$from_name,false,true);
	$to_link=hyperlink($to_url,$to_name,false,true);
	
	echo '<tr>';
//	echo '<td>'.$from_link->evaluate().'</td>';
	echo '<td>'.$to_link->evaluate().'</td>';
	echo '<td>'.escape_html(integer_format($amount)).'</td>';
	if (trim($reason)!='')
	{
		$blah=tpl_crop_text_mouse_over($reason.(($gift['anonymous']==0)?' ('.$from_name.')':' (Anonymous)'),5);
		echo '<td>'.$blah->evaluate().'</td>';
	}
	echo '</tr>';
}

echo '</table></div>';
