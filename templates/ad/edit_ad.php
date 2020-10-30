<?php
session_start();
include('../../includes/db-connect.inc.php');

//Initial status user not logged in
//========================================================================================================
$user_logged = $user_ID = $item_ID = $ad_ID = "";
$timestamp = time();

//Check if Session is started after login
//========================================================================================================
if (PHP_SESSION_ACTIVE && isset($_SESSION['user_logged']) && isset($_SESSION['user_ID']) && isset($_SESSION['timestamp'])) {
	$user_logged = $_SESSION['user_logged'];
	$user_ID = $_SESSION['user_ID'];
	$timestamp = $_SESSION['timestamp'];
  $item_ID = $_GET['id'];
} else {
  header("Location: ../../index.php");
}

//DB query to display ad details
//========================================================================================================
$ad_query = "SELECT * FROM `user_ads` WHERE `ID` = '$item_ID'";
$ad_result = $conn->query($ad_query);
$row_ad = $ad_result->fetch_assoc();

//DB query display category names
//========================================================================================================
$category_query = "SELECT * FROM `category`";
$category_result = $conn->query($category_query);

$title = $category = $publish_date = $condition = $description = $price = $status = $image_url = $errormessage = "";

  if (isset($_POST['submit'])) {
    if (empty($_POST['title']) || empty($_POST['category']) || empty($_POST['item_condition']) || empty($_POST['price']) || empty($_POST['ad_status'])) {
      //Validate if all required fields are filled out
      //========================================================================================================
      $errormessage = "Please fill out all required fields.";
    } else if (!is_numeric($_POST['price'])) {
      //Validate price format
      //========================================================================================================
      $errormessage = "Please enter a valid price value.";
    } else {
      //Take user submitted data and update ad item in DB by matching it according to ad ID
      //========================================================================================================
      $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
      $category = $_POST['category'];
      $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
      $price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_INT);
      $condition = $_POST['item_condition'];
      $status = $_POST['ad_status'];
      $ad_ID = $_POST['ad_ID'];

      if ($status === 'draft') {
        $publish_date = '0000-00-00 00:00:00';
      } else {
        $publish_date = strftime("%Y-%m-%d %T", time());
      }

      $update_query = "UPDATE `user_ads` SET `title`= '$title',`category_ID`= '$category',`user_ID`= '$user_ID',`publish_date`= '$publish_date', ";
      $update_query .="`condition`= '$condition',`description`= '$description',`price`= '$price',`status`= '$status' WHERE `ID` = '$ad_ID'";
      $res = mysqli_query($conn, $update_query);

      header("Location: edit_ad.php?action=edit&id=".$ad_ID);
    }
  }

  $maxFileSize = 5*1024*1024; // 5 MB in Bytes
  $allowed_fileformat = array('image/jpeg', 'image/png');

  if (isset($_POST['update_image'])) {
      //Check if uploaded image is correct size and format
      //========================================================================================================
      $mimeType = mime_content_type($_FILES['img']['tmp_name']);
      $typeOK = in_array($mimeType, $allowed_fileformat);
      $sizeOK = $_FILES['img']['size']<=$maxFileSize;
      $tmppfad = $_FILES['img']['tmp_name'];
      $folder = $_SERVER['DOCUMENT_ROOT'].'/xampp/custom_cms/images/product_images/'.time().'_'.$_FILES['img']['name'];

      if($typeOK==true && $sizeOK==true){
        $uploadSuccess = move_uploaded_file($tmppfad, $folder);
        $_SESSION['img'] = $image_url = "images/product_images/".time().'_'.$_FILES['img']['name'];
        $ad_ID = $_POST['ad_ID'];

        //Update item picture by matching the ad row in DB according to ad ID
        //========================================================================================================
        $update_query = "UPDATE `user_ads` SET `image`= '$image_url' WHERE `ID` = '$ad_ID'";
        $res = mysqli_query($conn, $update_query);

        header("Location: edit_ad.php?action=edit&id=".$ad_ID);
      } else {
        //Display error if image size or type not allowed
        //========================================================================================================
        $errorImage = "Please upload another image. Allowed formats are jpeg/png, size max 5MB.";
      }
  }

?><style>
  .item-image {
    max-width: 192px;
    max-height: 192px;
  }
