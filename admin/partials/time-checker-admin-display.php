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

include plugin_dir_path( __DIR__ ) . 'class-time-checker-admin.php';
include plugin_dir_path( __DIR__ ) . 'class-time-checker-admin-sql.php';
$dir = WP_PLUGIN_DIR . '/time-checker';







// There needs to be logic here that executes and shows the possible times for the dropdown.
// For enterting beginning/ending hours. 
// 
$sql_queue = new SQL_Init_Request(); 
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





// there's a fatal error coming from match p.m._or am so I have to discover that and link it. 
<p>Enter Begining Hours:</p> 
	<select name="courseName" id="courseNameId">
	<?php foreach (match_pm_or_am($array_unique_time_starts_no_repeats) as $hour_end ) : ?>
	<option value="<?php echo $hour_end; ?>"><?php echo $hour_end; ?></option>
  	<?php endforeach; ?>
  	</select>

<p>Enter Ending Hours:</p> 
	<select name="courseName" id="courseNameId">
	<?php foreach (match_pm_or_am($array_unique_time_ends_no_repeats ) as $hour_end ) : ?>
	<option value="<?php echo $hour_end; ?>"><?php echo $hour_end; ?></option>
	<?php endforeach; ?>
	</select>
	<h1>&nbsp;</h1>



<input type ="submit">


<h1>&nbsp;</h1>

</form>

