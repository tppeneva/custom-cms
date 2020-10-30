<?php
session_start();
include('../../includes/db-connect.inc.php');

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

//Pagination - ad and page count initial value setup
//========================================================================================================
$page = 1;
$perPage = 4;
$totalpages = null;
if (isset($_GET['page']) && $_GET['page']>1) {
	$page = (int)$_GET['page'];
}
$start = $perPage * ($page-1);

//DB query to display list of ads + pagination display limitation
//========================================================================================================
$ads_query = "SELECT * FROM `user_ads` WHERE `user_ID` = $user_ID LIMIT {$start}, {$perPage}";
$ads_result = $conn->query($ads_query);
$all_query = "SELECT * FROM `user_ads` WHERE `user_ID` = $user_ID";
$all_results = $conn->query($all_query);
$row_count = $all_results->num_rows;
$totalpages = ceil($row_count / $perPage);

//DB query display category name and color
//========================================================================================================
$category_query = "SELECT * FROM `category`";
$category_result = mysqli_query($conn, $category_query);
$all_categories = mysqli_fetch_all($category_result, MYSQLI_ASSOC);

if (isset($_POST['delete_button'])) {
  //DB query to delete ads
  //========================================================================================================
  $delete_query = "DELETE FROM `user_ads` WHERE `ID`=".$_POST['delete_button'];
  $delete_result = $conn->query($delete_query);

  header("Location: list_ads.php?action=view&id=$user_ID&page=$page");
  exit;
}
?><style>
  img {
    max-width: 60px;
    max-height: 60px;
  }
	.pagination li a {
		background-color: #fff;
		padding: 5px 10px;
		font-weight: bold;
		margin: 0 5px;
		color: #319795;
	}
	.pagination li a.selected {
		background-color: #319795;
		color: #fff;
	}
</style><?php include('../../templates/header.php'); ?>
<body class="overflow-auto flex flex-grow h-full w-full flex-col items-center bg-gray-200">
  <div class="w-full h-full flex-col bg-gray-200 flex items-center justify-center">
    <div class="container h-full flex flex-col justify-between">
      <h1 class='font-bold text-center text-2xl mt-6'>My Ads</h1>
			<div class="flex-grow py-3">
      <button class="px-3 py-2 bg-teal-600 text-white font-bold inline-block mb-4" type="submit" onclick="window.location.href='create_ad.php'">Add new</button>
		    <?php if ($ads_result->num_rows <= 0) {?>
		      <div class='w-full h-full flex justify-center'>
		        <h3>Currently you have no ads.</h3>
		      </div>
		    <?php } else { ?>
		      <?php  while($row_ad = $ads_result->fetch_assoc()) {  ?>
		        <div class="bg-white px-8 py-4 my-4 flex justify-between items-center">
		          <div class="flex justify-center w-40">
		            <img src="../../<?php echo $row_ad['image'] ?>" alt="">
		          </div>
		          <div class="w-40">
		            <span class="text-gray-400 text-xs font-bold">#<?php echo $row_ad['ID'] ?></span>
		            <h2 class="font-bold text-1xl leading-none"><?php echo $row_ad['title'] ?></h2>
		          </div>
		          <div class="w-32">
		            <?php foreach( $all_categories as $single_category ) { ?>
		              <?php if ($row_ad['category_ID'] === $single_category['ID']) { ?>
		                <span class="px-2 pb-1 rounded-full <?php echo "bg-".$single_category['color']."-600"; ?> text-white text-sm"><?php echo $single_category['category_name'] ?></span>
		              <?php } ?>
		            <?php } ?>
		          </div>
		          <div class="w-32">
		          <?php if ($row_ad['publish_date'] === '0000-00-00 00:00:00') { ?>
								<span class="text-gray-400 text-xs font-bold">draft</span>
		          <?php } else { ?>
								<span class="text-gray-400 text-xs font-bold">published on:</span><br>
		            <span class="text-gray-400 text-xs font-bold"><?php echo $row_ad['publish_date'] ?></span>
		          <?php } ?>
		          </div>
		          <div class="">
		            <span class="text-gray-400 text-xs font-bold">price:</span><br>
		            <span class="font-bold text-1xl leading-none"><?php echo $row_ad['price'] ?> CHF</span>
		          </div>
		          <div class="">
		            <form class="" action="list_ads.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this ad?')">
		              <a class="px-3 py-2 text-teal-600 border-2 border-teal-600 font-bold inline-block" href="edit_ad.php?action=edit&id=<?php echo $row_ad['ID']; ?>" >Edit</a>
		              <button class="px-3 py-2 text-red-600 border-2 border-red-600 font-bold inline-block" type="submit" name="delete_button" value="<?php echo $row_ad['ID']; ?>">Delete</button>
		            </form>
		          </div>
		        </div>
		      <?php } ?>
		    <?php } ?>
		</div>
		<div class="pagination mb-6">
			<ul class="flex items-center justify-center">
				<?php for ($i = 1; $i <= $totalpages; $i++) {
						echo "<li><a href='list_ads.php?action=view&id=$user_ID&page=$i' class=";
						if ($_GET['page'] == $i) {
							echo 'selected';
						};
						echo ">$i</a></li>";
					} ?>
			</ul>
		</div>
    </div>
  </div>
</body><?php include('../../templates/footer.php'); ?>