</style><?php include('../../templates/header.php'); ?>
<body class="overflow-auto flex flex-col items-center bg-gray-200">
	<div class="w-full h-full flex flex-grow flex-col justify-center items-center bg-gray-200">
		<form class="p-6 bg-white lg:w-2/3 w-full"  action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" class="" method="POST" enctype="multipart/form-data" novalidate>
      <div class="w-full block lg:flex">
      <div class="w-full lg:w-1/2">
        <div class="">
          <?php if (!empty($row_ad['image'])) { ?>
            <img class="item-image" src="../../<?php echo $row_ad['image'] ?>" alt="">
          <?php } else { ?>
            <img class="item-image" src="../../images/service/placeholder.png" alt="">
          <?php } ?>
        </div>
        <form class="w-full h-full flex flex-col" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" class="" method="POST" enctype="multipart/form-data" novalidate>
          <label for="img" class="text-gray-400 text-sm block my-2 my-8"><span class="text-teal-600 border-2 border-teal-600 bg-white font-bold px-3 pt-2 pb-3">Update image</span></label>
          <input type="file" id="img" name="img" class="hidden" onchange="document.getElementById('update_image').click();">
          <input class="px-3 py-2 bg-teal-500 text-white font-bold hidden" type="submit" id="update_image" name="update_image">
          <input type="hidden" name="ad_ID" value="<?php echo $row_ad['ID']; ?>">
        </form>
      </div>
      <div class="w-full lg:w-1/2 inline-block">
			<div class="mb-4">
				<span class="font-bold">Edit ad:</span>
			</div>
      <div class="flex text-xs mt-4">
        <span class="text-red-600 font-bold"><?php	if(!empty($errormessage)){echo $errormessage;}?></span>
      </div>

			<div class="w-full py-2">
				<label class="text-gray-400 text-sm" for="title">Title <span class="text-red-600">*</span></label>
				<input class="px-2 bg-gray-200 py-1 w-full" id="title" name="title" value="<?php echo $row_ad['title'] ?>" type="text" required>
			</div>
			<div class="py-2 w-full">
					<div class="mr-2 text-gray-400 text-sm">Category <span class="text-red-600">*</span></div>
					<div class="value w-full">
							<div class="">
									<div class="">
											<select class="bg-gray-200 px-4 py-1 w-full" name="category" required>
                        <?php while($row_category = $category_result->fetch_assoc()) { ?>
													<option value='<?php echo $row_category["ID"] ?>' <?php if($row_ad['category_ID']==$row_category["ID"]) echo 'selected'; ?>><?php echo $row_category["category_name"] ?></option>

                          <?php } ?>
											</select>
											<div class="select-dropdown"></div>
									</div>
							</div>
					</div>
			</div>
			<div class="py-2">
				<label for="item_condition" class="text-gray-400 text-sm block">Condition <span class="text-red-600">*</span></label>
				<input class="bg-gray-200 border-0" type="radio" id="new" name="item_condition" value="new" <?php if($row_ad['condition']==='new') echo 'checked'; ?> required>
			  <label class="mr-4" for="new">New</label>
			  <input type="radio" id="used" name="item_condition" value="used" <?php if($row_ad['condition']==='used') echo 'checked'; ?>>
			  <label for="used">Used</label>
			</div>
			<div class="w-full my-4">
					<div class="text-gray-400 text-sm">Description</div>
					<textarea class="w-full bg-gray-200 px-2 py-1" name="description" rows="4" cols="50"><?php echo $row_ad['description'] ?></textarea>
			</div>
			<div class="py-2">
				<label for="price" class="text-gray-400 text-sm">Price <span class="text-red-600">*</span></label>
				<input class="bg-gray-200 py-1 px-2" id="price" name="price" value="<?php echo $row_ad['price'] ?>" type="text" required><span> CHF</span>
			</div>
			<div class="py-2">
        <label for="ad_status" class="block text-gray-400 text-sm">Status <span class="text-red-600">*</span></label>
        <input type="radio" id="draft" name="ad_status" value="draft" <?php if($row_ad['status']=='draft') echo 'checked'; ?> required>
        <label class="mr-4" for="published">Draft</label>
        <input type="radio" id="published" name="ad_status" value="published" <?php if($row_ad['status']=='published') echo 'checked'; ?>>
        <label for="used">Publish</label>
			</div>
			<div class="pt-6 pb-4 flex">
        <input type="hidden" name="ad_ID" value="<?php echo $row_ad['ID']; ?>">
        <input class="px-3 py-2 bg-teal-600 text-white font-bold w-1/2" type="submit" name="submit" value="Edit">
			</div>
      </div>
      </div>
		</form>
	</div>
</body><?php include('../../templates/footer.php'); ?>
