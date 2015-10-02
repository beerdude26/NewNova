<form name="trader" action="trader.php" method="get">
<input type="hidden" name="action" value="SELECT_TYPE">
<br>
<table width="600">
<tr>
	<td class="c" colspan="10"><font color="#FFFFFF">{mod_ma_title}</font><td>
</tr><tr>
	<th colspan="10">
		{call_trader}<br/><br/>
		<input type="submit" name="metal" value="{metal}" /> 
		<input type="submit" name="crystal" value="{crystal}" /> 
		<input type="submit" name="deut" value="{deut}" />
		<br/>
	<br/>{exchange_rates_info}<br /><br />
	</th>
</tr>
</table>
</form>