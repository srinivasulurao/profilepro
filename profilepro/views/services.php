<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
global $siteurl;
//debug($paypal);
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
<h1>Services</h1>
<a href='<?php echo $siteurl."/consultation-services/?task=add-new"; ?>' class='button' style='float: right; margin-bottom: -40px; position: relative; bottom: 40px;'>Add New</a>
<hr>
<table cellspacing='0' cellpadding='0' width='100%' >
<tr><th>Order #</th><th>Service</th><th>Status</th><th>Action</th></tr>
<?php 
$userService=$model->userServices();
//debug($userService);
$serviceCounter=0;
foreach($userService as $key):
$status=(!$key->status)?"Open":"Closed";
$view="<a style='color:white;background: none repeat scroll 0% 0% #0568A6; text-decoration: none; padding: 1px 10px; border-radius: 5px;' href='$siteurl/consultation-services?task=view&view_id=$key->id'>View</a>";
echo"<tr><td>#$key->docket</td><td>$key->service_name</td><td>$status</td><td>$view</td></tr>";
$serviceCounter++;
endforeach;
if($serviceCounter==0):
echo "<tr><td colspan='4' style='color:red'>No Service found, please take a service immediately to interact with our attroneys</td></tr>";
endif;
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

th{
	background:tomato;
	color:black;
	padding:10px;
}

table{
	border:1px solid lightgrey;
}

td{
	padding:10px;
	color:black;
}

tr:nth-child(odd){
	background:lightgrey;
}

tr:nth-child(even){
	background:white;
}
</style>

                        