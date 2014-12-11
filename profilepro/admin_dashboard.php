<h1>Admin Dashboard</h1><hr>
<table width='99%' cellspacing='0' cellpadding='0' id='example' style='border:1px solid lightgrey;overflow-y:auto;display:blocvk;font-size:12px'>
<form method='post'>
    <tr style='background:tomato'><td><input type='text' name='docket' value='<?php echo $_POST['docket'];  ?>' placeholder="Docket"></td>
        <td><input type='text' name='user_id' value='<?php echo $_POST['user_id'];  ?>' placeholder="Client Id"></td>
<td><input type='text' name='full_name' value='<?php echo $_POST['full_name'];  ?>' placeholder="Name"></td>
<td><select name='service_id' style='width:100px'>
<option value=''>-Select Service-</option>
<?php

$services=$wpdb->get_results("SELECT * FROM wp_profilepro_services ORDER BY service_id ASC");

foreach($services as $key):
$sel=($key->service_id==$_POST['service_id'])?"selected='selected'":'';
echo "<option value='$key->service_id' $sel>$key->service_name</option>";
endforeach;
?>
</select>
</td>
<td><input type='text' name='payment_date_from' style='width:100px' id='datepicker' value='<?php echo $_POST['payment_date_from'];  ?>' placeholder="Start Date"> <br>
    <input type='text' name='payment_date_till' id='datepicker2' style='width:100px' value='<?php echo $_POST['payment_date_till'];  ?>' placeholder="End Date">
</td>
<td>&nbsp</td>
<td><input type='text' name='phone' value='<?php echo $_POST['phone'];  ?>' placeholder="phone"></td>
<td colspan='3'><input type='text' style='width:200px' name='user_email' value='<?php echo $_POST['user_email'];  ?>'></td>

<td>
<?php 
$all=($_POST['status']=="all")?"selected='selected'":"";
$open=($_POST['status']==0 and $_POST['status']!="all" and $_POST['status']!=1)?"selected='selected'":"";
$closed=($_POST['status']==1)?"selected='selected'":"";
?>
<select name='status' style='width:50px'>
<option value='all' <?php echo $all; ?>>All</option>
<option value='0' <?php echo $open; ?>>Open</option>
<option value='1' <?php echo $closed; ?>>Closed</option>
</select>
</td>
<td><input type='submit' class='button-primary' name='profileProSearch'  value='Search'></td>
<!--<td>&nbsp</td>-->
<tr>
</form>
<tr><th>Docket#</th><th>Client Id</th><th>Client Name</th><th>Service</th><th>Payment Date</th>
    <th>Payment Amount</th><th>Phone</th><th>Email</th><th>Consultation Dates</th><th>View Documents</th><th>Status</th><th>Action</th><!--<th>Action</th>--></tr>
<?php 
//debug($result);

foreach($result as $key):
$status=(!$key->status)?"Open":"Closed";
//$consult_time=explode("|",$key->consultation_time);
//$consultation_dates="";
//foreach($consult_time as $time):
//$consultation_dates.=date("m/d/y h:i:s A",strtotime($time))."<br>";
//endforeach;
$appointments=explode(",",$key->consultation_time);
$consultation_dates="";
foreach($appointments as $value):
        if(!$value):
        "No Appointment Dates found";
        $consultation_dates="No Appointments found";
        break;
        endif;
        $result=$wpdb->get_results("SELECT * FROM wp_ap_appointments WHERE id='$value'");
        $consultation_dates.="<font color='seagreen'>".date("m/d/y h:i A", strtotime($result[0]->date." ".$result[0]->start_time))."</font> -To- <font color='tomato'>".date("m/d/y h:i A", strtotime($result[0]->date." ".$result[0]->end_time))."</font><hr>";
endforeach;
$consultation_dates=rtrim($consultation_dates,"<hr>");
    
echo "<tr><td>#{$key->docket}</td>";
echo "<td>{$key->user_id}</td>";
echo "<td>{$key->full_name}</td>";
echo "<td>{$key->service_name}</td>";
echo "<td>".date("M,dS Y",strtotime($key->purchased_on))."</td>";
echo "<td>$".number_format($key->service_price,2)."</td>";
echo "<td>{$key->phone}</td>";
echo "<td>".wordwrap($key->user_email,10,"<br>")."</td>";
echo "<td>{$consultation_dates}</td>";
echo "<td>".getDocuments($key->id)."</td>";
echo "<td>$status</td>";
//echo "<td>{}</td>";
// echo "<td>{}</td>";
$changeStatus="<a href='javascript:void(0)' class='button button-primary' onclick='changeStatus($key->id,$key->status)' style='margin-top:5px;width: 108px; text-align: center;'>Change Status</a>";
$deleteRow="<a href='javascript:void(0)' class='button' onclick='deleteRow($key->id)' style='margin-top:5px;border:1px solid red;color:red;color:white;background:red;width: 108px; text-align: center;'>Delete</a>";
echo "<td><a href='javascript:void(0)' style=\"width: 108px; text-align: center;\" class='button' onclick=\"sendEmail('$key->user_email','$key->attorney_email')\">Email</a><br>$changeStatus<br>$deleteRow<br></td>";
//echo "<td><a href='$link' class='button-primary'>View</a></td></tr>";
endforeach;
?>
</table>

