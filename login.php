<?php

session_start();

include("connection.php");
include("functions.php");


if ($_SERVER['REQUEST_METHOD'] == "POST") {
	//something was posted
	$user_name = $_POST['user_name'];
	$password = $_POST['password'];

	if (!empty($user_name) && !empty($password) && !is_numeric($user_name)) {

		//read from database
		$query = "select * from users where user_name = '$user_name' limit 1";
		$result = mysqli_query($con, $query);

		if ($result) {
			if ($result && mysqli_num_rows($result) > 0) {

				$user_data = mysqli_fetch_assoc($result);

				if ($user_data['password'] === $password) {

					$_SESSION['user_id'] = $user_data['user_id'];
					header("Location: list.php");
					die;
				}
			}
		}

		echo "<p style='text-align:center'> wrong username or password! </p>";
	} else {
		echo "wrong username or password!";
	}
}

?>

<!DOCTYPE html>
<html>

<head>
	<title>Login</title>
	<link rel="stylesheet" href="login.css">
	<link rel="stylesheet" href="font.css">
	<link rel="shortcut icon" href="pamanlinan.png" type="image/x-icon">
</head>
<style>
	* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

	 body {
     display: flex;
    background-position: center;
    width: 100%;
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
	background-image: url('logs.jpg');
	background-size: cover; 
    position: relative;   
  }
 
    button {
    width: 100%;
    height: 32px;
    border-radius: 5px;
    outline: none;
    border: none;
    background: #057570;
    color: white;
    font-size: 16px;
    cursor: pointer;

  }

  a:link, a:visited {
	color:white;
	transition: all 0.3s;
	text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.71);
  }

  a:hover {
	color:#ffd43b;
  }

</style>


<body>


	<main class="login-form">
		<div class="forms">
			
			<form method="post" class="form">
  				<img src="pamanlinan.png" alt="">
				<h1>Sign in to Barangay Pamanlinan</h1><br>
				<div class="username">
					<label for="user_name">Username</label>
					<input id="text" type="text" name="user_name">
				</div>

				<div class="password">
					<label for="password">Password</label>
					<input id="text" type="password" name="password">
				</div>
				<button id="button" type="submit" value="Login"> Sign in </button>
				<a href="signup.php" class="click">Click to Signup</a>
			</form>
		</div>
		<br>
		<div class="check_user">
		
		</div>
	</main>

</body>

</html>