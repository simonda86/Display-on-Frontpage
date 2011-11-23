<?php

// Check if the WP_List_Table class is available and load it if it isn't
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class wp_admin_table extends WP_List_Table 
{
	function __construct()
	{
		parent::__construct(array(
			'singular' => 'Page',
			'plural' => 'Pages',
			'ajax' => false
		));
	}
	
	function column_default($item, $column_name){
        switch($column_name){
            case 'title':
            case 'author':
			case 'order':
                return $item[$column_name];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }

	function get_sortable_columns() {
        $sortable_columns = array(
            'title'     => array('title',false),     //true means its already sorted
            'order'    => array('order',true)
        );
        return $sortable_columns;
    }
	
	function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['ID']                //The value of the checkbox should be the record's id
        );
    }
	
	function get_columns()
	{
		$columns = array(
			'title' => 'Title',
			'order' => 'Order',
			'author' => 'Author',
		);
		return $columns;
	}
	
	function prepare_items()
	{
		$per_page = 10;
		
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		
		$this->_column_headers = array($columns, $hidden, $sortable);
		
		$frontpages =  new WP_Query(array(
			'post_type' => 'page',
			'posts_per_page' => 5,
			'meta_query' => array(
				array(
					'key' => 'tc_frontpage_option',
					'value' => 1,
					'type' => 'CHAR',
					'compare' => '='
				)	
			),
			'orderby' => 'meta_value',
			'meta_key' => 'tc_frontpage_order',
			'order' => 'ASC'
		));
				
		foreach($frontpages->posts as $page)
		{
			$user = get_userdata($page->post_author);
			
			$data[] = array(
				'title' => '<a href="' . get_bloginfo('wpurl') . '/wp-admin/post.php?post='. $page->ID .'&action=edit" class="row-title">' . $page->post_title . '</a>',
				'order' => get_post_meta($page->ID, 'tc_frontpage_order', true),
				'author' => $user->user_login,
				'date' => $page->post_date_gmt,
			);
		}
		
		function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'order'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
        usort($data, 'usort_reorder');
		
		$current_page = $this->get_pagenum();
		
		$total_items = count($data);
		
		$data = array_slice($data,(($current_page-1)*$per_page),$per_page);
		
		$this->items = $data;
		
		$this->set_pagination_args(array(
			'total_items' => $total_items,
			'per_page' => $per_page,
			'total_pages' => ceil($total_items/$per_page)
		));
	}
}