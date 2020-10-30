<?php
session_start();
include('../../includes/db-connect.inc.php');

//Initial status user not logged in
//========================================================================================================
$user_logged = $user_ID = $errormessage = $title = $category = $publish_date = $condition = $description = $price = $status = $image_url = "";
$timestamp = time();
$error = false;
$maxFileSize = 5*1024*1024; // 5 MB in Bytes
$allowed_fileformat = array('image/jpeg', 'image/png');

//Check if Session is started after login
//========================================================================================================
if (PHP_SESSION_ACTIVE && isset($_SESSION['user_logged']) && isset($_SESSION['user_ID']) && isset($_SESSION['timestamp'])) {
	$user_logged = $_SESSION['user_logged'];
	$user_ID = $_SESSION['user_ID'];
	$timestamp = $_SESSION['timestamp'];
}

//DB query to display category names
//========================================================================================================
$category_query = "SELECT * FROM `category`";
$category_result = $conn->query($category_query);

//Set and store user data entry in case of unsuccessful submit
//========================================================================================================
foreach($_POST as $key => $value) {
  $_SESSION['post'][$key] = $value;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
  foreach($_POST as $key => $value) {
    if(empty($_POST[$key])) {
      $title = $_SESSION['post']['title'];
      $category = $_SESSION['post']['category'];
      $description = $_SESSION['post']['description'];
      $price = $_SESSION['post']['price'];
    } else {
      $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
      $category = $_POST['category'];
      $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
      if (is_numeric($_POST['price'])) {
        $price = $_POST['price'];
      } else {
        $error = true;
        $errormessage = "Please enter a valid numeric value.";
      }
    }
  }
}

//Validation if form was sent successfully
//========================================================================================================
$formsent = isset($_POST['title']) && isset($_POST['category']) && isset($_POST['item_condition']) && isset($_POST['price']) && isset($_POST['ad_status']);
if (isset($_POST['submit'])) {
  if ($formsent == true) {
    $condition = $_POST['item_condition'];
    $status = $_POST['ad_status'];

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
    } else {
      //Display error if image size or type not allowed
      //========================================================================================================
      $errorImage = "Please upload another image. Allowed formats are jpeg/png, size max 5MB.";
    }
    //Set datetime if ad status published
    //========================================================================================================
    if ($status === 'draft') {
      $publish_date = '0000-00-00 00:00:00';
    } else {
      $publish_date = strftime("%Y-%m-%d %T", time());
    }
    //Insert new item ad in DB if entry successful
    //========================================================================================================
    $insert_query = "INSERT INTO `user_ads`(`ID`, `title`, `category_ID`, `user_ID`, `publish_date`, `condition`, `description`, `price`, `status`, `image`) ";
    $insert_query .=" VALUES ('', '$title', '$category', '$user_ID', '$publish_date', '$condition', '$description', '$price', '$status', '$image_url')";
    $res = mysqli_query($conn, $insert_query);

    header("Location: list_ads.php?action=view&id=".$user_ID."&page=1");
    exit;
  } else {
    //Display error not all required fields are filled out
    //========================================================================================================
    $error = true;
    $errormessage = "Please fill out all required fields.";
  }
}
?><?php include('../../templates/header.php'); ?>
<body class="overflow-auto flex flex-col items-center bg-gray-200">
	<div class="w-full h-full flex flex-grow flex-col justify-center items-center bg-gray-200">

		<form class="p-6 bg-white lg:w-2/3 w-full" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST" enctype="multipart/form-data" novalidate>
      <div class="w-full block lg:flex">
      <div class="md:w-1/2 w-full inline-block">
        <div class="w-full lg:w-1/2">
            <img class="item-image" src="../../images/service/placeholder.png" alt="">
        </div>
          <input type="file" id="img" name="img" class="py-4 text-teal-600 text-white font-bold">
      </div>
      <div class="w-full lg:w-1/2 inline-block">
			<div class="mb-4">
				<span class="font-bold">Create new ad:</span>
			</div>
			<div class="">
				<span class="text-red-600 text-xs"><?php	if($error){echo $errormessage;}?></span>
			</div>
			<div class="w-full py-2">
				<label class="text-gray-400 text-sm" for="title">Title <span class="text-red-600">*</span></label>
				<input class="px-2 bg-gray-200 py-1 w-full" id="title" name="title" value="<?php echo $title ?>" type="text" required>
			</div>
			<div class="w-full py-2">
					<div class="mr-2 text-gray-400 text-sm">Category <span class="text-red-600">*</span></div>
					<div class="value w-full">
							<div class="">
									<div class="">
											<select class="bg-gray-200 px-4 py-1 w-full" name="category" required>
                        <?php while($category_row = $category_result->fetch_assoc()) { ?>
													<option value='<?php echo $category_row["ID"] ?>' <?php if($category==$category_row["ID"]) echo 'selected'; ?>><?php echo $category_row["category_name"] ?></option>

                          <?php } ?>
											</select>
											<div class="select-dropdown"></div>
									</div>
							</div>
					</div>
			</div>
			<div class="py-2">
				<label for="item_condition" class="text-gray-400 text-sm block">Condition <span class="text-red-600">*</span></label>
				<input class="bg-gray-200 border-0" type="radio" id="new" name="item_condition" value="new" <?php if(isset($condition) && $condition=='new') echo 'checked'; ?> required>
			  <label class="mr-4" for="new">New</label>
			  <input type="radio" id="used" name="item_condition" value="used" <?php if(isset($condition) && $condition=='used') echo 'checked'; ?>>
			  <label for="used">Used</label>
			</div>
			<div class="w-full my-4">
					<div class="text-gray-400 text-sm">Description</div>
					<textarea class="w-full bg-gray-200 px-2 py-1" name="description" rows="4" cols="50"><?php echo $description ?></textarea>
			</div>
			<div class="py-2">
				<label for="price" class="text-gray-400 text-sm">Price <span class="text-red-600">*</span></label>
				<input class="bg-gray-200 py-1 px-2" id="price" name="price" value="<?php echo $price ?>" type="text" required><span> CHF</span>
			</div>
			<div class="py-2">
        <label for="ad_status" class="block text-gray-400 text-sm">Status <span class="text-red-600">*</span></label>
        <input type="radio" id="draft" name="ad_status" value="draft" <?php if(isset($status) && $status=='draft') echo 'checked'; ?> required>
        <label class="mr-4" for="published">Draft</label>
        <input type="radio" id="published" name="ad_status" value="published" <?php if(isset($status) && $status=='published') echo 'checked'; ?>>
        <label for="used">Publish</label>
			</div>
			<div class="pt-6 pb-4 flex">
          <input class="px-3 py-2 bg-teal-600 text-white font-bold w-1/2" type="submit" name="submit" value="Create">
			</div>
      </div>
      </div>
		</form>
	</div>
</body><?php include('../../templates/footer.php'); ?>
