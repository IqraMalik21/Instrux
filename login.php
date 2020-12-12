<?php

require_once 'includes.php';

// if session is set direct to index

if (checkLoginStatusUser()){
    redirect('index.php');
}

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $upass = $_POST['pass'];

    $password = password_encryption($upass); // password hashing using SHA256
    //$password = $upass;
    $sam = $PDO->prepare("SELECT id, username, password, email_verified FROM `users` WHERE email=:email");
    $sam->execute(array(':email'=>$email));
    $count = $sam->rowCount();

    if($count > 0){
        if($count == 1){
            $row = $sam->fetch();

            if($row['email_verified'] != 1){

                $error = 'Your email is not verified. <a class="btn btn-sm btn-danger" href="?resendVerification='.$email.'">Resend Verification Mail</a>';

            }elseif($row['password'] == $password){

                $userid = $row['id'];

                $_SESSION['user'] = $userid;
                $_SESSION['key'] = $password;
                setcookie('user',$userid,time()+365*24*3600);
                setcookie('key',$password,time()+365*24*3600);

                header("Location: dashboard.php");
                exit;

            }else{
                $error = 'The password is incorrect.';
            }

        }else{
            $error = 'There is an issue of multiple accounts associated with this email address, Kindly Contact Administrators.';
        }
    }else{
        $error = 'There exists no account associated with this email address.';
    }

}elseif(isset($_REQUEST['resendVerification'])){
    $email = $_REQUEST['resendVerification'];
    $verificationCode = get_email_verification_code($email);
    if(empty($verificationCode)){
        $verificationCode = update_verification_code($email);
    }
    $sent = send_email_verification($email,$verificationCode);
    if($sent===TRUE){
        $success = "Verification email sent again successfully.";
    }else{
        $error = "Verification email could not be sent (".$sent."), Kindly contact Administrator.";
    }
}

require_once "header-head.php";

?>


<div class="page login-page">
    <div class="container">
    <div class="form-outer text-center d-flex align-items-center">
        <div class="form-inner">
        <div class="logo text-uppercase"><span>InstruX</span><strong class="text-primary">Dashboard</strong></div>
        <p>Login using account credentials provided by your organization.</p>

        <?php
        if (isset($success)) {
        ?>
        <div class="form-group-material">
            <div class="alert alert-success">
                 <?php echo $success; ?>
            </div>
        </div>
        <?php
        }elseif (isset($error)) {
            ?>
            <div class="form-group-material">
                <div class="alert alert-danger">
                    <span class="glyphicon glyphicon-info-sign"></span> <?php echo $error; ?>
                </div>
            </div>
            <?php
            }
        ?>

        <form method="post" class="text-left form-validate">
            <div class="form-group-material">
            <input id="login-email" type="email" name="email" required data-msg="Please enter your email" class="input-material">
            <label for="login-email" class="label-material">Email</label>
            </div>

            <div class="form-group">
                <div class="input-group" id="show_hide_password">

                    <div class="input-group-prepend">
                        <div class="input-group-text"><span class="fa fa-lock"></span></div>
                    </div>

                    <input id="login-password" type="password" name="pass" value="<?php echo $_POST['pass']; ?>" required placeholder="Enter your Password" class="form-control">

                    <div class="input-group-prepend">
                        <div class="input-group-text">
                            <a href=""><i class="fa fa-eye-slash" aria-hidden="true"></i></a>
                        </div>
                    </div>

                </div>
            </div>

            <div class="form-group text-center">
            <input type="submit" name="login" value="Login" class="btn btn-primary" />
            <!-- This should be submit button but I replaced it with <a> for demo purposes-->
            </div>
        </form>
        
        <div><small>Forgot Password? </small><a href="resetPassword.php" class="signup">Reset it</a></div>
        <div><small>Don't have an account? </small><a href="register.php" class="signup">Signup</a></div>

        </div>
    </div>
    </div>
</div>

<?php 
require_once "footer-foot.php";
?>
