<?php

class profileProModels extends profileProController{
    
   public $user;
   public $currentLink;
   public function __construct(){
     global $user_ID;
     $this->user=get_userdata($user_ID);
     //debug($this->user); 
   }
   public function profileDetails(){
       return "hii Models";
   }
   public function profileLinks(){
       
       $root_link=get_option("siteurl");
       $profileLinks['My Profile']=$root_link."/profile";
       $profileLinks['Services']=$root_link."/consultation-services";
       $profileLinks['Documents']=$root_link."/documents";
       $profileLinks['Change Password']=$root_link."/profile?task=change-password";
       $profileLinks['Log Out']=$root_link."/profile?task=logout";
       //$profileLinks['Log Out']="$root_link/wp-login.php?action=logout&redirect_to=".$root_link."/login-register&_wpnonce=".base64_encode(rand(0,100000));
       
       $linkHtml="";
       foreach ($profileLinks as $key=>$value):
       $addStyle=($key==$this->currentLink)?"style='color:black !important'":"";
       $linkHtml.="<li><a href='$value' $addStyle>$key</a></li>";
       endforeach;
       
       return $linkHtml;
   }
   
   public function getServicesList(){
       global $wpdb;
       $results=$wpdb->get_results("SELECT * FROM wp_profilepro_services");
       return $results;
   }
   
   public function getPaypalPaymentParameters(){
       global  $siteurl,$user_ID;
       $paypal=array();  
       $orderValue=base64_encode($this->user->data->user_email."|".time()."|".$this->user->ID);
       $live="https://www.paypal.com/cgi-bin/webscr";
       $sandbox="https://www.sandbox.paypal.com/cgi-bin/webscr";
       $paypal['actionUrl']=(get_option('paypal_action')==1)?$live:$sandbox;
       $paypal['amount']="this changes using the ajax";
       $paypal['returnUrl']=$siteurl."/consultation-services/?task=order-complete&order_id=".$orderValue;
       $paypal['cancelUrl']=$siteurl."/consultation-services/?task=add-new"; //again back to this page.
       $paypal['currencyCode']=get_option('paypal_currency');
       $paypal['businessEmail']=get_option('paypal_business_email');
       $paypal['itemDescription']=get_option('paypal_item_description');
       return $paypal;
   }
   
   public function newCustomerNotification(){
       global $wpdb;
       $query="SELECT * FROM wp_profilepro_user WHERE user_id='{$this->user->ID}'";
       $result=$wpdb->get_results($query);
       if(!sizeof($result))
       return "<font color='steelblue;'>You seem to be a new user, please select a consultation to proceed further</font>";
       else
       return false;
   }
   
   public function completePayment($order_id){
       global $wpdb;
       session_start();
       $orderExplode=explode("|",base64_decode($order_id));
       //debug($orderExplode);
       $query="SELECT * FROM wp_profilepro_user WHERE user_id='{$orderExplode[2]}' AND docket='{$orderExplode[1]}'";
       $results=$wpdb->get_results($query);
       $report=array();
       $report['color']='';
       $report['msg']='';
       if(!sizeof($results)):
       $consultation_time=$_SESSION['profileProPayment']['consultation_time'];
       $opted_service=$_SESSION['profileProPayment']['opted_service'];
       $purchased_on=date("Y-m-d H:i:s",$orderExplode[1]);
       $wpdb->query("INSERT INTO wp_profilepro_user SET opted_service='$opted_service',consultation_time='$consultation_time',purchased_on='$purchased_on',user_id='{$orderExplode[2]}',docket='{$orderExplode[1]}'");
       $report['color']='green';
       $report['msg']="Thank you for your purchase! Please upload relevant documents";
       $service=$this->getService($opted_service);
       $report['orderDetails']['transaction_id']=$orderExplode[1];
       $report['orderDetails']['subscription']=$service->service_name;
       $report['orderDetails']['consultation_timings']=str_replace("|","<br>",$consultation_time);
       unset($_SESSION['profileProPayment']['consultation_time']);
       unset($_SESSION['profileProPayment']['opted_service']);
       return $report;
       endif;
       
       if(sizeof($results)):
       $report['color']='red';
       $report['msg']="Your Request is already confirmed, please go to services to see the services you have opted for.";
       return $report;
       endif;
       
       return $report;
   }
   
   public function getService($planId){
       global $wpdb;
       $query="SELECT * FROM wp_profilepro_services WHERE service_id='$planId'";
       $result=$wpdb->get_results($query);
       return $result[0];
   }
   
