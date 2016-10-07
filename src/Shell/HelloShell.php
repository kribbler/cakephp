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
    		'fields' => array('Products.title', 'Categories.name'),
    		'contain' => array('Categories')
    	));
    	$products = $products->toArray();
    	pr($products);
    }

    public function getMorrisonsProduct($name = null) 
    {

    	$name = implode("+", explode(" ", $name));
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

		$pattern = "/";
		$pattern .= "<div class=\"shelfTop\">\s*?";
		$pattern .= "(.*?)";
		$pattern .= "<div class=\"productImageContainer aisleProductImg\">\s*?";
		$pattern .= "<a href=\"(.*?)\" class=\"openQuickView\">\s*?";
		$pattern .= "<img src=\"(.*?)\" width=\"130\" height=\"130\" alt=\"(.*?)\" class=\"\"  \/>\s*?";
		$pattern .= "<span class=\"quickViewBtn\" data-href=\"(.*?)\"><span>Quick view<\/span><\/span>\s*?";
		$pattern .= "<\/a>\s*?<\/div>\s*?";
		$pattern .= "<div class=\"shelfRight\">(.*?)";
		$pattern .= "<\/div>\s*?";
		$pattern .= "<h4 class=\"productTitle\">\s*?";
		$pattern .= "<a href=\"(.*?)\">(.*?)";
		$pattern .= "<\/a>\s*?";
		$pattern .= "<\/h4>\s*?";
		$pattern .= "(.*?)";
		$pattern .= "<div class=\"fopLinksContainer\">\s*?";
		$pattern .= "<!-- FOP averageRating tag -->\s*?";
		$pattern .= "<p class=\"rating\">(.*?)<\/p>\s*?";
		$pattern .= "<!-- end FOP averageRating tag -->\s*?";
		$pattern .= "(.*?)";
		$pattern .= "<meta itemprop=\"price\" content=\"(.*?)\"\/>\s*?";
        $pattern .= "<meta itemprop=\"priceCurrency\" content=\"GBP\"\/>\s*?";
		$pattern .= "<\/div>\s*?";
        $pattern .= "<p class=\"pricePerWeight\">(.*?)<\/p>";
		$pattern .= "/s";

    	$html = $this->get_data("https://groceries.morrisons.com/webshop/getSearchProducts.do?clearTabs=yes&isFreshSearch=true&chosenSuggestionPosition=&entry=" . $name . "&dnr=y");
		$html = html_entity_decode($html, ENT_NOQUOTES, "UTF-8");

		preg_match_all(
			$pattern,
			$html,
			$matches,
			PREG_SET_ORDER
		);

		//pr($matches[0]); die();

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
				'title' => $matches[$i][8],
				'price' => $matches[$i][12],
				'link' => $main_url . $matches[$i][7],
				'image' => $matches[$i][3],
				'extra' => $matches[$i][13],
				'category' => $name,
				'store' => 'Morrisons'
			);
		}

		$this->saveProducts($products);

    }

    public function getTescoProduct($name = null)
    {

    	$name = implode("+", explode(" ", $name));
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

		$pattern = "/";
		#$pattern .= "<li data-product-id=\"(.*?)\" class=\"product (*.?)\">\s*?";
		$pattern .= "<div class=\"desc\"><h2><a href=\"(.*?)\">\s*?";
		$pattern .= "<span class=\"image\"(.*?)><img src=\"(.*?)\" height=\"110\" width=\"110\" alt=\"\" id=\"(.*?)\" title=\"(.*?)\" \/>\s*?";
		$pattern .= "(.*?)\s*?";
		$pattern .= "<span data-title=\"true\">(.*?)<\/span><\/a><\/h2>\s*?";
		$pattern .= "(.*?)<\/div>\s*?";
		$pattern .= "<div class=\"quantityWrapper\"><!---->\s*?";
		$pattern .= "<div class=\"content addToBasket\">\s*?";
		$pattern .= "<p class=\"price\"><span class=\"linePrice\">£(.*?)<\/span>\s*?";
		$pattern .= "<span class=\"linePriceAbbr\">\((.*?)\)<\/span><\/p>";
		$pattern .= "/s";

    	$html = $this->get_data("http://www.tesco.com/groceries/product/search/default.aspx?searchBox=" . $name . "&newSort=true&search=Search");
		$html = html_entity_decode($html, ENT_NOQUOTES, "UTF-8");
		preg_match_all(
			$pattern,
			$html,
			$matches,
			PREG_SET_ORDER
		);

		//pr($matches);die();

		$products = array();
		for ($i=0; $i<10; $i++) {
			$products[] = array(
				'title' => $matches[$i][5],
				'price' => $matches[$i][9],
				'link' => $main_url . $matches[$i][1],
				'image' => $matches[$i][3],
				'extra' => $matches[$i][10],
				'category' => $name,
				'store' => 'Tesco'
			);
		}
//pr($products); die();
		$this->saveProducts($products);

    }

    public function getAsdaProduct($name = null)
    {
    	$name = implode("+", explode(" ", $name));
    	$main_url = "https://groceries.asda.com";

    	$html = $this->get_data("https://groceries.asda.com/api/items/search?pagenum=1&productperpage=35&keyword=" . $name);
    	$html = json_decode(html_entity_decode($html, ENT_NOQUOTES, "UTF-8"));

    	$fetched = $html->items; 
		
		$products = array(); $i = 0;
		foreach ($fetched as $key => $value) {
			$products[] = array(
				'title' => $value->name,
				'price' => $value->price,
				'link' => "https://groceries.asda.com/product/x/x/" . $value->id,
				'image' => $value->imageURL,
				'extra' => $value->pricePerUOM,
				'category' => $name,
				'store' => 'Asda'
			);

			$i++;
			if ($i >= 10) break;
		}

		$this->saveProducts($products);

    }

    private function saveProducts($products)
    {

    	$category_id = $this->getCategoryId($products[0]['category']);
		$store_id = $this->getStoreId($products[0]['store']);

    	foreach ($products as $product) {
			$p = $this->Products->newEntity();
			$p->title = $product['title'];
			$p->link = $product['link'];
			$p->price = $product['price'];
			$p->image = $product['image'];
			$p->extra = $product['extra'];
			$p->category_id = $category_id;
			$p->store_id = $store_id;

			$this->Products->save($p);
		}

    }

    private function getCategoryId($name)
    {

    	$category = $this->Products->Categories->find('all', array(
    		'fields' => array('Categories.id'),
    		'conditions' => array('Categories.name' => $name)
    	));

    	$category = $category->first();

    	if (!$category) {
    		$c = $this->Products->Categories->newEntity();
    		$c->parent_id = null;
    		$c->name = $name;
    		$c->description = '';
    		
    		$result = $this->Products->Categories->save($c);
    		$category_id = $result->id;
    	} else {
    		$category_id = $category->id;
    	}

	    return $category_id;
    }

    private function getStoreId($name)
    {

    	$store = $this->Products->Stores->find('all', array(
    		'fields' => array('Stores.id'),
    		'conditions' => array('Stores.name' => $name)
    	));

    	$store = $store->first();

    	if (!$store) {
    		$s = $this->Products->Stores->newEntity();
    		$s->parent_id = null;
    		$s->name = $name;
    		$s->description = '';
    		
    		$result = $this->Products->Stores->save($s);
    		$store_id = $result->id;
    	} else {
    		$store_id = $store->id;
    	}

	    return $store_id;
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