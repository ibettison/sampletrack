<?php 
/*
createMenu Class
written By: 		Ian Bettison
date:					14/11/2012

The createMenu class creates a dropdown menu using an array of content and links
It uses JQuery to create the Unordered list and toggles the drop down box on clicks. 
*/

class createMenu() {
	
	public $menu_name;
	public $menu_content;
	public $menu_links;
	public $values;
	
	
	function __construct( $menu_name, $menu_content, $menu_links ) {
		$this->menu_name = $menu_name;
		$this->menu_content = $menu_content;
		$this->menu_links = $menu_links;
		
	}
	

	
}
?>