<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
global $siteurl;
echo $model->newCustomerNotification();
$appointments=$model->getLawpayPaymentAppointments();
$paymentNotificationMessage=$model->lawPayPaymentProcessor();
$usaStates=$model->usaStates();
//debug($appointments);
?>
<div><?php echo $paymentNotificationMessage; ?></div><br>
<div style='width:20%;display:inline-block !important;vertical-align: top;background:steelblue;border-radius:13px;box-shadow:0 0 10px inset'>
<?php 
echo"<ul style='list-style:none;margin:25px' id='profileLinks'>";
echo $profileLinks=$model->profileLinks();
echo"</ul>";
?>
</div>
<div style='width:75%;display:inline-block !important;vertical-align: top;margin-left:20px'>
    <form method='post' action='' onsubmit='return validatePaymentForm()'>
        <h1>Select Service</h1><hr>
        <label>Service Name</label>: <select name='law_service' id='law_service' required='requried' onchange='hideShowAppointments(this.value)'>
            <option value=''>--SELECT SERVICE--</option>
            <?php
        foreach($model->getServicesList() as $key):
            $service_sel=($key->service_id==$_POST['law_service'])?"selected='selected'":"";
            echo"<option value='$key->service_id' $service_sel>$key->service_name</option>";
        endforeach;
        ?>
        </select><br>
        
        <label>Appointment Dates</label>: <select name='appointment_1' id='appointment_1' required='requried'>
            <option value=''>--Select Appointment--</option>
            <?php
            
            foreach($appointments as $ap_key):
                $appointment_time_text=date("m-d-y h:i A",strtotime($ap_key->date." ".$ap_key->start_time))." -to- ".date("m-d-y h:i A",strtotime($ap_key->date." ".$ap_key->end_time));
                echo"<option value='{$ap_key->ap_id}' id='$ap_key->service_id' style='display:none'>$appointment_time_text</option>";
            endforeach;
            ?>
        </select><br>
        <label>&nbsp</label>: <select name='appointment_2' id='appointment_2' required='requried'>
            <option value=''>--Select Appointment--</option>
            <?php
            
            foreach($appointments as $ap_key):
                $appointment_time_text=date("m-d-y h:i A",strtotime($ap_key->date." ".$ap_key->start_time))." -to- ".date("m-d-y h:i A",strtotime($ap_key->date." ".$ap_key->end_time));
                echo"<option value='{$ap_key->ap_id}' id='$ap_key->service_id' style='display:none'>$appointment_time_text</option>";
            endforeach;
            ?>
        </select><br>
        <label>&nbsp</label>: <select name='appointment_3' id='appointment_3' required='requried'>
            <option value=''>--Select Appointment--</option>
            <?php
            
            foreach($appointments as $ap_key):
                $appointment_time_text=date("m-d-y h:i A",strtotime($ap_key->date." ".$ap_key->start_time))." -to- ".date("m-d-y h:i A",strtotime($ap_key->date." ".$ap_key->end_time));
                echo"<option $ap3_sel value='{$ap_key->ap_id}' id='$ap_key->service_id' style='display:none'>$appointment_time_text</option>";
            endforeach;
            ?>
        </select><br>
<h1>Fill Your Details</h1><hr>
<label>Name</label>: <INPUT type="text" name="card_name" size="30" maxlength="30" required='requried' value='<?php echo $_POST['card_name']; ?>'>
<br><label>Address1</label>: <INPUT type="text" name="card_address1" size="30" maxlength="30" required='requried' value='<?php echo $_POST['card_address1']; ?>'>
<br><label>Address2</label>: <INPUT type="text" name="card_address2" size="30" maxlength="30" required='requried' value='<?php echo $_POST['card_address2']; ?>'>
<br><label>City</label>: <INPUT type="text" name="card_city" size="30" maxlength="30" required='requried' value='<?php echo $_POST['card_city']; ?>'>
<br><label>State</label>: <select name="card_state"  maxlength="2" required='requried' style='width:375px'>
    <option value=''>--SELECT STATE--</option>
    <?php
    foreach($usaStates as $key=>$value):
    $stateSel=($key==$_POST['card_state'])?"selected='selected'":"";
    echo "<option value='$key' $stateSel>$value</option>";
    endforeach;
    ?>
    </select>
