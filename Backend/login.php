<?php 
    //LOGIN CODE FOR CONNECTING USER TO DATABASE
	if(ISSET ($_POST['user_login'])){
		$username = $_POST['username'];
		$password = $_POST['password'];
        session_start();
        $conn = mysqli_connect('localhost', 'root', '', 'susg_voting') or die('Unable to connect');
		$query = $conn->query("SELECT * FROM `student` WHERE `username` = '$username' && `password` = '$password'") or die(mysqli_error());
		$fetch = $query->fetch_array();
		$row = $query->num_rows;
		
		if($row > 0){
			$_SESSION['username'] = $fetch['username'];
			$_SESSION['userID'] = $fetch['userID'];
			header('location:homepage.php');
		}else{
			echo "<center><label style = 'color:red;'>Invalid username or password</label></center>";
		}
	}
?>