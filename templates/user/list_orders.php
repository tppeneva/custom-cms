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
$perPage = 3;
$totalpages = null;
if (isset($_GET['page']) && $_GET['page']>1) {
	$page = (int)$_GET['page'];
}
$start = $perPage * ($page-1);

//DB query to display list of orders + pagination display limitation
//========================================================================================================
$order_query = "SELECT * FROM `orders` WHERE `user_ID` = '$user_ID' ORDER BY `ID` DESC LIMIT {$start}, {$perPage}";
$order_result = $conn->query($order_query);
$all_query = "SELECT * FROM `orders` WHERE `user_ID` = '$user_ID' ORDER BY `ID` DESC LIMIT 10";
$all_results = $conn->query($all_query);
$row_count = $all_results->num_rows;
$totalpages = ceil($row_count / $perPage);

//DB query to select a particular ad
//========================================================================================================
$ads_query = "SELECT * FROM `user_ads`";
$ad_result = mysqli_query($conn, $ads_query);
$all_ads = mysqli_fetch_all($ad_result, MYSQLI_ASSOC);

//DB query display category name and color
//========================================================================================================
$category_query = "SELECT * FROM `category`";
$category_result = mysqli_query($conn, $category_query);
$all_categories = mysqli_fetch_all($category_result, MYSQLI_ASSOC);
?>
<style>
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
</style>
<?php include('../../templates/header.php'); ?>
<body class="overflow-auto flex flex-grow h-full w-full flex-col items-center bg-gray-200">
  <div class="w-full h-full flex-col bg-gray-200 flex items-center justify-center">
    <div class="container h-full flex flex-col justify-between">
      <h1 class='font-bold text-center text-2xl mt-6'>Last 10 orders</h1>
			<div class="flex-grow py-3">
				<?php if ($order_result->num_rows <= 0) {?>
					<div class='w-full h-full flex justify-center'>
						<h3>Currently you have no orders in record.</h3>
					</div>
					<?php } else { ?>

						  <?php  while($order_row = $order_result->fetch_assoc()) {  ?>
								<?php foreach( $all_ads as $ad_row ) { ?>
									<?php if ($order_row['ad_ID'] === $ad_row['ID']) { ?>


										<div class="bg-white px-8 py-4 my-4 flex justify-between items-center">
											<div class="flex justify-center w-40">
												<img src="../../<?php echo $ad_row['image'] ?>" alt="">
											</div>
											<div class="w-40">
												<span class="text-gray-400 text-xs font-bold">order</span>
												<h2 class="font-bold text-3xl leading-none">#<?php echo $order_row['ID'] ?></h2>
											</div>
											<div class="w-40">
												<h3 class="font-bold text-1xl leading-none"><?php echo $ad_row['title']; ?></h3>
											</div>
											<div class="">
												<?php while ($category_row = $category_result->fetch_assoc()) {  ?>
													<?php if ($category_row['ID'] === $ad_row['category_ID']) {?>

														<span class="px-2 pb-1 rounded-full <?php echo "bg-".$category_row['color']."-600"; ?> text-white text-sm"><?php echo strtolower($category_row['category_name']); ?></span>

													<?php } ?>
												<?php } ?>
											</div>
											<div class="w-32">
												<span class="text-gray-400 text-xs font-bold">date of purchase:</span><br>
												<span class="text-gray-400 text-xs font-bold"><?php echo $order_row['purchase_date'] ?></span>
											</div>
											<div class="">
												<span class="text-gray-400 text-xs font-bold">price:</span><br>
												<span class="font-bold text-1xl leading-none"><?php echo $ad_row['price'] ?> CHF</span>
											</div>
										</div>

									<?php } ?>
								<?php } ?>
							<?php } ?>

				 <?php } ?>
			</div>
		<div class="pagination mb-6">
			<ul class="flex items-center justify-center">
				<?php for ($i = 1; $i <= $totalpages; $i++) {
						echo "<li><a href='list_orders.php?action=view&id=$user_ID&page=$i' class=";
						if ($_GET['page'] == $i) {
							echo 'selected';
						};
						echo ">$i</a></li>";
					} ?>
			</ul>
		</div>
    </div>
  </div>
</body>
<?php include('../../templates/footer.php'); ?>
