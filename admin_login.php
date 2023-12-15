<?php
echo "hello";
require 'dbconfig.php'; 
$con=db_connect();
if(isset($_POST['SignIn']) && $_POST['SignIn']!='')
{ 
	if(isset($_POST['username'])&&  $_POST['username']!='' && isset($_POST['password'])&&  $_POST['password']!=''){
  $username=$_POST['username'];
  $password= md5($_POST['password']); 
  //  super Admin
  $sql="select * from admin_masters where email=?";
  $stmt = $con->prepare($sql);
  $stmt->bind_param('s', $username);
  $stmt->execute();
  $result = $stmt->get_result();
  $count=mysqli_num_rows($result); 


  // Admin Login
  $sql2="select * from companies where email=?  and status=1 and verification_status=1";
  $stmt2 = $con->prepare($sql2);
  $stmt2->bind_param('s', $username);
  $stmt2->execute();
  $result2 = $stmt2->get_result(); 
  $count2=mysqli_num_rows($result2); 


$sql3="select * from companies where email=?  and status=1 and verification_status=0";
  $stmt3 = $con->prepare($sql3);
  $stmt3->bind_param('s', $username);

  $stmt3->execute();
  $result3 = $stmt3->get_result(); 
  $count3=mysqli_num_rows($result3); 



 /* if($count3>0)
          {

          $failed=1;
              $UsernameError="Account is not verify.Please Check Your Email";
             
          header("Location: admin_login.php");
           echo "<script type='text/javascript'>alert('$UsernameError')

window.location.href='admin_login.php';
           </script>";
          return false;
          }*/



 if($count>0){ // Super Admin 


    while ($row = $result->fetch_assoc()) {  
      if($password==$row["password"])  
      {   


        if($row['role']==1){ // Super Admin 
       $SessionStatus=true;
         session_start();
         $_SESSION['valid'] = true;
         $_SESSION['timeout'] = time();
         $_SESSION['username'] = $username;
         $_SESSION['role'] = 1;
         header("Location: companies_list.php");
         $LogedInMessage="You are successfully Logged In..";
         echo "<script type='text/javascript'>alert('$LogedInMessage')</script>";
       }


  
   }  else{
       $failed=1;
       $UsernameError="Invalid User or Password! Enter a valid.";
       echo "<script type='text/javascript'>alert('$UsernameError')</script>";
     }
 
 }

 } else if($count2>0){// Admin  
   
while ($row = $result2->fetch_assoc()) {  
  if($password==$row["password"])  
      {    
     if($row['role']==2){// Admin 

        $userid= $row['id'];
        $SessionStatus=true;
         session_start();
         $_SESSION['valid'] = true;
         $_SESSION['timeout'] = time();
         $_SESSION['username'] = $username;
         $_SESSION['role'] = 2;
         $_SESSION['companyid'] = $userid;
         header("Location: companies_client_list.php");
         $LogedInMessage="You are successfully Logged In..";
         echo "<script type='text/javascript'>alert('$LogedInMessage')</script>";
     }

   } 


    else{

  
       $failed=1;
       $UsernameError="Invalid User or Password! Enter valid details.";
       echo "<script type='text/javascript'>alert('$UsernameError')</script>";

     }
 
 }
 }


        else if($count3>0)  {
        
while ($row = $result3->fetch_assoc()) {  
  if($password==$row["password"])  
      {    
     if($row['role']==2){

          $failed=1;
              $UsernameError="Account is not verified. Please Check Your Email";
           echo "<script type='text/javascript'>alert('$UsernameError')

window.location.href='admin_login.php';
           </script>";
        
          }
}

else
{
  $failed=1;
              $UsernameError="Invalid User or Password! Enter valid details.";
           echo "<script type='text/javascript'>alert('$UsernameError')

window.location.href='admin_login.php';
           </script>";
       

}

}

}


}
else{



      

              $failed=1;
              $UsernameError="Username or Password can not be empty.";
              echo "<script type='text/javascript'>alert('$UsernameError')</script>";
          


  }

}  
 

?>



<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Eworxs</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="plugins/iCheck/square/blue.css">
</head>
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
    <form action="admin_login.php" method="post">
          <div class="login-header" style="display:none">Sign in to Admin Panel</div>
          <div class="login-inner-box">
      <div class="form-group has-feedback">

      <label style="display:none">Email</label>
        <input type="text" class="form-control" placeholder="Email" name="username">
        <i class="fa fa-user login-inner-icon"></i>
     </div>
     
      <div class="form-group has-feedback">
      <label style="display:none">Password</label>
        <input type="password" class="form-control" placeholder="Password" name="password" autocomplete="off">
        <i class="fa fa-lock login-inner-icon"></i>
      </div>

      <div class="row">
        
        <!-- /.col -->
        <div class="col-xs-12">
        <a href="forgot_password.php" class="text-right">Forgot Password?</a>
        </div>

        <div class="col-xs-12">
        <center><input type="submit" name="SignIn" class="btn theme-btn " value="Sign In" /></center>
        </div>
        <!-- /.col -->
      </div>
       </div>

      </div>
    </form>
    </div>
   
 <div class="designby-text">Designed By <a href="http://www.cresol.in/" target="blank">Cresol.in</a></div>
  </div>

 
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
</html>
