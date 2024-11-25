<?php
require_once 'connect.php';

session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signin'])) {
    $student_id = filter_input(INPUT_POST, 'student_id', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];

    // Debugging: Log the received student_id and password
    error_log("Received Student ID: $student_id");
    error_log("Received Password: $password");

    if (empty($student_id)) {
        $errors['student_id'] = 'Student ID cannot be empty';
    }

    if (empty($password)) {
        $errors['password'] = 'Password cannot be empty';
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header('Location: loginasvoter.php');
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = :student_id");
    $stmt->execute(['student_id' => $student_id]);
    $user = $stmt->fetch();

    // Debugging: Log the fetched user data
    if ($user) {
        error_log("User found: " . print_r($user, true));
    } else {
        error_log("User not found");
    }

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'student_id' => $user['student_id'],
        ];

        header('Location: homepage.php');
        exit();
    } else {
        $errors['login'] = 'Invalid student ID or password';
        $_SESSION['errors'] = $errors;
        header('Location: loginasvoter.php');
        exit();
    }
}