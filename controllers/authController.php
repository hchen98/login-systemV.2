<?php

session_start();

require 'config/db.php';
// Choose mail provider at your own preference
require_once 'PHPMailer.php';

$errors = array();
$username = "";
$email = "";

//is the user clicks on the sign up button
if (isset($_POST['signup-btn'])) {
    //get variables from the user:
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $passwordConf = $_POST['passwordConf'];

    //validations:
    if (empty($username)) {
        $errors['username'] = 'An username is required!';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        //using PHP built-in email validation feature
        $errors['email'] = 'An email is required!';
    }
    if (empty($email)) {
        $errors['email'] = 'An email is required!';
    }
    if (empty($password)) {
        $errors['password'] = 'A password is required!';
    }

    if ($password !== $passwordConf) {
        $errors['password'] = 'The two passwords do not match!';
    }

    //duplicate email validation:
    $emailQuery = "SELECT * FROM users WHERE email=? LIMIT 1";
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
        $password = password_hash($password, PASSWORD_DEFAULT);
        //generating unique random string of length 100

        $token = bin2hex(random_bytes(50));
        $verified = false;

        $sql = "INSERT INTO users (username, timex, email, verified, token, password) VALUES (?, now(), ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssbss', $username, $email, $verified, $token, $password);

        if ($stmt->execute()) {
            //login user
            $user_id = $conn->insert_id;
            $_SESSION['id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['verified'] = $verified;

            //using unique token (as a key) to the user for verification
            sendVerificationEmail($email, $token);

            //flash message
            $_SESSION['message'] = "You are now logged in!";
            $_SESSION['alert-class'] = "alert-success";
            header('location: index.php');
            exit();
        } else {
            $errors['db_error'] = "Databases error: failed to register";
        }

    }
}

//is the user clicks on the login button
if (isset($_POST['login-btn'])) {
    //get variables from the user:
    $username = $_POST['username'];
    $password = $_POST['password'];

    //validations:
    if (empty($username)) {
        $errors['username'] = 'An username is required!';
    }
    if (empty($password)) {
        $errors['password'] = 'A password is required!';
    }

    //To avoid display "Wrong credentials" for the first time
    if (count($errors) === 0) {

        $sql = "SELECT * FROM users WHERE email=? OR username=? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            //using PHP built-in feature to verify the user's input password AND db password
            $_SESSION['id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['verified'] = $user['verified'];

            //flash message
            $_SESSION['message'] = "You are now logged in!";
            $_SESSION['alert-class'] = "alert-success";
            header('location: index.php');
            exit();
        } else {
            $errors['login_fail'] = "Wrong credentials";
        }

    }

}

//logout user
if (isset($_GET['logout'])) {
    session_destroy();
    //also delete all session info
    unset($_SESSION['id']);
    unset($_SESSION['username']);
    unset($_SESSION['email']);
    unset($_SESSION['verified']);
    header('location: login.php');
    exit();
}

//verify user
function verifyUser($token)
{
    global $conn;
    $sql = "SELECT * FROM users WHERE token= '$token' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $update_query = "UPDATE users SET verified = 1 WHERE token = '$token'";

        if (mysqli_query($conn, $update_query)) {
            //log user in
            $_SESSION['id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['verified'] = 1;

            //flash message
            $_SESSION['message'] = "Your email address was successfully verified!";
            $_SESSION['alert-class'] = "alert-success";
            header('location: index.php');
            exit();
        }
    } else {
        //rare case
        echo 'User not found!';
    }

}

//if the user clicks on the forgot password button
if(isset($_POST['forgot-password'])){
    $email = $_POST['email'];

    //email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        //using PHP built-in email validation feature
        $errors['email'] = 'A valid email is required!';
    }
    if (empty($email)) {
        $errors['email'] = 'An email is required!';
    }

    if(count($errors) == 0){
        $sql = "SELECT * FROM users WHERE email = '$email' LIMIT 1";

        $result = mysqli_query($conn, $sql);
        $user = mysqli_fetch_assoc($result);
        $token = $user['token'];
        sendPasswordResetLink($email, $token);
        header('location: messsage_flag.php');
        exit(0);
    }

}

//if the user clicks on reset password button
if(isset($_POST['reset-password-btn'])){
    $password = $_POST['password'];
    $passwordConf = $_POST['passwordConf'];

    if (empty($password) || empty($passwordConf)) {
        $errors['password'] = 'A password is required!';
    }

    if ($password !== $passwordConf) {
        $errors['password'] = 'The two passwords do not match!';
    }

    $password = password_hash($password, PASSWORD_DEFAULT);
    $email = $_SESSION['email'];

    if(count($errors) == 0){
        $sql = "UPDATE users SET password = '$password' WHERE email = '$email' ";
        $result = mysqli_query($conn, $sql);

        if($result){
            header('location: login.php');
            exit(0);
        }
    }
}

function resetPassword($token){
    global $conn;

    $sql = "SELECT * FROM users WHERE token='$token' LIMIT 1";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);
    $_SESSION['email'] = $user['email'];
    header('location: reset_password.php');
    exit(0);
}
?>
