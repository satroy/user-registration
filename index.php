<?php require('includes/config.php');

//redirect if user logged_in
if( $user->is_logged_in() ){ header('Location: members.php'); }

//if form submitted, start processing
if(isset($_POST['submit'])){

    //Basic validation, can be improved further as required
    if(strlen($_POST['username']) < 3){
        $error[] = 'Username is too short.';
    } else {
        $stmt = $db->prepare('SELECT username FROM members WHERE username = :username');
        $stmt->execute(array(':username' => $_POST['username']));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!empty($row['username'])){
            $error[] = 'Username provided is already in use.';
        }

    }

    if(strlen($_POST['password']) < 3){
        $error[] = 'Password is too short.';
    }

    if(strlen($_POST['passwordConfirm']) < 3){
        $error[] = 'Confirm password is too short.';
    }

    if($_POST['password'] != $_POST['passwordConfirm']){
        $error[] = 'Passwords do not match.';
    }

    //email validation
    if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
        $error[] = 'Please enter a valid email address';
    } else {
        $stmt = $db->prepare('SELECT email FROM members WHERE email = :email');
        $stmt->execute(array(':email' => $_POST['email']));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!empty($row['email'])){
            $error[] = 'Email provided is already in use.';
        }
    }


    //if no errors, forward to registration
    if(!isset($error)){

        //hash the password
        $hashedPassword = $user->password_hash($_POST['password'], PASSWORD_BCRYPT);

        //create the associated activation code//random
        $activation = md5(uniqid(rand(),true));

        try {

            //insert into database with a prepared statement
            $stmt = $db->prepare('INSERT INTO members (username,password,email,active) VALUES (:username, :password, :email, :active)');
            $stmt->execute(array(
                ':username' => $_POST['username'],
                ':password' => $hashedPassword,
                ':email' => $_POST['email'],
                ':active' => $activation
            ));
            $id = $db->lastInsertId('memberID');

            //send email with activation link
            $to = $_POST['email'];
            $subject = "Registration Confirmation";
            $body = "Thank you for registering.\n\n To activate your account, please click on this link:\n\n ".DIR."activate.php?x=$id&y=$activation\n\n Regards Site Admin \n\n";
            $additionalHeaders = "From: <".SITEEMAIL.">\r\n";
            $additionalHeaders .= "Reply-To: ".SITEEMAIL."";
            mail($to, $subject, $body, $additionalHeaders);

            //redirect to index page
            header('Location: index.php?action=registered');
            exit;

            //else catch the exception and show the error.
        } catch(PDOException $e) {
            $error[] = $e->getMessage();
        }

    }

}

//define page title
$title = 'Sample Login';

//include header template
require('layout/header.php');
?>


<div class="container">

    <div class="row">

        <div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">
            <form role="form" method="post" action="" autocomplete="off">
                <h2>Please Sign Up</h2>
                <p>Already a member? <a href='login.php'>Login</a></p>
                <hr>

                <?php
                //check for any errors
                if(isset($error)){
                    foreach($error as $error){
                        echo '<p class="bg-danger">'.$error.'</p>';
                    }
                }

                //if user registration completed, then show simple msg
                if(isset($_GET['action']) && $_GET['action'] == 'registered'){
                    echo "<h2 class='bg-success'>Registration successful, please check your email to activate your account.</h2>";
                }
                ?>

                <div class="form-group">
                    <input type="text" name="username" id="username" class="form-control input-lg" placeholder="User Name" value="<?php if(isset($error)){ echo $_POST['username']; } ?>" tabindex="1">
                </div>
                <div class="form-group">
                    <input type="email" name="email" id="email" class="form-control input-lg" placeholder="Email Address" value="<?php if(isset($error)){ echo $_POST['email']; } ?>" tabindex="2">
                </div>
                <div class="row">
                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <div class="form-group">
                            <input type="password" name="password" id="password" class="form-control input-lg" placeholder="Password" tabindex="3">
                        </div>
                    </div>
                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <div class="form-group">
                            <input type="password" name="passwordConfirm" id="passwordConfirm" class="form-control input-lg" placeholder="Confirm Password" tabindex="4">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-6 col-md-6"><input type="submit" name="submit" value="Sign UP" class="btn btn-primary btn-block btn-lg" tabindex="5"></div>
                </div>
            </form>
        </div>
    </div>

</div>

<?php
//include footer template
require('layout/footer.php');
?>
