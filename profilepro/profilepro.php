<?php
/**
 * Plugin Name: Profile Pro
 * Plugin URI: http://www.omkarsoft.com
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
add_action("init","updateProfileProServicesIntoScheduleCalendar");

//some Ajax Actions
//add_action( 'wp_ajax_getProfileproPlanDetails', 'getProfileproPlanDetails' ); // We are not using this anymore.
//add_action('wp_ajax_storePaymentSession','storePaymentSession'); // We are not using this anymore.
add_action('wp_ajax_getCalendarAppointments','getCalendarAppointments');
add_action('wp_ajax_checkSlotsAvailability','checkSlotsAvailability');
add_action('wp_ajax_assignAppointmentByAdmin','assignAppointmentByAdmin');

function checkSlotsAvailability(){
    global $wpdb;
   //we need to send the slot unavailability thing from here only.
    $plan_id=$_POST['plan_id'];
    $result=array();
    $result['result']="success"; // initially we have to make sure, its available.
    $result['msg']="slots are available";
    $notAvailable=array();
    $ap1=$_POST['appointment_1'];
    $ap2=$_POST['appointment_2'];
    $ap3=$_POST['appointment_3'];
    
    $bookedAppointments=$wpdb->get_results("SELECT consultation_time as booked,service_type FROM wp_profilepro_user as u INNER JOIN wp_profilepro_services as s ON u.opted_service=s.service_id");
       foreach($bookedAppointments as $bk):
       //just the consultation type services will be chosen.
       if($bk->service_type)
       $booked.=$bk->booked.",";
       endforeach;
       $booked=rtrim($booked,",");
       $bookedArray=explode(",",$booked);
       
    for($i=1;$i<4;$i++):
    $apppointment=${ap.$i};
    if(in_array($apppointment,$bookedArray)):
    $repeated=$wpdb->get_results("SELECT * FROM wp_ap_appointments WHERE id='$apppointment'");
    $notAvailable[]=$repeated[0]->start_time.'-'.$repeated[0]->end_time." on ".date("m-d-Y", strtotime($repeated[0]->date));
    $result['result']="failed";
    endif;
    endfor;
    
    if(sizeof($notAvailable))
    $result['msg']="The following slots are not available, please select the slots again !\n".implode("\n",$notAvailable)."";
    
    echo json_encode($result);  
    exit;
}


function getCalendarAppointments(){
    global $wpdb;
    $dateSelect=$_POST['dateText'];
    $dateSelect=date("Y-m-d",strtotime($dateSelect));
    $bookedAppointments=$wpdb->get_results("SELECT consultation_time as booked,service_type FROM wp_profilepro_user as u INNER JOIN wp_profilepro_services as s ON u.opted_service=s.service_id");
    foreach($bookedAppointments as $bk):
    //just the consultation type services will be chosen.
    if($bk->service_type)
    $booked.=$bk->booked.",";
    endforeach;
    $booked=rtrim($booked,",");
    $bookedArray=explode(",",$booked);
    $results=$wpdb->get_results("SELECT * FROM wp_ap_appointments WHERE date='$dateSelect' AND service_id='{$_POST['plan_id']}' AND FIND_IN_SET(id,'$booked')=0 GROUP BY start_time");
    //debug($results);
    //echo "SELECT * FROM wp_ap_appointments WHERE date='$dateSelect' AND service_id='{$_POST['plan_id']}' AND FIND_IN_SET(id,'$booked')=0 GROUP BY start_time";
    if(!sizeof($results)):
        echo "<font color='red'>Sorry, No appointments are <br>available on this day !</font>";
    endif;
    if(sizeof($results)):
        echo "<font color='green'><u>Available Appointments on {$_POST['dateText']}</u></font><br><br>";
        echo"<ul>";
        foreach($results as $ap):
            $apText=$ap->start_time."-".$ap->end_time." on ".$_POST['dateText'];
            //if(!in_array($ap->id,$bookedArray))
            echo "<li style='list-style:none'><a onclick=\"addAppointmentDate('$apText','$ap->id')\" style='margin-left:-10px;font-family: lucida sans unicode; font-size: small; box-shadow: 0px 0px 10px grey inset; border-radius: 20px; display: inline-block; margin-bottom: 3px; padding: 2px 10px;' href='javascript:void(0)'>{$ap->start_time}-{$ap->end_time}</a></li>";
        endforeach;
        echo"</ul>";
    endif;
    
    exit;
}


function assignAppointmentByAdmin(){
  global $wpdb;
  $id=$_POST['opted_user_service'];
  $result=$wpdb->get_results("SELECT * FROM wp_profilepro_user WHERE id='$id'");
  $consultation_time=$result[0]->consultation_time;
  $applied_consultation=explode(",",$result[0]->applied_consultation_time);
  $cse=explode(",",$consultation_time);
  echo $text="Following are the appointment Dates Chosen by the client, please assign one appointment date to the client by choosing one out of tht three.<br><br>";
  $i=1;
  foreach($applied_consultation as $value):
      $checked=(in_array($value,$cse))?"checked='checked'":"";
      $appointmentDetails=getAppointment($value);
      $appointmentText=date("m/d/Y",strtotime($appointmentDetails->date))." At ".$appointmentDetails->start_time." -To- ".$appointmentDetails->end_time;
      echo"<input name='fixAppointment[]' id='fixAppointment_$i' type='checkbox' value='$value' $checked>".$appointmentText."<br><br>";
  $i++;
  endforeach;
  echo "<input type='hidden' id='fixAppointmentId' value='$id'>";
  echo "<br><input onclick='fixAppointment()' type='button' class='button button-primary' name='assignAppointment' value='Fix Appointment'>";
  exit; //This is necessary, because after this some anonymous value is coming, don't know where is coming.  
}

function getAppointment($ap_id){
    global $wpdb;
    $result=$wpdb->get_results("SELECT * FROM wp_ap_appointments where id='$ap_id'");
    return $result[0];
}

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

function profilePro_current_page_url() {
	$pageURL = 'http';
	if( isset($_SERVER["HTTPS"]) ) {
		if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	return $pageURL;
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

add_action("wp_login","redirectToProfileProDashboard");
function redirectToProfileProDashboard(){
    global $siteurl,$user_ID;
    if($user_ID==1):
    header("Location:".$siteurl."/wp-admin/admin.php?page=profilepro-consultations");
    exit;
    endif;
}

function hide_update_notice() {
   remove_action( 'admin_notices', 'update_nag', 3 );
}
add_action( 'admin_notices', 'hide_update_notice', 1 );

function profileProMenus(){
    add_menu_page('Profile Pro', 'Profile Pro', 'administrator', 'profilepro-config', 'profileProSettings');
    add_submenu_page( 'profilepro-config', 'Consultation Services', 'Profilepro Services', 'administrator', 'profilepro-services', 'profileProServices' );
    add_submenu_page( 'profilepro-config', 'Consultation Dashboard', 'Profilepro Dashboard', 'administrator', 'profilepro-consultations', 'profileProAdminConsultation' );
    add_submenu_page( 'profilepro-config', 'Google Calendar Sync', 'Google Calendar Sync', 'administrator', 'google-calendar-sync', 'googleCalendarSynchronization' );
 
//add_users_page('testing','dashboard', $capability, $menu_slug, $function);
}

function importDeleteAppointmentFromGoogleToSpetslaw(){
    global $wpdb;
    if(isset($_POST['importFromGoogleToSpetslaw']) and $_POST['task']=="import"):
        foreach($_POST['googleAppointments'] as $key=>$value):
            $ga=unserialize(base64_decode($value));
            $st=($ga['start']['dateTime'])?date("h:i A",strtotime($ga['start']['dateTime'])):date("h:i A",strtotime($ga['start']['date']));
            $et=($ga['end']['dateTime'])?date("h:i A",strtotime($ga['end']['dateTime'])):date("h:i A",strtotime($ga['end']['date']));
            $date=($ga['start']['dateTime'])?date("Y-m-d",strtotime($ga['start']['dateTime'])):date("Y-m-d",strtotime($ga['start']['date']));
            if(!googleAppointmentAddedOrNot($ga['id']))
            $wpdb->query("INSERT INTO wp_ap_appointments SET name='{$ga['summary']}', email='{$ga['organizer']['email']}',service_id='{$_POST['law_service']}',start_time='$st',end_time='$et',date='$date',appointment_key='{$ga['id']}',status='{$ga['status']}',appointment_by='{$ga['organizer']['displayName']}',note='{$ga['description']}'");
        endforeach;
    endif;

    if($_POST['task']=="removeGoogleAppointmentFromSpetslaw"):
        foreach($_POST['googleAppointments'] as $key=>$value):
            $ga=unserialize(base64_decode($value));
            $wpdb->query("DELETE FROM wp_ap_appointments WHERE appointment_key='{$ga['id']}'");
        endforeach;
    endif;
}

function googleCalendarSynchronization(){
    global $wpdb;
    session_start();
    //session_destroy();
    $_SESSION['lawyer_no']=($_POST['lawyer_no'])?$_POST['lawyer_no']:$_SESSION['lawyer_no'];
    $_SESSION['calendar_id']=($_POST['calendar_id'])?$_POST['calendar_id']:$_SESSION['calendar_id'];
    importDeleteAppointmentFromGoogleToSpetslaw(); //Import and Delete the google Appointments Through this function;
    if($_SESSION['lawyer_no']):
    $calendars=getGoogleCalendarList();
    endif;
    //debug($calendars);
    
    $html="";
    $html.="<h1 style='text-transform:uppercase'>Import Events/Appointments from Google Calendar</h1><hr>";
    $html.="<form method='post' action='' name='calendarParameterInitializer'>";
    $html.="<div style='background:#123456;padding:10px;border:1px solid lightgrey'>";
    $html.="<select name='lawyer_no' onchange='document.calendarParameterInitializer.submit()'>";
    $html.="<option value=''>Select Lawyer</option>";
    for($i=1;$i<4;$i++):
    $lawyerSelected=($_SESSION['lawyer_no']==$i)?"selected='selected'":"";
    $html.="<option value='$i' $lawyerSelected>Lawyer # $i</option>";
    endfor;
    $html.="</select>";
    $html.="<select name='calendar_id' onchange='document.calendarParameterInitializer.submit()'>";
    $html.="<option value=''>Select Calendar</option>";
    foreach($calendars['items'] as $key):
    $calendarIdSelected=($key['id']==$_SESSION['calendar_id'])?"selected='selected'":"";
    $html.="<option value='{$key['id']}' $calendarIdSelected>{$key['summary']}</option>";
    endforeach;
    $html.="</select>";
    $html.="&nbsp <input style='float:right' type='button' class='button button-primary' value='Add Appointment' onclick='addAppointmentToSpetslaw()'>&nbsp";
    $html.="<input onclick='removeGoogleAppointmentFromSpetslaw()' style='float:right;background:red;color:white;box-shadow:0 0 0 inset;border:1px solid red;margin-right:10px' type='button' class='button' value='Remove Appointment'>";
    $html.="</div>";
    $html.="</form>";
    
    //From here start the GoogleCalendarEvent listing.
    //debug(getGoogleCalendarEvents());
    $html.="<form method='post' action='' name='googleCalendarListForm'>";
    $html.="<table style='width:100%;border:1px solid grey' cellspacing='0' cellpadding='0'>";
    $calendarEvents=getGoogleCalendarEvents();
    $html.="<tr><th><input type='checkbox' id='mainCheckbox'></th><th>Event/Appointment</th><th>Description</th><th>Location</th><th>Start Time</th><th>End Time</th><th>Organizer</th><th>Added</th></tr>";
    if(sizeof($calendarEvents['items'])):
    foreach($calendarEvents['items'] as $key):
    $eventStartTime=($key['start']['dateTime'])?date("m/d/Y h:i A",strtotime($key['start']['dateTime'])):date("m/d/Y",strtotime($key['start']['date']));
    $eventEndTime=($key['end']['dateTime'])?date("m/d/Y h:i A",strtotime($key['end']['dateTime'])):date("m/d/Y",strtotime($key['start']['date']));
    $CalendarAdded=(googleAppointmentAddedOrNot($key['id']))?"<font color='green'>&#x2714</font>":"<font color='red'>&#x2718</font>";
    $serialized=base64_encode(serialize($key));
    $html.="<tr><td><input type='checkbox' name='googleAppointments[]' value='$serialized'></td><td>{$key['summary']}</td><td>{$key['description']}</td><td>{$key['location']}</td><td>$eventStartTime</td><td>$eventEndTime</td><td>{$key['organizer']['displayName']}</td><td style='border-right:0px;text-align:center'>$CalendarAdded</td></tr>";
    endforeach;
    endif;
    $html.="</table>";

    ####The overlaybox
    $html.="<div id='splashScreenOverlay'></div><div style=\"top: 35%; height: 100px;\" id=\"newSplashScreenDiv\">
    <a href=\"javascript:void(0);\" class=\"close\"><img src=\"http://appddictionstudio.biz/conferencecms//mega_css/images/closebutton.png\" class=\"btn_close2\" title=\"Close Window\" alt=\"Close\"></a>
    <big style=\"color:#1377C3\">Assign Service to the chosen Appointments</big><hr>";
    $html.="<br><select name='law_service' required='required'>";
    $services=$wpdb->get_results("SELECT * FROM wp_profilepro_services");
    $html.="<option value=''>-SELECT LAW SERVICE</option>";
    foreach($services as $s):
    $html.="<option value='$s->service_id'>$s->service_name</option>";
    endforeach;
    $html.="";
    $html.="</select>";
    $html.="<br><br><input class='button button-primary' name='importFromGoogleToSpetslaw' value='Import Appointments' type='submit'>";
    $html.="</div>";
    $html.="<input type='hidden' name='task' id='task'>";
    $html.="</form>";
    //Very important function it is we have import all the appointments here.
    
    $html.="<script>
    jQuery('#mainCheckbox').click(function(){

    if(jQuery(this).is(':checked')){
    jQuery(\"td input[type='checkbox']\").each(function(){
    jQuery(this).attr('checked','checked');
    });
    }
    else{
    jQuery(\"td input[type='checkbox']\").each(function(){
    jQuery(this).removeAttr('checked');
    });
    }

    });

jQuery('.close').click(function(){
        
        jQuery('#newSplashScreenDiv,#splashScreenOverlay,#newSplashScreenDiv2,#splashScreenOverlay2').fadeOut();
    });
function removeGoogleAppointmentFromSpetslaw(){
    if(jQuery('td input:checked').length==0){
        alert('Please select atleast one appointment!');
        return false;
    }
    jQuery('#task').val('removeGoogleAppointmentFromSpetslaw');
    document.googleCalendarListForm.submit();
}
function addAppointmentToSpetslaw(){
    if(jQuery('td input:checked').length==0){
        alert('Please select atleast one appointment!');
        return false;
    }
        jQuery('#splashScreenOverlay').fadeToggle();
        jQuery('#newSplashScreenDiv').fadeToggle();
        jQuery('#task').val('import');
    }

    </script>
    <style>
    tr:nth-child(odd){
        background:lightgrey;
    }
    th{
        background:#123456;
        color:white;
        padding:10px;
    }
    td{
        padding:10px;
        border-right:1px dashed grey;
    }

    #newSplashScreenDiv{
display:none;
width: 350px;
height: 150px !important;
border: 15px solid #000;
background: #FFF;
z-index: 501;
border-radius: 20px;
top: 20% !important;
left: 37%;
position: fixed;
padding: 30px;
padding-top: 10px;
line-height: 16px;
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

 #splashScreenOverlay,#splashScreenOverlay2{
        background: black;
        opacity:0.7;
        top:0px;
        height:100%;
        width:100%;
        position: absolute;
        display: none;
        z-index: 501;
    }
    </style>
    ";
    echo $html;
}

function googleAppointmentAddedOrNot($googleAppointmentId){
global $wpdb;
$results=$wpdb->get_results("SELECT * FROM wp_ap_appointments WHERE appointment_key='$googleAppointmentId'");
return sizeof($results);
}

function getGoogleCalendarList(){
require_once plugin_dir_path(__FILE__).'google-api-php-client/src/Google_Client.php';
require_once plugin_dir_path(__FILE__).'google-api-php-client/src/contrib/Google_CalendarService.php';
session_start();
//session_destroy();
$client = new Google_Client();
$client->setApplicationName("Spetslaw");
$gcParams=getGoogleCalendarOptionsOfLawyer($_SESSION['lawyer_no']);
//debug($gcParams);
// Visit https://code.google.com/apis/console?api=calendar to generate your
// client id, client secret, and to register your redirect uri.
$client->setClientId($gcParams['client_id']);
$client->setClientSecret($gcParams['secret_key']);
$client->setRedirectUri($gcParams['redirect_url']);
$client->setDeveloperKey($gcParams['developer_key']);
//echo $_SESSION['token'];
$cal = new Google_CalendarService($client);
if (isset($_GET['logout'])) {
  unset($_SESSION['token']);
}

if (isset($_GET['code'])) {
  $client->authenticate($_GET['code']);
  $_SESSION['token'] = $client->getAccessToken();
  header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
}

if (isset($_SESSION['token'])) {
  $client->setAccessToken($_SESSION['token']);
}

if ($client->getAccessToken()):
$calList = $cal->calendarList->listCalendarList();
$_SESSION['calendar_events']=($_SESSION['calendar_id'])?$cal->events->listEvents($_SESSION['calendar_id']):"";
//debug($calList);
return $calList;
endif;

if(!$client->getAccessToken()):
//Please make the client login first to his account respective account.
$authUrl = $client->createAuthUrl();
print "<br><br><br><a class='button button-primary' href='$authUrl'>Access Google Account</a>";
exit;
endif;



} //Function ends here.



function getGoogleCalendarEvents(){
session_start();
return $_SESSION['calendar_events'];
}
    

function getGoogleCalendarOptionsOfLawyer($lawyer_id){
    $googleCalendarCredentials=array();
    $googleCalendarCredentials['client_id']=  get_option('gc_client_id_'.$lawyer_id);
    $googleCalendarCredentials['secret_key']=  get_option('gc_secret_key_'.$lawyer_id);
    $googleCalendarCredentials['redirect_url']=  get_option('gc_redirect_url_'.$lawyer_id);
    $googleCalendarCredentials['developer_key']=  get_option('gc_developer_key_'.$lawyer_id);
    //debug($googleCalendarCredentials);
    return $googleCalendarCredentials;
}

function appointmentScheduler(){
    include_once($_SERVER['DOCUMENT_ROOT']."/spetslaw/wp-content/plugins/profilepro/wdCalendar/sample.php");
}


################################################### All front-end stuff kept here########################################################

function restrictUser(){
    //only logged in user is allowed.
    global $user_ID,$siteurl,$wpdb;
    $loginPage=$siteurl."/login-register";
    
    if($user_ID==0):
       header("Location:".$loginPage);
       exit;
    endif;
    
    // We need to check from where the previous page is coming.
    $prevUrl=$_SERVER['HTTP_REFERER'];
    
    $searchFor=str_replace(get_option('siteurl'),"",$prevUrl);
    $searchFor=trim($searchFor,"/");
    $ready_to_proceed=$wpdb->get_results("SELECT * FROM wp_profilepro_services WHERE (ready_to_proceed_link='/{$searchFor}' OR ready_to_proceed_link='{$searchFor}') AND service_type='0'");
    $readyToProceedLinker=get_option('siteurl')."/".trim($ready_to_proceed[0]->ready_to_proceed_link,"/")."/";
    $ready_to_proceed_link=($readyToProceedLinker==$prevUrl)?get_option('siteurl')."/consultation-services?task=add-new-specific&law_service={$ready_to_proceed[0]->service_id}":get_option('siteurl')."/consultation-services?task=add-new-specific";
//    debug($ready_to_proceed);
//    exit;
    if($ready_to_proceed[0]->service_id and !$_REQUEST['type']):
     header("Location:".$ready_to_proceed_link);
     exit;
    endif;
    
    $servicesTaken=checkUserService();
    if(!$servicesTaken and $user_ID!=1): //Not for the admin, we have to set a role for the attorneys
    $consultation=$wpdb->get_results("SELECT * FROM wp_profilepro_services WHERE (consultation_link='/{$searchFor}' OR consultation_link='{$searchFor}') AND service_type='1'");
    $consultationLinker=get_option('siteurl')."/".trim($consultation[0]->consultation_link,"/")."/";
    $consultation_link=($consultationLinker==$prevUrl)?get_option('siteurl')."/consultation-services?task=add-new&law_service={$consultation[0]->service_id}":get_option('siteurl')."/consultation-services?task=add-new";

        if($consultation[0]->service_id and $_REQUEST['type']):
        header("Location:".$consultation_link);
        endif;
    endif;
}



function checkUserService(){
    global $wpdb,$user_ID;
    $user=get_userdata($user_ID);
    $result=$wpdb->get_results("SELECT * FROM wp_profilepro_user WHERE user_id='$user->ID'"); // He should have atleast one service, that is enough.
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
    //and make sure that the user is online.
    if($user_ID==0):
       $loginPage=get_option('siteurl')."/login-register";
       header("Location:".$loginPage);
       exit;
    endif;
    
    
    if(!$task):
        
        $controller->services();
    endif;
    
    if($task=="add-new"):
       
        $controller->addNewService();
    endif;
    
    if($task=="add-new-specific"):
        $controller->addNewSpecificService();
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
    if($user_ID==0):
       $loginPage=get_option('siteurl')."/login-register";
       header("Location:".$loginPage);
       exit;
    endif;
    $user=get_userdata($user_ID); //this is the user Info.
    include_once($modulePath."controller.php");
    $controller=new profileProController($user);
    
    if(!$task):
    $controller->documentManager();
    endif;
    
    if($task=='view-documents'):
        $controller->showDocuments();
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
    if($_POST['docket']):
    $_POST['docket']=  str_replace("#","",$_POST['docket']);
    $conditionsApply.=" AND  docket LIKE '%{$_POST['docket']}%' ";
    endif;
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
    if($_POST['status']!="all" and $_POST['status']):
    $postStatus=($_POST['status']=="open")?0:1;
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
    $html.="<tr><td>$i .</td><td>$key->service_name</td><td>$$price</td><td style='padding-left:40px;'>$key->consultation_limit minutes</td><td>$edit</td><td>$delete</td></tr>";
    $i++;
    endforeach;
    $html.="</table>
            <style>
            tr:nth-child(odd){
            background:lightgrey;
            }
            </style>";
    endif;
    
    if($task=='edit' and $_REQUEST['service']):
        
        if($_POST['editService']):
            $wpdb->query("UPDATE wp_profilepro_services SET service_name='{$_POST['service_name']}', consultation_limit='{$_POST['consultation_limit']}', service_price='{$_POST['service_price']}', attorney_name='{$_POST['attorney_name']}', attorney_email='{$_POST['attorney_email']}',service_type='{$_POST['service_type']}',ready_to_proceed_link='{$_POST['ready_to_proceed_link']}',consultation_link='{$_POST['consultation_link']}'  WHERE service_id='{$_POST['service_id']}'");
        endif;
        $service=$wpdb->get_results("SELECT * FROM wp_profilepro_services WHERE service_id='{$_REQUEST['service']}'");
        $service=$service[0];
        $nct=($service->service_type==0)?"selected='selected'":"";
        $ct=($service->service_type==1)?"selected='selected'":"";
        $back=$siteurl."/wp-admin/admin.php?page=profilepro-services";
        $html="<h1>Edit Services - $service->service_name</h1><hr>";
        $html.="<form method='post' action=''>";
        $html.="<label>Service Name </label><input type='text' name='service_name' value='$service->service_name'><br><br>";
        $html.="<label>Consultation Price</label><input type='text' name='service_price' value='$service->service_price'>(in dollars)<br><br>";
        $html.="<label>Consultation Limit</label><input type='text' name='consultation_limit' value='$service->consultation_limit'>(in Minutes)<br><br>";
        $html.="<label>Attorney Name</label><input type='text' name='attorney_name' value='$service->attorney_name'><br><br>";
        $html.="<label>Attorney Email</label><input type='text' name='attorney_email' value='$service->attorney_email'><br><br>";
        $html.="<label>Service Type</label><select name='service_type'><option value='0' $nct >Non-Consulation Type</option><option value='1' $ct>Consultation Type</option></select>";
        $html.="<input type='hidden' name='service_id' value='$service->service_id'><br><br>";   
        $html.="<label>Ready to proceed Link:</label><input type='text' name='ready_to_proceed_link' value='$service->ready_to_proceed_link'><br><br>";
        $html.="<label>Consultation Link</label><input type='text' name='consultation_link' value='$service->consultation_link'><br><br>";
        $html.="<label>&nbsp</label><input value='Save' name='editService' type='submit' class='button button-primary'>&nbsp <a href='$back' class='button button-warning'>Back</a>";
        
        $html.="</form>";
    endif;
    
    if($task=='add-new'):
        
        if($_POST['addService']):
            $wpdb->query("INSERT INTO wp_profilepro_services SET service_name='{$_POST['service_name']}', consultation_limit='{$_POST['consultation_limit']}', service_price='{$_POST['service_price']}',attorney_name='{$_POST['attorney_name']}', attorney_email='{$_POST['attorney_email']}', service_type='{$_POST['service_type']}',ready_to_proceed_link='{$_POST['ready_to_proceed_link']}',consultation_link='{$_POST['consultation_link']}'");
        endif;
        
        $back=$siteurl."/wp-admin/admin.php?page=profilepro-services";
        $html="<h1>Add New Services</h1><hr>";
        $html.="<form method='post' action=''>";
        $html.="<label>Service Name </label><input type='text' name='service_name' ><br><br>";
        $html.="<label>Consultation Price</label><input type='text' name='service_price' >(in dollars)<br><br>";
        $html.="<label>Consultation Limit</label><input type='text' name='consultation_limit' >(in Minutes)<br><br>";
        $html.="<label>Attorney Name</label><input type='text' name='attorney_name' ><br><br>";
        $html.="<label>Attorney Email</label><input type='text' name='attorney_email' ><br><br>";
        $html.="<label>Service Type</label><select name='service_type'><option value='0'>Non-Consulation Type</option><option value='1'>Consultation Type</option></select><br><br>";
        $html.="<label>Ready to proceed Link:</label><input type='text' name='ready_to_proceed_link' ><br><br>";
        $html.="<label>Consultation Link</label><input type='text' name='consultation_link' ><br><br>";
        $html.="<input type='hidden' name='service_id' ><br><br>";
        $html.="<label>&nbsp</label><input value='Save' name='addService' type='submit' class='button button-primary'>&nbsp <a href='$back' class='button button-warning'>Back</a>";
        $html.="</form>";
    endif;
    
    
    
    addCss();
    echo $html;
    return true;
}

function profileProSettings(){
    
    error_reporting(0);
    if(isset($_POST['subLawpaySettings'])):
      update_option('lawpay_publisher_name',$_POST['lawpay_publisher_name']);
      update_option('lawpay_business_email',$_POST['lawpay_business_email']);
      update_option('lawpay_currency',$_POST['lawpay_currency']);
      update_option('lawpay_action',$_POST['lawpay_action']);
      update_option('lawpay_item_description',$_POST['lawpay_item_description']);
      
      ##google calendar lawyer1
      update_option('gc_client_id_1',$_POST['gc_client_id_1']);
      update_option('gc_secret_key_1',$_POST['gc_secret_key_1']);
      update_option('gc_redirect_url_1',$_POST['gc_redirect_url_1']);
      update_option('gc_developer_key_1',$_POST['gc_developer_key_1']);
      
      ##google calendar lawyer2
      update_option('gc_client_id_2',$_POST['gc_client_id_2']);
      update_option('gc_secret_key_2',$_POST['gc_secret_key_2']);
      update_option('gc_redirect_url_2',$_POST['gc_redirect_url_2']);
      update_option('gc_developer_key_2',$_POST['gc_developer_key_2']);
      
      ##google calendar lawyer3
      update_option('gc_client_id_3',$_POST['gc_client_id_3']);
      update_option('gc_secret_key_3',$_POST['gc_secret_key_3']);
      update_option('gc_redirect_url_3',$_POST['gc_redirect_url_3']);
      update_option('gc_developer_key_3',$_POST['gc_developer_key_3']);
      
      
      
    endif;
    $pbn=get_option('lawpay_publisher_name');
    $pbe=get_option('lawpay_business_email');
    $pc=get_option('lawpay_currency');
    $pa=get_option('lawpay_action');
    $live=($pa==1)?"checked='checked'":" ";
    $sandbox=(!$pa)?"checked='checked'":'';
    $pid=get_option('lawpay_item_description');
    
    $gci1=get_option('gc_client_id_1');
    $gsk1=get_option('gc_secret_key_1');
    $gru1=get_option('gc_redirect_url_1');
    $gdk1=get_option('gc_developer_key_1');
            
    $gci2=get_option('gc_client_id_2');
    $gsk2=get_option('gc_secret_key_2');
    $gru2=get_option('gc_redirect_url_2');
    $gdk2=get_option('gc_developer_key_2');
            
    $gci3=get_option('gc_client_id_3');
    $gsk3=get_option('gc_secret_key_3');
    $gru3=get_option('gc_redirect_url_3');
    $gdk3=get_option('gc_developer_key_3');
            
    
    
    $html="<h1>Lawpay Payment Gateway Settings</h1><hr>";
    $html.="<div style='line-height:40px'>";
    $html.="<form method='post' action=''>";
    $html.="<label>Lawpay Publisher Name</label><input type='text' name='lawpay_publisher_name' value='$pbn'><br>";
    $html.="<label>Lawpay Business Email</label><input type='text' name='lawpay_business_email' value='$pbe'><br>";
    $html.="<label>Currency</label><input type='text' name='lawpay_currency' value='{$pc}'>(Ex: USD)<br>";
    $html.="<label>Lawpal Action</label><input type='radio'  name='lawpay_action' value='1' $live>Live &nbsp <input $sandbox value='0' type='radio' name='lawpay_action'>Sandbox <br>";
    $html.="<label>Item Description</label><input type='text' name='lawpay_item_description' value='$pid'><br>";
    
    ///Now Google calendar stuff.
    $html.="<h1>Google Calendar API Settings</h1><hr>";
    $html.="<font style='border-radius:4px;background:lightgrey;padding:5px 10px'>Lawyer #1 Google Calendar Credentials</font><br>";
    $html.="<label>Api Client ID</label><input type='text' name='gc_client_id_1' value='$gci1'><br>";
    $html.="<label>Client Secret Key</label><input type='text' name='gc_secret_key_1' value='$gsk1'><br>";
    $html.="<label>Api Redirect Url</label><input type='text' name='gc_redirect_url_1' value='$gru1'><br>";
    $html.="<label>Api Developer Key</label><input type='text' name='gc_developer_key_1' value='$gdk1'><br>";
    
    $html.="<font style='border-radius:4px;background:lightgrey;padding:5px 10px'>Lawyer #2 Google Calendar Credentials</font><br>";
    $html.="<label>Api Client ID</label><input type='text' name='gc_client_id_2' value='$gci2'><br>";
    $html.="<label>Client Secret Key</label><input type='text' name='gc_secret_key_2' value='$gsk2'><br>";
    $html.="<label>Api Redirect Url</label><input type='text' name='gc_redirect_url_2' value='$gru2'><br>";
    $html.="<label>Api Developer Key</label><input type='text' name='gc_developer_key_2' value='$gdk2'><br>";
    
    $html.="<font style='border-radius:4px;background:lightgrey;padding:5px 10px'>Lawyer #3 Google Calendar Credentials</font><br>";
    $html.="<label>Api Client ID</label><input type='text' name='gc_client_id_3' value='$gci3'><br>";
    $html.="<label>Client Secret Key</label><input type='text' name='gc_secret_key_3' value='$gsk3'><br>";
    $html.="<label>Api Redirect Url</label><input type='text' name='gc_redirect_url_3' value='$gru3'><br>";
    $html.="<label>Api Developer Key</label><input type='text' name='gc_developer_key_3' value='$gdk3'><br>";
    
    
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
    
    if($_POST['performTask']=="fixAppointment"):
        $message="Appointment fixed successfully for the client!";
        echo messageUpdateNotification($message);
    endif;
    if(isset($_POST['importFromGoogleToSpetslaw'])):
    $message="Appointment(s) from Google Calendar to spetslaw imported successfully!";
        echo messageUpdateNotification($message);
    endif;

    if($_POST['task']=="removeGoogleAppointmentFromSpetslaw"):
    $message="Google Appointment(s) removed successfully from spetslaw!";
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
//debug($_POST);
//exit;
if($_POST['performTask']=="changeServiceStatus"):
$user_service_id=$_POST['user_service_id'];
$status=$_POST['conveyValue'];
$wpdb->query("UPDATE wp_profilepro_user SET status='$status' WHERE id='$user_service_id'");
endif;

if($_POST['performTask']=="deleteUserService"):
$user_service_id=$_POST['user_service_id'];
$wpdb->query("DELETE from wp_profilepro_user WHERE id='$user_service_id'");
endif;

if($_POST['performTask']=="fixAppointment"):
$user_service_id=$_POST['user_service_id'];
$wpdb->query("UPDATE wp_profilepro_user SET consultation_time='{$_POST['conveyValue']}' WHERE id='$user_service_id'");
endif;

}


 function messageUpdateNotification($message="Updated Successfully !",$type='green',$back="#AAFFAC"){
     $tick=($type=="green")?"<b style='background: none repeat scroll 0% 0% steelblue; color: white; border-radius: 30px; padding: 4px 6px;'>&#x2714;</b>":"<b style='background:red; color: white; border-radius: 30px; padding: 4px 6px;'>&#x2717;</b>";
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
            
            tr td{
            border-bottom:1px solid lightgrey;
            }
            
</style>
xyz;
    echo $str;
}

##############################################Admin Stuff Present till here###############################################################
