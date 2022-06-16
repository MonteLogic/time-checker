<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://github.com/dchavours/
 * @since      1.0.0
 *
 * @package    Time_Checker
 * @subpackage Time_Checker/admin/partials
 *
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://wp2mag.blogspot.com
 * @since      1.0.0
 *
 * @package    Course_Manager
 * @subpackage Course_Manager/admin/partials
 */
global $wpdb;
// I have to include this file below into different file because there should be no include statement here.
$dir = WP_PLUGIN_DIR . '/time-checker';







// There needs to be logic here that executes and shows the possible times for the dropdown.
// For enterting beginning/ending hours. 
// 
$sql_queue = new SQL_Init_Request();


// I should be able to call get_sql_vars_two() and have the file automatically know 
// $all_booking_starts_sql_command exists. 

$array_unique_time_starts_no_repeats = SQL_Init_Request::get_sql_vars_two()[0];
$array_unique_time_ends_no_repeats = SQL_Init_Request::get_sql_vars_two()[1];







?>




<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<h1>&nbsp;</h1>


<script src = "https://code.jquery.com/jquery-1.10.2.js"></script>
<script src = "https://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>

<!-- Javascript -->
<script>
   $(function() {
      $( "#datepicker-13" ).datepicker();
      $( "#datepicker-13" ).datepicker("show");
   });
</script>

<form action="<?php $dir . 'admin/class-time-checker-admin-sql.php'  ?>" method="post">
	<p>Enter Date:</p> 
	<input name="date" type = "text" id = "datepicker-13">
	<h1>&nbsp;</h1>
	<p>Enter Course:</p> 
	<select name="course_name" id="courseNameId">
	<?php foreach ( WC_Bookings_Admin::get_booking_products() as $product ) : ?>
		<option value="<?php echo esc_attr( $product->get_id() ); ?>">
		<?php echo esc_html( sprintf( '%s (#%s)', $product->get_name(), $product->get_id() ) ); ?>
		</option>
  	<?php endforeach; ?>
	</select>
	<h1>&nbsp;</h1>



<!--it would be dope if I could record a voice message on g-drive have it be here and then I click on it and it opens a window where it reads out what I said.   -->

<p>Enter Begining Hours:</p> 
	<select name="begin_hours" id="courseNameId">
	<?php foreach (SQL_Init_Request::match_pm_or_am($array_unique_time_starts_no_repeats) as $hour_end ) : ?>
	<option value="<?php echo $hour_end; ?>"><?php echo $hour_end; ?></option>
  	<?php endforeach; ?>
  	</select>

<p>Enter Ending Hours:</p> 
	<select name="end_hours" id="courseNameId">
	<?php foreach (SQL_Init_Request::match_pm_or_am($array_unique_time_ends_no_repeats ) as $hour_end ) : ?>
	<option value="<?php echo $hour_end; ?>"><?php echo $hour_end; ?></option>
	<?php endforeach; ?>
	</select>
	<h1>&nbsp;</h1>



<input type ="submit">


<h1>&nbsp;</h1>

</form>







<meta name="viewport" content="width=device-width, initial-scale=1.0">
<br>
<br>
<br>
<!-- Modal Template -->
<div id="myModal1" class="modal">
  <!-- Modal content -->
  <div class="modal-content">
    <div class="modal-header">
      <span class="close">×</span>
      <h2 id="modal-number">Modal One</h2>
    </div>
	<div class="modal-body" id="id-modal-body" >
		<table id="order-note-table">
			<thead>
				<tr>
     				<th>note_id</th><th>note_date</th><th>note_author</th><th>note_content</th>
				</tr>
			</thead>

			<tbody id="modal-table-body">
			<!-- Rows will be inserted here. -->
			</tbody>
		</table>

      <p id="paragraph-id"></p>
	</div>
    <div class="modal-footer">
      <h3>Modal Footer</h3>
    </div>
  </div>

</div>
<br>
<br>
<br>
<br>
<div id="example-id" href="example-href" class="example-class">
</div>
<br>
<br>




<?php

