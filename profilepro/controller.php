<?php
class profileProController{
public $user;
public function __construct($wp_user="") {
    $this->user=$wp_user;
}
public function profile(){
    global $modulePath;
    include_once($modulePath."models/mod_profile.php");
    $model=new profileProModels();
    $model->currentLink="My Profile";
    include_once($modulePath."views/profile.php");
}

public function services(){
    global $modulePath;
    include_once($modulePath."models/mod_profile.php");
    $model=new profileProModels();
    $model->currentLink="Services";
    include_once($modulePath."views/services.php");
}

public function addNewServicePaypal(){
    global $modulePath;
    include_once($modulePath."models/mod_profile.php");
    $model=new profileProModels();
    $model->currentLink="Services";
    include_once($modulePath."views/buyNewService.php");
    
}

public function addNewService(){
    global $modulePath;
    include_once($modulePath."models/mod_profile.php");
    $model=new profileProModels();
    $model->currentLink="Services";
    include_once($modulePath."views/lawPay.php");
    
}

public function getPlanDetails(){
    global $modulePath,$siteurl;
    include_once($modulePath."models/mod_profile.php");
    $plan=$model->getPlan($planId);
    return $plan;
}

public function orderComplete($order_id){
    
    global $modulePath;
    include_once($modulePath."models/mod_profile.php");
    $model=new profileProModels();
    $model->currentLink="Services";
    include_once($modulePath."views/orderComplete.php");
    
}

public function viewService(){  
    global $modulePath;
    include_once($modulePath."models/mod_profile.php");
    $model=new profileProModels();
    $model->currentLink="Services";
    include_once($modulePath."views/viewService.php");
}

public function documentManager(){
    
    global $modulePath;
    include_once($modulePath."models/mod_profile.php");
    $model=new profileProModels();
    $model->currentLink="Documents";
    include_once($modulePath."views/documentManager.php");
    
}
public function sendNotificationMail($to,$message,$from,$subject,$cc="myboss@example.com"){
    // Always set content-type when sending HTML email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    // More headers
    $headers .= "From: <$from>" . "\r\n";
    $headers .= "Cc: $cc" . "\r\n";
    $mailSend=mail($to,$subject,$message,$headers);
    return $mailSend;
}

public function changePassword(){
    global $modulePath;
    include_once($modulePath."models/mod_profile.php");
    $model=new profileProModels();
    $model->currentLink="Change Password";
    include_once($modulePath."views/changePassword.php");
}

public function editClientDetails(){
    global $modulePath;
    include_once($modulePath."models/mod_profile.php");
    $model=new profileProModels();
    $model->currentLink="My Profile";
    include_once($modulePath."views/editProfile.php");
}




}
?>