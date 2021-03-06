{TITLE}

<h2>{!REASON}</h2>
<p>
	{REASON*}
</p>

<h2>{!DETAILS}</h2>

<div class="expansive_section">
	<div class="wide_table_wrap"><table summary="{!MAP_TABLE}" class="wide_table solidborder">
		<colgroup>
			<col style="width: 140px" />
			<col style="width: 100%" />
		</colgroup>

		<tbody>
			<tr>
				<th>{!USERNAME}</th>
				<td>{USERNAME}</td>
			</tr>
			<tr>
				<th>{!IP_ADDRESS}</th>
				<td>{IP}</td>
			</tr>
			<tr>
				<th>{!URL}</th>
				<td>
					{URL*} {$*,Do not make this a clickable URL or you risk creating an attack vector}
				</td>
			</tr>
			<tr>
				<th>{!REFERER}</th>
				<td>
					{+START,IF_NON_EMPTY,{REFERER}}
						{REFERER*} {$*,Do not make this a clickable URL or you risk creating an attack vector}
					{+END}
					{+START,IF_EMPTY,{REFERER}}
						{!NONE_EM}
					{+END}
				</td>
			</tr>
			<tr>
				<th>{!USER_AGENT}</th>
				<td><kbd>{USER_AGENT*}</kbd></td>
			</tr>
			<tr>
				<th>{!USER_OS}</th>
				<td><kbd>{USER_OS*}</kbd></td>
			</tr>
		</tbody>
	</table></div>
</div>

{+START,IF_NON_EMPTY,{POST}}
	<h2>{!POST_DATA}</h2>

	<p>{!POST_DATA_EXPLANATION}</p>

	{+START,BOX,,,light}
		{POST}
	{+END}
{+END}

