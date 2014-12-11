<?php
/**
 * Plugin Name: Profile Pro
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: Allows to upload documents with payment option.
 * Version: The Plugin's Version Number, e.g.: 2.0
 * Author: N.Srinivasulu Rao
 * Author URI: http://facebook.com/n.srinivasulurao
 * License: A "Slug" license name e.g. GPL2
 */

/** Step 3. */

    //status 
    //open ==0 
    //and close=1 // for the consultation.

error_reporting(-1);
error_reporting(~E_STRICT || ~E_NOTICE);
ob_start();
$siteurl = get_option('siteurl');
$root_path=  getcwd();
$modulePath=$root_path."/wp-content/plugins/profilepro/";
global $wpdb; //This is the wordpress db factory class.

//$userInfo= get_userdata($userId);
add_action('publish_post','profile');
add_action('admin_menu','profileProMenus');
add_shortcode("profiler_user_profile","profile");
add_shortcode('profilepro_services',"services");
add_shortcode('profilepro_documents','documentManager');
add_action( 'wp_ajax_getProfileproPlanDetails', 'getProfileproPlanDetails' );
add_action('wp_ajax_storePaymentSession','storePaymentSession');
add_action("init","updateProfileProServicesIntoScheduleCalendar");
//add_action( 'wp_ajax_nopriv_getProfileproPlanDetails', 'getProfileproPlanDetails');

function updateProfileProServicesIntoScheduleCalendar(){
    global $wpdb;
    $query1="TRUNCATE wp_ap_services";
    $wpdb->query($query1);
    $query2="SELECT * FROM wp_profilepro_services";
    $services=$wpdb->get_results($query2);
    foreach($services as $key):
        $query3="INSERT INTO wp_ap_services SET id='{$key->service_id}', name='{$key->service_name}',availability='yes',duration='{$key->consultation_limit}',unit='minute',cost='{$key->service_price}',paddingtime='0',category_id='2'";
        $wpdb->query($query3);
    endforeach;
     
}

function profileProUserId(){
    $profileProUserId=get_current_user_id();
}

function set_html_content_type() {
return 'text/html';
}

function debug($arrayObject){
    echo"<pre>";
    print_r($arrayObject);
    echo"</pre>";
}

function debug_t($arrayObject){
    echo"<textarea>";
    print_r($arrayObject);
    echo"</textarea>";
}



function profileProMenus(){
    add_menu_page('Profile Pro', 'Profile Pro', 'administrator', 'profilepro-config', 'profileProSettings');
    add_submenu_page( 'profilepro-config', 'Consultation Services', 'Profilepro Services', 'administrator', 'profilepro-services', 'profileProServices' );
    add_submenu_page( 'profilepro-config', 'Consultation Dashboard', 'Profilepro Dashboard', 'administrator', 'profilepro-consultations', 'profileProAdminConsultation' );
        //add_users_page('testing','dashboard', $capability, $menu_slug, $function);
}

function appointmentScheduler(){
    include_once($_SERVER['DOCUMENT_ROOT']."/spetslaw/wp-content/plugins/profilepro/wdCalendar/sample.php");
}


################################################### All front-end stuff kept here########################################################

function restrictUser(){
    //only logged in user is allowed.
    global $user_ID,$siteurl;
    $loginPage=$siteurl."/login-register";
    
    if($user_ID==0):
       header("Location:".$loginPage);
       exit;
    endif;
    
    $servicesTaken=checkUserService();
    if(!$servicesTaken and $user_ID!=1): //Not for the admin, we have to set a role for the attorneys
        $buyservice=$siteurl."/consultation-services?task=add-new";
        header("Location:".$buyservice);
    endif;
}



function checkUserService(){
    global $wpdb,$user_ID;
    $user=get_userdata($user_ID);
    $result=$wpdb->get_results("SELECT * FROM wp_profilepro_user WHERE user_id='$user->ID' and status='0'");
    return sizeof($result);
}

//This is a ajax invoked function.
function getProfileproPlanDetails(){
    global $wpdb;
    $result=$wpdb->get_results("SELECT * FROM wp_profilepro_services WHERE service_id='{$_POST['plan_id']}' LIMIT 0,1");
    echo json_encode($result[0]);
    exit; // the exit is required, or else it is appending a zero at the end.
}