<form name='performTaskForm' method='post' action=''>
<input type='hidden' name='performTask' id='performTask'>
<input type='hidden' name='user_service_id' id='user_service_id'>
<input type='hidden' name='conveyValue' id='conveyValue'>
</form>

<script>
function deleteRow(id){
conf=window.confirm("Are you sure, you want to delete this service taken by the client?");
if(conf){
document.getElementById('performTask').value="deleteUserService";
document.getElementById('user_service_id').value=id;
document.performTaskForm.submit();
}

}

function changeStatus(id,val){
var orig_val=(val==1)?0:1;
document.getElementById('performTask').value="changeServiceStatus";
document.getElementById('user_service_id').value=id;
document.getElementById('conveyValue').value=orig_val;
document.performTaskForm.submit();
}

</script>

<style>
td input[type='text']{
	width:70px;
}

td,th{
	width:10% !important;
}
select{
	font-size:10px;
}
</style>

<link rel="stylesheet" href="//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
<script>
$(function() {
$( "#datepicker,#datepicker2" ).datepicker({format:"mm/dd/yy"});
});


function sendEmail(to,from){
    $("#newSplashScreenDiv").fadeIn();
    $("[name='to']").val(to);
    $("[name='from']").val(from);
   
}
</script>

<div id='splashScreenOverlay'></div>
<div style="top: 35%; height: 100px; display:none;" id="newSplashScreenDiv">
<a href="javascript:void(0);" class="close"><img src="http://appddictionstudio.biz/conferencecms//mega_css/images/closebutton.png" class="btn_close2" title="Close Window" alt="Close"></a>
    <big style="color:#1377C3">Send Mail To Client</big> <hr>

    <form enctype="multipart/form-data" action="" method="post">
        <label>To</label><br>
        <input type="text" required="required" name="to"><br>
        <label>From</label><br>
        <input type="text" name="from" required='required'><br>
        <label>Subject</label><br>
        <input type="text" name="subject" required='required'><br>
        <label>Message</label><br>
        <textarea name='message'></textarea>
        <input type="submit" style="margin-left:135px;margin-top:15px;" value="Send Mail" name="sendMailToClient" class='button-primary'>
    </form>
<style>
    #splashScreenOverlay,#splashScreenOverlay2{
        background: black;
        opacity:0.7;
        top:0px;
        height:100%;
        width:100%;
        position: absolute;
        display: none;
    }
    #newSplashScreenDiv input[type='text']{
        border:1px solid lightgrey;
        width:350px;
    }
    #newSplashScreenDiv textarea{
        width:100%;
        height:130px;
    }
    

    #newSplashScreenDiv,#newSplashScreenDiv2{
        width:350px;
        height:350px !important;
        border:15px solid black;
        background:white;
        z-index: 501;
        border-radius:20px;
        top:13% !important;
        left:37%;
        position: fixed;
        padding:30px;
        padding-top:10px;
        line-height: 16px;
    }

    #newSplashScreenDiv select{
        width:200px;
    }

    #newSplashScreenDiv input[type='file']{
        margin:10px;
    }
    
    .close{
        color: #000;
    float: right;
    font-size: 20px;
    font-weight: bold;
    line-height: 20px;
    opacity: 0.2;
    position: absolute;
    right: 5px;
    text-shadow: 0 1px 0 #fff;
    top: 2px;
    }
</style>




<script type="text/javascript">
    $(".active").removeClass("active");
    $("#conference").addClass("active");


    $("#newSplashScreen").click(function(){

        $("#splashScreenOverlay").fadeToggle();
        $("#newSplashScreenDiv").fadeToggle();

    });

  

    $(".btn_close2").click(function(){
        
        $("#newSplashScreenDiv").fadeOut();
    });

    function askConfirmation(){
        conf=window.confirm("Are you sure You want to Delete this ");
        if(conf==false)
            return false;
    }


    function editMenuIcon(appModuleIconId){
        $("#splashScreenOverlay2").fadeToggle();
        $("#newSplashScreenDiv2").fadeToggle();
        $("#appModuleIconId").val(appModuleIconId);
    }
</script>



</div>


