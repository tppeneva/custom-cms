<?php
session_start();
include('../../includes/db-connect.inc.php');
include('../../includes/.env.php');

//Initial status user not logged in
//========================================================================================================
$user_logged = $user_ID = $activationCode = "";
$timestamp = time();

//Check if Session is started after login
//========================================================================================================
if (PHP_SESSION_ACTIVE && isset($_SESSION['activationCode']) && isset($_SESSION['email'])) {
  $activationCode = $_SESSION['activationCode'];
  $email = $_SESSION['email'];
} else {
  header("Location: ../../index.php");
}

$session_lifetime = 15;
$emailConfirmed = $error = false;
$errormessage = "";
$select_query = "SELECT * FROM `users`";
$result = $conn->query($select_query);

//Send email to confirm succesful account activation
//========================================================================================================
function confirmActivationEmail ($emailValue) {
  require_once('../../phpmailer/PHPMailerAutoload.php');
  $mail = new PHPMailer;
  $mail->isSMTP();
  $mail->Host = 'smtp.mail.yahoo.com';
  $mail->SMTPAuth = true;
  $mail->Username = $_ENV["AUTH_USER"];
  $mail->Password = $_ENV["AUTH_PASSWORD"];
  $mail->SMTPSecure = 'tls';
  $mail->Port = 587;
  $mail->setFrom($_ENV["AUTH_USER"]);
  $mail->addAddress($emailValue);
  $mail->isHTML(true);
  $mail->Subject = "Digital_Marketplace - successful account activation";
  $mail->Body    = "<div>You have successfully activated your Digital Marketplace account! <br> Have fun with our platform! </div>";
  $mail->AltBody = "You have successfully activated your Digital Marketplace account!";
  $mail->send();
}

//Validate that account is activated within 15 min
//========================================================================================================
if ((time()<=($_SESSION['timestamp'] + $session_lifetime*60))) {
		while($row = $result->fetch_assoc()) {
			if ($row['status'] != 'blocked') {
        //Validate user IP is not blocked
        //========================================================================================================
				if (isset($_POST['activationCode'])) {
					if ($activationCode == $_POST['activationCode']) {
            //Activate user account in case submitted activation code match
            //========================================================================================================
						$update_query = "UPDATE `users` SET `status`= 'active' WHERE `email` = '$email'";
						$res = mysqli_query($conn, $update_query);

            $_SESSION['confirmed_account'] = true;

						confirmActivationEmail($email);

						header("Location: activated_account.php");
						exit;
					} else {
            //If submitted activation code does not match - display error message
            //========================================================================================================
						$error = true;
						$errormessage = "The activation code is invalid. Please try to register again.";
						$update_query = "DELETE `users` WHERE `email` = '$email'";
						$res = mysqli_query($conn, $update_query);
					}
				}
			} else {
          //If user is blocked - display error message
          //========================================================================================================
					$error = true;
					$errormessage = 'This IP address has been blocked.';
			}
		}
} else {
  //If Activation session expired - display error message and delete user profile from DB
  //========================================================================================================
	$error = true;
	$errormessage = "Your activation code has expired. Please try to register again.";
	$update_query = "DELETE `users` WHERE `email` = '$email'";
	$res = mysqli_query($conn, $update_query);
	session_destroy();
	exit;
}
?><?php include('../../templates/header.php'); ?>
 <body class="overflow-auto flex flex-col items-center">
 <div class="container flex flex-col w-full flex-grow justify-center items-center">
	 <h2 class="font-bold inline-block flex items-center mb-24">Thank you for signing up! Please confirm your account by entering your activation code:</h2>
	 <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" class="block w-1/3" method="POST" novalidate>
		 <div class="text-center">
			 <div class="flex flex-col">
				 <label for="activationCode">Activation code <span class="text-red-600">*</span></label>
				 <input class="px-3 py-2 bg-gray-200" id="activationCode" name="activationCode" value="" type="text" required>
				 <span class="text-center text-red-600 font-bold"><?php	if($error){echo $errormessage;}?></span>
			 </div>
				<input class="my-6 px-3 py-2 bg-teal-900 text-white font-bold" type="submit" name="submit" value="Activate">
		 </div>
	 </form>
 </div>
</body>
<?php include('../../templates/footer.php'); ?>
