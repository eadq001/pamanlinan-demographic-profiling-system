<!-- <?php 
session_start();

	include("connection.php");
	include("functions.php");


	if($_SERVER['REQUEST_METHOD'] == "POST")
	{
		//something was posted
		$user_name = $_POST['user_name'];
		$password = $_POST['password'];

		if(!empty($user_name) && !empty($password) && !is_numeric($user_name))
		{

			//save to database
			$user_id = random_num(20);
			$query = "insert into users (user_id,user_name,password) values ('$user_id','$user_name','$password')";

			mysqli_query($con, $query);

			header("Location: login.php");
			die;
		}else
		{
			echo "Please enter some valid information!";
		}
	}
?> -->
<style>
	
  .overlay {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background-color: rgba(0, 0, 0, 0.56); /* Green overlay */
  z-index: 0;
}
  * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Inter', sans-serif;
      color: #fff;
      line-height: 1.6;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
       background-size: cover; 
       position: relative;
    }
	
  .bg-video {
    position: absolute;
	background-size: cover;
    left: 0;
    width: 100%;
    z-index: -90;
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
	text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
  }

  a:hover {
	color:#ffd43b;
  }

</style>

<!DOCTYPE html>
<html>
<head>
	<title>Signup</title>

	<!-- <link rel="stylesheet" href="login.css"> -->
	 <link rel="stylesheet" href="signup.css">
	 <link rel="stylesheet" href="font.css">
	 <link rel="shortcut icon" href="pamanlinan.png" type="image/x-icon">
</head>
<body>
		<video autoplay muted loop playsinline class="bg-video">
		<source src="video.mp4" type="video/mp4">
		</video>
		 <div class="overlay"></div>

	<main class="login-form">
		<div class="forms">
			<form method="post" class="form">
				<img src="pamanlinan.png" alt="">
			<h1>Sign up to Barangay Pamanlinan</h1><br>
			<div class="inputs">	<div class="username">
					<label for="user_name">Username</label>
					<input id="text" type="text" name="user_name">
				</div>

				<div class="password">
					<label for="password">Password</label>
					<input id="text" type="password" name="password">
				</div>
				<button id="button" type="submit" value="Login"> Sign up </button>
				</div>
				<a href="login.php" class="click">Already have an account?</a>
			</form>
		</div>
	</main>
</body>
</html>