<div class="float_surrounder news_piece_summary">
	{+START,IF,{$NOT,{$IS_NON_EMPTY,{AUTHOR_URL}}}}
		<div class="newscat_img_member">
			{+START,IF_NON_EMPTY,{$USERNAME*,{SUBMITTER}}}
				<div class="news_by">
					<a class="poster_member" href="{$MEMBER_PROFILE_LINK*,{SUBMITTER}}">{$USERNAME*,{SUBMITTER}}</a>
				</div>
			{+END}

			{+START,IF_NON_EMPTY,{$AVATAR*,{SUBMITTER}}}
				<img src="{$AVATAR*,{SUBMITTER}}" title="{!AVATAR}" alt="{!AVATAR}" />
			{+END}

			{+START,IF,{$OCF}}
				<br /><br />
				{$OCF_RANK_IMAGE,{SUBMITTER}}
			{+END}
		</div>
	{+END}

	<h5><a title="{!POSTED_TIME,{DATE*}} {$STRIP_TAGS,{$TRUNCATE_LEFT,{NEWS_TITLE},70,1,1}}: #{ID*}" href="{FULL_URL*}">{$TRUNCATE_LEFT,{NEWS_TITLE},70,1,1}</a></h5>

	{+START,IF_NON_EMPTY,{NEWS}}
		{+START,IF,{$AND,{$NOT,{$IN_STR,{NEWS},<p>}},{$NOT,{$IN_STR,{NEWS},<h}}}}<p class="news_summary_p">{+END}
		{$TRUNCATE_LEFT,{NEWS},100,0,1,1,0.4}
		{+START,IF,{$AND,{$NOT,{$IN_STR,{NEWS},<p>}},{$NOT,{$IN_STR,{NEWS},<h}}}}</p>{+END}
	{+END}
</div>

{+START,IF_PASSED,TAGS}
	{+START,IF,{$CONFIG_OPTION,show_content_tagging_inline}}{TAGS}{+END}
{+END}
