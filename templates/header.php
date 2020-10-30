<?php
$session_lifetime = 15;

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
}

//DB query to access to user profile, ads and orders, according to user ID
//========================================================================================================
$select_query = "SELECT * FROM `users` WHERE `ID` = '$user_ID'";
$result = $conn->query($select_query);

//Destroy session on Logout or session expiry after 15 min and reset login attempts count in DB
//========================================================================================================
$logout_button = isset($_POST['logout_button']);
if ($logout_button || (time()>=($timestamp + $session_lifetime*60))) {

	$update_query = "UPDATE `users` SET `login-attempts`= '0' WHERE `ID` = '$user_ID'";
	$res = mysqli_query($conn, $update_query);
	if (strpos($_SERVER['REQUEST_URI'], 'index.php')) {
		 	header("Location: ./index.php");
	} else {
		 	header("Location: ../../index.php");
	};
	session_destroy();
	exit;
}
?><style><?php if (strpos($_SERVER['REQUEST_URI'], 'index.php')) {include('./vendor/tailwind.css');} else {include('../../vendor/tailwind.css');}; ?>
  body {
    overflow: hidden;
  }
    #profile-image img,
    .fa-user-circle {
      width: 32px;
      max-height: 32px;
      border-radius: 100%;
    }
    #profile-image label {
      display: none;
    }
    #profile-image:hover label {
      display: flex;
    }
    #edit-button {
      bottom: -8px;
      background: rgba(255, 255, 255, 0.7);
    }
</style>
<div class="z-10 sticky top-0 bg-teal-600 w-full py-3 flex justify-center">
  <div class="container flex justify-between">
    <a href="<?php $_SERVER['DOCUMENT_ROOT'] ?>/xampp/custom_cms/index.php"><img class="w-32" src="<?php if (strpos($_SERVER['REQUEST_URI'], 'index.php')) {
       echo './images/service/logo.svg';
     } else {
       echo '../../images/service/logo.svg';
     }; ?>" alt="logo"></a>
<?php if (!$user_logged) { ?>
      <div class="flex items-center justify-center relative" id="profile-image" class="min-w-40 min-h-40">
          <a class="flex items-center" href="<?php $_SERVER['DOCUMENT_ROOT'] ?>/xampp/custom_cms/templates/service/login.php">
            <span><svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="user-circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512" class="svg-inline--fa fa-user-circle fa-w-16 fa-3x w-5 h-5 text-white"><path fill="currentColor" d="M248 8C111 8 0 119 0 256s111 248 248 248 248-111 248-248S385 8 248 8zm0 96c48.6 0 88 39.4 88 88s-39.4 88-88 88-88-39.4-88-88 39.4-88 88-88zm0 344c-58.7 0-111.3-26.6-146.5-68.2 18.8-35.4 55.6-59.8 98.5-59.8 2.4 0 4.8.4 7.1 1.1 13 4.2 26.6 6.9 40.9 6.9 14.3 0 28-2.7 40.9-6.9 2.3-.7 4.7-1.1 7.1-1.1 42.9 0 79.7 24.4 98.5 59.8C359.3 421.4 306.7 448 248 448z" class=""></path></svg></span>
            <div class="ml-2">
                  <span class="text-white font-bold">Login</span>
            </div>
          </a>
      </div>
<?php } else { ?>
<?php while($row = $result->fetch_assoc()) { ?>
        <div class="flex items-center justify-center relative min-w-16 min-h-16 lg:min-w-40 lg:min-h-40 cursor-pointer" id="profile-image" onclick="showMenu()">
          <?php if (!empty($row['image'])) { ?>
            <img class="w-8 h-8 rounded-full" src="<?php if (strpos($_SERVER['REQUEST_URI'], 'index.php')) {
				       echo "./".$row['image'];
				     } else {
				       echo "../../".$row['image'];
				     }; ?>" alt="">
          <?php } else { ?>
            <div class="flex justify-center items-center p-6 bg-white w-8 h-8 rounded-full">
              <span class="font-bold text-teal-600 text-1xl"><?php echo strtoupper(substr($row['name'], 0, 1).substr($row['surname'], 0, 1))?></span>
            </div>
          <?php } ?>
          <div class="ml-2">
                <span class="text-white font-bold">Profile <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="chevron-down" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="svg-inline--fa fa-chevron-down fa-w-14 fa-3x w-4 h-4 inline text-white"><path fill="currentColor" d="M207.029 381.476L12.686 187.132c-9.373-9.373-9.373-24.569 0-33.941l22.667-22.667c9.357-9.357 24.522-9.375 33.901-.04L224 284.505l154.745-154.021c9.379-9.335 24.544-9.317 33.901.04l22.667 22.667c9.373 9.373 9.373 24.569 0 33.941L240.971 381.476c-9.373 9.372-24.569 9.372-33.942 0z" class=""></path></svg></span>
          </div>

          <div class="hidden absolute bg-white w-48 right-0 shadow-lg" style="top: 52px;" id="user-profile-menu">
            <ul>
              <li class="font-bold text-teal-600 hover:bg-teal-600 hover:text-white"><a class="block px-6 py-2" href="<?php $_SERVER['DOCUMENT_ROOT'] ?>/xampp/custom_cms/templates/user/edit_user.php?action=edit&id=<?php echo $user_ID; ?>">Settings</a></li>
              <li class="font-bold text-teal-600 hover:bg-teal-600 hover:text-white"><a class="block px-6 py-2" href="<?php $_SERVER['DOCUMENT_ROOT'] ?>/xampp/custom_cms/templates/ad/list_ads.php?action=view&id=<?php echo $user_ID; ?>&page=1">My ads</a></li>
              <li class="font-bold text-teal-600 hover:bg-teal-600 hover:text-white"><a class="block px-6 py-2" href="<?php $_SERVER['DOCUMENT_ROOT'] ?>/xampp/custom_cms/templates/user/list_orders.php?action=view&id=<?php echo $user_ID; ?>&page=1">My orders</a></li>
              <li class="font-bold text-teal-600 hover:bg-teal-600 hover:text-white">
                <form class="m-0" method="post" class="inline-block">
          					<button class="font-bold px-6 py-2 w-full text-left" type="submit" name="logout_button">Logout</button>
          			</form></li>
            </ul>
          </div>
        </div>
<?php } ?>
<?php } ?>
</div>
</div>
<script type="text/javascript">
function showMenu() {
  var x = document.getElementById("user-profile-menu");
  if (x.style.display === "none") {
    x.style.display = "block";
  } else {
    x.style.display = "none";
  }
}
</script>
