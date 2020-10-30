<?php
session_start();
include('includes/db-connect.inc.php');

//Default values of filters
//========================================================================================================
$sort_filter = "latest";
$category = $search_query = "";
$totalpages = null;

//Pagination - ad and page count initial value setup
//========================================================================================================
$page = 1;
$perPage = 4;
if (isset($_GET['page']) && $_GET['page']>1) {
	$page = (int)$_GET['page'];
} else {
	$page = $_GET['page'] = 1;
}
$start = $perPage * ($page-1);

//Sort and filter rules to list results according to category and price or search string match in item title + pagination limitation rules
//========================================================================================================
if (isset($_POST['sort_filter_submit']) && ($_POST['sort_filter'] == 'price_ascending')) {
  $sort_filter = $_POST['sort_filter'];
  $ads_query = "SELECT * FROM `user_ads` WHERE `status` = 'published' ORDER BY `price` LIMIT {$start}, {$perPage}";
  $ads_results = $conn->query($ads_query);
	$all_query = "SELECT * FROM `user_ads` WHERE `status` = 'published' ORDER BY `price`";
	$all_results = $conn->query($all_query);
  $row_count = $all_results->num_rows;
  $totalpages = ceil($row_count / $perPage);
} else if (isset($_POST['sort_filter_submit']) && ($_POST['sort_filter'] == 'price_descending')) {
  $sort_filter = $_POST['sort_filter'];
  $ads_query = "SELECT * FROM `user_ads` WHERE `status` = 'published' ORDER BY `price` DESC LIMIT {$start}, {$perPage}";
  $ads_results = $conn->query($ads_query);
	$all_query = "SELECT * FROM `user_ads` WHERE `status` = 'published' ORDER BY `price` DESC";
	$all_results = $conn->query($all_query);
	$row_count = $all_results->num_rows;
	$totalpages = ceil($row_count / $perPage);
} else if (isset($_POST['category_filter_submit']) && !empty($_POST['category_filter'])) {
  $category = $_POST['category_filter'];
  $ads_query = "SELECT * FROM `user_ads` WHERE `status` = 'published' AND `category_ID` = '$category' LIMIT {$start}, {$perPage}";
  $ads_results = $conn->query($ads_query);
	$all_query = "SELECT * FROM `user_ads` WHERE `status` = 'published' AND `category_ID` = '$category'";
	$all_results = $conn->query($all_query);
  $row_count = $all_results->num_rows;
  $totalpages = ceil($row_count / $perPage);
} else if (isset($_POST['search_button_submit'])) {
  $search_query = $_POST['search_field'];
  $ads_query = "SELECT * FROM `user_ads` WHERE `status` = 'published' AND `title` LIKE CONCAT('%', '$search_query', '%') LIMIT {$start}, {$perPage}";
  $ads_results = $conn->query($ads_query);
	$all_query = "SELECT * FROM `user_ads` WHERE `status` = 'published' AND `title` LIKE CONCAT('%', '$search_query', '%')";
	$all_results = $conn->query($all_query);
  $row_count = $all_results->num_rows;
  $totalpages = ceil($row_count / $perPage);
} else {
  $ads_query = "SELECT * FROM `user_ads` WHERE `status` = 'published' ORDER BY `publish_date` DESC LIMIT {$start}, {$perPage}";
  $ads_results = $conn->query($ads_query);
	$all_query = "SELECT * FROM `user_ads` WHERE `status` = 'published' ORDER BY `publish_date` DESC";
	$all_results = $conn->query($all_query);
  $row_count = $all_results->num_rows;
  $totalpages = ceil($row_count / $perPage);
}

//Database SQL query for the category filter
//========================================================================================================
$select_query_category = "SELECT * FROM `category`";
$res_category = mysqli_query($conn, $select_query_category);
$all_categories = mysqli_fetch_all($res_category, MYSQLI_ASSOC);

//Separate Database SQL query for displaying name for each category label
//========================================================================================================
$select_query_category_2 = "SELECT * FROM `category`";
$res_category_2 = mysqli_query($conn, $select_query_category_2);
$all_categories_2 = mysqli_fetch_all($res_category_2, MYSQLI_ASSOC);

