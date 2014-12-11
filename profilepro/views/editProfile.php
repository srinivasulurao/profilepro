<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
global $siteurl;
//debug($paypal);
$msg=$model->updateProfileProUserDetails();
$profile=$model->getUserInfo();
//debug($profile);
?>
<div><?php echo $msg; ?></div><br>
<div style='width:20%;display:inline-block !important;vertical-align: top;background:steelblue;border-radius:13px;box-shadow:0 0 10px inset'>
<?php 
echo"<ul style='list-style:none;margin:25px' id='profileLinks'>";
echo $profileLinks=$model->profileLinks();
echo"</ul>";
?>
</div>
<div style='width:75%;display:inline-block !important;vertical-align: top;margin-left:20px'>

<h1>Edit Business Details</h1><hr>
<form method='post' action='' enctype='multipart/form-data'>
<table width='100%'>
<?php 
$male=($profile->gender=='male')?"selected='selected'":"";
$female=($profile->gender=='female')?"selected='selected'":"";
echo "<tr><td width='210px'><label>Full Name</label>:</td><td align='left'><input type='text' name='full_name' value='{$profile->full_name}'></td></tr>";
echo "<tr><td width='210px'><label>Occupation</label>:</td><td align='left'><input type='text' name='occupation' value='{$profile->occupation}'></td></tr>";
echo "<tr><td width='210px'><label>Phone</label>:</td><td align='left'><input type='text' name='phone' value='{$profile->phone}'></td></tr>";
echo "<tr><td width='210px'><label>Gender</label>:</td><td align='left'><select name='gender'><option value=''>-SELECT-</option><option $male value='male'>Male</option><option $female value='female'>Female</option></select></td></tr>";
echo "<tr><td width='210px'><label>Address</label>:</td><td align='left'><textarea name='address' >{$profile->address}</textarea></td></tr>";
echo "<tr><td width='210px'><label>About Me</label>:</td><td align='left'><textarea name='about' >{$profile->about}</textarea></td></tr>";
echo "<tr><td width='210px'><label>Profile Pic</label>:</td><td align='left'><input type='file' name='profile_pic'>".basename($profile->profile_pic)."</td></tr>";
echo "<tr><td align='center' colspan='2' style='padding:20px;'><input value='Save Details' type='submit' name='profilepro_submit_user'>&nbsp <a style='top:13px;position:relative' class='button' href='$siteurl/profile'>Cancel</a></td></tr>";
?>
</table>
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

table{
	//border:1px solid lightgrey;
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

                        