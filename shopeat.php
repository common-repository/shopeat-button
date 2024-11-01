<?php
/*
Plugin Name: ShopEat
Plugin URI: http://go.shopeat.com
Description: Recipes to shopping list
Version: 1.2
Author: Shopeat LTD
License: GPL2
*/
load_plugin_textdomain('shopeat_textdomain', false, basename( dirname( __FILE__ ) ) . '/languages' );

/* add_filter( 'the_content', 'shopeat_content_filter', 20 );

function shopeat_content_filter( $content ) {
	$urls = array(
		'he' => 'http://www.shopeat.co.il/api/api.js?pub_code=WDGT-895254782',
		'en' => 'http://www.shopeat.com/api/api.js?pub_code=SE-23838486',
	);
    if ( is_single() )
    {
    	$current_language = get_option('button_language','he');
    	$checked = get_post_meta(get_the_ID(), 'shopeat_button', true);
        // Add image to the beginning of each page
        if($checked == '1')
        {
	        $content = sprintf(
	            '%s<script type="text/javascript" src="'.$urls[$current_language].'"></script>',
	            $content
	        );
	    }
     }

    // Returns the content.
    return $content;
}

add_action( 'add_meta_boxes', 'shopeat_add_custom_box' );

add_action( 'save_post', 'shopeat_save_postdata' );

function shopeat_add_custom_box() {
    add_meta_box( 
        'shopeat_addwidget',
        __( 'View shopeat button', 'shopeat_textdomain' ),
        'shopeat_addwidget_checkbox',
        'post' 
    );
    add_meta_box(
        'shopeat_addwidget',
        __( 'View shopeat button', 'shopeat_textdomain' ),
        'shopeat_addwidget_checkbox',
        'page'
    );
} 

function shopeat_addwidget_checkbox( $post ) {
  // The actual fields for data entry
  $checked = get_post_meta($post->ID, 'shopeat_button', true);
  echo '<input type="checkbox" id="shopeat_use_button" name="shopeat_use_button" value="1"';
  if($checked == '1')
  	echo ' CHECKED';
  echo ' />&nbsp;';
  echo '<label for="shopeat_use_button">';
       _e("View shopeat button on this page", 'shopeat_textdomain' );
  echo '</label> ';
}

function shopeat_save_postdata( $post_id ) {
  // verify if this is an auto save routine. 
  // If it is our form has not been submitted, so we dont want to do anything
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      return;

  // verify this came from the our screen and with proper authorization,
  // because save_post can be triggered at other times

  
  // Check permissions
  if ( 'page' == $_POST['post_type'] ) 
  {
    if ( !current_user_can( 'edit_page', $post_id ) )
        return;
  }
  else
  {
    if ( !current_user_can( 'edit_post', $post_id ) )
        return;
  }

  // OK, we're authenticated: we need to find and save the data

  $use_button = (isset($_POST['shopeat_use_button'])) ? 1 : 0;

  if(!add_post_meta($post_id, 'shopeat_button', $use_button, true))
  	update_post_meta($post_id, 'shopeat_button', $use_button);
} */

add_action('admin_menu', 'shopeat_settings_menu');

function shopeat_settings_menu() {
	add_menu_page(__('ShopEat Settings','shopeat_textdomain'), __('ShopEat Settings','shopeat_textdomain'), 'administrator', __FILE__, 'shopeat_settings_form',plugins_url('/images/icon.png', __FILE__));

	add_action( 'admin_init', 'register_shopeat_settings' );
}


function register_shopeat_settings() {
	register_setting( 'shopeat-settings-group', 'button_language' );
}

