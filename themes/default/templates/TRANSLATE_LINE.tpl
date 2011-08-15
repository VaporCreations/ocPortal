<tr>
	<td class="field_secondary_title_left translate_line_first">
		<a name="jmp_{NAME*}" id="jmp_{NAME*}"></a>

		{NAME*}
		
		{+START,IF_NON_EMPTY,{DESCRIPTION}}
			<p>
				{DESCRIPTION*}
			</p>
		{+END}
	</td>
	<td class="translate_line_second">
		<div class="accessibility_hidden"><label for="old__trans_{NAME*}">{!OLD} {NAME*}</label></div>
		<div class="constrain_field">
			<textarea readonly="readonly" class="translate_original_text wide_field" cols="60" rows="{$ADD,{$DIV,{$LENGTH,{OLD}},80},1}" id="old__trans_{NAME*}" name="old__{NAME*}">{OLD*}</textarea>
		</div>

		<div class="arrow_ruler"><img alt="" src="{$IMG*,arrow_ruler_small}" /></div>

		<div class="accessibility_hidden"><label for="trans_{NAME*}">{NAME*}</label></div>
		<div class="constrain_field">
			<textarea {+START,IF_PASSED,TRANSLATE_AUTO}onclick="if (this.value=='') this.value='{TRANSLATE_AUTO*^;}';" {+END}class="wide_field" cols="60" rows="{+START,IF,{$EQ,{CURRENT},}}{$ADD,{$DIV,{$LENGTH,{OLD}},80},1}{+END}{+START,IF,{$NEQ,{CURRENT},}}{$ADD,{$DIV,{$LENGTH,{CURRENT}},80},1}{+END}" id="trans_{NAME*}" name="{NAME*}">{CURRENT*}</textarea>
		</div>
	</td>
	{+START,IF_NON_EMPTY,{ACTIONS}}
		<td>
			{ACTIONS}
		</td>
	{+END}
</tr>
<tr id="rexp_{NAME*}" style="display: none">
	<td colspan="{$?,{$IS_EMPTY,{ACTIONS}},3,4}">
		<div id="exp_{NAME*}">&nbsp;</div>
	</td>
</tr>

