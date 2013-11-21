<?php

Class Crawler {
	set_time_limit(900);
	public $myRootData;
	public $myRootUrl;
	public $myRootPage;
	public $to_scrape; 
	public $myVisited; 

	public function __construct($root){
		global $root_url;
		$root_url = $root;
		$this->to_scrape = array();
		$this->myVisited = array();
		$this->myRootUrl = $root;
		
		$root_data = new Raw_Data($root);
		$this->myRootData = $root_data;
		$this->myRootPage = new Page($root, $root_data->html);
		$this->myRootPage->init();

		foreach ($this->myRootPage->links_in as $key => $value) {
			if(!in_array($value, $this->to_scrape)){
				array_push($this->to_scrape, $value);
			}
		}
	}
	public function doCrawl(){
		include "information.php";
		$myDB = new ScrapeDB($username, $password, $hostname, $database);
		$hostid = $myDB->init_host($this->myRootData);
		while (!empty($this->to_scrape)) {
			$url = array_shift($this->to_scrape);
			array_push($this->myVisited, $url);
			$raw_data = new Raw_Data($url);
			if($raw_data->valid == false){
				continue;
			}
			else{
				$current = new Page($raw_data->url, $raw_data->html);
				$current->init();
				$myDB->init_save_page($current, $hostid);
				//$myDB->save_scripts($current->get_scripts());
				//$myDB->save_images($current->get_images());
				//$current->get_all_table_data();
				//$current->table_traverse(tableid, x to the right, y down); //returns contents of the specified cell
				//$current->get_tag(specified tag) //returns an array containing all contents of the tag.
				//$myDB->save_css($current->get_css());
				$all_links_in = $current->links_in;
				foreach ($all_links_in as $key => $next) {
					if(!in_array($next, $this->myVisited) && !in_array($next, $this->to_scrape)){
						array_push($this->to_scrape, $next);
					}
				}
			}
		}
		return true;
		
	}
}


?>