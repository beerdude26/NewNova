<script type="text/javascript" >
function calculate() {
    var thisForm = document.forms['trader'];
	var Res1 = thisForm.elements['resource1'].value;
	var Res2 = thisForm.elements['resource2'].value;
    
    Res1 =  Res1 / {exchange_rate_1};
    Res2 =  Res2 / {exchange_rate_2};

	var Bought_Res = Res1 + Res2;
	document.getElementById("bought_resource").innerHTML = Bought_Res;

    var isValid = true;
	if (isNaN(thisForm.elements['resource1'].value)) {
		document.getElementById("bought_resource").innerHTML = "{value_not_a_number}";
        isValid = false;
	}
	if (isNaN(thisForm.elements['resource2'].value)) {
		document.getElementById("bought_resource").innerHTML = "{value_not_a_number}";
        isValid = false; 
	}
    
    if( !isValid )
    {
        $("#exchange_resource").slideUp(500);
    }
    else
    {
        $("#exchange_resource").slideDown(500);
    }
}
</script>
<br>
<center>
<form id="trader" action="trader.php" method="get">
<input type="hidden" name="action" value="{SELLING_ACTION}">
<table width="569">
<tr>
	<td class="c" colspan="5"><b>{sell_item}</b></td>
</tr><tr>
	<th>{resource}</th>
	<th>{quantity}</th>
	<th>{exchange_rate}</th>
</tr><tr>
	<th>{resource_being_bought_name}</th>
	<th><span id='bought_resource'>0</span></th>
	<th>{resource_being_bought_rate}</th>
</tr><tr>
	<th>{resource1}</th>
	<th><input name="resource1" type="text" value="0" onkeyup="calculate()"/></th>
	<th>{exchange_rate_1}</th>
</tr><tr>
	<th>{resource2}</th>
	<th><input name="resource2" type="text" value="0" onkeyup="calculate()"/></th>
	<th>{exchange_rate_2}</th>
</tr><tr>
	<th colspan="6"><input type="submit" id="exchange_resource" value="{exchange}" /></th>
</tr>
</form>
</table>

<div id="error_message" style="display:{error_display_type};">{error_message}</div>

</center>