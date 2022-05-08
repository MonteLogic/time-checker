<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/dchavours/
 * @since      1.0.0
 *
 * @package    Time_Checker
 * @subpackage Time_Checker/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * File includes logic used to queue database in SQL. 
 *
 *
 *
 * @package    Time_Checker
 * @subpackage Time_Checker/admin
 * @author     Dennis Z. Chavours <zchavours@gmail.com>
 */

// Once this class is instantiated the variables will be in scope. 




class SQL_Init_Request{
	
	
public function __construct()
{
// empty
}

// Start booking_start logic.
/**
 * fill_all_booking_times - This runs a query and gets the wholestart times but it's just one big string that's in in this array 
 * 			    and also they're not unique because they're all the times. 
 * 			    so there's multiple strings repeating it within the all_booking_begins arrray below should 
 * 			    be changed anywaysbecause it's supposed to be agnostic towards booking times.
 * 
 * @param mixed $arrayParam 
 * @access public
 * @return void
 */
public static function fill_all_booking_times($arrayParam){
	foreach($arrayParam as $booking_start ){
		$all_booking_times[] = $booking_start['meta_value'];
	}
  return $all_booking_times;
}




/**
 * turn_into_units - This function takes the large string that includes 
 * 		     the days in the months and puts it into units that can be used better.
 * 		     Also used to delineate hours.
 * 		     Some of the code below is commented out because the only focus atm 
 * 		     is the hour value.
 * @param mixed $unicode_full_time_string 
 * @access public
 * @return void
 */
public static function turn_into_units($unicode_full_time_string){
	foreach ($unicode_full_time_string as $array_unique_time_unit){
   		$hourInt = (int)substr($array_unique_time_unit,8,2);
   		$all_booking_hours_begin_or_end[] = $hourInt;
	} 
	return $all_booking_hours_begin_or_end;
}



/**
 * match_pm_or_am - This function takes the time of strings produced by turn_into_units and decided if its am or pm. 
 * 
 * @param mixed $hour_unit_array 
 * @access public
 * @return array
 */
public static function match_pm_or_am($hour_unit_array){
	sort($hour_unit_array);
	foreach($hour_unit_array as $booking_int_time){
		if($booking_int_time < 12){
        	 $formatted_times_hours[] = $booking_int_time . ":00am"; 
		}
		if($booking_int_time == 12){
			$formatted_times_hours[] = $booking_int_time . ":00pm"; 
		}
		if($booking_int_time > 12){
			$formatted_times_hours[] = $booking_int_time - 12 . ":00pm"; 
      	}
	}
   return $formatted_times_hours;
}


public static function get_sql_vars_two() {

	global $wpdb;

	// toDo: I'm going to have to make then ending var a return value. 
	// Start booking start variables.
	$all_booking_starts_sql_command = 
				"SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key = '_booking_start'";
	$all_booking_starts_row = $wpdb->get_results($all_booking_starts_sql_command, ARRAY_A);
	$array_unique_time_starts = array_unique(self::fill_all_booking_times($all_booking_starts_row));
 	$array_unique_time_starts_no_repeats = array_unique(self::turn_into_units($array_unique_time_starts));

	// Start booking end variables. 
	$all_booking_ends_sql_command = 
				"SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key = '_booking_end'";
	$all_booking_ends_row = $wpdb->get_results($all_booking_ends_sql_command, ARRAY_A);
	$array_unique_time_ends = array_unique(self::fill_all_booking_times($all_booking_ends_row));
	$array_unique_time_ends_no_repeats = array_unique(self::turn_into_units($array_unique_time_ends));

	return array($array_unique_time_starts_no_repeats , $array_unique_time_ends_no_repeats );

	}



}