<br><label>Zip</label>: <INPUT type="text" name="card_zip" size="12" maxlength="12" required='requried' value='<?php echo $_POST['card_zip']; ?>'>
<h1 style='margin-top:10px;'>Payment Card Details</h1><hr>
<label>Consultation Amount</label>: <INPUT type="text" size="6" id='consultationAmount' maxlength="6" disabled style='border:1px solid white' value='Select Service'>
    <br><label>Card Number</label>: <INPUT type="text" name="card_number" size="20" maxlength="20" value='' required='requried'>
    <br><label>Card Exp Date</label>: 
    <select name='expiration_month' style="width:150px"><option value=''>--Month--</option>
    <?php
    for($i=1;$i<=12;$i++):
        echo"<option value='$i'>$i</option>";
    endfor;
    ?>
    </select>
    
    <select name='expiration_year' style="width:221px"><option value=''>--Year--</option>
    <?php
    for($i=14;$i<=24;$i++):
        echo"<option value='$i'>20$i</option>";
    endfor;
    ?>
    </select>
    <br><label>CVV</label>: <INPUT type="text" name="card_cvv" size="20" maxlength="20" value='' required='requried'>


<br><label>&nbsp</label><span style='display:inline-block;margin-left:100px'><INPUT type="submit" value="Submit" name='processLawPay'></span>
       <?php
        foreach($model->getServicesList() as $key):
        echo "<input type='hidden' id='service_{$key->service_id}' value='$key->service_price'>";
        endforeach;
        ?>
<!--<input type='hidden' name='card_amount' size='6' maxlength="6">
<INPUT type="hidden" name="publisher_name" value="pnpdemo">
<INPUT type="hidden" name="publisher_email" value="trash@plugnpay.com">-->
    </form>
</div>
<style>
    label{
        width:150px;
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


function validatePaymentForm(){
var appointment_1=jQuery("#appointment_1").val();
var appointment_2=jQuery("#appointment_2").val();
var appointment_3=jQuery("#appointment_3").val();

if(appointment_1==appointment_2 || appointment_2==appointment_3 || appointment_3==appointment_1){
    alert("Please select different timing of the appointments");
    jQuery("#appointment_1").css("border","1px solid red");
    jQuery("#appointment_2").css("border","1px solid red");
    jQuery("#appointment_3").css("border","1px solid red");
    return false;
 }
 
 return true;
}

jQuery("select").focusin(function(){
  jQuery(this).css("border","1px solid lightgrey");
});


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

hideShowAppointments(0);

function hideShowAppointments(service_id){
service_id=(!service_id)?jQuery("#law_service").val():service_id;
if(service_id){
jQuery("#appointment_1").val("");
jQuery("#appointment_2").val("");
jQuery("#appointment_3").val("");

jQuery("#appointment_1 option").css("display","none");
jQuery("#appointment_1 #"+service_id).each(function(){
jQuery(this).css("display","block");     
});

jQuery("#appointment_2 option").css("display","none");
jQuery("#appointment_2 #"+service_id).each(function(){
jQuery(this).css("display","block");     
});

jQuery("#appointment_3 option").css("display","none");
jQuery("#appointment_3 #"+service_id).each(function(){
jQuery(this).css("display","block");     
});

//Update the consultation amount, just for showing.
var servicePrice=jQuery("#service_"+service_id).val();
jQuery("#consultationAmount").val("$"+parseFloat(servicePrice).toFixed(2));
}
else
{
jQuery("#appointment_1 option").css("display","none");  
jQuery("#appointment_1").val(""); // Awesome this is working like a charm.

jQuery("#appointment_2 option").css("display","none");    
jQuery("#appointment_2").val("");

jQuery("#appointment_3 option").css("display","none");
jQuery("#appointment_3").val("");
jQuery("#consultationAmount").val("Select Service");

}

}//the function ends here.
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

hr{
    border:0px;
    border-bottom:1px solid lightgrey;
}
</style>

                        