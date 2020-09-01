<?php

require 'config/db.php';
// Choose a mail provider at your own preference
require_once 'PHPMailer.php';

session_start();

$errors = array();
$username = "";
$email = "";

//is the user clicks on the sign up button
if (isset($_POST['signup-btn'])) {

    //filter sensitive SQL injection
    $POST = filter_var_array($_POST, FILTER_SANITIZE_STRING);

    //get variables from the user:
    $email = $POST['email'];
    $password = $POST['password'];
    $passwordConf = $POST['passwordConf'];

    //validations:
    if (!empty($email)){
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            //using PHP built-in email validation feature
            $errors['email'] = 'An email is required!';
    } else
        $errors['email'] = 'An email is required!';

    if (!empty($password)){
        if ($password !== $passwordConf)
            $errors['password'] = 'The two passwords do not match!';
    } else
        $errors['password'] = 'A password is required!';

    //duplicate email validation:
    $emailQuery = "SELECT email FROM users WHERE email=? LIMIT 1";
    $stmt = $conn->prepare($emailQuery);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $userCount = $result->num_rows;
    $stmt->close();

    if ($userCount > 0) {
        $errors['email'] = "Email already exists!";
    }

    // end all validations

    //errors check && password encryption
    if (count($errors) == 0) {

        //hash the password
        $password_encryp = password_hash($password, PASSWORD_DEFAULT);

        //generating unique random string token of length 100
        $token = bin2hex(random_bytes(100));
        $verified = false;

        $sql = "INSERT INTO usr (email, register_date, verify, tok, passwrd) VALUES (?, now(), ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssss', $email, $verified, $token, $password_encryp);

        if ($stmt->execute()) {
            // user successfully login
            // start session now

            $user_id = $conn->insert_id;
            $_SESSION['id'] = $user_id;
            $_SESSION['email'] = $email;
            $_SESSION['verify'] = $verified;

            //using unique token (as a key) to the user for verification
            sendVerificationEmail($email, $token);

            //flash message
            $_SESSION['message'] = "You are now logged in!";
            $_SESSION['alert-class'] = "alert-success";
            header('location: index.php');
            exit();
        } else
            $errors['db_error'] = "Databases error: failed to register";

    }
}

//user clicks on the login button
if (isset($_POST['login-btn'])) {

    //filter sensitive SQL injection
    $POST = filter_var_array($_POST, FILTER_SANITIZE_STRING);

    //get variables from the user:
    $email = $POST['email'];
    $password = $POST['password'];

    //validations:
    if (empty($email)) {
        $errors['username'] = 'An email is required!';
    }
    if (empty($password)) {
        $errors['password'] = 'A password is required!';
    }

    //To avoid display "Wrong credentials" for the first time
    if (count($errors) === 0) {

        $sql = "SELECT usr_id, email, verify, passwrd FROM usr WHERE email=? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['passwrd'])) {
            //using PHP built-in feature to verify the user's input password AND db password

            //successfully login user
            $_SESSION['id'] = $user['usr_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['verify'] = $user['verify'];

            //flash a message
            $_SESSION['message'] = "You are now logged in!";
            $_SESSION['alert-class'] = "alert-success";
            header('location: index.php');
            exit();
        } else
            $errors['login_fail'] = "Wrong credentials";

    }

}

//logout user
if (isset($_GET['logout'])) {
    session_destroy();
    //also delete all session info
    unset($_SESSION['id']);
    unset($_SESSION['email']);
    unset($_SESSION['verify']);
    unset($_SESSION['reset_email']);
    unset($errors);
    header('location: login.php');
    exit();
}

