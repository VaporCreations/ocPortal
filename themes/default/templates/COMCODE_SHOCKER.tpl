{$SET,RAND_ID_SHOCKER,rand{$RAND}}

{$JAVASCRIPT_INCLUDE,javascript_dyn_comcode}
{$JAVASCRIPT_INCLUDE,javascript_pulse}
<div class="shocker">
	<div class="shocker_left" id="comcodeshocker{$GET%,RAND_ID_SHOCKER}_left">&nbsp;</div>
	<div class="shocker_right" id="comcodeshocker{$GET%,RAND_ID_SHOCKER}_right">&nbsp;</div>
</div>
<script type="text/javascript">// <![CDATA[
if (typeof window.shocker_parts=='undefined')
{
	window.shocker_parts=[];
	window.shocker_pos=[];
}
window.shocker_parts['{$GET%,RAND_ID_SHOCKER}']=[{PARTS/}''];
window.shocker_pos['{$GET%,RAND_ID_SHOCKER}']=0;
addEventListenerAbstract(window,'load',function () {
	shocker_tick('{$GET%,RAND_ID_SHOCKER}',{TIME%},'{MAX_COLOR;}','{MIN_COLOR;}');
	window.setInterval(function() { shocker_tick('{$GET%,RAND_ID_SHOCKER}',{TIME%},'{MAX_COLOR;}','{MIN_COLOR;}'); },{TIME%});
} );
//]]></script>
<noscript>
	{FULL*}
</noscript>

