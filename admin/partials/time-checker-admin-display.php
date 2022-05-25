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
//echo "508";

//echo "509";

$no_match_entries =
array_diff(
	 SQL_Post_Request::fill_a2_simple_array($a2),
       	 SQL_Post_Request::fill_a1_simple_array($a1));

// $no_match_entries is appropriate

$a2 =  SQL_Post_Request::prune_a2($no_match_entries, $a2);


$combined_array = SQL_Post_Request::arrays_to_combine($a1,$a2);


$combine_un_assoc = array_values($combined_array);

$filtered_date = SQL_Post_Request::date_enter_filter($date_entered, $combine_un_assoc);

$filtered_time =  SQL_Post_Request::time_enter_filter($filtered_date, $begin_hours, $end_hours);

$formatted_date = SQL_Post_Request::formatted_date($filtered_date, $begin_hours, $end_hours);



SQL_Post_Request::search_form_output($date_entered, $formatted_date, $combine_un_assoc);

$booking_obj = new WC_Booking( 8884);


echo "<br>";
echo "<br>";





SQL_Post_Request::output_times_dates($filtered_date, $filtered_time);




if (count($filtered_time) > 0): ?>
<table>
  <thead>
    <tr>
      <th><?php echo implode('</th><th>', array_keys(current($filtered_time))); ?></th>
    </tr>
  </thead>
  <tbody>
<?php foreach ($filtered_time as $row): array_map('htmlentities', $row); ?>
    <tr>
      <td><?php echo implode('</td><td>', $row); ?></td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table>
<?php endif; 








}
