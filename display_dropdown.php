<?php


function display_dropdown() {
    global $wpdb;
	
	$sql = "select * from  ".$wpdb->prefix . "magix_dropdown where parent_category=0";
	$cat_arr = $wpdb->get_results($sql);
	
	$html = "";
	
	if(empty($cat_arr)){
		$html = "No category Added";
		return $html;
	}
	$html .= "<div class='md-cat-main-div'>";
 	$html .= "<select class='md-cat-main'>";
	foreach($cat_arr as $cat_obj){
		$html .= "<option value='".$cat_obj->id."'>".$cat_obj->category_name."</option>";
	}
	$html .= "</select>";
	$html .= "</div>";
	
	
	$html .= "<div class='md-cat-sub-div'>";
 	$html .= "<select class='md-cat-sub'>";
	$html .= "</select>";
	$html .= "</div>";
	$html .= '<div class="md_spinner" style="display:none">Loading...</div>';
	$html .= "<div class='md-cat-res-div' style='display:none'>";
 	$html .= "<p class='md-cat-res'>";
	$html .= "</p>";
	$html .= "</div>";
	return $html;

}


add_action('wp_head', function() {
		$admin_url = admin_url("admin-ajax.php");
		?>
		<script type="text/javascript">
		
		jQuery(document).ready(function(){
			var cat = "";
			var sub_cat = "";
			jQuery(".md-cat-res-div").hide();
			jQuery(".md_spinner").show();
			cat = jQuery(".md-cat-main").val();
			sub_cat = jQuery(".md-cat-sub").val();
			md_data_ajax(cat,sub_cat);
			
			jQuery(".md-cat-main").on("change",function(){
				cat = jQuery(".md-cat-main").val();
				sub_cat = "null";
				jQuery(".md-cat-res-div").hide();
				jQuery(".md_spinner").show();
				md_data_ajax(cat,sub_cat);
			});
			jQuery(".md-cat-sub").on("change",function(){
				cat = jQuery(".md-cat-main").val();
				sub_cat = jQuery(".md-cat-sub").val();
				jQuery(".md-cat-res-div").hide();
				jQuery(".md_spinner").show();
				md_data_ajax(cat,sub_cat);
			});
			
		});
		
		function md_data_ajax(main_cat,sub_cat){
			var data = {
			'action': 'md_show_result',
			'main_cat': encodeURI(main_cat),
			'sub_cat': encodeURI(sub_cat),
			'ajaxCall':true
			};
			jQuery(".yaf_spinner").show();
			jQuery.get("<?php echo $admin_url; ?>", data, function(response) {
				response = JSON.parse(response);
				jQuery(".md-cat-sub").html("");
				jQuery.each(response.options, function( index, value ) {
					selected = "";
					if(index == response.selected){
						selected = "selected";
					}
					jQuery(".md-cat-sub").append("<option value='"+index+"' "+selected+">"+value+"</option>");
				});
				jQuery(".md-cat-res-div").show();
				jQuery(".md-cat-res").html(response.res);
				jQuery(".md_spinner").hide();
			});
	
		}
		
		
		
		</script>
		<?php
});



add_action( 'wp_ajax_md_show_result', 'md_show_result' );
add_action( 'wp_ajax_nopriv_md_show_result', 'md_show_result' );


function md_show_result(){
	global $wpdb;
	$response = array();
	
	$sql = "select * from ".$wpdb->prefix . "magix_dropdown where parent_category='".$_GET['main_cat']."'";
	$sub_cat_arr = $wpdb->get_results($sql);
	$response['options'] = "";
	$sub_cat = "";
	foreach($sub_cat_arr as $sub_cat_obj){
		if($sub_cat == ""){
			$sub_cat = $sub_cat_obj->id;
		}
		$response['options'][$sub_cat_obj->id] = $sub_cat_obj->category_name;	
	}

	if($_GET['sub_cat'] != "null"){
		$sub_cat = $_GET['sub_cat'];
		
	}
	$response['res'] = "";
	$sql = "select category_desc from ".$wpdb->prefix . "magix_dropdown where id='".$sub_cat."'";
	
	$response['res']  =  $wpdb->get_var($sql);
	$response['selected'] = $sub_cat;
	echo json_encode($response);
	exit;
}

?>