function storePaymentSession(){
    session_start();
    $_SESSION['profileProPayment']['consultation_time']=rtrim($_POST['consultation_time'],"|");
    $_SESSION['profileProPayment']['opted_service']=$_POST['opted_service'];
    echo 1;
    exit;
}
function profile(){
    global $user_ID,$siteurl,$root_path,$modulePath,$userId;
    $task=$_REQUEST['task'];
    $user=get_userdata($user_ID); //this is the user Info.
    restrictUser(); //RestrictUser if he is not logged in or not taken services.
    
    include_once($modulePath."controller.php");
    $controller=new profileProController($user);
    if(!$task):
    $controller->profile();
    endif;
    
    if($task=='change-password'):
    $controller->changePassword();
    endif;
    
    if($task=="edit-user-details"):
    $controller->editClientDetails();
    endif;
    
    if($task=="logout"):
    $root_link=$siteurl;
    wp_logout();
    $logout=$root_link."/login-register";
    header("Location:".$logout);
    endif;
}


function services(){
    global $user_ID,$siteurl,$root_path,$modulePath,$wpdb;
    $task=$_REQUEST['task'];
    $user=get_userdata($user_ID); //this is the user Info.
    include_once($modulePath."controller.php");
    $controller=new profileProController($user);
    
    if(!$task):
        restrictUser();
        $controller->services();
    endif;
    
    if($task=="add-new"):
       
        $controller->addNewService();
    endif;
    
    if($task=="view"):
    
    $controller->viewService();
    endif;
    
    if($task=="order-complete"):
    $controller->orderComplete($_REQUEST['order_id']);
    endif;
}




function documentManager(){
  global $user_ID,$siteurl,$root_path,$modulePath,$wpdb;
    $task=$_REQUEST['task'];
    restrictUser();
    $user=get_userdata($user_ID); //this is the user Info.
    include_once($modulePath."controller.php");
    $controller=new profileProController($user);
    
    if(!$task or $task=='upload'):
    $controller->documentManager();
    endif;
}


###################################################All front-end Stuff Kept till here#####################################################





######################################################All The Admin stuff Kept here.######################################################

function profileProAdminConsultation(){
    global $wpdb;

    performTaskOnUserServiceByAdmin();
    //Now construct the conditions apply.
    $conditionsApply="";
    //debug($_POST);
    if($_POST['docket'])
    $conditionsApply.=" AND  docket LIKE '%{$_POST['docket']}%' ";
    if($_POST['user_id'])
    $conditionsApply.=" AND  u.user_id LIKE '%{$_POST['user_id']}%' ";
    if($_POST['full_name'])
    $conditionsApply.=" AND full_name LIKE '%{$_POST['full_name']}%' ";
    if($_POST['service_id'])
    $conditionsApply.="AND  opted_service LIKE '%{$_POST['service_id']}%' ";
    if($_POST['phone'])
    $conditionsApply.="AND   LIKE '%{$_POST['phone']}%' ";
    if($_POST['user_email'])
    $conditionsApply.="AND   LIKE '%{$_POST['user_email']}%' ";
    
    //change the search a little for status and dates in between.
    if($_POST['status'] or $_POST['status']):
    $postStatus=str_replace("all","",$_POST['status']);
    $conditionsApply.="AND status LIKE '%{$postStatus}%' ";
    endif;
    
    if($_POST['payment_date_from'] and $_POST['payment_date_till']):
    $from=date("Y-m-d h:i:s",strtotime($_POST['payment_date_from']));
    $till=date("Y-m-d h:i:s",strtotime($_POST['payment_date_till']));
    $conditionsApply.="AND $from < user_registered < $till";
    endif;
    
    
    
    $query="SELECT * FROM wp_profilepro_user as u INNER JOIN wp_profilepro_services as s ON u.opted_service=s.service_id  INNER JOIN wp_profilepro_user_info as ui ON u.user_id=ui.user_id INNER JOIN wp_users as wpu ON u.user_id=wpu.ID WHERE 1 $conditionsApply ";
    $result=$wpdb->get_results($query);
    include_once("admin_dashboard.php");
    addCss();
}

function getDocuments($userPlanId){
    global $wpdb,$siteurl;
    $query="SELECT * FROM wp_profilepro_documents WHERE user_plan_id='$userPlanId'";
    $results=$wpdb->get_results($query);
    foreach($results as $key):
    $path=$siteurl."$key->path";
    $docs.="<a href='$path' target='_blank'>".basename($path)."</a><br>";
    endforeach;
    return $docs;
}


