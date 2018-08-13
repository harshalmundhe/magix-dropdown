<?php
/**
* Plugin Name: Magix Dropdown
* Plugin URI: https://harshalmundhe.wordpress.com/
* Description: The very simple plugin for dropdown.
* Version: 1.0
* Author: Harshal
* Author URI: https://harshalmundhe.wordpress.com/
*/

global $md_db_version;
$md_db_version = '1.0';

function md_datatable() {
	global $wpdb;
	global $md_db_version;

	$table_name = $wpdb->prefix . 'magix_dropdown';

	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
						   id mediumint(9) NOT NULL AUTO_INCREMENT,
						   category_name varchar(100) NOT NULL DEFAULT '',
						   category_desc text,
						   parent_category int NOT NULL DEFAULT '0',
						   UNIQUE KEY id (id)
			   ) $charset_collate;";

   require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta($sql);

	add_option('md_db_version', $md_db_version);
}

register_activation_hook(__FILE__, 'md_datatable');

add_action('admin_menu', 'md_display_in_menu');

function md_display_in_menu() {
	add_menu_page('Magix Dropdown', //page title
			'Magix Dropdown', //menu title
			'manage_options', //capabilities
			'magix_dropdown', //menu slug
			'magix_dropdown_list'
	);
	add_submenu_page('magix_dropdown', 'Add New', 'Add New', 'manage_options', 'add-new-category', 'md_add_new' );
   add_submenu_page(null, 'Edit', 'Edit', 'manage_options', 'md-edit-cat', 'md_edit_cat' );
}


// returns the root directory path of particular plugin
define('ROOTDIR', plugin_dir_path(__FILE__));
require_once(ROOTDIR . 'display_dropdown.php');
add_shortcode('md', 'display_dropdown');


function magix_dropdown_list(){
	global $wpdb;
	if(isset($_GET['del'])){
				$sql = "delete from ".$wpdb->prefix . "magix_dropdown where id='".$_GET['del']."' or parent_category='".$_GET['del']."'";
				$wpdb->query($sql);
				wp_redirect(admin_url( 'admin.php?page=magix_dropdown'));
				exit;
	}
	
	$sql = "select * from  ".$wpdb->prefix . "magix_dropdown where parent_category=0";
	$cat_arr = $wpdb->get_results($sql);
	
	
	
	?>
	<h1>Magix Dropdown</h1>
	<a href="<?php echo admin_url( 'admin.php?page=add-new-category' ); ?>" class="page-title-action">Add New</a><br><br>
	<?php if($cat_arr){ ?>
	<table width="50%" class="wp-list-table widefat fixed striped">
		<tr>
			<th><strong>Category</strong></th>
			<th><strong>Action</strong></th>
		</tr>
		<?php foreach($cat_arr as $cat_obj){ ?>
				<tr>
						<td><?=_e($cat_obj->category_name);?></td>
						<td><a href="<?php echo admin_url( 'admin.php?page=md-edit-cat&edt='.$cat_obj->id ); ?>">Edit</a> | <a href="<?php echo admin_url( 'admin.php?page=magix_dropdown&del='.$cat_obj->id ); ?>">Delete</a></td>
				</tr>
				<?php
		}?>
	</table>
	
	<?php
	}else{
			_e("<h2>No category Added<h2>");
	} ?>
	
	<div class="help">
		<h2>ShortCodes</h2>
		<p>Use shortcode
		<kbd><strong>[md]</strong></kbd><br> to display the dropdown
		</p>
	</div>
	<?php
}

function md_add_new(){
   global $wpdb;
   $success = "";
   if(isset($_POST['add_new'])){
			   $arr = array(
				   "category_name"=> sanitize_text_field($_POST['main_category']),
				 );
				   $wpdb->insert($wpdb->prefix . 'magix_dropdown', $arr);
				   
				   $main_cat = $wpdb->insert_id;
		   
		   foreach($_POST['sub_category'] as $key => $sub_cat){
					   $arr = array(
					   "category_name"=> sanitize_text_field($sub_cat),
					   "category_desc"=> sanitize_text_field($_POST['sub_category_desc'][$key]),
					   "parent_category"=> sanitize_text_field($main_cat),
						   );
					   $wpdb->insert($wpdb->prefix . 'magix_dropdown', $arr);
		   }
		   $success = "Category Added Successfully";
   }
   
   
	   ?>
		   <h1>Add New Category</h1>
		   <a href="<?php echo admin_url( 'admin.php?page=add-new-category' ); ?>">Add New</a>
		   <?php if($success!=""){?>
				   <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
			   <p><strong><?php _e($success); ?></strong></p><button type="button" class="notice-dismiss">
			   <span class="screen-reader-text">Dismiss this notice.</span></button>
			   </div>
			   <?php } ?>
		   
		   <form method="post" action="">
				   <table class="form-table">
						   <tr>
									   <th>Categoty Name</th>
									   <td><input type="text" name="main_category" value="" required></td>
						   </tr>
						   <tr>
									   <th>Sub Category</th>
									   <td>
											   <table class="sub-cat-table">
												   <tr>
															   <th>Sub Category Name</td>
															   <th>Sub Category Description</td>
															   <td><a href="javascript:void(0)" class="md-add">Add New Row</a></td>
													   </tr>
													   <tr>
															   <td><input type="text" name="sub_category[]" value="" required></td>
															   <td><input type="text" name="sub_category_desc[]" value="" required></td>
															   <td><a href="javascript:void(0)" class="md-delete">Delete Row</a></td>
													   </tr>
											   </table>
									   </td>
						   </tr>
				   </table>
				   <input type="submit" name="add_new" value="Add New">
		   </form>
		   
		   <?php
		    md_admin_js();
}


