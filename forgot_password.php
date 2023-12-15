
<?php 
/*include('header.php'); */
/*if($_SESSION['username']==""){
echo "<script> window.location = 'admin_login.php';</script>";
}
if($_SESSION['role']==1){
  echo "<script> window.location = 'admin_login.php';</script>";
}
*/
/*$company_id= $_SESSION['companyid'];
$db=db_connect(); 
*///status
/*$sql = "SELECT id, company_id,first_name,last_name,email , phone , company_name , office_address,postal_code,created_at,work_rate, mileage_rate,due_date_range,clock_setting   FROM company_clients where company_id = $company_id ORDER BY id DESC";
$exe = $db->query($sql);
$data = $exe->fetch_all(MYSQLI_ASSOC);

  foreach ($data as $key => $value){
      $data[$key]['id']=$value['id']; 
      $data[$key]['company_id']=$value['company_id']; 
      $data[$key]['first_name']=$value['first_name']; 
      $data[$key]['last_name']=$value['last_name']; 
      $data[$key]['email']=$value['email']; 
      $data[$key]['phone']=$value['phone']; 
      // $data[$key]['password']=$value['password']; 
      $data[$key]['company_name']=$value['company_name']; 
      $data[$key]['office_address']=$value['office_address']; 
      $data[$key]['postal_code']=$value['postal_code']; 
    /*  $data[$key]['status']=$value['status']; */
     /* $data[$key]['created_at']=$value['created_at']; 
      $data[$key]['work_rate']=$value['work_rate']; 
      $data[$key]['mileage_rate']=$value['mileage_rate'];
      $data[$key]['due_date_range']=$value['due_date_range'];
      $data[$key]['clock_setting']=$value['clock_setting'];
      
    }
 $sqlComp = "SELECT * FROM companies";
  $exeComp = $db->query($sqlComp);
  $dataComp = $exeComp->fetch_all(MYSQLI_ASSOC);*/
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Eworxs</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="font-awesome-4.7.0/css/font-awesome.css">
  <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
  <link rel="stylesheet" href="plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
   <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
</head>

<style>
  body{
    padding: 0px !important;
  }
</style>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="login-logo">
  
  </div>

 

  <!-- /.login-logo -->
  <div class="login-box-body">
  <img src="dist/img/mobile.png">
 <div class="mycompany-name">
  Eworxs
 <!--  <p>To ensure that the quality repair done</p> -->
  </div>
    <hr>
    <img src="dist/img/mobile.png" style="display: none;">
    <div class="theme-form">

<?php
require 'dbconfig.php'; 
$con=db_connect();
if(isset($_POST["email"]) && (!empty($_POST["email"]))){
$email = $_POST["email"];
$email = filter_var($email, FILTER_SANITIZE_EMAIL);
$email = filter_var($email, FILTER_VALIDATE_EMAIL);
if (!$email) {
   $error .="<p>Invalid email address please type a valid email address!</p>";
   }else{
   $sel_query = "SELECT id FROM `companies` WHERE email='".$email."'";
   $results = mysqli_query($con,$sel_query);
   $row = mysqli_num_rows($results);
   if ($row==""){
   $error .= "<p>No user is registered with this email address!</p>";
   }
  }
   if($error!=""){
   echo "<div class='error'>".$error."</div>
   <br /><a href='javascript:history.go(-1)'>Go Back</a>";
   }else{
   $expFormat = mktime(
   date("H"), date("i"), date("s"), date("m") ,date("d")+1, date("Y")
   );
   $expDate = date("Y-m-d H:i:s",$expFormat);
   $key = md5(2418*2+$email);
   $addKey = substr(md5(uniqid(rand(),1)),3,10);
   $key = $key . $addKey;
// Insert Temp Table
mysqli_query($con,
"INSERT INTO `password_reset_temp` (`email`, `key`, `expDate`)
VALUES ('".$email."', '".$key."', '".$expDate."');");

$output='<p>Dear user,</p>';
$output.='<p>Please click on the following link to reset your password.</p>';
$output.='<p>-------------------------------------------------------------</p>';
$output.='<p><a href="https://eworxs.app/EworxsAdmin/reset-password.php?
key='.$key.'&email='.$email.'&action=reset" target="_blank">
https://eworxs.app/EworxsAdmin/reset-password.php
?key='.$key.'&email='.$email.'&action=reset</a></p>';    
$output.='<p>-------------------------------------------------------------</p>';
$output.='<p>Please be sure to copy the entire link into your browser.
The link will expire after 1 day for security reason.</p>';
$output.='<p>If you did not request this forgotten password email, no action 
is needed, your password will not be reset. However, you may want to log into 
your account and change your security password as someone may have guessed it.</p>';      
$output.='<p>Thanks,</p>';
$output.='<p>Eworxs Team</p>';
$message = $output; 
$subject = "Password Recovery - Eworxs.app";
$from = 'support@eworxs.app';
$headers = "From: $fromName"." <".$from.">";
$returnpath = "-f" . $from;

$email_to = $email;

$mail = @mail($email_to, $subject, $message, $headers, $returnpath); 
if(!$mail){
echo "Mailer Error: " . $mail->ErrorInfo;
}else{
echo "<div class='error'>
<p>An email has been sent to you with instructions on how to reset your password.</p>
</div><br /><br /><br />";
   }
   }
}else{
?>
<form method="post" action="" name="reset"><br /><br />
<div style="text-align: center;font-weight: 800;">Enter Your Email Address:</center></strong></div><br />
<input type="email" class="form-control" name="email" placeholder="username@email.com" style="border: 1px solid #e1e4e8 !important" />

<div style="text-align:center;">
<input type="submit" class="btn btn-primary" value="Reset Password"/>
</div>
</form>

<?php } ?>

    </div>
   

  </div>
 <div class="designby-text">Designed By <a href="http://www.cresol.in/" target="blank">Cresol.in</a></div>
 
  <!-- /.login-box-body -->
</div>
<!-- /.login-box -->

<!-- jQuery 2.2.3 -->
<script src="plugins/jQuery/jquery-2.2.3.min.js"></script>
<!-- Bootstrap 3.3.6 -->
<script src="bootstrap/js/bootstrap.min.js"></script>
<!-- iCheck -->
<script src="plugins/iCheck/icheck.min.js"></script>
<script>
  $(function () {
    $('input').iCheck({
      checkboxClass: 'icheckbox_square-blue',
      radioClass: 'iradio_square-blue',
      increaseArea: '20%' // optional
    });
  });
</script>
</body>
<!-- ./wrapper -->
<!-- jQuery 2.2.3 -->
<script src="plugins/jQuery/jquery-2.2.3.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="plugins/jQuery/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button);
</script>
<!-- Bootstrap 3.3.6 -->
<script src="bootstrap/js/bootstrap.min.js"></script>
<!-- Morris.js charts -->
<script src="plugins/jQuery/raphael-min.js"></script>

<!-- daterangepicker -->
<script src="plugins/jQuery/moment.min.js"></script>

<!-- Bootstrap WYSIHTML5 -->
<script src="plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>

<!-- AdminLTE App -->
<script src="dist/js/app.min.js"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="dist/js/pages/dashboard.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="dist/js/demo.js"></script>

<!-- page script -->

<script src="plugins/jQuery/jquery-2.2.3.min.js"></script>
<!-- Bootstrap 3.3.6 -->
<script src="bootstrap/js/bootstrap.min.js"></script>
<!-- DataTables -->
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables/dataTables.bootstrap.min.js"></script>

<!-- AdminLTE App -->
<script src="dist/js/app.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="dist/js/demo.js"></script>


</script>
</body>
</html>
