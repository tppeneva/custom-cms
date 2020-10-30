<?php
session_start();;
include('../../includes/db-connect.inc.php');
include('../../includes/.env.php');

//Initial status user not logged in
//========================================================================================================
$user_logged = $user_ID = "";
$timestamp = time();

//Check if Session is started after login
//========================================================================================================
if (PHP_SESSION_ACTIVE && isset($_SESSION['user_logged']) && isset($_SESSION['user_ID']) && isset($_SESSION['timestamp'])) {
	$user_logged = $_SESSION['user_logged'];
	$user_ID = $_SESSION['user_ID'];
	$timestamp = $_SESSION['timestamp'];
} else {
  header("Location: ../../index.php");
}

//DB query for selecting user according to session user ID
//========================================================================================================
$select_query = "SELECT * FROM `users` WHERE `ID` = '$user_ID'";
$user_result = $conn->query($select_query);

$error = false;
$phone = $postcode = $address = $db_password = "";
$phoneErr = $postCodeErr = $errormessage = $errorImage = "";
$maxFileSize = 5*1024*1024; // max size images to upload 5 MB
$allowed_fileformat = array('image/jpeg', 'image/png'); //allowed image formats

//Send email to confirm succesful password change
//========================================================================================================
function sendConfirmationEmail ($emailValue) {
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
  $mail->Subject = "Digital_Marketplace - password changed";
  $mail->Body    = "<div>You have successfully changed your password! </div>";
  $mail->AltBody = "You have successfully changed your password!";
  $mail->send();
}

//Update user profile settings
//========================================================================================================
if (isset($_POST['update_profile'])) {
  if(!empty($_POST['postcode']) && !preg_match('/^[0-9]{4}$/', $_POST['postcode'])) {
		//Verify entered postcode format
		//========================================================================================================
    $postCodeErr = "Please enter a valid 4 digit post code.";
  } else if (!empty($_POST['phone']) && !preg_match('/^[1-9][0-9 ]{8}$/', $_POST['phone'])) {
		//Verify phone number format
		//========================================================================================================
    $phoneErr = "Invalid entry. Please enter the last 9 digits of a valid mobile number, without first 0.";
  } else {
		//Update DB on succesful form submit
		//========================================================================================================
    $address =  filter_var($_POST['address'], FILTER_SANITIZE_STRING);
    $postcode = filter_var($_POST['postcode'], FILTER_SANITIZE_NUMBER_INT);
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_NUMBER_INT);

    $update_query = "UPDATE `users` SET `address`= '$address', `postcode`= '$postcode',`phone`= '$phone'  WHERE `ID` = '$user_ID'";
    $res = mysqli_query($conn, $update_query);

    header("Location: edit_user.php?action=edit&id=".$user_ID);
  }
}

//Update user password
//========================================================================================================
if (isset($_POST['update_password'])) {
  $res = mysqli_query($conn, $select_query);
  $row = mysqli_fetch_array($res);
  $db_password = $row['password'];

    if (empty($_POST['old_password']) || empty($_POST['new_password']) || empty($_POST['confirm_password'])) {
			//Verify all required fields are filled out
			//========================================================================================================
      $errormessage = "Please fill out all fields.";
    } else if (!password_verify($_POST['old_password'], $db_password)) {
			//Verify passowrd matches DB record, if not show error message
			//========================================================================================================
      $errormessage = "Invalid password. Entered password does not match database record.";
    } else if ($_POST['new_password'] != $_POST['confirm_password']) {
			//Verify both new password entries match
			//========================================================================================================
      $errormessage = "Could not confirm new password. Entry does not match.";
    } else {
			//Update DB on successful submit and send confirmation email
			//========================================================================================================
      $new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);

      $update_query = "UPDATE `users` SET `password`= '$new_password' WHERE `ID` = '$user_ID'";
      $res = mysqli_query($conn, $update_query);

      sendConfirmationEmail($row['email']);

      header("Location: edit_user.php?action=edit&id=".$user_ID);
    }
}