function md_edit_cat(){
	global $wpdb;
	if(!isset($_GET['edt'])){
		echo "Invalid Category";
		return;
	}
	$cat_id = $_GET['edt'];
	
	$success = "";
	if(isset($_POST['update'])){
		
		$wpdb->query("UPDATE `".$wpdb->prefix . "magix_dropdown` SET `category_name` = '".$_POST['main_category']."' WHERE id='".$cat_id."';");
		
		$wpdb->query("DELETE FROM ".$wpdb->prefix ."magix_dropdown WHERE parent_category='".$cat_id."'");
		
		foreach($_POST['sub_category'] as $key => $sub_cat){
			$arr = array(
			"category_name"=> sanitize_text_field($sub_cat),
			"category_desc"=> sanitize_text_field($_POST['sub_category_desc'][$key]),
			"parent_category"=> sanitize_text_field($cat_id),
			);
			$wpdb->insert($wpdb->prefix . 'magix_dropdown', $arr);
		}
		$success = "Category Updated Succesfully";
	}
	
	$sql = "select category_name from ".$wpdb->prefix ."magix_dropdown WHERE id='".$cat_id."'";
	$cat_name = $wpdb->get_var($sql);
	
	$sql = "select * from ".$wpdb->prefix ."magix_dropdown WHERE parent_category='".$cat_id."'";
	$sub_cat_arr = $wpdb->get_results($sql);
	
	
   ?>
   <h1>Edit Category</h1>
   <a href="<?php echo admin_url( 'admin.php?page=add-new-category' ); ?>">Add New</a>
   <?php if($success!=""){?>
				   <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
			   <p><strong><?php _e($success); ?></strong></p><button type="button" class="notice-dismiss">
			   <span class="screen-reader-text">Dismiss this notice.</span></button>
			   </div>
			   <?php } ?>
		   
		   <form method="post" action="">
				<table class="form-table">
					<tr>
						<th>Categoty Name</th>
						<td><input type="text" name="main_category" value="<?=$cat_name;?>" required></td>
					</tr>
						<tr>
							<th>Sub Category</th>
							<td>
								<table class="sub-cat-table">
									<tr>
												<th>Sub Category Name</td>
												<th>Sub Category Description</td>
												<td><a href="javascript:void(0)" class="md-add">Add New Row</a></td>
										</tr>
										<?php foreach($sub_cat_arr as $sub_cat_obj){ ?>
										<tr>
												<td><input type="text" name="sub_category[]" value="<?=$sub_cat_obj->category_name;?>" required></td>
												<td><input type="text" name="sub_category_desc[]" value="<?=$sub_cat_obj->category_desc;?>" required></td>
												<td><a href="javascript:void(0)" class="md-delete">Delete Row</a></td>
										</tr>
										<?php } ?>
								</table>
							</td>
						</tr>
				</table>
				<input type="submit" name="update" value="Update">
		</form>
   <?php
    md_admin_js();
}

function md_admin_js(){
	?>
	<script>
		jQuery(document).ready(function(){
			jQuery('.md-add').on("click",function(){
						 jQuery('.sub-cat-table').append('<tr><td><input type="text" name="sub_category[]" value="" required></td><td><input type="text" name="sub_category_desc[]" value="" required></td><td><a href="javascript:void(0)" class="md-delete">Delete Row</a></td></tr>');
			});
			 jQuery(document).on("click", "a.md-delete" , function() {
				if(jQuery(".sub-cat-table tr").length<=2){
						alert("Cannot delete all rows");
				}else{
						jQuery(this).closest("tr").remove();
				}
			});
		});
	</script>
	<?php
}

?>