function shopeat_settings_form() {
	if(@$_GET['addincat'] > 0)
	{
		$posts_array = get_posts(array(
	    'numberposts'     => -1,
	    'offset'          => 0,
	    'category'        => intval($_GET['addincat'])
	    ));
	    $current_language = get_option('button_language','he');
	    foreach($posts_array as $post)
	    {
	    	if(substr_count($post->post_content,'[shopeat_button]') == 0 && current_user_can('edit_post', $post->ID))
	    	{
				$my_post = array();
				$my_post['ID'] = $post->ID;
				$my_post['post_content'] = $post->post_content."\n[shopeat_button]";
				
				wp_update_post( $my_post );
			}
	    }
	}
	if(@$_GET['delfromcat'] > 0)
	{
		$posts_array = get_posts(array(
	    'numberposts'     => -1,
	    'offset'          => 0,
	    'category'        => intval($_GET['addincat'])
	    ));
	    $current_language = get_option('button_language','he');
	    foreach($posts_array as $post)
	    {
	    	if(substr_count($post->post_content,'[shopeat_button]') > 0 && current_user_can('edit_post', $post->ID))
	    	{
				$my_post = array();
				$my_post['ID'] = $post->ID;
				$my_post['post_content'] = str_replace('[shopeat_button]','',$post->post_content);
				
				wp_update_post( $my_post );
			}
	    }
	}
	?>
	<div class="wrap">
	<h2><? _e('ShopEat Settings','shopeat_textdomain');?></h2>
	<form method="post" action="options.php">
	    <?php settings_fields( 'shopeat-settings-group' ); ?>
	    <?php $current_language = get_option('button_language'); ?>
	    <table class="form-table">
	        <tr valign="top">
	        <th scope="row"><? _e('Button language','shopeat_textdomain');?></th>
	        <td>
		        <select name="button_language">
					<option value="he">עברית</option>
		        	<option value="en"<?=($current_language=='en') ? ' SELECTED' : '';?>>English</option>
		        	<option value="de"<?=($current_language=='de') ? ' SELECTED' : '';?>>German</option>
		        </select>
	        </td>
	        </tr>
	    </table>
	    
	    <p class="submit">
	    	<input type="submit" class="button-primary" value="<?php _e('Save Changes','shopeat_textdomain') ?>" />
	    </p>
	
	</form>
	</div>
	<div class="wrap">
		<h2><? _e('Add ShopEat button to','shopeat_textdomain');?>:</h2>
		<select id="shopeat_cat">
			<option value="0"><?=_e('Choose category','shopeat_textdomain');?></option>
			<? $categories = get_categories();
			
			foreach ($categories as $category) {
			  	$option = '<option value="'.$category->cat_ID.'">';
				$option .= $category->cat_name;
				$option .= ' ('.$category->category_count.')';
				$option .= '</option>';
				echo $option;
			  } ?>
		</select>
		<p class="submit">
	    	<input onclick="submitShopEatMassAdd();" type="button" class="button-primary" value="<?php _e('Add ShopEat Button to posts in the selected category','shopeat_textdomain') ?>" />
	    	<input onclick="submitShopEatMassDel();" type="button" class="button-primary" value="<?php _e('Remove ShopEat Button from posts in the selected category','shopeat_textdomain') ?>" />
	    </p>
	</div>
	<script type="text/javascript">
		function submitShopEatMassAdd(){
			var combo = document.getElementById('shopeat_cat');
			var catID = combo.options[combo.selectedIndex].value;
			if(catID < 1)
				alert('<?=_e('Choose category','shopeat_textdomain');?>!');
			else
			{
				var loc = document.location.toString();
				var ex = loc.split('&addincat=');
				if(ex.length > 1)
					loc = ex[0];
				ex = loc.split('&delfromcat=');
				if(ex.length > 1)
					loc = ex[0];
				document.location.href = loc+'&addincat='+catID;
			}
		}
		function submitShopEatMassDel(){
			var combo = document.getElementById('shopeat_cat');
			var catID = combo.options[combo.selectedIndex].value;
			if(catID < 1)
				alert('<?=_e('Choose category','shopeat_textdomain');?>!');
			else
			{
				var loc = document.location.toString();
				var ex = loc.split('&addincat=');
				if(ex.length > 1)
					loc = ex[0];
				ex = loc.split('&delfromcat=');
				if(ex.length > 1)
					loc = ex[0];
				document.location.href = loc+'&delfromcat='+catID;
			}
		}
	</script>
	<?php
} 

/* Shopeat mark ingredients button in editor */

add_shortcode('shopeat_ingredients', 'shopeat_mark_ingredients'); 

function shopeat_mark_ingredients( $atts, $content = null ) {
	if(is_single())
	{  
    	return '<!--shopeat_ingredients-->'.$content.'<!--/shopeat_ingredients-->';  
    }
    return $content;
} 

add_action('init', 'add_shopeat_ingredients_button');  

function add_shopeat_ingredients_button() {  
   if ( current_user_can('edit_posts') &&  current_user_can('edit_pages') )  
   {  
     add_filter('mce_external_plugins', 'add_shopeat_ingredients_plugin');  
     add_filter('mce_buttons', 'register_shopeat_ingredients_button');  
   }  
}

function register_shopeat_ingredients_button($buttons) {  
   array_push($buttons, 'shopeat_ingredients');  
   return $buttons;  
}

function add_shopeat_ingredients_plugin($plugin_array) {  
   $plugin_array['shopeat_ingredients'] = plugins_url('/js/ingredients.js', __FILE__);  
   return $plugin_array;  
} 

/* Shopeat button button in editor */

add_shortcode('shopeat_button', 'shopeat_redner_button'); 

function shopeat_redner_button() { 
	if(is_single())
	{
		$shopeatWidgetUrls = array(
			'he' => 'http://www.shopeat.co.il/api/api.js?pub_code=WDGT-122086423',
			'en' => 'http://www.shopeat.com/api/api.js?pub_code=WDGT-122086423',
			'de' => 'http://de.shopeat.com/api/api.js?pub_code=WDGT-122086423',
		);
		$current_language = get_option('button_language','he');
	    return '<script type="text/javascript" src="'.$shopeatWidgetUrls[$current_language].'"></script>'; 
	}
	return ''; 
} 

add_action('init', 'add_shopeat_button');  

function add_shopeat_button() {  
   if ( current_user_can('edit_posts') &&  current_user_can('edit_pages') )  
   {  
     add_filter('mce_external_plugins', 'add_shopeat_plugin');  
     add_filter('mce_buttons', 'register_shopeat_button');  
   }  
}

function register_shopeat_button($buttons) {  
   array_push($buttons, 'shopeat');  
   return $buttons;  
}

function add_shopeat_plugin($plugin_array) {  
   $plugin_array['shopeat'] = plugins_url('/js/button.js', __FILE__);  
   return $plugin_array;  
} 

?>