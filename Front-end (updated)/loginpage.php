<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSG Election System - Login Page</title>

    <link rel="icon" href="asset/susg.png" type="image/png">
    <!-- Link to external CSS -->
    <link rel="stylesheet" href="cssscript/loginpagestyle.css">
    <script src="script/load.js" type="module" defer></script>
</head>

<body>
    
    <header id="header"></header>
    
    <main class="main">
        <div class="login-container">
            <div class="login-box">
                <img src="asset/surem.png" alt="University Logo" class="logo">
                <h2>Login</h2>
                <form action ="" method = "POST">
                    <div class="input-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="input-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit" class="login-btn">Login</button>
                </form>

    </script>
            <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                $username = $_POST['username'];
                $password = $_POST['password'];

                echo "<script>console.log('Username: " . $username . "');</script>";
                echo "<script>console.log('Password: " . $password . "');</script>";

                $correctUsername = "RamAmper";
                $correctPassword = "123";


                if ($username === $correctUsername && $password === $correctPassword){
                    echo "<script>window.location.href = 'homepage.html';</script>";
                } else {
                    echo "<p style ='color: red;'> Invalid username and/or password!</p>";
                }
            }
            ?>
            </div>
        </div>
    </main>

    <footer id="footer"></footer>
</body>

</html>