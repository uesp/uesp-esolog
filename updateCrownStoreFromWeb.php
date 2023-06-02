<?php


	// Database users, passwords and other secrets
require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");


class CUespEsoCrownStoreWebUpdater
{
	
	public $ROOT_URL = "https://www.elderscrollsonline.com/en-us/crownstore/";
	public $CATEGORY_URL = "https://www.elderscrollsonline.com/en-us/crownstore/category/";
	public $OUTPUT_PATH = "/imagetmp/crownstore";
	public $PAGE_LOAD_DELAY = 8;	//Seconds
	
	public $CUSTOM_TAGS = [
			"<crown-item>" => "<span class='crown-item'>",
			"</crown-item>" => "</span>",
	];
	
	public $IGNORE_CATEGORIES = [
			"Featured" => true,
			"ESO Plus Deals" => true,
			"Special Offers" => true,
	];
	
	public $db = null;
	public $crownItems = [];
	
	public $loadContentFromFiles = false;
	
	
	public function __construct()
	{
		$this->ParseInputParams();
		$this->InitDatabase();
	}
	
	
	public function ReportError($errorMsg)
	{
		error_log($errorMsg);
		print("$errorMsg\n");
		
		foreach (libxml_get_errors() as $error)
		{
			print($error->message);
		}
		
		$this->errorMessages[] = $errorMsg;
		
		return false;
	}
	
	
	private function ParseInputParams ()
	{
		global $argv;
		
		for ($i = 1; $i < count($argv); $i++)
		{
			$arg = $argv[$i];
			print("Parsing command line arg '$arg'\n");
			
			if ($arg == "--fromfile")  $this->loadContentFromFiles = true;
			if ($arg == "--fromfiles") $this->loadContentFromFiles = true;
		}
	}
	
	
	private function CreateTable()
	{
		$query = "CREATE TABLE IF NOT EXISTS crownStoreItems (
						id INTEGER NOT NULL PRIMARY KEY,
						name TINYTEXT NOT NULL,
						description MEDIUMTEXT NOT NULL,
						imageUrl MEDIUMTEXT NOT NULL,
						category TINYTEXT NOT NULL,
						subCategory TINYTEXT NOT NULL,
						price TINYTEXT NOT NULL,
						esoPlusPrice TINYTEXT NOT NULL,
						lastUpdated INT UNSIGNED NOT NULL,
						saleTimestamp INT UNSIGNED NOT NULL,
						isNew TINYINT(1) NOT NULL,
						FULLTEXT(name, description)
					)";
		
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("Error: Failed to create the crownStoreItems table!\n\t" . $this->db->error);
		return true;
	}
	
	
	private function InitDatabase()
	{
		global $uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase;
		
		$this->db = new mysqli($uespEsoLogWriteDBHost, $uespEsoLogWriteUser, $uespEsoLogWritePW, $uespEsoLogDatabase);
		if ($this->db->connect_error) return $this->ReportError("ERROR: Could not connect to mysql database!");
		
		return true;
	}
	
	
	public function SavePagesHtml()
	{
		$content = file_get_contents($this->ROOT_URL);
		
		if (!$content) return $this->ReportError("Error: Failed to load root URL {$this->ROOT_URL}!");
		$outputFile = $this->OUTPUT_PATH . "/index.html";
		
		mkdir($this->OUTPUT_PATH, 0775);
		
		$result = file_put_contents($outputFile, $content);
		if (!$result) return $this->ReportError("Error: Failed to save root HTML to '$outputFile'!");
		
		if (!$this->ParseIndex($content)) return $this->ReportError("Error: Failed to parse index.html!");
		
		foreach ($this->categories as $id => $category)
		{
			$this->SaveCategoryPageHtml($id, $category);
		}
		
		return true;
	}
	
	
	public function SaveCategoryPageHtml($id, $category)
	{
		if ($this->PAGE_LOAD_DELAY)
		{
			print("\tWaiting {$this->PAGE_LOAD_DELAY} secs before loading $category page...\n");
			sleep($this->PAGE_LOAD_DELAY);
		}
		
		$url = $this->CATEGORY_URL . $id;
		
		print("\tLoading $category page from $url...\n");
		
		$content = file_get_contents($url);
		
		if (!$content) return $this->ReportError("\t\tError: Failed to load category URL {$url}!");
		$outputFile = $this->OUTPUT_PATH . "/$id.html";
		
		$result = file_put_contents($outputFile, $content);
		if (!$result) return $this->ReportErr0r("\t\tError: Failed to save category HTML to '$outputFile'!");
		
		libxml_use_internal_errors(true);
		
		$domDocument = new DOMDocument();
		$domContent = $domDocument->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES'), LIBXML_NOERROR);
		
		if (!$domContent) $this->ReportError("\t\tError: Failed to parse category HTML from {$this->ROOT_URL}!");
		
		libxml_clear_errors();
		
		return true;
	}
	
	
	public function TransformCustomTags($content)
	{
		$newContent = $content;
		
		foreach ($this->CUSTOM_TAGS as $customTag => $newTag)
		{
			$newContent = str_replace($customTag, $newTag, $newContent);
		}
		
		return $newContent;
	}
	
	
	public function ParsePages()
	{
		$indexFile = $this->OUTPUT_PATH . "/index.html";
		$content = file_get_contents($indexFile);
		
		if (!$content) return $this->ReportError("Error: Failed to load index.html from $indexFile!");
		
		//$content = $this->TransformCustomTags($content);
		
		if (!$this->ParseIndex($content)) return $this->Report("Error: Failed to parse index.html!");
		
		foreach ($this->categories as $id => $category)
		{
			$this->ParseCategoryPage($id, $category);
		}
		
		return true;
	}
	
	
	public function TransformItemPrice($price)
	{
		if ($price == null) return "";
		if ($price == "") return "Free";
		
		$newPrice = str_replace(" UNTRANSLATED: crown-plus-deal", "", $price);
		
		if ($newPrice == "") return "Free";
		return $newPrice;
	}
	
	
	public function ParseCategoryPage($id, $category)
	{
		//if ($id != 62) return false;
		
		$categoryFile = $this->OUTPUT_PATH. "/$id.html";
		print("\tParsing category $category ($id) from '$categoryFile'...\n");
		
		$content = file_get_contents($categoryFile);
		if (!$content) return $this->ReportError("\tError: Failed to load category HTML from '$categoryFile'!");
		
		//$content = $this->TransformCustomTags($content);
		
		libxml_use_internal_errors(true);
		
		$domDocument = new DOMDocument();
		$domContent = $domDocument->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES'), LIBXML_NOERROR);
		
		if (!$domContent) $this->ReportError("\tError: Failed to parse category HTML!");
		
		libxml_clear_errors();
		
		$crownItems = $domDocument->getElementsByTagName("crown-item");
		
		$count = $crownItems->length;
		print("\t\tFound $count crown-items...\n");
		
		foreach ($crownItems as $i => $element)
		{
			//print("$i) Parsing crown-item...\n");
			$parent = $element->parentNode;
			$prevParent = $parent->previousSibling->previousSibling;
			$prevTitle = $this->GetChildElementsByClassName($prevParent, 'title', 1);
			$subCategory = "";
			
			if ($prevTitle)
			{
				$subCategory = $prevTitle->textContent;
				print("\t\t\tFound subCategory $subCategory!\n");
			}
			
			$link = $this->GetChildElementsByTagName($element, 'a', 1);
			$crownId = -1;
			
			if ($link)
			{
				$href = $link->attributes->getNamedItem("href");
				
				if ($href)
				{
						///en-us/crownstore/item/12234?chronometer-of-the-tribunal
					$result = preg_match('/.*\/([0-9]+)\?/', $href->value, $matches);
					if ($result) $crownId = intval($matches[1]);
				}
			}
			else
			{
				//print("\t\tNo link element found!\n");
			}
			
			if ($crownId <= 0) print("\t\tNo crown ID for crown-item found!\n");
			
			$image = $this->GetChildElementsByTagName($element, 'img', 1);
			$imageUrl = "";
			
			if ($image)
			{
				$src = $image->attributes->getNamedItem("data-lazy-src");
				if ($src) $imageUrl = $src->value;
				print("\t\t\tFound image URL: $imageUrl\n");
			}
			
			$title = $this->GetChildElementsByClassName($element, 'crown-title', 1);
			if ($title === false) { print("\tNo crown-title found in crown-item!\n"); continue; }
			
			$title = $title->textContent;
			//print("\t\t$title\n");
			
			$new = $this->GetChildElementsByClassName($element, 'new', 1);
			$isNew = 0;
			if ($new) $isNew = 1;
			
			$crownsPrice = $this->GetChildElementsByClassName($element, 'crowns-price', 1);
			$sealsPrice = $this->GetChildElementsByClassName($element, 'seals-price', 1);
			$gemsPrice = $this->GetChildElementsByClassName($element, 'gems-price', 1);
			
			$prices = [];
			$esoPlusPrices = [];
			
			if ($crownsPrice)
			{
				$srOnly = $this->GetChildElementsByClassName($crownsPrice, 'sr-only');
				
				if ($srOnly !== false)
				{
					$prices[] = $this->TransformItemPrice($srOnly[0]->textContent);
					if ($srOnly[1]) $esoPlusPrices[] = $this->TransformItemPrice($srOnly[1]->textContent);
				}
			}
			
			if ($sealsPrice)
			{
				$srOnly = $this->GetChildElementsByClassName($sealsPrice, 'sr-only');
				
				if ($srOnly !== false)
				{
					$prices[] = $this->TransformItemPrice($srOnly[0]->textContent);
					if ($srOnly[1]) $esoPlusPrices[] = $this->TransformItemPrice($srOnly[1]->textContent);
				}
			}
			
			if ($gemsPrice)
			{
				$srOnly = $this->GetChildElementsByClassName($gemsPrice, 'sr-only');
				
				if ($srOnly !== false)
				{
					$prices[] = $this->TransformItemPrice($srOnly[0]->textContent);
					if ($srOnly[1]) $esoPlusPrices[] = $this->TransformItemPrice($srOnly[1]->textContent);
				}
			}
			
			$priceText = implode(', ', $prices);
			$esoPlusPriceText = implode(', ', $esoPlusPrices);
			
			if ($priceText == "")
				print("\t\t$title ($crownId) has no price!\n");
			else
				print("\t\t$title ($crownId) = $priceText (ESO+ $esoPlusPriceText)\n");
			
			//$detail = $this->GetChildElementsByClassName($element, 'crown-details', 1);
			//if (!$detail) { print("\tNo crown-details found in crown-item!\n"); continue; }
			
			//gems-price
				//sr-only
			//seals-price
				//sr-only
			//crowns-price
				//sr-only
				//eso-plus-loyalty
			//crown-flag countdown
				//attribute data-timestamp="1685973600"
			
			//$price = $detail->textContent;
			//print("\t\t$title = $price\n");
			
			$countdown = $this->GetChildElementsByClassName($element, 'countdown', 1);
			$saleTimestamp = 0;
			
			if ($countdown)
			{
				$timestamp = $countdown->attributes->getNamedItem('data-timestamp');
				if ($timestamp) $saleTimestamp = intval($timestamp->value);
				if ($saleTimestamp > 0) print("\t\t\tFound sale timestamp of $saleTimestamp\n");
			}
			
			if ($crownId <= 0)
			{
				print("\t\tNot saving crown-item with no ID!\n");
				continue;
			}
			
			$crownItem = [];
			$crownItem['id'] = $crownId;
			$crownItem['name'] = $title;
			$crownItem['description'] = "";
			$crownItem['price'] = $priceText;
			$crownItem['esoPlusPrice'] = $esoPlusPriceText;
			$crownItem['saleTimestamp'] = $saleTimestamp;
			$crownItem['lastUpdated'] = time();
			$crownItem['imageUrl'] = $imageUrl;
			$crownItem['category'] = $category;
			$crownItem['subCategory'] = $subCategory;
			$crownItem['isNew'] = $isNew;
			
			$this->crownItems[$crownId] = $crownItem;
		}
		
		//print("\tDone!\n");
		return true;
	}
	
	
	public function GetChildElementsByTagName($parent, $tagName, $limit = -1)
	{
		$elements = [];
		
		foreach ($parent->childNodes as $pp)
		{
			//print("\t\t{$pp->nodeName} : $tagName\n");
			
			if ($pp->nodeName == $tagName)
			{
				if ($limit == 1) return $pp;
				$elements[] = $pp;
			}
			
			$newElements = $this->GetChildElementsByTagName($pp, $tagName, $limit);
			
			if ($newElements !== false && count($newElements) > 0)
			{
				$count = count($newElements);
				//print("\tMatched child class name $count/$limit!\n");
				if ($limit == 1) return $newElements;
				
				$elements = array_merge($elements, $newElements);
				if ($limit > 0 && count($elements) >= $limit) break;
			}
		}
		
		if (count($elements) <= 0) return false;
		if ($limit == 1) return $elements[0];
		return $elements;
	}
	
	
	public function GetChildElementsByClassName($parent, $className, $limit = -1)
	{
		$elements = [];
		//print("GetChildElementsByClassName\n");
		
		foreach ($parent->childNodes as $pp)
		{
			//print("\t\t{$pp->nodeName}\n");
			
			$attributes = $pp->attributes;
			if ($attributes == null) continue;
			
			$classStr = $attributes->getNamedItem('class');
			if ($classStr == null) continue;
			
			$classes = explode(' ', $classStr->value);
			
			//print("\t\t{$pp->nodeName} : {$classStr->value}\n");
			
			foreach ($classes as $class)
			{
				if ($class == $className)
				{
					//print("\tMatched class name!\n");
					
					if ($limit == 1) return $pp;
					$elements[] = $pp;
					break;
				}
			}
		}
		
		foreach ($parent->childNodes as $pp)
		{
			//print("\t\tChild {$pp->nodeName}\n");
			
			$newElements = $this->GetChildElementsByClassName($pp, $className, $limit);
			
			if ($newElements !== false && count($newElements) > 0)
			{
				$count = count($newElements);
				//print("\tMatched child class name $count/$limit!\n");
				if ($limit == 1) return $newElements;
				
				$elements = array_merge($elements, $newElements);
				if ($limit > 0 && count($elements) >= $limit) break;
			}
		}
		
		$count = count($elements);
		//print("\tNum Elements: $count\n");
		
		if (count($elements) <= 0) return false;
		return $elements;
	}
	
	
	public function ParseIndex($content)
	{
		libxml_use_internal_errors(true);
		
		$domDocument = new DOMDocument();
		$domContent = $domDocument->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES'), LIBXML_NOERROR);
		
		if (!$domContent) $this->ReportError("Error: Failed to parse root HTML!");
		
		libxml_clear_errors();
		$count = 0;
		
		$this->categories = [];
		
		foreach ($domDocument->getElementsByTagName("h4") as $element)
		{
			$text = $element->textContent;
			$category = trim(preg_replace('/\([0-9]+\)/', '', $text));
			$attributes = $element->attributes;
			
			if ($attributes == null) continue;
			$class = $attributes->getNamedItem("class");
			if ($class == null) continue;
			if (strpos($class, "accordion-heading") === false) continue;
			
			if ($this->IGNORE_CATEGORIES[$category] === true) continue;
			
			$id = $attributes->getNamedItem("id");
			if ($id == null) continue;
			
			$id = preg_replace('/[a-zA-Z_]+/', '', $id->value);
			
			$this->categories[$id] = $category;
			
			print("\t$category ($id)\n");
			++$count;
		}
		
		print("Found $count crown store categories...\n");
		
		return true;
	}
	
	
	public function SaveItems()
	{
		if (!$this->CreateTable()) return $this->ReportError("Error: Failed to create tables!");
		
		foreach ($this->crownItems as $id => $crownItem)
		{
			$id = intval($id);
			$name = $this->db->real_escape_string($crownItem['name']);
			$desc = $this->db->real_escape_string($crownItem['description']);
			$imageUrl = $this->db->real_escape_string($crownItem['imageUrl']);
			$price = $this->db->real_escape_string($crownItem['price']);
			$esoPlusPrice = $this->db->real_escape_string($crownItem['esoPlusPrice']);
			$saleTimestamp = intval($crownItem['saleTimestamp']);
			$lastUpdated = intval($crownItem['lastUpdated']);
			$category = $this->db->real_escape_string($crownItem['category']);
			$subCategory = $this->db->real_escape_string($crownItem['subCategory']);
			$isNew = intval($crownItem['isNew']);
			
			$newCols = ['id', 'name', 'description', 'imageUrl', 'price', 'esoPlusPrice', 'lastUpdated', 'saleTimestamp', 'category', 'subCategory', 'isNew'];
			$newValues = [$id, $name, $desc, $imageUrl, $price, $esoPlusPrice, $lastUpdated, $saleTimestamp, $category, $subCategory, $isNew];
			
			$updates = [];
			
			foreach ($newCols as $i => $col)
			{
				if ($col == "id") continue;
				
				$value = $newValues[$i];
				$updates[] = "$col='$value'";
			}
			
			$newCols = implode(',', $newCols);
			$newValues = "'" . implode("','", $newValues) . "'";
			$updates = implode(',', $updates);
			
			$query = "INSERT INTO crownStoreItems($newCols) VALUES($newValues) ON DUPLICATE KEY UPDATE $updates;";
			$result = $this->db->query($query);
			if (!$result) $this->ReportError("Error: Failed to insert/update database row!\n\t" . $this->db->error);
		}
		
		return true;
	}
	
	
	public function Run()
	{
		if (!$this->loadContentFromFiles)
		{
			print("Loading content from URL and updating cache...\n");
			if (!$this->SavePagesHtml()) return false;
		}
		else
		{
			print("Loading content from cache...\n");
		}
		
		$this->ParsePages();
		
		$count = count($this->crownItems);
		print("Found a total of $count unique crown items!\n");
		
		$this->SaveItems();
		
		return true;
	}
	
};

$updater = new CUespEsoCrownStoreWebUpdater();
$updater->Run();