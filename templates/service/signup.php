<?php
session_start();
include('../../includes/db-connect.inc.php');
include('../../includes/.env.php');

$email = $password = $name = $surname = $errormessage = $mailmessage = $activationCode = "";
$error = $errorSignup = $errorBlocked = $newUser = false;
$user_ip = $_SERVER['REMOTE_ADDR'];

//DB query to selects all user profiles
//========================================================================================================
$select_query = "SELECT * FROM `users`";
$result = $conn->query($select_query);

//Send email with activation code after succesful registration
//========================================================================================================
function sendActivationEmail ($emailValue, $accountActivationCode) {
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
  $mail->Subject = "Digital_Marketplace account activation";
  $mail->Body    = "<div>Thank you for signing up to Digital Marketplace! <br> Please confirm your email address within the next 15 min. Your <strong>activation code</strong> is: <br><div style='border: 1px solid #319795; padding: 15px; background-color: #f2f2f2; color: #319795; font-size: 25px; display: inline-block;'><strong> $accountActivationCode </strong></div>.</div>";
  $mail->AltBody = "Thank you for signing up to Digital Marketplace! <br> Your account activation code is: $accountActivationCode.";
  $mail->send();
}

//Create user in DB
//========================================================================================================
function createNewUser ($userEmail, $userPassword, $userName, $userSurname, $user_ip, $conn) {
  $insert_query = "INSERT INTO `users`(`ID`, `email`, `password`, `name`, `surname`, `address`, `postcode`, `phone`, `user_ip`, `login_attempts`, `status`, `image`)";
  $insert_query .=" VALUES ('', '$userEmail', '$userPassword', '$userName', '$userSurname', '', '', '', '$user_ip', '0', 'not active', '')";
  $res = mysqli_query($conn, $insert_query);

  $_SESSION['email'] = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
  $activationCode = $_SESSION['activationCode'] = rand(10,1000000);
  $_SESSION['timestamp'] = time();

  sendActivationEmail($userEmail, $activationCode);

  header("Location: confirm_email.php");
  exit;
}

//Loop thorugh all POST submitted data
//========================================================================================================
foreach($_POST as $key => $value) {
  $_SESSION['post'][$key] = $value;
}

//Set or store user entered input fields values in case of succesful or unsuccessful form submit
//========================================================================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
  foreach($_POST as $key => $value) {
    if(empty($_POST[$key])) {
      $email = $_SESSION['post']['email'];
      $password = $_SESSION['post']['password'];
      $name = $_SESSION['post']['name'];
      $surname = $_SESSION['post']['surname'];
    } else {
      $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
      $password = $_POST['password'];
      $name = $_POST['name'];
      $surname = $_POST['surname'];
    }
  }
}


$formSignup = (isset($_POST['email']) && isset($_POST['password']) && isset($_POST['name']) && isset($_POST['surname']));

  //Validate form was submitted successfully
  //========================================================================================================
 	if(isset($_POST['submit'])) {
 		$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
		$enter_password = $_POST['password'];
 		$password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $name = $_POST['name'];
    $surname = $_POST['surname'];

    //Validate user submitted data
    //========================================================================================================
    if (empty($_POST['email']) || empty($_POST['password']) || empty($_POST['name']) || empty($_POST['surname'])) {
      //Validate if all required fields were filled out
      //========================================================================================================
      $error = true;
      $errormessage = 'Please fill out all fields.';
    } else if  ($formSignup == true && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      //Validate email format
      //========================================================================================================
      $error = true;
      $errormessage = "Invalid email address. Please enter a valid email address.";
    } else {
  			if ($result->num_rows === 0) {
  			    $newUser = true;
  			} else {
          while($row = $result->fetch_assoc()) {
            if ($email === $row['email']) {
              //validate if email already exists in DB
              //========================================================================================================
              $newUser = false;
              $error = true;
              $errormessage = 'This email address already exists in our database.';
            } else if (($user_ip === $row['user_ip']) && ($row['status'] === 'blocked')) {
              //validate if user IP is not blocked
              //========================================================================================================
              $newUser = false;
              $error = $errorBlocked = true;
              $errormessage = 'This IP address has been blocked.';
            } else {
              $newUser = true;
            }
          }
  			}

        //If form submit succesful - call function to create new user
        //========================================================================================================
        if ($newUser) {
          createNewUser($email, $password, $name, $surname, $user_ip, $conn);
        }
 		}
 	}

?><?php include('../header.php'); ?>
<body class="overflow-auto flex flex-col items-center">
<div class="w-full h-full flex flex-grow justify-center items-center">

	<div class="pb-6 w-full xl:w-1/3 lg:w-1/2">

		<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" class="" method="POST" novalidate>
			<div class="py-4 flex justify-center">
				<span class="font-bold">Register:</span>
			</div>
			<div class="flex justify-center text-xs mt-4">
				<span class="text-center text-red-600 font-bold"><?php	if($error){echo $errormessage;}?></span>
        <span class="text-center"><?php echo $mailmessage;?></span>
			</div>
			<div class="px-6 pt-4">
				<label for="email">Email <span class="text-red-600">*</span></label>
				<input class="w-full px-3 py-2 bg-gray-200" id="email" name="email" value="<?php echo $email ?>" type="text" required>
			</div>
			<div class="px-6 pt-4">
				<label for="password">Password <span class="text-red-600">*</span></label>
				<input class="w-full px-3 py-2 bg-gray-200" id="password" name="password" type="password" required>
			</div>
      <div class="px-6 pt-4">
        <label for="name">Name <span class="text-red-600">*</span></label>
        <input class="w-full px-3 py-2 bg-gray-200" id="name" name="name" value="<?php echo $name ?>" type="text" required>
      </div>
      <div class="px-6 pt-4">
        <label for="surname">Surname <span class="text-red-600">*</span></label>
        <input class="w-full px-3 py-2 bg-gray-200" id="surname" name="surname" value="<?php echo $surname ?>" type="text" required>
      </div>
			<div class="px-6 pt-6 pb-4 flex justify-center">
				<?php if (!$errorBlocked) { ?>
          <input class="px-3 py-2 bg-teal-600 text-white font-bold w-1/2" type="submit" name="submit" value="Register">
				<?php } ?>
			</div>
		</form>

		<div class="flex justify-center text-sm">
			<span>You already have an account? <strong class="font-bold text-teal-600 hover:underline"><a href="login.php">Login now!</a></strong></span>
		</div>

	</div>

</div>
</body><?php include('../footer.php'); ?>