function profileProServices(){
    global $wpdb,$siteurl;
    $task=$_REQUEST['task'];
    
    if(!$task):
    $addNew=$siteurl."/wp-admin/admin.php?page=profilepro-services&task=add-new";
    $html.="<h1>List Services</h1><hr><a href='$addNew' class='button-primary' style='float:right;right:10px;position:relative;margin-bottom:10px'>New</a>";
    $services=$wpdb->get_results("SELECT * FROM wp_profilepro_services ORDER BY service_id ASC");
    $html.="<table style='width:98%;;border:1px solid lightgrey' cellspacing='0' cellpadding='0'>";
    $html.="<tr><th>Sl.NO</th><th>Service Name</th><th>Price</th><th>Consultation Limit</th><th>Edit</th><th>Delete</th></tr>";
    $i=1;
    foreach($services as $key):
    $price=number_format($key->service_price,2);
    $delete="<a href='javascript:void(0)' style='color:black;background:tomato; text-decoration: none; padding: 1px 10px; border-radius: 5px;'>Delete</a>";
    $edit="<a style='color:black;background: none repeat scroll 0% 0% dodgerblue; text-decoration: none; padding: 1px 10px; border-radius: 5px;' href='$siteurl/wp-admin/admin.php?page=profilepro-services&task=edit&service=$key->service_id'>Edit</a>";
    $html.="<tr><td>$i .</td><td>$key->service_name</td><td>$$price</td><td style='padding-left:40px;'>$key->consultation_limit times</td><td>$edit</td><td>$delete</td></tr>";
    $i++;
    endforeach;
    $html.="</table>";
    endif;
    
    if($task=='edit' and $_REQUEST['service']):
        
        if($_POST['editService']):
            $wpdb->query("UPDATE wp_profilepro_services SET service_name='{$_POST['service_name']}', consultation_limit='{$_POST['consultation_limit']}', service_price='{$_POST['service_price']}', attorney_name='{$_POST['attorney_name']}', attorney_email='{$_POST['attorney_email']}' WHERE service_id='{$_POST['service_id']}'");
        endif;
        $service=$wpdb->get_results("SELECT * FROM wp_profilepro_services WHERE service_id='{$_REQUEST['service']}'");
        $service=$service[0];
        $back=$siteurl."/wp-admin/admin.php?page=profilepro-services";
        $html="<h1>Edit Services - $service->service_name</h1><hr>";
        $html.="<form method='post' action=''>";
        $html.="<label>Service Name </label><input type='text' name='service_name' value='$service->service_name'><br><br>";
        $html.="<label>Consultation Price</label><input type='text' name='service_price' value='$service->service_price'>(in dollars)<br><br>";
        $html.="<label>Consultation Limit</label><input type='text' name='consultation_limit' value='$service->consultation_limit'><br><br>";
        $html.="<label>Attorney Name</label><input type='text' name='attorney_name' value='$service->attorney_name'><br><br>";
        $html.="<label>Attorney Email</label><input type='text' name='attorney_email' value='$service->attorney_email'>";
        $html.="<input type='hidden' name='service_id' value='$service->service_id'><br><br>";
        $html.="<label>&nbsp</label><input value='Save' name='editService' type='submit' class='button button-primary'>&nbsp <a href='$back' class='button button-warning'>Back</a>";
        $html.="</form>";
    endif;
    
    if($task=='add-new'):
        
        if($_POST['addService']):
            $wpdb->query("INSERT INTO wp_profilepro_services SET service_name='{$_POST['service_name']}', consultation_limit='{$_POST['consultation_limit']}', service_price='{$_POST['service_price']}',attorney_name='{$_POST['attorney_name']}', attorney_email='{$_POST['attorney_email']}'");
        endif;
        
        $back=$siteurl."/wp-admin/admin.php?page=profilepro-services";
        $html="<h1>Add New Services</h1><hr>";
        $html.="<form method='post' action=''>";
        $html.="<label>Service Name </label><input type='text' name='service_name' ><br><br>";
        $html.="<label>Consultation Price</label><input type='text' name='service_price' >(in dollars)<br><br>";
        $html.="<label>Consultation Limit</label><input type='text' name='consultation_limit' >";
        $html.="<label>Attorney Name</label><input type='text' name='attorney_name' >";
        $html.="<label>Attorney Email</label><input type='text' name='attorney_email' >";
        $html.="<input type='hidden' name='service_id' ><br><br>";
        $html.="<label>&nbsp</label><input value='Save' name='addService' type='submit' class='button button-primary'>&nbsp <a href='$back' class='button button-warning'>Back</a>";
        $html.="</form>";
    endif;
    
    
    
    addCss();
    echo $html;
    return true;
}