//Update user profile image
//========================================================================================================
if (isset($_POST['update_image'])) {
		//Check if image format and size OK
		//========================================================================================================
    $mimeType = mime_content_type($_FILES['img']['tmp_name']);
    $typeOK = in_array($mimeType, $allowed_fileformat);
    $sizeOK = $_FILES['img']['size']<=$maxFileSize;
    $tmppfad = $_FILES['img']['tmp_name'];
    $folder = $_SERVER['DOCUMENT_ROOT'].'/xampp/custom_cms/images/users/'.time().'_'.$_FILES['img']['name'];

    if($typeOK==true && $sizeOK==true){
			//Update DB and save file if image format and size OK
			//========================================================================================================
      $uploadSuccess = move_uploaded_file($tmppfad, $folder);
      $_SESSION['img'] = $image_url = "images/users/".time().'_'.$_FILES['img']['name'];

      $update_query = "UPDATE `users` SET `image`= '$image_url' WHERE `ID` = '$user_ID'";
      $res = mysqli_query($conn, $update_query);

      header("Location: edit_user.php?action=edit&id=".$user_ID);
    } else {
			//If image format or size do to meet requirements, show error message
			//========================================================================================================
      $errorImage = "Please upload another image. Allowed formats are jpeg/png, size max 5MB.";
    }
}
?>
<style>
  #user-profile-image-box {
    border-radius: 100%;
		overflow: hidden;
		max-width: 160px;
    max-height: 160px;
  }
  #user-profile-image label {
    display: none;
  }
  #user-profile-image:hover label {
    display: flex;
  }
  #edit-button {
    bottom: -8px;
    background: rgba(255, 255, 255, 0.7);
  }
