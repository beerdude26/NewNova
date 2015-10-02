<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <link href="skins/xnova/formate.css" rel="stylesheet" type="text/css">
  <title>XNova</title>
  <link rel="shortcut icon" href="./favicon.ico" type="image/x-icon">
  <link rel="stylesheet" type="text/css" href="css/styles.css">
  <link rel="stylesheet" type="text/css" href="css/about.css">
</head>
<body>
  <div id="main">
     <script type="text/javascript">
	var lastType = "";
	function changeAction(type) {
	if (document.formular.Uni.value == '') {
	    alert('{log_univ}'); }
	else {
	    if(type == "login" && lastType == "") {
	    var url = "http://" + document.formular.Uni.value + "";
	    document.formular.action = url; }
	else {
            var url = "http://" + document.formular.Uni.value + "/reg.php";
            document.formular.action = url;
            document.formular.submit(); } } }
     </script>
  <div id="login"><a name="pustekuchen"></a>
  <div id="login_input">
<table border="0" cellpadding="8" cellspacing="0">
<tbody>
<tr style="vertical-align: buttom;">
<td style="padding-left: 4px;">
<form name="formular" action="" method="post" onsubmit="changeAction('login');" style="margin-top: -9px; margin-left: 70px;">
{login_username} <input name="username" value="" type="text">
{login_password} <input name="password" value="" type="password"><br/>
<div style="display:{login_display_error};">{login_incorrect}</div>
{login_remember_me} <input name="rememberme" type="checkbox"> <script type="text/javascript">document.formular.Uni.focus(); </script><input name="submit" value="{login_login}" type="submit"><label></label></form>
<a href="lostpassword.php">{login_lost_password}</a>
</td>
</tr>
</tbody>
</table>
</div>
<div id="downmenu">&nbsp;</div>
</div>
<div id="mainmenu" style="margin-top: 20px;">
<a href="reg.php">{log_reg}</a>
<a href="{forum_url}">Forum</a>
<a href="contact.php">Contact</a>
<a href="credit.php">{log_cred}</a>
</div>
<div id="rightmenu" class="rightmenu">
<div id="title"></div>
<div id="content">
<div style="text-align: left;"></div>
<center>
<div style="text-align: left;"></div>
<div id="text1">
<div style="text-align: left;"><strong>{servername}</strong> {log_desc} {servername}.
</div>
</div>
<div id="register" class="bigbutton" onclick="document.location.href='reg.php';"><font color="#cc0000">{log_toreg}</font></div>
<div id="text2">
<div id="text3">
<center><b><font color="#00cc00">{log_online}: </font>
<font color="#c6c7c6">{online_users}</font> - <font color="#00cc00">{log_lastreg}: </font>
<font color="#c6c7c6">{last_user}</font> - <font color="#00cc00">{log_numbreg}:</font> <font color="#c6c7c6">{users_amount}</font>
<br>{status}
</b></center>
</div>
</div>
</center>
</div>
<div id="text3">
<center><br>
<div style="text-align: left; color: white;"><big style="font-weight: bold; margin-left: 25px;"><big>{log_welcome} {servername}</big></big></div>
</center>
</div>
</div>
</div>
</body></html>