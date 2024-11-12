<?php
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=susg_project', 'root', ''); // Adjust with your database details
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
    ?>