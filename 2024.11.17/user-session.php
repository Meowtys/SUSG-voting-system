<?php
require_once 'dbConnect.php';

session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signin'])) {
    $student_id = filter_input(INPUT_POST, 'student_id', FILTER_SANITIZE_NUMBER_INT);
    $password = $_POST['password'];

    if (empty($student_id)) {
        $errors['student_id'] = 'Student ID cannot be empty';
    }

    if (empty($password)) {
        $errors['password'] = 'Password cannot be empty';
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header('Location: index.php');
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE student_id = :student_id");
    $stmt->execute(['student_id' => $student_id]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'student_id' => $user['student_id'],
            'name' => $user['name'],
            'created_at' => $user['created_at']
        ];

        header('Location: home.php');
        exit();
    } else {
        $errors['login'] = 'Invalid student ID or password';
        $_SESSION['errors'] = $errors;
        header('Location: index.php');
        exit();
    }
}