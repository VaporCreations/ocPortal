{+START,IF_EMPTY,{$META_DATA,video}}
	{$META_DATA,video,{SCRIPT}?id={ID}{SUP_PARAMS}{$KEEP,0,1}&thumb=0&for_session={$SESSION_HASHED*}&no_count=1}
	{$META_DATA,video:height,{A_HEIGHT}}
	{$META_DATA,video:width,{A_WIDTH}}
	{$META_DATA,video:type,{MIME_TYPE}}
{+END}

<object width="{A_WIDTH*}" height="{$ADD,{A_HEIGHT*},16}" type="video/quicktime" classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab">
	<param name="src" value="{SCRIPT*}?id={ID*}{+START,IF_PASSED,SUP_PARAMS}{SUP_PARAMS*}{+END}{$KEEP*,0,1}&amp;for_session={$SESSION_HASHED*}" />
	<param name="quality" value="high" />
	<param name="autoplay" value="false" />
	<param name="controller" value="true" />
	<param name="pluginspage" value="http://www.apple.com/quicktime/download/" />
	<param name="width" value="{A_WIDTH*}" />
	<param name="height" value="{$ADD,{A_HEIGHT*},16}" />

	<!--[if !IE]> -->
		<object width="{A_WIDTH*}" height="{$ADD,{A_HEIGHT*},16}" data="{SCRIPT*}?id={ID*}{+START,IF_PASSED,SUP_PARAMS}{SUP_PARAMS*}{+END}{$KEEP*,0,1}&amp;for_session={$SESSION_HASHED*}" type="video/quicktime">
			<param name="src" value="{SCRIPT*}?id={ID*}{+START,IF_PASSED,SUP_PARAMS}{SUP_PARAMS*}{+END}{$KEEP*,0,1}&amp;for_session={$SESSION_HASHED*}" />
			<param name="quality" value="high" />
			<param name="autoplay" value="false" />
			<param name="controller" value="true" />
			<param name="pluginspage" value="http://www.apple.com/quicktime/download/" />
			<param name="width" value="{A_WIDTH*}" />
			<param name="height" value="{$ADD,{A_HEIGHT*},16}" />

			{!VIDEO}{+START,IF_NON_EMPTY,{A_DESCRIPTION}}; {A_DESCRIPTION}{+END}
		</object>
	<!-- <![endif]-->
</object>

{+START,IF_NON_EMPTY,{A_DESCRIPTION}}
	<p class="associated_caption">
		{A_DESCRIPTION}
	</p>
{+END}

{$,Uncomment for a download link <span class="attachment_action">&raquo; <a rel="enclosure" target="_blank" title="{!_DOWNLOAD,{A_ORIGINAL_FILENAME*}}: {!_ATTACHMENT} #{ID*} {!LINK_NEW_WINDOW}" href="{SCRIPT*}?id={ID*}\{+START,IF_PASSED,SUP_PARAMS\}{SUP_PARAMS*}\{+END\}{$KEEP*,0,1}&amp;for_session={$SESSION_HASHED*}">{!_DOWNLOAD,{A_ORIGINAL_FILENAME*}}</a> ({CLEAN_SIZE*}\{+START,IF,{$INLINE_STATS}\}, {!DOWNLOADS_SO_FAR,{$ATTACHMENT_DOWNLOADS*,{ID},{FORUM_DB_BIN}}}\{+END\})</span>}
