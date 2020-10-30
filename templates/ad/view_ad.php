<?php
session_start();
include('../../includes/db-connect.inc.php');
include('../../includes/.env.php');

//Initial status user not logged in
//========================================================================================================
$user_logged = $user_ID = $sellerID = $ad_ID = "";
$timestamp = time();

//Check if Session is started after login
//========================================================================================================
if (PHP_SESSION_ACTIVE && isset($_SESSION['user_logged']) && isset($_SESSION['user_ID']) && isset($_SESSION['timestamp']) && isset($_GET['id'])) {
	$user_logged = $_SESSION['user_logged'];
	$user_ID = $_SESSION['user_ID'];
	$timestamp = $_SESSION['timestamp'];
}

$ad_ID = $_GET['id'];
$purchase_date = $sellerID = $sellerEmail = $buyerEmail = "";

//DB query to select a particular ad
//========================================================================================================
$select_ad = "SELECT * FROM `user_ads` WHERE `ID` = '$ad_ID'";
$result_ad = $conn->query($select_ad);
$row_ad = $result_ad->fetch_assoc();
$sellerID = $row_ad['user_ID'];

//DB query to display details about seller
//========================================================================================================
$select_user = "SELECT * FROM `users` WHERE `ID` = '$sellerID'";
$result_user = $conn->query($select_user);
$row_user = $result_user->fetch_assoc();

//DB query to display ad category and category color
//========================================================================================================
$select_category = "SELECT * FROM `category`";
$result_category = $conn->query($select_category);

//Send email to notify buyer of succesful order
//========================================================================================================
function sendOrderConfirmation ($order_id, $buyerEmail, $purchase_date, $item_title, $item_description, $item_price) {
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
  $mail->addAddress($buyerEmail);
  $mail->isHTML(true);
  $mail->Subject = "Digital_Marketplace - Order confirmation";
  $mail->Body    = "<div>Thank you for your order!<br> Here is the summary: </div><br>";
  $mail->Body   .= "<table border='1' style='border-collapse:collapse'>\n";
  $mail->Body   .= "<tr>\n";
  $mail->Body   .= "<th><strong>ID</strong></th>\n";
  $mail->Body   .= "<th><strong>Item Title</strong></th>\n";
  $mail->Body   .= "<th><strong>Description</strong></th>\n";
  $mail->Body   .= "<th><strong>Price</strong></th>\n";
  $mail->Body   .= "<th><strong>Date of purchase</strong></th>\n";
  $mail->Body   .= "</tr>\n";
  $mail->Body   .= "<tr>";
  $mail->Body   .= "<td>$order_id</td>\n";
  $mail->Body   .= "<td>$item_title</td>\n";
  $mail->Body   .= "<td>$item_description</td>\n";
  $mail->Body   .= "<td>$item_price</td>\n";
  $mail->Body   .= "<td>$purchase_date</td>\n";
  $mail->Body   .= "</tr>";
  $mail->Body   .= "</table>\n";
  $mail->AltBody = "You have successfully activated your Digital Marketplace account!";
  $mail->send();
}

//Send email to seller that item has been sold
//========================================================================================================
function sendOrderNotification ($order_id, $sellerEmail, $purchase_date, $item_title, $buyerName) {
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
  $mail->addAddress($sellerEmail);
  $mail->isHTML(true);
  $mail->Subject = "Digital_Marketplace - Your item was sold!";
  $mail->Body    = "<div>Your item #$order_id <strong>$item_title</strong> was purchased by <strong>$buyerName</strong> on the $purchase_date</div>";
  $mail->AltBody = "Your item was sold on the Digital Marketplace platform!";
  $mail->send();
}


if (isset($_POST['order_button'])) {
  $purchase_date = strftime("%Y-%m-%d %T", time());
	$ad_ID = $_POST['ad_ID'];
	$buyer_ID = $_POST['buyer_ID'];
	$seller_ID = $_POST['seller_ID'];
	$item_title = $_POST['ad_title'];
	$item_description = $_POST['ad_description'];
	$item_price = $_POST['ad_price'];

  //DB query to create order
  //========================================================================================================
  $insert_query = "INSERT INTO `orders`(`ID`, `user_ID`, `ad_ID`, `purchase_date`) VALUES ('', '$buyer_ID', '$ad_ID', '$purchase_date')";
  $insert_result = mysqli_query($conn, $insert_query);

  //DB query to update item status
   //========================================================================================================
  $update_query = "UPDATE `user_ads` SET `status`= 'sold' WHERE `ID` = '$ad_ID'";
  $update_result = mysqli_query($conn, $update_query);

	//DB query to display details about buyer
	//========================================================================================================
	$select_buyer = "SELECT * FROM `users` WHERE `ID` = '$buyer_ID'";
	$result_buyer = $conn->query($select_buyer);
	$row_buyer = $result_buyer->fetch_assoc();
	$buyerName = $row_buyer['name']." ".$row_buyer['surname'];
	$buyerEmail = $row_buyer['email'];

	//DB query to display details about seller
	//========================================================================================================
	$select_seller = "SELECT * FROM `users` WHERE `ID` = '$seller_ID'";
	$result_seller = $conn->query($select_seller);
	$row_seller = $result_seller->fetch_assoc();
	$sellerEmail = $row_seller['email'];

  //DB query to get new order ID
  //========================================================================================================
  $order_query = "SELECT * FROM `orders` WHERE `ID` = LAST_INSERT_ID()";
  $orderResult = $conn->query($order_query);
  $orderRow = $orderResult->fetch_assoc();
  $order_id = $orderRow['ID'];

  //Call functions to send email confirmations to buyer and seller on succesful purchase
  //========================================================================================================
  sendOrderConfirmation($order_id, $buyerEmail, $purchase_date, $item_title, $item_description, $item_price);
  sendOrderNotification($order_id, $sellerEmail, $purchase_date, $item_title, $buyerName);

  header("Location: ../user/list_orders.php?action=view&id=$buyer_ID&page=1");
}

