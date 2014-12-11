<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
global $siteurl;
//debug($paypal);
$msg=$model->changePassword();
?>
<div id='profilpro_notice'><?php echo $msg; ?><br></div>
<div style='width:20%;display:inline-block !important;vertical-align: top;background:steelblue;border-radius:13px;box-shadow:0 0 10px inset'>
<?php 
echo"<ul style='list-style:none;margin:25px' id='profileLinks'>";
echo $profileLinks=$model->profileLinks();
echo"</ul>";
?>
</div>
<div style='width:75%;display:inline-block !important;vertical-align: top;margin-left:20px'>
<h1>Change Password</h1><hr>
<form method='post' action=''>
<label>Old Password </label>: <input type='password' name='profileProOldPass' required='required'>
<label>New Password </label>: <input type='password' name='profileProNewPass' required='required'>
<label>Confirm Password </label>: <input type='password' name='profileProConfPass' required='required'>
<br>
<label>&nbsp </label> <input type='submit' value='Change Password' name='profileChangePassword'>
</form>
</div>

<style>
label{
        width:200px;
	vertical-align:top;
    }

#profileLinks a{
color:white;
}

#profileLinks a:hover{
color:black;
}

#middle .content_wrap {
    background: none repeat scroll 0 0 ivory;
    border: 1px dashed;
    font-family: trebuchet ms;
    padding: 20px;
	min-height:500px;
}
hr{
border:0px;
border-bottom:1px solid lightgrey;
}
</style>

                        