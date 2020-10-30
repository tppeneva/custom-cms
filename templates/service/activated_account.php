<?php
session_start();
include('../../includes/db-connect.inc.php');
//Check if Session is started after login
//========================================================================================================
if (!PHP_SESSION_ACTIVE && !isset($_SESSION['confirmed_account'])) {
  header("Location: ../../index.php");
} else {
	session_destroy();
}
?>
<?php include('../../templates/header.php'); ?>
<body class="overflow-auto flex flex-col items-center bg-gray-200">
<div class="h-full w-full flex flex-grow justify-center items-center text-sm">
		<p>Thank you for confirming your email address! <strong class="font-bold text-teal-600 hover:underline"><a href="login.php">Login now!</a></strong></p>
</div>
</body>
<?php include('../../templates/footer.php'); ?>
