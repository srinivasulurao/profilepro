<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
global $siteurl;
//debug($paypal);
?>
<div><?php
$paymentStatus=$model->completePayment($_REQUEST['order_id']);
$back=($paymentStatus['color']=='red')?"lightpink":"lightgreen";
echo messageUpdateNotification($paymentStatus['msg'],$paymentStatus['color'],$back);
?></div><br>
<div style='width:20%;display:inline-block !important;vertical-align: top;background:steelblue;border-radius:13px;box-shadow:0 0 10px inset'>
<?php 
echo"<ul style='list-style:none;margin:25px' id='profileLinks'>";
echo $profileLinks=$model->profileLinks();
echo"</ul>";
?>
</div>
<div style='width:75%;display:inline-block !important;vertical-align: top;margin-left:20px'>

<?php 
if($paymentStatus['color']=="green"):
echo"<hr><h1>ORDER DETAILS</h1><hr>";
echo"<label>Transaction ID</label>: #{$paymentStatus['orderDetails']['transaction_id']}"."<br>";
echo"<label>Subscription</label>: {$paymentStatus['orderDetails']['subscription']}"."<br>";
echo"<label>Consultation Timings</label><span style='vertical-align:top;display:inline-block'>:</span> <span style='width:250px;display:inline-block'> {$paymentStatus['orderDetails']['consultation_timings']} </span>";
endif;
?>

</div>

<style>

hr{
	border:0px;
	border-bottom:1px solid lightgrey
}
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
</style>

                        