</style>
<?php include('../../templates/header.php'); ?>
<body class="overflow-auto flex flex-grow flex-col items-center bg-gray-200">
  <div class="container flex flex-col flex-grow justify-center">
      <?php while($user_row = $user_result->fetch_assoc()) { ?>
        <div class="w-full flex justify-center items-start pb-4">
          <div class="w-full p-4 bg-white flex">
            <div class="flex items-center justify-center relative w-1/2" id="user-profile-image" class="min-w-40 min-h-40">
              <?php if (!empty($user_row['image'])) { ?>
								<div id="user-profile-image-box">
									<img class="w-40 max-h-40" src="../../<?php echo $user_row['image'] ?>" alt="">
								</div>
              <?php } else { ?>
                <div class="flex justify-center items-center p-6 bg-teal-600 w-24 h-24 rounded-full">
                  <span class="font-bold text-white text-4xl"><?php echo strtoupper(substr($user_row['name'], 0, 1).substr($user_row['surname'], 0, 1))?></span>
                </div>
              <?php } ?>

              <form class="w-full h-full flex flex-col absolute" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" class="" method="POST" enctype="multipart/form-data" novalidate>
              <!-- <div class="md:w-1/2 w-full inline-block"> -->
                <label id="edit-button" for="img" class="text-sm block my-2 absolute w-full h-full flex justify-center items-center font-bold text-white"><span class="px-2 py-1 bg-teal-600">Edit</span></label>
                <input type="file" id="img" name="img" class="hidden" onchange="document.getElementById('update_image').click();">
                <input class="px-3 py-2 bg-teal-500 text-white font-bold hidden" type="submit" id="update_image" name="update_image">
              <!-- </div> -->
              </form>
              <?php if (!empty($errorImage)) { ?>
                <span class="text-red-600 text-xs"><?php echo $errorImage;?></span>
              <?php } ?>
            </div>
            <div class="w-1/2 pl-4">
              <h2 class="font-bold text-2xl leading-none py-4"><?php echo $user_row['name']." ".$user_row['surname'] ?></h2>
              <?php if (!empty($user_row['address']) || !empty($user_row['postcode'])) { ?>
                <p class="font-bold py-2"><span class="text-gray-400 text-sm">Address: </span><?php echo $user_row['address']; ?></span></p>
              <?php } ?>
              <?php if (!empty($user_row['postcode'])) { ?>
                <p class="font-bold py-2"><span class="text-gray-400 text-sm">Post code: </span><?php echo $user_row['postcode']; ?></span></p>
              <?php } ?>
              <?php if (!empty($user_row['phone'])) { ?>
                <p class="font-bold py-2"><span class="text-gray-400 text-sm">Phone number: </span><?php echo $user_row['phone']; ?></p>
              <?php } ?>
            </div>
          </div>
        </div>

        <div class="lg:flex w-full">
          <div class="w-full mb-4 lg:flex-1 lg:pr-2">
            <div class="px-12 py-4 w-full h-full bg-white ">

              <form class="min-h-full flex flex-col" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" class="" method="POST" novalidate>
                <div class="flex">
                  <span class="font-bold">Change settings:</span>
                </div>

                <div class="w-full flex flex-row pt-4">
                  <div class="w-3/4 inline-block pr-2">
                      <div class="flex flex-col">
                        <label for="address" class="text-gray-400 text-sm">Address</label>
                        <input class="w-full px-3 py-2 bg-gray-200" id="address" name="address" value="<?php echo $user_row['address'] ?>" type="text">
                      </div>
                  </div>
                  <div class="w-1/4 inline-block pl-2">
                      <div class="flex flex-col">
                          <label for="postcode" class="text-gray-400 text-sm">Postcode</label>
                          <input class="px-3 py-2 bg-gray-200" type="number" name="postcode" value='<?php echo $user_row['postcode'] ?>'>
                          <?php if (!empty($postCodeErr)) { ?>
                            <span class="text-red-600 text-xs"><?php echo $postCodeErr;?></span>
                          <?php } ?>
                      </div>
                  </div>
                </div>

                <div class="pt-4">
                  <label for="surname" class="text-gray-400 text-sm">Phone</label>
                  <input class="w-full px-3 py-2 bg-gray-200" id="phone" name="phone" value="<?php if ($user_row['phone'] != 0) {echo $user_row['phone'];} ?>" type="number">
                  <?php if (!empty($phoneErr)) { ?>
                    <span class="text-red-600 text-xs"><?php echo $phoneErr;?></span>
                  <?php } ?>
                </div>
                <div class="mt-6">
                    <input class="px-3 py-2 bg-teal-500 text-white font-bold" type="submit" name="update_profile" value="Save changes">
                </div>
              </form>

            </div>
          </div>

          <div class="w-full mb-4 lg:flex-1 lg:pl-2">
            <div class="px-12 py-4 w-full bg-white ">

              <form class="min-h-full flex flex-col" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" class="" method="POST" novalidate>
                <div class="flex">
                  <span class="font-bold">Change password:</span>
                </div>
                <div class="flex justify-center text-xs mt-4">
                  <span class="text-center text-red-600 font-bold"><?php	if(!empty($errormessage)){echo $errormessage;}?></span>
                </div>

                <div class="pt-4">
                  <label for="old_password" class="text-gray-400 text-sm">Old password:</label>
                  <input class="w-full px-3 py-2 bg-gray-200" id="old_password" name="old_password" type="password">
                </div>

                <div class="pt-4">
                  <label for="new_password" class="text-gray-400 text-sm">New password:</label>
                  <input class="w-full px-3 py-2 bg-gray-200" id="new_password" name="new_password" type="password">
                </div>

                <div class="pt-4">
                  <label for="confirm_password" class="text-gray-400 text-sm">Confirm password:</label>
                  <input class="w-full px-3 py-2 bg-gray-200" id="confirm_password" name="confirm_password" type="password">
                </div>
                <div class="mt-6">
                    <input class="px-3 py-2 bg-teal-500 text-white font-bold" type="submit" name="update_password" value="Change password">
                </div>
              </form>

            </div>
          </div>
        </div>
      <?php } ?>
  </div>
</body><?php include('../../templates/footer.php'); ?>
