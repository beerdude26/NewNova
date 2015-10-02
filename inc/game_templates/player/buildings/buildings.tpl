<center>
<br />

<script type="text/javascript">
$(document).ready(function()
{
    var updateButtonVisible = false;
    $("#building_overview").tableDnD(
    {
        onDragStart: function(table, row) {
            var order_set = Boolean( parseInt( $('#original_order_set').html() ) );
            
            if( order_set )
                return;
            
            var rows = table.tBodies[0].rows;
            var str = "";
            for (var i=0; i<rows.length; i++) {
                str += (i+1) + "-" + rows[i].id.replace("_row","") + "_";
            }
            str = str.slice(0,-1);

            $('#original_order').html(str);
            $('#original_order_set').html('1');
        },
        onDrop: function(table, row) {
            var rows = table.tBodies[0].rows;
            var str = "";
            for (var i=0; i<rows.length; i++) {
                str += (i+1) + "-" + rows[i].id.replace("_row","") + "_";
            }
            str = str.slice(0,-1);
	        $('#row_values').val(str);
            
            var orig_order = $('#original_order').html();

            if( orig_order == str ) {
                $('#update_queue_overview').fadeOut();
            } else {
             $('#update_queue_overview').fadeIn();
            }
	    }
    });

    var timeLayout = '{timer_layout}';
    var urlBase = 'buildings.php?command=update';
    var completedText = '{construction_complete}';
    var buildTime = new Date();
    var urlTotal = '';
    {building_timers}
});
</script>

<span style="display:none"; id="original_order_set">0</span>
<span style="display:none;" id="original_order"></span>

<table width="530" id="building_overview">
    <tbody>
	{building_queue}
    </tbody>
    <tbody>
    <tr class="nodrop nodrag"><td colspan="3" id="update_queue_overview" style="display:none;text-align:center;">
    <form action="buildings.php" method="GET" id="update_building_queue_form">
        <input type="hidden" id="row_values" value="" name="rows" />
        <input type="hidden" value="reorder" name="command" />
        <input type="submit" value="{update_building_queue}" />
    </form>
    </td></tr>
    </tbody>
    <tbody>
	<tr class="nodrop nodrag">
		<th>{build_fields_remaining_title}</th>
		<th colspan="2" >
			<font color="#00FF00">{fields_used}</font> / <font color="#FF0000">{fields_total}</font> {total} <br/>{fields_left} {fields_remaining_text}
		</th >
	</tr>
	{build_list_overiew}
    </tbody>
</table>
<br />
</center>