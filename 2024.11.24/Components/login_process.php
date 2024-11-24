<?php 
session_start();
include 'db_connect.php';

if($_SERVER["REQUEST_METHOD"]== "POST"){
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $query = "SELECT * FROM students WHERE username = :username";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":username", $username, PDO::PARAM_STR);
    $stmt->execute();

    if($stmt->rowCount() == 1){
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if($password === $user['password']){
            $_SESSION['logged_in'] = true;
            $_SESSION['student_id'] = $user['student_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['stud_name'] = $user['stud_name'];
            header("Location: ../homepage.php");
            exit;
        } else{
            $error = "Invalid Password.";
        }
    }else{
        $error = "Username not found.";
    }
}
if(isset($error)){
    $_SESSION['error_message'] = $error;
    header("Location: ../loginasvoter.php");
    exit;
}
?>