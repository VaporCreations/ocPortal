{+START,IF_NON_PASSED,AS_FIELD}
<form title="{!PRIMARY_PAGE_FORM}" action="{$BASE_URL*}/index.php" method="post">
{+END}
	<div>
		<div class="accessibility_hidden"><label for="{NAME*}">{!ENTRY}</label></div>
		<input onchange="{+START,IF_NON_PASSED,AS_FIELD}window.returnValue=this.value; window.close();{+END}{+START,IF_PASSED,AS_FIELD}this.value+=' '+this.selected_title;{+END}" style="display: none" type="text" id="{NAME*}" name="{NAME*}" value="{VALUE*}" />
		<div id="tree_list__root_{NAME*}">
			<!-- List put in here -->
		</div>
		<script type="text/javascript">// <![CDATA[
			new tree_list('{NAME*}','data/site_tree.php?get_perms=0{$KEEP;}&start_links=1{+START,IF_PASSED,PAGE_TYPE}&page_type={PAGE_TYPE*}{+END}','','',false,null,false,true);
		//]]></script>

		<p class="associated_caption">
			{!CLICK_ENTRY_POINT_TO_USE_2}
		</p>
	</div>
{+START,IF_NON_PASSED,AS_FIELD}
</form>
{+END}