   public function getServiceInvoice($serviceId){
       global $wpdb;
       $query="SELECT * FROM wp_profilepro_services as s INNER JOIN wp_profilepro_user as u ON u.opted_service=s.service_id WHERE u.id='{$serviceId}'";
       $result=$wpdb->get_results($query);
       return $result[0];
   }
   public function userServices(){
       global $wpdb;
       $query="SELECT * FROM wp_profilepro_services as s INNER JOIN wp_profilepro_user as u ON u.opted_service=s.service_id WHERE u.user_id='{$this->user->ID}'";
       $result=$wpdb->get_results($query);
       return $result;
   }
   
   public function uploadDocument(){
       global $wpdb;
       if(isset($_POST['profileProDocUpload']) and $_FILES['profileProDocumentUpload']['tmp_name']):
       $file="/wp-content/plugins/profilepro/uploads/".str_replace(" ","_",$_FILES['profileProDocumentUpload']['name']);
       $destination=getcwd().$file;
       $now=date("Y-m-d H:i:s",time());
       move_uploaded_file($_FILES['profileProDocumentUpload']['tmp_name'],$destination);
       $wpdb->query("INSERT INTO wp_profilepro_documents SET user_id='{$this->user->ID}', path='$file', user_plan_id='{$_POST['profileProService']}', uploaded_on='$now'");
       
       //Need to send a mail once the document is uploaded.
       $service=$this->getServiceByDocumentPlanId($_POST['profileProService']);
       $attachments =getcwd().$file;
       $headers = "From: My Name <".$this->user->user_email.">" . "\r\n";
       $message="<div style='font-family:candara;line-height:20px;'>";
       $message.="Hello ".$service->attorney_name.",<br>";
       $message.="A new document has been uploaded by one of your client with following details<br>";
       $message.="<label style='width:150px;display:inline-block'>Client Name </label>: ".$this->user->data->display_name."<br>";
       $message.="<label style='width:150px;display:inline-block'>Client E-Mail</label>: ".$this->user->data->user_email."<br>";
       $message.="<label style='width:150px;display:inline-block'>Service Taken</label>: ".$service->service_name."<br>";
       $message.="<label style='width:150px;display:inline-block'>Attachment Added</label>: Please find the attachment, attached to this mail.<br>"; 
       $message.="<br>Thank You";
       $message.="<div>";
       add_filter('wp_mail_content_type','set_html_content_type');
       wp_mail($service->attorney_email,$service->service_name."-New Document uploaded",$message,$headers, $attachments );
       //Mail Sending code done till here.
       return messageUpdateNotification("Document Uploaded Successfully !","green","lightgreen");
       endif;
   }
   
   function set_html_content_type(){
   return ‘text/html’;
   }

   public function getServiceByDocumentPlanId($user_plan_id){
       global $wpdb;
       $result=$wpdb->get_results("SELECT * FROM wp_profilepro_user as u INNER JOIN wp_profilepro_services as s ON u.opted_service=s.service_id WHERE u.id='$user_plan_id'");
       //debug("SELECT * FROM wp_profilepro_user as u INNER JOIN wp_profilepro_services as s ON u.opted_service=s.service_id WHERE u.id='$user_plan_id'");
       return $result[0];
   }
   
   public function userDocumentList(){
       global $wpdb;
       $query="SELECT * FROM wp_profilepro_documents as d INNER JOIN wp_profilepro_user as u ON d.user_plan_id=u.id INNER JOIN wp_profilepro_services as s ON u.opted_service=s.service_id WHERE d.user_id='{$this->user->ID}' ORDER BY uploaded_on DESC";
       $results=$wpdb->get_results($query);
       return $results;
   }
   
   public function deleteProfileProDocument(){
       global $wpdb;
       if(isset($_POST['deleteProfileProDocument'])):
       $query="DELETE FROM wp_profilepro_documents WHERE document_id='{$_POST['deleteProfileProDocument']}'";
       $wpdb->query($query);
       return messageUpdateNotification("Document Deleted Successfully !","green","lightgreen");
       endif;
   }
   
   public function serviceDocuments($id){
       global $wpdb;
       $siteurl=get_option('siteurl');
       $query="SELECT path FROM wp_profilepro_documents where user_plan_id='$id'";
       $results=$wpdb->get_results($query);
       $docs=(!sizeof($results))?"No Documents Found,Click <a href='$siteurl/documents'>Here</a> to add":"";
       foreach($results as $key):
       $link=$siteurl.$key->path;
       $name=basename($link);
       $docs.="<a href='$link' target='_blank'>$name</a>"."<br>";
       endforeach;
       return $docs;
   }
   
