<?php
/*
Page Scraper
Author: Nick Balkissoon
@FrontCoding
Email: ngbalk@gmail.com

TODO: 
CREATE method for getting doctype, whois data, file size, file type.  Currently we are inserting into the database using static attributes
for sake of debugging.
*/

$myPage = new Page("http://www.frontcoding.com");
echo $myPage->title->plaintext . " " . var_dump($myPage->myWords) . var_dump($myPage->myTags) . $myPage->html->plaintext;
class Page {
	public $html; //store as simple_html_dom
	public $url; //should be a string
	public $title;
	public $doctype; //not important, maybe we should delete this
	public $links_count_in; //no longer needed but will keep for now anyway for sake of debugging
	public $links_count_out; //no longer needed
	public $timestamp;
	public $websiteid; //not sure how this will be determined
	public $myTags;
	public $myWords;
	public $links_out;
	public $links_in;
	public $size;

	public function __construct($url){
		
		$this->url = $url;
		$this->html = new simple_html_dom();
		$web = new WebBrowser();
		$result = $web->Process($url);
	  	if (!$result["success"])  echo "Error retrieving URL.  " . $result["error"] . "\n";
	  	else if ($result["response"]["code"] != 200)  echo "Error retrieving URL.  Server returned:  " . $result["response"]["code"] . " " . $result["response"]["meaning"] . "\n";
	  	else
	  		{
	  			$this->get_html($result);//get the body of the html, store as attribute in class (blob)
	  			$this->parse_title();//parse for title
	  			$this->get_doctype();
	  			$this->find_html_tags();
	  			$this->find_all_words();
	  			$this->store_links_in();
	  			$this->store_links_out();
	  			$this->init_save_page();
	  		}

	}
	public function get_html($result){
		$this->html->load($result["body"]);
	}
	public function parse_title(){
		$this->title = $this->html->find("title",0);
	}

	/*
	public function parse_linksin(){
		$linksin = array();
		$all_links = $this->html->find("a"); //this array stores all anchors, now need to find if are links in/links out
		foreach ($all_links as $link) {
			$href = $link->href;
			if(strpos($href, $this->url) !== false){
				array_push($linksin, $href);
			}
		}
			$this->links_count_in = count($linksin);
	}
	public function parse_linksout(){
		$linksout = array();
		$all_links = $this->html->find("a"); 
		foreach ($all_links as $link) {
			$href = $link->href;
			if(strpos($href, $this->url) == false){
				array_push($linksout, $href);
			}
		}
			$this->links_count_out = count($linksout);

	}
	*/
	public function store_links_in(){
		$linksin = array();
		$all_links = $this->html->find("a"); //this array stores all anchors, now need to find if are links in/links out
		foreach ($all_links as $link) {
			$href = $link->href;
			if(strpos($href, $this->url) !== false){
				array_push($linksin, $href);
			}
		}
		$this->link_in = $linksin;
	}
	public function store_links_out(){
		$linksout = array();
		$all_links = $this->html->find("a"); 
		foreach ($all_links as $link) {
			$href = $link->href;
			if(strpos($href, $this->url) == false){
				array_push($linksout, $href);
			}
		}
		$this->links_out = $linksout;
	}

	public function get_doctype(){

	// $content = file_get_contents($this->url);
 //   	$content = str_replace("\n","",$content);
 //    $get_doctype = preg_match_all('/(<!DOCTYPE.+\">)<html/i',$content,$matches);
 //    $doctype = $matches[1][0];
 //    $this->doctype = $doctype;
		}

	public function find_html_tags(){
		$this->myTags = array();
		$htmltags = array("head","meta[content]","title","body","div","a[href]","span","bold","p",
				"header","ul","ol","li","table","tr","td","h1","h2","h3","h4","h5", "h6", "footer",
				"img[src]","img[alt]","menu","strong","a");
		foreach ($htmltags as $tag) {
			$concat = "";
			$tagcontent = $this->html->find($tag);
			foreach ($tagcontent as $it) {
				$concat.= " " . $it->plaintext;
			}
			$this->myTags[$tag] = $concat;
			
		}
	}
	public function find_all_words(){
		$tagstocheck = array("p","h1","h2","h3","h4","h5","h6","title","li","td","alt","a");
		$wordfreqs = array();
		foreach ($tagstocheck as $tag) {
			$elements= $this->html->find($tag);
				foreach ($elements as $element) {
					$words = explode(" ", $element->plaintext);
					foreach ($words as $word) {
						if(!array_key_exists($word, $wordfreqs)){
							$wordfreqs[$word] = 0;
						}
						$wordfreqs[$word]+=1;
					}
				}
			}
			arsort($wordfreqs);
			$this->myWords=$wordfreqs;
		}
	public function get_file_size(){
			$this->size = strlen($this->html->plaintext);		
		}
	public function get_whois(){
			/*
			TODO implement method to scrape whois data
			*/
		}
}




?>