function profileProSettings(){
    
    error_reporting(~E_NOTICE);
    if(isset($_POST['subLawpaySettings'])):
      update_option('lawpay_publisher_name',$_POST['lawpay_publisher_name']);
      update_option('lawpay_business_email',$_POST['lawpay_business_email']);
      update_option('lawpay_currency',$_POST['lawpay_currency']);
      update_option('lawpay_action',$_POST['lawpay_action']);
      update_option('lawpay_item_description',$_POST['lawpay_item_description']);
    endif;
    $pbn=get_option('lawpay_publisher_name');
    $pbe=get_option('lawpay_business_email');
    $pc=get_option('lawpay_currency');
    $pa=get_option('lawpay_action');
    $live=($pa==1)?"checked='checked'":" ";
    $sandbox=(!$pa)?"checked='checked'":'';
    $pid=get_option('lawpay_item_description');
    
    
    $html="<h1>Lawpay Payment Gateway Settings</h1><hr>";
    $html.="<div style='line-height:40px'>";
    $html.="<form method='post' action=''>";
    $html.="<label>Lawpay Publisher Name</label><input type='text' name='lawpay_publisher_name' value='$pbn'><br>";
    $html.="<label>Lawpay Business Email</label><input type='text' name='lawpay_business_email' value='$pbe'><br>";
    $html.="<label>Currency</label><input type='text' name='lawpay_currency' value='{$pc}'>(Ex: USD)<br>";
    $html.="<label>Lawpal Action</label><input type='radio'  name='lawpay_action' value='1' $live>Live &nbsp <input $sandbox value='0' type='radio' name='lawpay_action'>Sandbox <br>";
    $html.="<label>Item Description</label><input type='text' name='lawpay_item_description' value='$pid'><br>";
    $html.="<label></label><input type='submit' class='button button-primary button-large' name='subLawpaySettings' id='subLawpaySettings' value='Submit'><br>";  
    $html.="</form>";
    $html.="</div>";
    
    echo $html;
    addCss();
    return true;
}


function profileproNotices(){
    if($_POST['subLawpaySettings']):
    $message="Lawpay Settings Saved Successfully !";
    echo messageUpdateNotification($message);
    endif;
    
    if($_POST['editService']):
        $message="Service Details Updated Successfully !";
        echo messageUpdateNotification($message);
    endif;
    
     if($_POST['addService']):
        $message="New Service Details added Successfully !";
        echo messageUpdateNotification($message);
    endif;

    if($_POST['performTask']=="changeServiceStatus"):
        $message="Client's service status changed successfully!";
        echo messageUpdateNotification($message);
    endif;

    if($_POST['performTask']=="deleteUserService"):
        $message="Service taken by the client deleted successfully!";
        echo messageUpdateNotification($message);
    endif;
    
    if($_POST['sendMailToClient']):
        $headers = 'From: Spetlaw <'.$_POST['from'].'>' . "\r\n";
        add_filter('wp_mail_content_type','set_html_content_type');
        if(wp_mail($_POST['to'], $_POST['subject'], $_POST['message'], $headers))
            echo messageUpdateNotification("Mail has been successfully sent to the client !");
       else
            echo messageUpdateNotification("Something is wrong with mail server configuration, can't send the mail now","red","pink");
        
    endif;
    
}
function performTaskOnUserServiceByAdmin(){
    global $wpdb;
if($_POST['performTask']=="changeServiceStatus"):
$user_service_id=$_POST['user_service_id'];
$status=$_POST['conveyValue'];
$wpdb->query("UPDATE wp_profilepro_user SET status='$status' WHERE id='$user_service_id'");
endif;

if($_POST['performTask']=="deleteUserService"):
$user_service_id=$_POST['user_service_id'];
$wpdb->query("DELETE from wp_profilepro_user WHERE id='$user_service_id'");
endif;

}

 function messageUpdateNotification($message="Updated Successfully !",$type='green',$back="#AAFFAC"){
     $tick=($type=="green")?"<b style='background: none repeat scroll 0% 0% steelblue; color: white; border-radius: 30px; padding: 2px 6px;'>&#x2714;</b>":"";
     return "<font color='$type' style='padding: 10px; display: block; border: 1px solid lightgrey; border-radius: 7px; margin-top: 10px; width: 97%;background:$back;'>$tick &nbsp $message</font>";
 }
add_action('admin_notices','profileProNotices');
function addCss(){
    $str = <<<xyz
<style>
         label{
            width:150px;
            display:inline-block;
            }
          input[type='text']{
            width:300px;
            }
            th{
            text-align:left;
            background:steelblue;
            color:white;
            padding:10px;
            }
            
            td{
            padding:10px;
            }
            
            tr:nth-child(odd){
            background:lightgrey;
            }
            tr:nth-child(even){
            background:lightyellow;
            }
</style>
xyz;
    echo $str;
}

##############################################Admin Stuff Present till here###############################################################