function signup_token_verify($token) {
    //verify user when the first time sign up the account
    //INPUT: token
    //however, token needs to be filtered too! In the future version, this might be fixed

    global $conn;

    $sql = "SELECT usr_id, email, verify FROM usr WHERE tok=? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (strlen($user) > 0) {

        $update_query = "UPDATE usr SET verify = 1 WHERE tok = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('s', $token);

        if ($stmt->execute()) {
            //successfully verify user sign up token
            $_SESSION['id'] = $user['usr_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['verified'] = 1;

            //flash message
            $_SESSION['message'] = "Your email address was successfully verified!";
            $_SESSION['alert-class'] = "alert-success";
            header('location: index.php');
            exit();
        }
    } else
        //rare case
        echo 'User not found!';

}

//if the user clicks on the forgot password button
if(isset($_POST['forgot-password'])){

    //filter sensitive SQL injection
    $POST = filter_var_array($_POST, FILTER_SANITIZE_STRING);

    $email = $POST['email'];

    //email validation
    if (!empty($email)) {

        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            //using PHP built-in email validation feature
            $errors['email'] = 'A valid email is required!';

    } else
        $errors['email'] = 'An email is required!';

    if(count($errors) == 0){
        $sql = "SELECT email FROM usr WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if(!empty($user['email']) && strlen($user['email']) > 0){
            //this user does exist

            //generate a new token temporary
            $token_temp = bin2hex(random_bytes(100));

            if(insert_reset_password_tbl($token_temp) == 1 && sendPasswordResetLink($email, $token_temp))
                //make sure there's a record inserted and email is successfully sent
                header('location: messsage_flag.php');

        }
        exit(0);

    }

}

function resetPassword($token){
    //this func will activate when the user clicks on the reset password link on the email
    //INPUT: temporary token

    global $conn;

    $sql = "SELECT email FROM rest_passwrd WHERE temp_token=? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if(!empty($user['email']) && strlen($user['email']) > 0){
        //make sure such token exists

        global $_SESSION;
        $_SESSION['reset_email'] = $user['email'];
        header('location: reset_password.php');
        exit(0);
    } else {
        //token does not exist
        header('location: 404.php');
        exit(0);
    }

}

if(isset($_POST['reset-password-btn'])){
    //if the user clicks on reset password button on reset_password.php

    //filter sensitive SQL injection
    $POST = filter_var_array($_POST, FILTER_SANITIZE_STRING);

    $password = $POST['password'];
    $passwordConf = $POST['passwordConf'];

    if(!empty($_SESSION['reset_email']) && strlen($_SESSION['reset_email']) > 0) {
        //make sure the user did request to reset the password
        if (!empty($password) || !empty($passwordConf)) {

            if ($password !== $passwordConf)
                $errors['password'] = 'The two passwords do not match!';

        } else
            $errors['password'] = 'A password is required!';
    } else {
        //user did not request to reset the email
        header('location: 404.php');
        exit(0);
    }

    $password = password_hash($password, PASSWORD_DEFAULT);

    if(count($errors) == 0){

        $sql = "
            UPDATE
                usr T1,
                rest_passwrd T2
            SET
                T1.passwrd = ?, T2.rest = 1
            WHERE
                T1.email = T2.email AND T2.email = ? AND T2.rest = 0";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $password, $_SESSION['reset_email']);

        if($stmt->execute()){
            $_SESSION['message'] = "Your password have successfully changed!";
            $_SESSION['alert-class'] = "alert-success";
            header('location: login.php');
            exit(0);
        }
    }
}

function insert_reset_password_tbl($temp_token){
    //insert a record into reset password table
    //INPUT: temporary token
    //OUTPUT: return 1 successfully insert, 0 insert fail

    $client_ip = $_SERVER['REMOTE_ADDR'];
    $reset = 0;
    //0 for user haven't reset the password, 1 for user rested the password
    //this is useful when validating password reset

    global $conn;
    $sql = "INSERT INTO rest_passwrd (email, when_, location_ip, temp_token, rest) VALUES (?, now(), ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssi', $email, $client_ip, $temp_token, $reset);

    if ($stmt->execute())
        return 1;
    else
        return 0;

}

?>
