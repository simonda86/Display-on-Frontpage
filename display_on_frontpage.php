<?php
/*
Plugin Name: Display on Frontpage
Plugin URI: http://simon-davies.name
Description: Select what content you would like to display on the frontpage
Author: Simon Davies
Version: 1.0 

*/

class Display_On_Frontpage 
{
	var $post_type;
	
	function __construct()
	{
		$this->post_type = 'page'; // Select type of Post type to use
		
		add_action('save_post', array($this, 'save_meta_data'));
		add_action('add_meta_boxes', array($this, 'add_meta_box'));
		add_action('admin_menu', array($this, 'frontpage_menu'));
	}
	
	function add_meta_box()
	{
		add_meta_box( 
			'tc_frontpage_meta', 
			'Front page', 
			array(&$this, 'frontpage_meta'), 
			$this->post_type, 
			'side'
		);
	}

	function frontpage_meta()
	{
		global $post;
		$frontpage = get_post_meta($post->ID, 'tc_frontpage_option', TRUE);
		$frontpage_title = get_post_meta($post->ID, 'tc_frontpage_title', TRUE);
		$frontpage_order = get_post_meta($post->ID, 'tc_frontpage_order', TRUE);
		wp_nonce_field(plugin_basename(__FILE__),'tc_frontpage_nonce');
		?>
		<p><label for="display_on_frontpage"><strong>Display on front page? </strong> </label><input type="checkbox" name="display_on_frontpage" id="display_on_frontpage?" <?php if($frontpage) echo 'checked="checked"' ?>></p>
		<p><label for="frontpage_title"><strong>Front page title</strong></label></p>
		<input type="text" name="frontpage_title" value="<?php echo $frontpage_title; ?>" id="frontpage_title" style="width: 100%">
		<p><label for="frontpage_order"><strong>Front page order</strong></p>
		<input type="text" name="frontpage_order" value="<?php echo $frontpage_order; ?>" id="frontpage_title" style="width: 30%">
		<?
	}

	function save_meta_data()
	{
		global $post;
		if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
		if(!wp_verify_nonce($_POST['tc_frontpage_nonce'], plugin_basename(__FILE__)));
		if(isset($_POST['display_on_frontpage']))
		{
			update_post_meta($post->ID, 'tc_frontpage_option', true);
			if(isset($_POST['frontpage_title']))
			{
				if($_POST['frontpage_title'] == '') 
				{
					update_post_meta($post->ID, 'tc_frontpage_title', $post->post_title);
				}
				else
				{
					update_post_meta($post->ID, 'tc_frontpage_title', $_POST['frontpage_title']);
				}	
			}
			if(isset($_POST['frontpage_order']))
			{
				update_post_meta($post->ID, 'tc_frontpage_order', $_POST['frontpage_order']);
			}
			else
			{
				update_post_meta($post->ID, 'tc_frontpage_order', 0);
			}
		}
		else
		{
			update_post_meta($post->ID, 'tc_frontpage_option', false);
		}
	}

	// TODO - Add an Admin page to display page appearing on the Frontpage
	function frontpage_menu()
	{
		add_pages_page( 'Frontpage', 'Front page', 'read', 'tc_frontpage', 'tc_frontpage_list');
	}

	function tc_frontpage_list()
	{
		echo "<h2>" . __( 'Front page', 'front-page' ) . "</h2>";
	}
}

$display_on_frontpage = new Display_On_Frontpage();