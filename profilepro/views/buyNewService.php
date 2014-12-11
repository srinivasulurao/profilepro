<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
global $siteurl;
echo $model->newCustomerNotification();
$paypal=$model->getPaypalPaymentParameters();
//debug($paypal);
?>
<br><br>
<div style='width:20%;display:inline-block !important;vertical-align: top;background:steelblue;border-radius:13px;box-shadow:0 0 10px inset'>
<?php 
echo"<ul style='list-style:none;margin:25px' id='profileLinks'>";
echo $profileLinks=$model->profileLinks();
echo"</ul>";
?>
</div>
<div style='width:75%;display:inline-block !important;vertical-align: top;margin-left:20px'>
<form action="<?php echo $paypal['actionUrl']; ?>" method="post" name="paypal_form" onsubmit="return storeSession()">
<div style='min-height:200px'>
    <label>Select Service</label>:
    <select name='opted_service' required='required' onchange='getPlan()'>
        <option value=''>--SELECT--</option>
        <?php
        foreach($model->getServicesList() as $key):
            echo"<option value='$key->service_id'>$key->service_name</option>";
        endforeach;
        ?>
    </select>
    <br>
        <label style='vertical-align:top'>Select Consultation Timings</label><span style='display:inline-block;vertical-align:top'>:</span>
        <span id='consultationTime' style='width:400px;display:inline-block'>
        Please select the service to select the time.
        </span>
   </div>
  <input type="hidden" name="cmd" value="_cart">
  <input type="hidden" name="upload" value="1">
  <input type="hidden" name="currency_code" value="<?php echo $paypal['businessEmail']; ?>">
  <input type="hidden" name="return" value="<?php echo $paypal['returnUrl']; ?>">
  <input type="hidden" name="cancel_return" value="<?php echo $paypal['cancelUrl']; ?>">
  <input type="hidden" name="rm" value="2">  
  <input type="hidden" name="cbt" value= "Please Click Here to Finalize the Payment">
  <input type="hidden" name="business" value="<?php echo $paypal['businessEmail']; ?>">
  <input type="hidden" name="item_name_1" value="<?php echo $paypal['itemDescription']; ?>">
  <input type="hidden" name="amount_1" value="" required='required'>
  <input type="hidden" name="quantity_1" value="1" >
  <input type="hidden" name="no_shipping" value="1">
  <input type="hidden" name="image_url" value="<?php echo $siteurl; ?>/wp-content/uploads/2014/11/logo-340x60.jpg">
  <input type="hidden" value="PayPal"><br>
  <input style='margin-left:200px' type="image" src='http://empiresweepstakesconvention.com/wp-content/uploads/2013/09/paypal-button.png' value='Submit Paymeny'>
</form>
</div>
<style>
    label{
        width:200px;
    }
    </style>
    
    
<script>
function getPlan(){
	var plan_id=jQuery("[name='opted_service']").val();
	if(!plan_id){
		jQuery('#consultationTime').html("Please select the service to select the time.");
	}
jQuery.ajax({
	type:"POST",
	url: "<?php echo $siteurl; ?>/wp-admin/admin-ajax.php", // our PHP handler file
	data:{action:"getProfileproPlanDetails",'plan_id':plan_id},
	success:function(result){
		var rec=JSON.parse(result);
	    jQuery("[name='amount_1']").val(rec.service_price);	    
	    jQuery("[name='item_name_1']").val(rec.service_name);
	    var consultationTime="";
	    for(i=1;i<=rec.consultation_limit;i++){
	    consultationTime+="<input type='text' placeholder='mm/dd/yy h:i:s AM/PM' id='consultationTimes' required='required' name='consultation_time_"+i+"'><br>";
	    }
	    jQuery('#consultationTime').html(consultationTime);
	}
});
}


function storeSession(){
	var consultation_time="";
	jQuery("#consultationTime input[type='text']").each(function(){
		consultation_time+=jQuery(this).val()+"|";
	});
	
	jQuery.ajax({
		type:"POST",
		url: "<?php echo $siteurl; ?>/wp-admin/admin-ajax.php", // our PHP handler file
		data:{action:"storePaymentSession",opted_service:jQuery("[name='opted_service']").val(),consultation_time:consultation_time},
		success:function(result){
			if(result)
			document.paypal_form.submit();
			else
		    return false;
		}
	});
	
	return false;
}
</script>

<link rel="stylesheet" type="text/css" href="<?php echo $siteurl; ?>/wp-content/plugins/profilepro/css/jquery.datetimepicker.css"/ >
<script src="<?php echo $siteurl; ?>/wp-content/plugins/profilepro/js/dateTimePicker/jquery.datetimepicker.js"></script>
<script>
$=jQuery.noConflict();
        $( "#consultationTimes" ).datetimepicker({
            format:'m/d/Y h:i A'
        });

</script>

<style>
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
}
</style>

                        