if(
isset($_POST["date"]) && isset($_POST["course_name"])
&& isset($_POST["begin_hours"]) && isset($_POST["end_hours"])
){ 

  $int_date_entered = $_POST['date'];
  $begin_hours = $_POST["begin_hours"];
  $end_hours = $_POST["end_hours"];
  $date_entered = 
	  SQL_Post_Request::format_entered_date($begin_hours, $end_hours, $int_date_entered);


$findDateBooking = new  WC_Bookings_Calendar();


  $product_filter  = isset( $_REQUEST['filter_bookings_product'] ) ? absint( $_REQUEST['filter_bookings_product'] ) : '';
  
  $booking_filter = array();
  if ( $product_filter ) {
     array_push( $booking_filter, $product_filter );
  }

$product_id = $_POST['course_name'];
$array_booking_product_id_sql_cmd =
	SQL_Post_Request::find_metadata_sql($product_id);
$parent_post_array_return = 
	SQL_Post_Request::find_ids($array_booking_product_id_sql_cmd)[1];
$a1 = SQL_Post_Request::pair_parent_with_child(SQL_Post_Request::reduce_sql_array_by_one_dimension($array_booking_product_id_sql_cmd), $parent_post_array_return, $product_id);


// Begin a2 creation.
$a2 = 
	SQL_Post_Request::find_ids($array_booking_product_id_sql_cmd)[0];

$no_match_entries =
array_diff(
	 SQL_Post_Request::fill_a2_simple_array($a2),
       	 SQL_Post_Request::fill_a1_simple_array($a1));

$a2 =  SQL_Post_Request::prune_a2($no_match_entries, $a2);
$combined_array = SQL_Post_Request::arrays_to_combine($a1,$a2);
$combine_un_assoc = array_values($combined_array);
$filtered_date = SQL_Post_Request::date_enter_filter($date_entered, $combine_un_assoc);
$filtered_time =  SQL_Post_Request::time_enter_filter($filtered_date, $begin_hours, $end_hours);
$formatted_date = SQL_Post_Request::formatted_date($filtered_date, $begin_hours, $end_hours);

SQL_Post_Request::search_form_output($date_entered, $formatted_date, $combine_un_assoc);


SQL_Post_Request::display_html_table($filtered_time);
SQL_Post_Request::output_times_dates($filtered_date, $filtered_time);


}




?>




<script>

// Access the array elements
var published_php_array = 
    <?php echo json_encode($filtered_time); ?>;

var published_array = Object.values(published_php_array);
console.log(published_array);

// Get the button that opens the modal
var btn = document.querySelectorAll("button.modal-button");

// All page modals
var modals = document.querySelectorAll('.modal');

// Get the <span> element that closes the modal
var spans = document.getElementsByClassName("close");


// When the user clicks on <span> (x), close the modal
for (var i = 0; i < spans.length; i++) {
 spans[i].onclick = function() {
    for (var index in modals) {
      if (typeof modals[index].style !== 'undefined') modals[index].style.display = "none";    
    }
 }
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
     for (var index in modals) {
      if (typeof modals[index].style !== 'undefined') modals[index].style.display = "none";    
     }
    }
}



function place_html(ticker_value){
	console.log(ticker_value);
	var para = document.getElementById('paragraph-id');
	var div = document.createElement("table"); 
	document.getElementById("id-modal-body").appendChild(div);
	console.log("place_html() ticker = " + ticker_value);
	published_array_values = Object.values(published_array[ticker_value]);
	published_array_length = published_array.length;
	console.log(published_array_length); 
	// para.innerHTML = "This is a paragraph";
  var modal_number_id = document.getElementById("modal-number");
  modal_number_id.innerHTML = "Modal " + ticker_value; 

  order_note_body();
  modal = document.getElementById("myModal1");
  modal.style.display = "block";
}







function order_note_body(){
	order_notes_values = Object.values(published_array_values[9]);
	console.log(1070);
	console.log(order_notes_values);

	table = document.getElementById("order-note-table");
	table_body = document.getElementById("modal-table-body");
	table_body.innerHTML = "";

	for(var i = 0; i < order_notes_values.length; i++){
		var insert_row = table_body.insertRow(i);
		
		order_notes_values_iterate = Object.values(order_notes_values[i]);
			console.log(1250);

			note_id = order_notes_values_iterate[0];
			console.log(note_id);
			note_id_cell = insert_row.insertCell(0);
			note_id_cell.innerHTML = note_id;

			note_date = order_notes_values_iterate[1];
			console.log(note_date);
			note_date_cell = insert_row.insertCell(1);
			note_date_cell.innerHTML = note_date;

			note_author = order_notes_values_iterate[2];
			console.log(note_author);
			note_author_cell = insert_row.insertCell(2);
			note_author_cell.innerHTML = note_author;

			note_date = order_notes_values_iterate[3];
			console.log(note_date);
			note_date_cell = insert_row.insertCell(3);
			note_date_cell.innerHTML = note_date;
	}
}



</script>


