<div class="accessibility_hidden"><label for="multi_{I*}">{!UPLOAD}</label></div>
<input tabindex="{TABINDEX*}" class="input_upload{REQUIRED*}" onchange="if (!key_pressed(event,9)) ensureNextFieldUpload(this);" type="file" id="multi_{I*}" name="{NAME_STUB*}_{I*}" />
{+START,IF,{$JS_ON}}<button class="button_pageitem" type="button" id="clearBtn_multi_{I*}" onclick="var x=document.getElementById('multi_{I*;}'); x.value=''; if (typeof x.fakeonchange!='undefined' &amp;&amp; x.fakeonchange) x.fakeonchange(); return false;">{!CLEAR}</button>{+END}
<input type="hidden" name="label_for_{NAME_STUB*}{I*}" value="{!UPLOAD}" />

{+START,IF,{SWFUPLOAD}}{+START,IF,{$NOT,{$IS_HTTPAUTH_LOGIN}}}
	<script type="text/javascript">
	// <![CDATA[
		addEventListenerAbstract(window,'load',function () {
			preinitFileInput('upload_multi','multi_{I}',null,null,'{FILTER;}');
		} );
	//]]>
	</script>
{+END}{+END}