<script>
  $(document).ready(function(){
    $("#registrationForm").validate();
  });
</script>

<center>
<br/><br/>
<h2><font size="+3">{registration}</font><br>{servername}</h2>

<form action="register.php" class="cmxform" id="registrationForm" method="post">
<table width="538">
<tbody>
	  <tr>
	    <td colspan="3" class="c" align="Center"><b>{formname}</b></td>
</tr>
<!-- TODO: do remote check on username, the validation plugin supports this -->
<tr>
	<th width="143" colspan="1">{username}</th>
    <th align="left" width="143" colspan="2"><input id="cusername" name="username" size="20" class="required" maxlength="20" type="text" onKeypress="
     if (event.keyCode==60 || event.keyCode==62) event.returnValue = false;
     if (event.which==60 || event.which==62) return false;"></th>
</tr>
<tr>
  <th colspan="1">{password}</th>
  <th colspan="2"><input id="cpassword" name="password" size="20" maxlength="20" class="required" type="password" onKeypress="
     if (event.keyCode==60 || event.keyCode==62) event.returnValue = false;
     if (event.which==60 || event.which==62) return false;"></th>
</tr>
<!-- TODO: do remote check on emails, the validation plugin supports this -->
<tr>
  <th colspan="1">{email}</th>
  <th colspan="2"><input name="email" id="cemail" size="20" maxlength="40" class="required email" type="text" onKeypress="
     if (event.keyCode==60 || event.keyCode==62) event.returnValue = false;
     if (event.which==60 || event.which==62) return false;"></th>
</tr>
<tr>
  <th colspan="1">{secondary_email}</th>
  <th colspan="2"><input name="secondary_email" id="csecondaryemail" size="20" maxlength="40" type="text" onKeypress="
     if (event.keyCode==60 || event.keyCode==62) event.returnValue = false;
     if (event.which==60 || event.which==62) return false;"></th>
</tr>
<tr>
  <th colspan="1">{home_planet_name}</th>
  <th colspan="2"><input name="planet_name" size="20" maxlength="20" type="text" onKeypress="
     if (event.keyCode==60 || event.keyCode==62) event.returnValue = false;
     if (event.which==60 || event.which==62) return false;"></th>
</tr>
<tr>
  <td height="20" colspan="3"></td>
  </tr>
<tr>
  <th colspan="2"><input name="submit" class="submit" type="submit" value="{signup}"></th>
</tr>
</table>
</form>
</center>