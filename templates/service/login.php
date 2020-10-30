<?php
session_start();
include('../../includes/db-connect.inc.php');

$error = $emailMatch = $blockedUser = false;
$loginAttempts = 0;
$user_ip = $_SERVER['REMOTE_ADDR'];

$formsent = (isset($_POST['email']) && isset($_POST['password']));

if($formsent == true){
	$email = $_POST['email'];
	$password = $_POST['password'];

	//DB query to selects all user profiles
	//========================================================================================================
	$select_query = "SELECT * FROM `users` WHERE `email` = '$email'";
	$res = mysqli_query($conn, $select_query);
	$row = mysqli_fetch_array($res);

  if ($email != $row['email']) {
			//If email not registered - display error message
			//========================================================================================================
			$error = true;
			$errormessage = 'Incorrect email address. Please enter a valid email.';
	} else if (!password_verify($password, $row['password']) && ($row['status'] === "active")) {
			//If invalid password, display error message and count unsuccessful login attempts.
			//========================================================================================================
			$emailMatch = true;
			$error = true;
			$errormessage = 'Incorrect password. Please enter a valid password.';
			$_SESSION['loginAttempts'] = $row['login_attempts'] + 1;
			$loginAttempts = $_SESSION['loginAttempts'];
			$update_query = "UPDATE `users` SET `login_attempts`= '$loginAttempts' WHERE `email` = '$email'";
			$res = mysqli_query($conn, $update_query);
	} else if (($loginAttempts >= 3) || ($row['status'] === "blocked")) {
			//If user is blocked - display error message
			//========================================================================================================
			$blockedUser = true;
			$error = true;
			$errormessage = 'Your account has been locked.<br>Please contact your administrator.';
	} else {
			//On successful login set Session parameters and reset login_attempts
			//========================================================================================================
			$update_query = "UPDATE `users` SET `login_attempts`= '0' WHERE `email` = '$email'";
			$res = mysqli_query($conn, $update_query);

			$_SESSION['user_logged'] = true;
			$_SESSION['user_ID'] = $row['ID'];
			$_SESSION['timestamp'] = time();
			//Redirect to homepage
			//========================================================================================================
			header("Location: ../../index.php");
			exit;
	}


	//Block user IP after 3 unsuccessful login attempts.
	//========================================================================================================
	if ($loginAttempts >= 3) {
			$update_query = "UPDATE `users` SET `status`= 'blocked' WHERE `user_ip` = '$user_ip'";
			$res = mysqli_query($conn, $update_query);
	}
}
?><?php include('../header.php'); ?>
<body class="overflow-auto flex flex-col items-center">
<div class="w-full h-full flex-grow flex justify-center items-center">

	<div class="pb-6 w-full xl:w-1/3 lg:w-1/2">

		<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" class="" method="POST" novalidate>
			<div class="py-4 flex justify-center">
				<span class="font-bold">Login:</span>
			</div>
			<div class="flex justify-center text-xs mt-4">
				<span class="text-center text-red-600 font-bold"><?php	if($error){echo $errormessage;}?></span>
			</div>
			<div class="px-6 pt-4">
				<label for="email">Email:</label>
				<input class="w-full px-3 py-2 bg-gray-200" id="email" name="email" value="" type="email">
			</div>
			<div class="px-6 pt-4">
				<label for="password">Password:</label>
				<input class="w-full px-3 py-2 bg-gray-200" id="password" name="password" type="password">
			</div>
			<div class="px-6 pt-6 pb-4 flex justify-center">
				<?php if (($loginAttempts <= 3 || !$emailMatch) && !$blockedUser) { ?>
					<button class="px-3 py-2 bg-teal-600 text-white font-bold w-1/2" type="submit" name="login_button">Login</button>
				<?php } ?>
			</div>
		</form>
		<div class="flex justify-center text-sm">
			<span>You don't have an account? <strong class="font-bold text-teal-600 hover:underline"><a href="signup.php">Sign up now!</a></strong></span>
		</div>

	</div>

</div>
</body><?php include('../footer.php'); ?>
