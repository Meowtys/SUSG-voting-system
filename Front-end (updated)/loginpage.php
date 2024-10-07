<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSG Election System - Login Page</title>

    <style>

        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            scroll-behavior: smooth;
        }

        .main {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            height: 80vh;
            background-color: #fff; 
        }

        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
        }

        .login-box {
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 320px;
            position: relative;
        }

        .login-box .logo {
            width: 120px;
            height: 120px;
            margin-bottom: 20px;
        }


        h2 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }

        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .input-group label {
            font-weight: 500;
            color: #333;
        }

        .input-group input {
            margin-top: 5px;
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border: 2px solid #ccc;
            border-radius: 5px;
            transition: border 0.3s ease;
        }

        .input-group input:focus {
            
            outline: none;
        }

        .login-btn {
            margin-top: 15px;
            background-color: #c41f1f;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            width: 80%; 
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: block; 
            margin: 0 auto; 
            text-align: center; 
        }


        .login-btn:hover {
            background-color: #b01919;
        }

        @media (max-width: 768px) {
            .login-box, .sidebar {
                width: 100%;
                margin: 0 auto;
            }
        }
    </style>
    <script src="script/load.js" type="module" defer></script>
</head>

<body>
    
    <header id="header">
    </header>
    
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

    <footer id="footer">
    </footer>
</body>

</html>