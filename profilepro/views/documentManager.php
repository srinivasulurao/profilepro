<?php

/*
 * To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/
global $siteurl;
//debug($paypal);
$msg1=$model->uploadDocument();
$msg2=$model->deleteProfileProDocument();
?>
<div><?php echo $msg1,$msg2; ?></div><br>
<div style='width:20%;display:inline-block !important;vertical-align: top;background:steelblue;border-radius:13px;box-shadow:0 0 10px inset'>
<?php 
echo"<ul style='list-style:none;margin:25px' id='profileLinks'>";
echo $profileLinks=$model->profileLinks();
echo"</ul>";
$userService=$model->userServices();
$documents=$model->userDocumentList();
?>
</div>
<div style='width:75%;display:inline-block !important;vertical-align: top;margin-left:20px'>
<h1>Documents</h1>
<a href='javascript:void(0)' class='button' id='showHideForm' style='float: right; margin-bottom: -40px; position: relative; bottom: 40px;'>Upload Document</a>
<div id='uploader'>
<a href=''>test</a>
<form action="" method='post' name='profileProFileUploader' enctype="multipart/form-data">
<label>Select Service</label>:
<select name='profileProService' required='required'>
<option value=''>--SELECT--</option>
<?php 
foreach($userService as $key):
echo"<option value='$key->id'>$key->service_name</option>";
endforeach;
?>
</select>
<br>
<label>Upload Document </label>: <input type='file' name='profileProDocumentUpload' required='required'><br><br>
<label>&nbsp </label>&nbsp <input type='submit' name='profileProDocUpload' value='UPLOAD' required='required'>
</form>
</div>
<hr>
<table cellspacing='0' cellpadding='0' width='100%' >
<tr><th>Docket #</th><th>Service</th><th>Document</th><th colspan='2' >Action</th></tr>
<?php 

//debug($documents);
$documentCounter=0;
foreach($documents as $key):
$path=$siteurl.$key->path;
$view="<a style='color:white;background: none repeat scroll 0% 0% #0568A6; text-decoration: none; padding: 1px 10px; border-radius: 10px;' href='$path' target='_blank'>View</a>";
$delete="<a style='color:white;background:red; text-decoration: none; padding: 1px 10px; border-radius: 10px;' href='javascript:void(0)' onclick='deleteProfileProDocument($key->document_id)' >Delete</a>";;
$docName=basename($key->path);
echo"<tr><td>#$key->docket</td><td>$key->service_name</td><td>$docName<td>$view</td><td>$delete</td></tr>";
$documentCounter++;
endforeach;
if($documentCounter==0):
echo"<tr><td colspan='4' style='color:red'>Sorry, No documents uploaded yet, please upload documents by clicking the 'Upload Document' button at the top.</td></tr>";
endif;
?>
</table>
</div>

<form method='post' action='' name='deleteProfileProDocumentForm'>
<input type='hidden' name='deleteProfileProDocument' id='deleteProfileProDocument' >
</form>

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

#uploader{
	background:lightgrey;
	width:95%;
	padding:20px;
	display:none;
	margin-bottom:-18px;
	
}

#middle .content_wrap {
    background: none repeat scroll 0 0 ivory;
    border: 1px dashed;
    font-family: trebuchet ms;
    padding: 20px;
	min-height:500px;
	color:black;
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

<script>
jQuery("#showHideForm").click(function(){
jQuery("#uploader").slideToggle();
	});

function deleteProfileProDocument(doc_id){
	conf=window.confirm("Are you sure you want to delete this document ?");
	if(conf){
		document.getElementById("deleteProfileProDocument").value=doc_id;
		document.deleteProfileProDocumentForm.submit();
	}
}
	
</script>

<!-- <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.js"></script>  -->
<!-- <script src="http://malsup.github.com/jquery.form.js"></script>  -->
<!-- <script> -->
<!-- // //wait for the DOM to be loaded  -->
<!-- // $(document).ready(function() {  -->
<!-- //     // bind 'myForm' and provide a simple callback function  -->
<!-- //     $("[name='fileUploader']").ajaxForm(function() {  -->
<!-- //         alert("Thank you for your comment!");  -->
<!-- //     });  -->
<!-- // });  -->
<!-- </script> -->

                        