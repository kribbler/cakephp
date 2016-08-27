<?php
namespace App\Shell;

use Cake\Console\Shell;

class HelloShell extends Shell
{

	public function initialize()
	{
		parent::initialize();
		$this->loadModel('Products');
	}

    public function main()
    {
        $this->out('Hello world.');
    }

    public function hi($name = 'Anonymous')
    {
    	$this->out('Hello ' . $name . ' !!!');
    }

    public function showProducts()
    {
    	$products = $this->Products->find('all', array(
    		'fields' => array('Products.title')
    	));
    	$products = $products->toArray();
    	pr($products);
    }

    public function getAsdaProduct($name = null)
    {
    	$main_url = "https://groceries.asda.com";

    	$html = $this->get_data("https://groceries.asda.com/api/items/search?pagenum=1&productperpage=35&keyword=" . $name);

    	$html = json_decode(html_entity_decode($html, ENT_NOQUOTES, "UTF-8"));

    	$fetched = $html->items; 
		
		$products = array(); $i = 0;
		foreach ($fetched as $key => $value) {
			$products[] = array(
				'title' => $value->name,
				'price' => $value->price,
				'link' => "https://groceries.asda.com/product/x/x/" . $value->id
			);

			$i++;
			if ($i >= 10) break;
		}

		$this->saveProducts($products);

    }

    public function getMorrisonsProduct($name = null) 
    {

    	$main_url = "https://groceries.morrisons.com";
    	$pattern_title = "/";
		$pattern_title .= "<h\d class=\"productTitle\">\s*?";
		$pattern_title .= "<a href=\"(.*?)\">\s*?";
		$pattern_title .= "(.*?)<\/a>\s*?";
		$pattern_title .= "<\/h\d>";
		$pattern_title .= "/s";

		$pattern_price = "/";
		$pattern_price .= "<meta itemprop=\"price\" content=\"(.*?)\"\/>";
		$pattern_price .= "/s";

    	$html = $this->get_data("https://groceries.morrisons.com/webshop/getSearchProducts.do?clearTabs=yes&isFreshSearch=true&chosenSuggestionPosition=&entry=" . $name . "&dnr=y");
		$html = html_entity_decode($html, ENT_NOQUOTES, "UTF-8");

		preg_match_all(
			$pattern_title,
			$html,
			$matches_title,
			PREG_SET_ORDER
		);

		preg_match_all(
			$pattern_price,
			$html,
			$matches_price,
			PREG_SET_ORDER
		);

		$products = array();
		for ($i=0; $i<10; $i++) {
			$products[] = array(
				'title' => $matches_title[$i][2],
				'price' => $matches_price[$i][1],
				'link' => $main_url . $matches_title[$i][1]
			);
		}

		$this->saveProducts($products);

    }

    public function getTescoProduct($name = null)
    {

    	$main_url = "http://tesco.com";

    	$pattern_title = "/";
		$pattern_title .= "<span data-title=\"true\">(.*?)<\/span>";
		$pattern_title .= "/s";

		$pattern_price = "/";
		$pattern_price .= "<p class=\"price\"><span class=\"linePrice\">£(.*?)<\/span>";
		$pattern_price .= "/s";

		$pattern_link = "/";
		$pattern_link .= "<div class=\"desc\">\s*?<h2>\s*?<a href=\"(.*?)\">";
		$pattern_link .= "/s";

		$pattern_image = "/";
		$pattern_image .= "<span class=\"image\">\s*?";
		$pattern_image .= "<img src=\"(.*?)\"";
		$pattern_image .= "/s";

    	$html = $this->get_data("http://www.tesco.com/groceries/product/search/default.aspx?searchBox=" . $name . "&newSort=true&search=Search");
		$html = html_entity_decode($html, ENT_NOQUOTES, "UTF-8");
		preg_match_all(
			$pattern_title,
			$html,
			$matches_title,
			PREG_SET_ORDER
		);

		preg_match_all(
			$pattern_price,
			$html,
			$matches_price,
			PREG_SET_ORDER
		);

		preg_match_all(
			$pattern_link,
			$html,
			$matches_link,
			PREG_SET_ORDER
		);

		preg_match_all(
			$pattern_image,
			$html,
			$matches_image,
			PREG_SET_ORDER
		);

		//pr($matches_image); die();

		$products = array();
		for ($i=0; $i<10; $i++) {
			$products[] = array(
				'title' => $matches_title[$i][1],
				'price' => $matches_price[$i][1],
				'link' => $main_url . $matches_link[$i][1],
				'image' => $matches_image[$i][1]
			);
		}

		$this->saveProducts($products);

    }

    private function saveProducts($products)
    {

    	foreach ($products as $product) {
			$p = $this->Products->newEntity();
			$p->title = $product['title'];
			$p->link = $product['link'];
			$p->price = $product['price'];
			$p->image = $product['image'];
			$p->category_id = 1;
			$this->Products->save($p);
		}

    }

    function get_data($url) 
    {
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
}