   public function getLawpayPaymentAppointments(){
       global $wpdb;
       //$results=$wpdb->get_results("SELECT *,ap.id as ap_id FROM wp_ap_appointments as ap INNER JOIN wp_profilepro_user as u ON u.opted_service=ap.service_id WHERE u.consultation_time <> ap.id GROUP BY ap.id");
       $results=$wpdb->get_results("SELECT *,ap.id as ap_id FROM wp_ap_appointments as ap INNER JOIN wp_profilepro_user as u ON u.opted_service=ap.service_id WHERE FIND_IN_SET(ap.id,consultation_time)='0' GROUP BY ap.id"); //unique appointments.
       //$results=$wpdb->get_results("SELECT *,ap.id as ap_id FROM wp_ap_appointments as ap INNER JOIN wp_profilepro_user as u ON u.opted_service=ap.service_id WHERE u.consultation_time <> ap.id GROUP BY ap.id");
       return $results;
   }
   
   public function changePassword(){
       //debug($this->user->data->user_pass);
       include_once (getcwd()."/wp-includes/class-phpass.php");
       $wp_hasher = new PasswordHash($iteration_count_log2, $portable_hashes);
       if(isset($_POST['profileChangePassword'])):
       $currentPassword=$this->user->data->user_pass;
       $oldPassword=$_POST['profileProOldPass'];
       $newPass=$_POST['profileProNewPass'];
       $confPass=$_POST['profileProConfPass'];
       $passwordMatch=$wp_hasher->CheckPassword($oldPassword,$this->user->data->user_pass);
       if(!$passwordMatch):
       $msg=messageUpdateNotification("Please enter the correct old password to apply changes in the new password !",'red','lightpink');
       return $msg;
       endif;
       
       if($newPass!=$confPass):
       $msg=messageUpdateNotification("Please Type Identical passwords in both the cases !",'red','lightpink');
       return $msg;
       endif;
       
       if($passwordMatch and $newPass==$confPass):
       wp_set_password($_POST['profileProOldPass'], $this->user->ID);
       $msg=messageUpdateNotification("Your Password has been changed successfully !");
       return $msg;
       endif;
       
      endif;
   }
   
   public function getUserInfo(){
       global $wpdb;
       $result=$wpdb->get_results("SELECT * FROM wp_profilepro_user_info WHERE user_id='{$this->user->ID}'");
       if(sizeof($result)):
       return $result[0];
       endif;
       
       if(!sizeof($result)):
       $wpdb->query("INSERT INTO wp_profilepro_user_info SET user_id='{$this->user->ID}'");
       $result=$wpdb->get_results("SELECT * FROM wp_profilepro_user_info WHERE user_id='{$this->user->ID}'");
       return $result[0];
       endif;
   }
   
