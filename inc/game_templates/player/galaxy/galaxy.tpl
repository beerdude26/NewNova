<center>
<form action="" method="get" id="galaxy_form">
<table border="0"> 
  <tr>
    <td>
      <table>
        <tbody><tr>
         <td class="c" colspan="3">{galaxy_text}</td>
        </tr>
        <tr>
          <td class="l"><input name="galaxyLeft" value="&lt;-" type="submit"></td>
          <td class="l"><input name="galaxy" value="{current_galaxy}" size="5" maxlength="3" tabindex="1" type="text">
          </td><td class="l"><input name="galaxyRight" value="-&gt;" type="submit"></td>
        </tr>
       </tbody></table>
      </td>
      <td>
       <table>
        <tbody><tr>
         <td class="c" colspan="3">{system_text}</td>
        </tr>
         <tr>
          <td class="l"><input name="systemLeft" value="&lt;-" type="submit"></td>
          <td class="l"><input name="system" value="{current_system}" size="5" maxlength="3" tabindex="2" type="text">
          </td><td class="l"><input name="systemRight" value="-&gt;" type="submit"></td>
         </tr>
        </tbody></table>
       </td>
      </tr>
      <tr>
        <td colspan="2" align="center"> <input value="{display_text}" type="submit"></td>
      </tr>
     </tbody></table>
</form>


<table width=569>
<tbody>
{galaxy_view}
</tbody>
</table>
</center>