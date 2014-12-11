<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
global $siteurl;
//debug($paypal);
$profile=(array)$model->getUserInfo();
//debug($profile);
?>
<div><?php echo $msg; ?></div><br>
<div style='width:20%;display:inline-block !important;vertical-align: top;background:steelblue;border-radius:13px;box-shadow:0 0 10px inset'>
<?php 
echo"<ul style='list-style:none;margin:25px' id='profileLinks'>";
echo $profileLinks=$model->profileLinks();
echo"</ul>";
$default=$siteurl."/wp-content/plugins/profilepro/uploads/default_user.gif";
$img=($profile['profile_pic'])?$siteurl.$profile['profile_pic']:$default;
?>
</div>
<div style='width:75%;display:inline-block !important;vertical-align: top;margin-left:20px'>
<h1>Business Details</h1><hr>
<a href='?task=edit-user-details' style='float:right;bottom:10px;position:relative' class='button'>Edit Business Details</a>
<table width='100%'>
<tr><td width='25%' style='background:white;text-align:center'>
<img src='<?php echo $img; ?>' style='height:155px;width:140px;border-radius:10px;border:1px solid grey;padding:3px;'>
</td>
<td width='70%' style='padding:0px;'>
<table width='100%' style='border-top:0px;border-bottom:0px;border-right:0px'>
<?php 
foreach($profile as $key=>$value):
$label=str_replace("_"," ",ucfirst($key));
$value=($value=="")?"--Not Mentioned--":$value;
if($key!=="info_id" and $key!='user_id' and $key!="profile_pic")
echo "<tr><td width='210px'><label>$label</label>:</td><td align='left'>".$value."</td></tr>";
endforeach
?>
</table>
</td></tr>

</table>
<br>
<h1>Account Credentials</h1><hr>
<table width='100%'>
<?php 
echo "<tr><td width='210px'><label>Username</label>:</td><td align='left'>".$model->user->data->display_name."</td></tr>";
echo "<tr><td width='210px'><label>Email</label>:</td><td align='left'>".$model->user->data->user_email."</td></tr>";
echo "<tr><td width='210px'><label>Registered On</label>:</td><td align='left'>".date("M, dS Y",strtotime($model->user->data->user_registered))."</td></tr>";
echo "<tr><td width='210px'><label>Change Password</label>:</td><td align='left'>Click <a href='$siteurl"."/profile?task=change-password"."'>Here</a> to change </td></tr>";
echo "<tr><td width='210px'><label>User Status</label>:</td><td align='left'> Active </td></tr>";
?>
</table>
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

hr{
	border:0px;
	border-bottom:1px solid ligthgrey;
}
#middle .content_wrap {
    background: none repeat scroll 0 0 ivory;
    border: 1px dashed;
    font-family: trebuchet ms;
    padding: 20px;
	min-height:500px;
}



td{
	padding:10px;
	color:black;
}

tr:nth-child(odd){
	background:lightyellow;
}

tr:nth-child(even){
	background:white;
}
</style>

                        