?>
<style>
  img.product-image {
    max-width: 180px;
    height: 120px;
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
<?php include('templates/header.php'); ?>
<body class="overflow-auto flex flex-grow h-full w-full flex-col items-center bg-gray-200">
  <div class="w-full flex justify-center bg-white py-3">
    <div class="container">
      <div class="">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" class="" method="POST" novalidate>
          <div class=" flex pt-4">
            <input class="w-full px-3 py-2 bg-gray-200" name="search_field" type="text" placeholder="Search">
            <button class="px-6 py-2 bg-teal-600 text-white font-bold" type="submit" name="search_button_submit"><svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="search" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-search fa-w-16 fa-3x w-6 h-6"><path fill="currentColor" d="M505 442.7L405.3 343c-4.5-4.5-10.6-7-17-7H372c27.6-35.3 44-79.7 44-128C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c48.3 0 92.7-16.4 128-44v16.3c0 6.4 2.5 12.5 7 17l99.7 99.7c9.4 9.4 24.6 9.4 33.9 0l28.3-28.3c9.4-9.4 9.4-24.6.1-34zM208 336c-70.7 0-128-57.2-128-128 0-70.7 57.2-128 128-128 70.7 0 128 57.2 128 128 0 70.7-57.2 128-128 128z" class=""></path></svg></button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="w-full bg-gray-200 flex flex-grow justify-center pt-3 pb-6">
    <div class="container h-full flex flex-col justify-between">
      <div class="flex">
        <div class="py-2 pr-4 flex flex-col justify-center w-full md:w-1/2 lg:w-1/4">
            <div class="mr-2 text-gray-400 text-xs pb-2">Sort</div>
            <div class="value">
                <div class="">
                    <div class="">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" class="" method="POST" novalidate>
                        <select class="bg-white px-4 py-1 w-full" name="sort_filter" onchange="document.getElementById('sort_filter_submit').click();">
                            <option value='latest' <?php if($sort_filter=='latest') echo 'selected'; ?>>Latest</option>
                            <option value='price_ascending' <?php if($sort_filter=='price_ascending') echo 'selected'; ?>>Price ascending</option>
                            <option value='price_descending' <?php if($sort_filter=='price_descending') echo 'selected'; ?>>Price descending</option>
                        </select>
                        <input class="hidden" type="submit" id="sort_filter_submit" name="sort_filter_submit">
                        </form>
                        <div class="select-dropdown"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="py-2 pr-4 flex flex-col justify-center w-full md:w-1/2 lg:w-1/4">
            <div class="mr-2 text-gray-400 text-xs pb-2">Filter category</div>
            <div class="value">
                <div class="">
                    <div class="">
                      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" class="" method="POST" novalidate>
                      <select class="bg-white px-4 py-1 w-full" name="category_filter" onchange="document.getElementById('category_filter_submit').click();">
                          <option value='' <?php if(empty($category)) echo 'selected'; ?>>All categories</option>
                          <?php foreach( $all_categories as $single_category ) { ?>
                          <option value='<?php echo $single_category['ID'] ?>' <?php if($category == $single_category["ID"]) echo 'selected'; ?>><?php echo $single_category["category_name"] ?></option>
                          <?php } ?>
                      </select>
                      <input class="hidden" type="submit" id="category_filter_submit" name="category_filter_submit">
                      </form>
                      <div class="select-dropdown"></div>
                    </div>
                </div>
            </div>
        </div>

      </div>
      <div class="">
        <?php  while($row_ad = $ads_results->fetch_assoc()) {  ?>
          <a class="py-2 pr-4 float-left inline-flex flex-col justify-center w-full lg:w-1/2 xl:w-1/4" href="templates/ad/view_ad.php?action=view&id=<?php echo $row_ad['ID']; ?>">

            <div class="bg-white p-6 mb-4">
              <div class="mb-4">

                <?php foreach( $all_categories_2 as $single_category_2 ) { ?>
                  <?php if ($row_ad['category_ID'] === $single_category_2['ID']) { ?>
                    <span class="px-2 pb-1 rounded-full <?php echo "bg-".$single_category_2['color']."-600"; ?> text-white text-sm"><?php echo $single_category_2['category_name'] ?></span>
                  <?php } ?>
                <?php } ?>


              </div>
              <div class="flex justify-center mb-6">
                <img class="product-image" src="<?php echo $row_ad['image'] ?>" alt="">
              </div>
              <div class="">
                <h2 class="font-bold text-2xl leading-none"><?php echo substr(ucfirst($row_ad['title']), 0, 15) ?>...</h2>
                <p class="text-sm py-4"><?php echo substr($row_ad['description'], 0, 50)?>...</p>
              </div>
              <div class="flex justify-between items-baseline mt-4">
                <span class="font-bold text-xl leading-none"><?php echo $row_ad['price'] ?> CHF</span>
                <span class="text-gray-400 text-xs font-bold"><?php echo $row_ad['publish_date'] ?></span>
              </div>
            </div>

        </a>
        <?php } ?>
      </div>
      <div class="pagination">
        <ul class="flex items-center justify-center">
					<?php for ($i = 1; $i <= $totalpages; $i++) {
							echo "<li><a href='index.php?action=view&page=$i' class=";
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
<?php include('templates/footer.php'); ?>