?><style>
  .profile-image {
    max-width: 100px;
    max-height: 100px;
    border-radius: 100%;
  }

  #product-image {
    max-width: 400px;
    max-height: 250px;
  }
</style>
<?php include('../../templates/header.php'); ?>
<body class="overflow-auto flex flex-col items-center bg-gray-200">
<div class="w-full h-full flex flex-grow justify-center items-center bg-gray-200">
  <div class="container">
        <div class="w-full flex justify-center">
          <div class="w-full lg:w-2/3 inline-block p-2 min-h-full">
            <div class="flex justify-center items-center px-8 py-4 bg-white min-h-full">
              <img id="product-image" src="../../<?php echo $row_ad['image'] ?>" alt="">
            </div>
          </div>
          <div class="w-full lg:w-1/3 inline-block p-2 min-h-full">

            <div class="flex flex-col justify-center bg-white px-8 py-4 min-h-full">
              <span class="text-gray-400 text-xs font-bold pb-4 pl-1">Seller</span>
              <?php if (!empty($row_user['image'])) { ?>
                <img class="profile-image" src="../../<?php echo $row_user['image'] ?>" alt="">
              <?php } else { ?>
                <div class="flex justify-center items-center p-6 bg-teal-600 w-24 h-24 rounded-full profile-image">
                  <span class="font-bold text-white text-2xl"><?php echo strtoupper(substr($row_user['name'], 0, 1).substr($row_user['surname'], 0, 1))?></span>
                </div>
              <?php } ?>
                <h2 class="font-bold text-2xl leading-none py-4 pl-1"><?php echo ucfirst($row_user['name'])." ". ucfirst($row_user['surname']) ?></h2>
                <?php if (!empty($row_user['address'])) { ?>
                  <p class="font-bold py-2 pl-1"><span class="text-gray-400 text-xs">Address: <br></span><?php echo $row_user['address']; ?>
                    <?php if (!empty($row_user['postcode'])) { echo ", ".$row_user['postcode']; } ?>
                  </p>
                <?php } ?>

                <?php if (($user_logged == true) && ($user_ID != $row_ad['user_ID'])) { ?>
                  <form class="w-full h-full flex flex-col" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" class="" method="POST" novalidate>
                    <div class="mt-6 w-full">
												<input type="hidden" name="ad_ID" value="<?php echo $row_ad['ID']; ?>">
												<input type="hidden" name="buyer_ID" value="<?php echo $user_ID; ?>">
												<input type="hidden" name="seller_ID" value="<?php echo $row_ad['user_ID']; ?>">
												<input type="hidden" name="ad_title" value="<?php echo $row_ad['title']; ?>">
												<input type="hidden" name="ad_description" value="<?php echo $row_ad['description']; ?>">
												<input type="hidden" name="ad_price" value="<?php echo $row_ad['price']; ?>">
                        <input class="px-3 py-2 bg-teal-600 text-white font-bold  w-full" type="submit" name="order_button" value="Buy now">
                    </div>
                  </form>
								<?php } else if (($user_logged == true) && ($user_ID === $row_ad['user_ID'])) { ?>
									<p class="font-bold py-2 pl-1"><span class="text-gray-400 text-xs">You published this ad on: <br></span><?php echo $row_ad['publish_date']; ?></p>
                <?php } else { ?>
                  <div class="mt-6 w-full">
                      <a class="px-3 py-2 bg-white text-teal-600 font-bold border-2 border-teal-600 text-center block w-full" id="register_button" href="../service/login.php">Sign in now!</a>
                  </div>
                <?php } ?>
            </div>

          </div>
        </div>
        <div class="block w-full">
          <div class="w-full lg:w-2/3 inline-block p-2">
            <div class="px-8 py-4 bg-white ">
              <div class="flex justify-between w-full">
                <div class="">
                  <span class="text-gray-400 text-xs font-bold">#<?php echo $row_ad['ID'] ?></span>
                  <h2 class="font-bold text-4xl leading-none pb-4"><?php echo $row_ad['title'] ?></h2>
                  <span class="font-bold">Condition: <span class="text-teal-600"><?php echo $row_ad['condition'] ?></span></span>
                </div>
                <div class="">
                  <?php while ($row_category = $result_category->fetch_assoc()) {  ?>
                    <?php if ($row_ad['category_ID'] === $row_category['ID']) {?>
                      <span class="px-2 pb-1 rounded-full <?php echo "bg-".$row_category['color']."-600"; ?> text-white text-sm"><?php echo strtolower($row_category['category_name']); ?></span>
                    <?php } ?>
                  <?php } ?>
                </div>
              </div>
              <div class="w-full block py-6">
                <p><?php echo $row_ad['description'] ?></p>
              </div>
              <div class="flex justify-between w-full">
                <div class="">
                  <h2 class="font-bold text-2xl leading-none pb-4"><?php echo $row_ad['price'] ?> CHF</h2>
                </div>
                <div class="">
                    <span class="text-gray-400 text-xs font-bold">published on:</span><br>
                    <span class="text-gray-400 text-xs font-bold"><?php echo $row_ad['publish_date'] ?></span>
                </div>
              </div>
            </div>
          </div>
        </div>
  </div>
</div>
</body><?php include('../../templates/footer.php'); ?>
