<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
global $siteurl;
//debug($paypal);
?>
<div></div><br>
<div style='width:20%;display:inline-block !important;vertical-align: top;background:steelblue;border-radius:13px;box-shadow:0 0 10px inset'>
<?php 
echo"<ul style='list-style:none;margin:25px' id='profileLinks'>";
echo $profileLinks=$model->profileLinks();
echo"</ul>";
$service=$model->getServiceInvoice($_REQUEST['view_id']);

?>
</div>
<div style='width:75%;display:inline-block !important;vertical-align: top;margin-left:20px'>
<h1>Service - <?php echo $service->service_name; ?></h1>
<?php 
//debug($service);
$purchased_on=date("M,dS Y",strtotime($service->purchased_on));
//$consult=explode("|",$service->consultation_time);
//foreach($consult as $key=>$value):
//$status=(strtotime($value) > time())?"(Over)":"";
//$color=(strtotime($value) > time())?"green":"";
//$consultation.="<font color='$color'>".date("M,dS Y h:i:s A",strtotime($value))."</font> $status <br>";
//endforeach;
$consultation=$model->getConsultationTimingsByAppointmentId($service->consultation_time);
$status=(!$service->status)?"Open":"Closed";
?>
<a href='<?php echo $siteurl."/consultation-services/?task=add-new"; ?>' class='button' style='float: right; margin-bottom: -40px; position: relative; bottom: 40px;'>Add New</a>
<hr>
<table width='100%'>
<tr><td width='30%'><label>Service Name</label> :</td><td><?php echo $service->service_name; ?></td></tr>
<tr><td><label>Order No. </label> :</td><td> <?php echo "#".$service->docket; ?></td></tr>
<tr><td><label>Amount Paid </label>  :</td><td><?php echo "$".number_format($service->service_price,2); ?></td></tr>
<tr><td><label>Purchased On </label> :</td><td><?php echo $purchased_on; ?></td></tr>
<tr><td><label>Status </label> :</td><td><?php echo $status; ?></td></tr>
<tr><td style='vertical-align:top'><label>Consultation Timing </label> :</td><td> <?php echo $consultation; ?></td></tr>
<tr><td style='vertical-align:top'><label>Documents </label> :</td><td> <?php echo $model->serviceDocuments($service->id); ?></td></tr>
</table>
<a href='<?php echo $siteurl."/consultation-services"; ?>' class='button' style='float:right;margin-top:5px'>Back</a>
</div>

<style>
label{
        width:180px;
	vertical-align:top;
	color:steelblue;
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

                        