class SQL_Post_Request {

/**
 * fill_a1_simple_array - 
 * 
 * @param mixed $a1 - Param appears to be records  matching WC with a proper wcb id. 
 *$a1 =  pair_parent_with_child(reduce_sql_array_by_one_dimension($array_booking_product_id_sql_cmd), $parent_post_array_return, $product_id ));

 * @access public
 * @return array
 */
// Unimp
public static function fill_a1_simple_array($a1){
	$a1_simple_array_wcb = array();
	for ($i = 0; $i < count($a1); $i++) {
		$a1_simple_array_wcb[] = $a1[$i]["wcb"]; 
	}
	return $a1_simple_array_wcb;
}

/**
 * fill_a2_simple_array - Make $a2 a simple array. Also avoids duplicates.
 * 
 * @param mixed $a2 
 * @static
 * @access public
 * @return void
 */
public static function fill_a2_simple_array($a2){
	$a2_simple_array_wcb = array();
	for ($i = 0; $i < count($a2); $i++) {
		if($i % 2 == 0){
			$a2_simple_array_wcb[] =
			$a2[$i]["post_id"];
		}
	}
	return $a2_simple_array_wcb;
}


public static function prune_a2($no_match_entries, $a2){
$no_match_keys = array_keys($no_match_entries);
            
	for ($i = 0; $i < count($a2); $i++) {
		for
		  ($j= 0;$j<count($no_match_entries);$j++)
		  {
			if  ($no_match_entries[$no_match_keys[$j]]
			 == $a2[$i]["post_id"]){ 
			 unset(  $a2[$i]);
			}
		}
	}
	return $a2;
}




public static function find_metadata_sql($product_id) {
	global $wpdb;	
	// Find billing emails in the DB order table
	$statuses = array_map( 'esc_sql', wc_get_is_paid_statuses() );

	$customer_emails = $wpdb->get_col("
   SELECT DISTINCT pm.meta_value FROM {$wpdb->posts} AS p
   INNER JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
   INNER JOIN {$wpdb->prefix}woocommerce_order_items AS i ON p.ID = i.order_id
   INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS im ON i.order_item_id = im.order_item_id
   WHERE p.post_status IN ( 'wc-" . implode( "','wc-", $statuses ) . "' )
   AND pm.meta_key IN ( '_billing_email' )
   AND im.meta_key IN ( '_product_id', '_variation_id' )
   AND im.meta_value = $product_id
");


	$customer_emails = $wpdb->get_col("
   SELECT DISTINCT pm.meta_value FROM {$wpdb->posts} AS p
   INNER JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
   INNER JOIN {$wpdb->prefix}woocommerce_order_items AS i ON p.ID = i.order_id
   INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS im ON i.order_item_id = im.order_item_id
   WHERE p.post_status IN ( 'wc-" . implode( "','wc-", $statuses ) . "' )
   AND pm.meta_key IN ( '_billing_email' )
   AND im.meta_key IN ( '_product_id', '_variation_id' )
   AND im.meta_value = $product_id
");



	$customer_phone = $wpdb->get_col("
   SELECT DISTINCT pm.meta_value FROM {$wpdb->posts} AS p
   INNER JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
   INNER JOIN {$wpdb->prefix}woocommerce_order_items AS i ON p.ID = i.order_id
   INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS im ON i.order_item_id = im.order_item_id
   WHERE p.post_status IN ( 'wc-" . implode( "','wc-", $statuses ) . "' )
   AND pm.meta_key IN ( '_billing_phone' )
   AND im.meta_key IN ( '_product_id', '_variation_id' )
   AND im.meta_value = $product_id
");

	$payment_method_title = $wpdb->get_col("
   SELECT pm.meta_value FROM {$wpdb->posts} AS p
   INNER JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
   INNER JOIN {$wpdb->prefix}woocommerce_order_items AS i ON p.ID = i.order_id
   INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS im ON i.order_item_id = im.order_item_id
   AND pm.meta_key IN ( '_payment_method_title' )
   AND im.meta_key IN ( '_product_id', '_variation_id' )
   AND im.meta_value = $product_id
");


/**
 *  This going into wp_postmeta and looks through the column of 
 *  meta_key for the value of every booking customer who booked $course_name.
 */
	$booking_product_id_sql_cmd = 
		"SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = '_booking_product_id' AND meta_value = $product_id ";


/**
 * The variable $array_booking_product_id_sql_cmd takes the sql queued results and turns it into an array. 
 */
	$array_booking_product_id_sql_cmd =
		$wpdb->get_results( $booking_product_id_sql_cmd, ARRAY_A);

	return $array_booking_product_id_sql_cmd;
}


/**
 * reduce_sql_array_by_one_dimension - This function reduces the sql command it takes by one dimension because the sql is queued with one exta dimension we don't need. 
 * 
 * @param mixed $arrayParam2 This takes in an Array from $wpdb->get_results.         
 * @access public
 * @return void
 */
public static function reduce_sql_array_by_one_dimension($arrayParam){
	$new_array = array();
	foreach ( $arrayParam as $arrayThing ) {
		$new_array[] = $arrayThing["post_id"];
	}
	return $new_array;
}




public static function find_ids($array_booking_product_id_sql_cmd){
	
global $wpdb;	
/**
 * This takes all the ids who bought $product_id reduces it to a string for a subsequent sql statement query
 * This variable is used to search through wp_posts table.  
 */
var_dump( self::reduce_sql_array_by_one_dimension($array_booking_product_id_sql_cmd));

// I have to figure out why this is outputting null.
$ids = implode(', ',  self::reduce_sql_array_by_one_dimension($array_booking_product_id_sql_cmd));
var_dump($ids);



/**
 * This sql query finds the parent_post for the booking, in the table wp_posts
 */

$sql_parent_array = 
	"SELECT post_parent, post_date,post_status, post_name, post_type FROM
{$wpdb->prefix}posts WHERE ID IN ($ids)";
$parent_post_array_return = $wpdb->get_results($sql_parent_array, ARRAY_A);
echo "611";
var_dump($parent_post_array_return);



/**
 * This variable finds the post_purchase_id for all wcb entries. 
 */
$sql_find_child_booking =  "SELECT meta_key, meta_value, post_id  FROM {$wpdb->prefix}postmeta WHERE post_id IN ($ids)
AND meta_key NOT IN
( '_edit_lock', 'rs_page_bg_color', '_wc_bookings_gcalendar_event_id', '_booking_resource_id', '_booking_customer_id', '_booking_parent_id','_booking_all_day','_booking_cost','_booking_order_item_id','_booking_persons','_booking_product_id','_local_timezone','_edit_last')";

// I feel like this should be returned as well but I'm going to go up for now. 
$sql_find_child_wcb_array = $wpdb->get_results($sql_find_child_booking,  ARRAY_A);


return array($sql_find_child_wcb_array,	$parent_post_array_return);

}

// This is going to take in 2 arrays as well as $product_id
/**
 * array_level_output - This function outputs all the booking_starts and booking_ends of $product_id inputted into the search form.  
 * 
 * @param mixed $wcb_meta_data_info 
 * @access public
 * @return void
 */
public static function array_level_output($sql_find_child_wcb_array){
	for ($i = 0; $i < count($sql_find_child_wcb_array); $i++) {
	  echo  $sql_find_child_wcb_array[$i]["meta_key"] . ": "  .   $sql_find_child_wcb_array[$i]["meta_value"] .  " 511 <br><br>" ;

	}
}




/**
 * split_array_into_twos - Takes the $sql_find_child_wcb_array and returns an array for 
 * the booking_start and booking_end values. This will hopefully be added on later to the
 * array of pair_parent_with_child(reduce_sql_array_by_one_dimension($array_booking_product_id_sql_cmd), $parent_post_array_return, $product_id ));
 * 
 * @param mixed $sql_find_child_wcb_array 
 * @access public
 * @return void
 */
public static function split_array_into_twos ($sql_find_child_wcb_array){
	$split_two_array = array();

	$group_size = 2;
	$count =  count($sql_find_child_wcb_array); 
	$number_increment = $count / 2;
	for ($i = 0; $i < $number_increment;) {
		$group = array_slice($sql_find_child_wcb_array,$i,2);
		$split_two_array[] = $group;
		$i = $i +2;
	}
	return $split_two_array;
}


//var_dump(split_array_into_twos($sql_find_child_wcb_array));



/**
 * pair_parent_with_child - This function correlates the wcb purcahse id with the wc purchase id. As well as filtering out entries that have a wcb but not a wc. 
 * - This needs to return an array with relevant information.  
 * @param mixed $array_wp_postmeta_child 
 * @param mixed $array_wp_posts_2 
 * @param mixed $product_id 
 * @access public
 * @return void
 */
public static function pair_parent_with_child($array_wp_postmeta_child, $parent_post_array_return, $product_id){
	
	$wc_purchase_ids = array();
	var_dump($parent_post_array_return);
	for ($i = 0; $i < count($parent_post_array_return); $i++) {
		if( $parent_post_array_return[$i]["post_parent"] == 0 ){
			echo $array_wp_postmeta_child[$i] . " did not buy " . $product_id . "<br><br>"; 
		}
		else{	
	        	 $wc_purchase_ids[] =  $wc_pairings =array( "wc" =>  $parent_post_array_return[$i]["post_parent"], "wcb" => $array_wp_postmeta_child[$i] );
	
			//$valid_wc_and_wcb_id = ($array_wp_postmeta_child[$i] => "Some value.");
			//echo $array_wp_postmeta_child[$i] . "-wcb & " . $parent_post_array_return[$i]["post_parent"]. "-wc,  he or she bought " , $product_id . " and paid with " . "<br><br>";
		}
	}
	return $wc_purchase_ids;
}






public static function arrays_to_combine($a1,$a2){
	$array_to_combine = array();
	$j =0;
	$a2_un_assoc =  array_values($a2);
	print_r($a2_un_assoc);
	for($i = 0; $i < count($a1); $i++) {

		if( isset( $a1[$i]["wcb"] ) 
			&& 
			isset($a2_un_assoc[$j]["post_id"])
			&&
			isset($a2_un_assoc[$j+1]["post_id"]))	
			{
			echo " i = $i ";
			echo " j = $j ";
			echo $a2_un_assoc[$j]["post_id"];


			if (($a1[$i]["wcb"] == 
				$a2_un_assoc[$j]["post_id"])
				&&
				($a2_un_assoc[$j+1]["post_id"]
				== 
				$a1[$i]["wcb"])
				){

				$combined_array[$j] = array(	
				"wcb_id" =>	
				$a2_un_assoc[$j]["post_id"],
				"booking_start" =>	
					$a2_un_assoc[$j]["meta_value"],


				// Starting +1 side
				"booking_end" =>	
					$a2_un_assoc[$j+1]["meta_value"],
				"wc_id" => 	
					$a1[$i]["wc"]
				
				);	
			}
		}

		 if ($i == count($a2) - 1){ 

			$i = -1;
			$j++;
			echo PHP_EOL;
			if( $j == count($a2)){
			echo "j or $j is at count of". 
			count($a2);
			// This whole for loop should be exited.
				return $combined_array; 
		 	
		 }
	}
	echo PHP_EOL;
	}
}



// This works only once the the file is not in the folder. Basically, cannot update but only start // a new.
public function create_json_file ( $array_param_one){
	$dir = WP_PLUGIN_DIR . '/woocommerce-order-manager-assign';
	$target_file = $dir . '/array-struct.json';
	// encode array to json
	$json = json_encode($array_param_one);
	//display it
	//generate json file
	if (!file_exists($target_file)){
			fopen($target_file, "w");
		file_put_contents($target_file, $json);
	}
// It would be called like this.
//create_json_file(pair_parent_with_child(reduce_sql_array_by_one_dimension($array_booking_product_id_sql_cmd), $parent_post_array_return, $product_id ));

}




}