   public function updateProfileProUserDetails(){
       global $wpdb;
       //debug($_POST['gender']);
      $msg=""; 
       if(isset($_POST['profilepro_submit_user'])):
           $profilePic="/wp-content/plugins/profilepro/uploads/".$this->user->ID."_".$_FILES['profile_pic']['name'];
           move_uploaded_file($_FILES['profile_pic']['tmp_name'],getcwd().$profilePic);
           $query="UPDATE wp_profilepro_user_info SET full_name='{$_POST['full_name']}', gender='{$_POST['gender']}',about='{$_POST['about']}',address='{$_POST['address']}',occupation='{$_POST['occupation']}',phone='{$_POST['phone']}' WHERE user_id='{$this->user->ID}'";
           if($_FILES['profile_pic']['tmp_name'])
           $query="UPDATE wp_profilepro_user_info SET full_name='{$_POST['full_name']}', gender='{$_POST['gender']}',about='{$_POST['about']}',address='{$_POST['address']}',occupation='{$_POST['occupation']}',phone='{$_POST['phone']}',profile_pic='$profilePic' WHERE user_id='{$this->user->ID}'";
           $wpdb->query($query);
           if(!$wpdb->last_error)
           $msg=messageUpdateNotification("User profile details updated successfully!");
           else
           $msg=messageUpdateNotification($wpdb->last_error,'red','lightpink');
      endif;
      
      return $msg;
}
   

public function getLawPayTimeSlots($service_id){
    //Create a 
    global $wpdb;
    //We have to remove those time slots which are already booked.
    $results=$wpdb->get_results("select * from wp_ap_appointments as a INNER JOIN");
    return $results;
}
   public function usaStates(){
       $states=array("AL"=>"Alabama",
            "AK"=>"Alaska", 
            "AZ"=>"Arizona", 
            "AR"=>"Arkansas", 
            "CA"=>"California", 
            "CO"=>"Colorado", 
            "CT"=>"Connecticut", 
            "DE"=>"Delaware", 
            "DC"=>"District Of Columbia", 
            "FL"=>"Florida", 
            "GA"=>"Georgia", 
            "HI"=>"Hawaii", 
            "ID"=>"Idaho", 
            "IL"=>"Illinois", 
            "IN"=>"Indiana", 
            "IA"=>"Iowa", 
            "KS"=>"Kansas", 
            "KY"=>"Kentucky", 
            "LA"=>"Louisiana", 
            "ME"=>"Maine", 
            "MD"=>"Maryland", 
            "MA"=>"Massachusetts", 
            "MI"=>"Michigan", 
            "MN"=>"Minnesota", 
            "MS"=>"Mississippi", 
            "MO"=>"Missouri", 
            "MT"=>"Montana", 
            "NE"=>"Nebraska", 
            "NV"=>"Nevada", 
            "NH"=>"New Hampshire", 
            "NJ"=>"New Jersey", 
            "NM"=>"New Mexico", 
            "NY"=>"New York", 
            "NC"=>"North Carolina", 
            "ND"=>"North Dakota", 
            "OH"=>"Ohio", 
            "OK"=>"Oklahoma", 
            "OR"=>"Oregon", 
            "PA"=>"Pennsylvania", 
            "RI"=>"Rhode Island", 
            "SC"=>"South Carolina", 
            "SD"=>"South Dakota", 
            "TN"=>"Tennessee", 
            "TX"=>"Texas", 
            "UT"=>"Utah", 
            "VT"=>"Vermont", 
            "VA"=>"Virginia", 
            "WA"=>"Washington", 
            "WV"=>"West Virginia", 
            "WI"=>"Wisconsin", 
            "WY"=>"Wyoming");

return $states;
}

//this will return a html form for the consultation HMTL.
public function getConsultationTimingsByAppointmentId($appointments){
    global $wpdb;
    $appointments=explode(",",$appointments);
    $ap_html="";
    foreach($appointments as $value):
        if(!$value)
        return "No Appointment Dates found";
        $result=$wpdb->get_results("SELECT * FROM wp_ap_appointments WHERE id='$value'");
        $ap_html.="<font color='seagreen'>".date("m/d/y h:i A", strtotime($result[0]->date." ".$result[0]->start_time))."</font> -To- <font color='tomato'>".date("m/d/y h:i A", strtotime($result[0]->date." ".$result[0]->end_time))."</font><br>";
    endforeach;
    return $ap_html;
}

public function getServicePrice($service_id){
    global $wpdb;
    $result=$wpdb->get_results("SELECT * FROM wp_profilepro_services WHERE service_id='$service_id'");
    return $result[0]->service_price;
    
}
   public function lawPayPaymentProcessor(){
    global $wpdb,$user_ID;
    $pnp_post_url = "https://pay1.plugnpay.com/payment/pnpremote.cgi";
    if(isset($_POST['processLawPay'])):
   
//    "publisher-name=pnpdemo&amp;publisher-email=trash%40plugnpay.com&amp;mode=auth
//   &amp;card-name=cardtest&amp;card-number=4111111111111111&amp;card-exp=0105&amp;card-cvv=123
//   &amp;card-amount=1.23" https://pay1.plugnpay.com/payment/pnpremote.cgi"
       
   $publisher_name=get_option("lawpay_publisher_name");
   $card_number=$_POST['card_number'];
   $card_cvv=$_POST['card_cvv'];
   $card_exp=$_POST['expiration_month'].$_POST['expiration_year'];
   $card_amount=$this->getServicePrice($_POST['law_service']); // this could be tampered from client side wise, hence get it from server....
   $card_name=$_POST['card_name'];
   $email=get_option("lawpay_business_email");
   $card_address1=$_POST['card_address1'];
   $card_address1=$_POST['card_address2'];
   $card_zip=$_POST['card_zip'];
   $card_city=$_POST['card_city'];
   $card_state=$_POST['card_state'];
   $card_country="United States";
   
                $pnp_post_values = "";
                $pnp_post_values .= "publisher-name=" . $publisher_name . "&";
                $pnp_post_values .= "card-number=" . $card_number . "&";
                $pnp_post_values .= "card-cvv=" . $card_cvv . "&";
                $pnp_post_values .= "card-exp=" . $card_exp . "&";
                $pnp_post_values .= "card-amount=" . $card_amount . "&";
                $pnp_post_values .= "card-name=" . $card_name . "&";
                $pnp_post_values .= "email=" . $email . "&";
                $pnp_post_values .= "ipaddress=" . $email . "&";
                // billing address info
                $pnp_post_values .= "card-address1=" . $card_address1 . "&";
                $pnp_post_values .= "card-address2=" . $card_address2 . "&";
                $pnp_post_values .= "card-zip=" . $card_zip . "&";
                $pnp_post_values .= "card-city=" . $card_city . "&";
                $pnp_post_values .= "card-state=" . $card_state . "&";
                $pnp_post_values .= "card-country=" . $card_country . "&";
                // shipping address info
                $pnp_post_values .= "shipname=" . $shipname . "&";
                $pnp_post_values .= "address1=" . $card_address1 . "&";
                $pnp_post_values .= "address2=" . $card_address2 . "&";
                $pnp_post_values .= "zip=" . $card_zip . "&";
                $pnp_post_values .= "state=" . $card_state . "&";
                $pnp_post_values .= "country=" . $card_country . "&";
            
           
              $pnp_ch = curl_init();
              curl_setopt($pnp_ch, CURLOPT_URL, $pnp_post_url);
              curl_setopt($pnp_ch, CURLOPT_RETURNTRANSFER, 1);
              curl_setopt($pnp_ch, CURLOPT_POSTFIELDS, $pnp_post_values);
              curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);  // Upon problem, uncomment for additional Windows 2003 compatibility
              curl_setopt($ch,CURLOPT_CAINFO,"D:\xampp\htdocs\cacert.crt");
              #curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, "rsa_rc4_128_sha");  //CentOS 6 Compatibility
              $result=curl_exec($pnp_ch);
              $result=(curl_error($pnp_ch)=="")?$result:"FinalStatus=fraud&IPaddress=106%2e51%2e232%2e214&MStatus=badcard&User%2dAgent=Mozilla%2f5%2e0%20%28Windows%20NT%206%2e2%3b%20WOW64%3b%20rv%3a34%2e0%29%20Gecko%2f20100101%20Firefox%2f34%2e0&acct_code4=pnpremote%2ecgi&address1=test&address2=&auth%2dcode=&auth%2dmsg=%20Credit%20Card%20Expiration%20Date%20Expired%2e%7c&auth_date=20141209&card%2daddress1=test&card%2daddress2=&card%2damount=1%2e23&card%2dcity=New%20York&card%2dcountry=US&card%2dexp=01%2f05&card%2dname=cardtest&card%2dstate=New%20York&card%2dtype=VISA&card%2dzip=10022&country=US¤cy=usd&easycart=0&email=trash%40plugnpay%2ecom&errdetails=card%2dexp%7cExpiration%20Date%20Expired%2e%7c&errlevel=1&ipaddress=&merchant=pnpdemo&orderID=2014120915161802013&publisher%2dname=pnpdemo&resp%2dcode=P57&shipinfo=0&shipname=&sresp=X&state=New%20York&success=no&zip=10022&MErrMsg=Credit%20Card%20Expiration%20Date%20Expired%2e%7c&a=b";
             $lawPayOutput=urldecode($result);
             parse_str($lawPayOutput,$result);
             //debug($result);
             if($result['FinalStatus']!="success"):
             return messageUpdateNotification("<b>PAYMENT ERROR</b>:".trim($result['auth-msg'],"|"),"red","lightpink");
             endif;
             
             if($result['FinalStatus']=="success"):
             //exit;
            $consultation_time=$_POST['appointment_1'].",".$_POST['appointment_2'].",".$_POST['appointment_3'];
            $opted_service=$_POST['law_service'];
            $purchased_on=date("Y-m-d H:i:s",time());
            $wpdb->query("INSERT INTO wp_profilepro_user SET opted_service='$opted_service',consultation_time='$consultation_time',purchased_on='$purchased_on',user_id='{$user_ID}',docket='{$result['orderID']}'");

            return messageUpdateNotification("Payment process completed succesfully !","green","lightgreen");   
             endif;
            
    
  endif; //Post Form ends here.
    
   } //Lawpay payment processor ends here.
   
   
}