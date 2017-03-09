<?php 


// Database users, passwords and other secrets
require_once("/home/uesp/secrets/esosalesdata.secrets");
require_once("/home/uesp/secrets/esolog.secrets");
require_once(__DIR__."/esoCommon.php");
require_once(__DIR__."/esoPotionData.php");


$g_EsoSalesDataSortOrder = 1;
$g_EsoItemData = null;


class EsoViewSalesData
{
	const ESOVSD_ICON_URL = UESP_ESO_ICON_URL;
	const ESOVSD_ICON_UNKNOWN = "unknown.png";
	const ESOVSD_MAXZSCORE = 3.0;
	const ESOVSD_MAXZSCORE_WEIGHTED = 3.0;
	const ESOVSD_WEIGHTED_CONSTANT = 30.0;
	
	const MIN_WEIGHTED_AVERAGE_INTERVAL = 11;
	const WEIGHTED_AVERAGE_BUCKETS = 20;
	const WEIGHTED_SMOOTH_INTERVAL = 21;	
		
	public $OMIT_BUYER_INFO = true;
	public $OMIT_SELLER_INFO = true;
		
	public $ESOVSD_HTML_TEMPLATE = "";
	public $ESOVSD_HTML_TEMPLATE_EMBED = "";
	public $ESOVSD_HTML_SALES_TEMPLATE = "";
	public $ESOVSD_HTML_SALES_TEMPLATE_EMBED = "";
	public $ESOVSD_HTML_GUILDS_TEMPLATE = "";
	public $ESOVSD_HTML_GUILDS_TEMPLATE_EMBED = "";
	
	public $MAX_SALES_RECORD_DISPLAYED = 1000;
	public $ESOVSD_MAX_LISTING_TIME = 2592000;
	
	public $server = "NA";
	public $isEmbedded = false;
	
	public $db = null;
	private $dbReadInitialized  = false;
	private $dbWriteInitialized = false;
	public $lastQuery = "";
	
	public $htmlTemplate = "";
	
	public $viewSalesItemId = -1;
	public $formValues = array();
	public $finalItemLevel = -1;
	public $finalItemQuality = -1;
	public $sortField = "itemname";
	public $sortOrder = 1;
	public $displayServer = "??";
	public $rawShowForm = "";
	public $showForm = "ItemSearch";
	public $viewRawData = false;
	public $hasSearchData = false;
	public $outputType = "html";
	
	public $guildData = array();
	public $searchCount = 0;
	public $itemCount = 0;
	public $itemResults = array();
	public $itemIds = array();
	public $singleItemData = null;
	public $itemSortedKeys = array();
	public $searchResults = array();
	public $searchLimitCount = 500;
	public $searchItemIdsLimit = 1000;
	public $totalItemCount = 0;
	public $totalSalesCount = 0;
	public $totalSoldCount = 0;
	public $totalListedCount = 0;
	public $allItemsCount = 0;
	public $allSalesCount = 0;
	
	public $salePriceAverageAll = 0;
	public $salePriceAverageCountAll = 0;
	public $salePriceAverageItemsAll = 0;
	
	public $salePriceAverageSold = 0;
	public $salePriceAverageCountSold = 0;
	public $salePriceAverageItemsSold = 0;
	
	public $salePriceAverageListed = 0;
	public $salePriceAverageCountListed = 0;
	public $salePriceAverageItemsListed = 0;
	
	public $goodPriceAll = 0;
	public $goodPriceSold = 0;
	public $goodPriceListed = 0;
	
	public $salePriceWeightAll = 0;
	public $salePriceWeightItemsAll = 0;
	public $salePriceWeightSold = 0;
	public $salePriceWeightItemsSold = 0;
	public $salePriceWeightListed = 0;
	public $salePriceWeightItemsListed = 0;
	
	public $salePriceAverageLastTimestampListed = 0;
	public $salePriceAverageLastTimestampSold = 0;
	public $salePriceAverageLastTimestampAll = 0;
	
	public $salePriceStdDevAll = 0;
	public $salePriceStdDevSold = 0;
	public $salePriceStdDevListed = 0;
	
	public $salePriceAdjCountAll = 0;
	public $salePriceAdjCountSold = 0;
	public $salePriceAdjCountListed = 0;
	public $salePriceAdjItemsAll = 0;
	public $salePriceAdjItemsSold = 0;
	public $salePriceAdjItemsListed = 0;
	public $salePriceAdjAll = 0;
	public $salePriceAdjSold = 0;
	public $salePriceAdjListed = 0;
	
	public $salePriceStdDevWeightAll = 0;
	public $salePriceStdDevWeightListed = 0;
	public $salePriceStdDevWeightSold = 0;
	
	public $salePriceAdjWeightCountAll = 0;
	public $salePriceAdjWeightCountSold = 0;
	public $salePriceAdjWeightCountListed = 0;
	public $salePriceAdjWeightItemsAll = 0;
	public $salePriceAdjWeightItemsSold = 0;
	public $salePriceAdjWeightItemsListed = 0;
	public $salePriceAdjWeightAll = 0;
	public $salePriceAdjWeightSold = 0;
	public $salePriceAdjWeightListed = 0;
	
	public $itemQuery = "";
	public $salesQuery = "";
	
	public $errorMessages = array();
	
	static public $ESOVSD_TRAITS = array();
	static public $ESOVSD_QUALITIES = array();
	static public $ESOVSD_ITEMTYPES = array();
	static public $ESOVSD_EQUIPTYPES = array();
	static public $ESOVSD_ARMORTYPES = array();
	static public $ESOVSD_WEAPONTYPES = array();
	
	static public $ESOVSD_TIMEPERIODS = array(
			0 => "All Time",
			86400 => "Last Day",
			604800 => "Last Week",
			2678400 => "Last Month",
			31558150 => "Last Year",
	);
		
	static public $ESOVSD_SERVERS = array(
			"NA" => "PC-NA",
			"EU" => "PC-EU",
			"PTS" => "PTS",
			"Other" => "Other",
	);
	
	static public $ESOVSD_SALETYPES = array(
			"all"    => "All",
			"sold"   => "Only Sold Items",
			"listed" => "Only Listed Items",
	);
	

	public function __construct ()
	{
		global $ESO_ITEMTRAIT10_TEXTS, $ESO_ITEMTYPE_TEXTS, $ESO_ITEMEQUIPTYPE_TEXTS;
		global $ESO_ITEMARMORTYPE_TEXTS, $ESO_ITEMWEAPONTYPE_TEXTS, $ESO_ITEMQUALITY_TEXTS;
		
		$this->ESOVSD_HTML_TEMPLATE = __DIR__."/templates/esosales_template.txt";
		$this->ESOVSD_HTML_TEMPLATE_EMBED = __DIR__."/templates/esosales_embed_template.txt";
		
		$this->ESOVSD_HTML_SALES_TEMPLATE = __DIR__."/templates/esosales_sales_template.txt";
		$this->ESOVSD_HTML_SALES_TEMPLATE_EMBED = __DIR__."/templates/esosales_sales_embed_template.txt";
		
		$this->ESOVSD_HTML_GUILDS_TEMPLATE = __DIR__."/templates/esosales_guilds_template.txt";
		$this->ESOVSD_HTML_GUILDS_TEMPLATE_EMBED = __DIR__."/templates/esosales_guilds_embed_template.txt";
		
		self::$ESOVSD_TRAITS = self::MakeUniqueArray($ESO_ITEMTRAIT10_TEXTS);
		self::$ESOVSD_QUALITIES = self::MakeUniqueArray($ESO_ITEMQUALITY_TEXTS, true);
		self::$ESOVSD_ITEMTYPES = self::MakeUniqueArray($ESO_ITEMTYPE_TEXTS);
		self::$ESOVSD_EQUIPTYPES = self::MakeUniqueArray($ESO_ITEMEQUIPTYPE_TEXTS);
		self::$ESOVSD_ARMORTYPES = self::MakeUniqueArray($ESO_ITEMARMORTYPE_TEXTS);
		self::$ESOVSD_WEAPONTYPES = self::MakeUniqueArray($ESO_ITEMWEAPONTYPE_TEXTS);
		
		self::$ESOVSD_TRAITS[-1] = "(none)";
		//array_unshift(self::$ESOVSD_TRAITS, "(none)");
	
		$this->InitDatabaseRead();

		$this->SetInputParams();
		$this->ParseInputParams();
	}
	
	
	private static function MakeUniqueArray($src, $noSort = false)
	{
		$newArray = array_unique($src);
		if (!$noSort) sort($newArray);
		return $newArray;
	}

	
	public function Escape ($html)
	{
		return htmlspecialchars($html);	
	}
	

	public function ReportError ($errorMsg)
	{
		error_log($errorMsg);

		if ($this->db != null && $this->db->error)
		{
			error_log("\tDB Error:" . $this->db->error);
			error_log("\tLast Query:" . $this->lastQuery);
		}
		
		return false;
	}


	private function InitDatabaseRead ()
	{
		global $uespEsoSalesDataReadDBHost, $uespEsoSalesDataReadUser, $uespEsoSalesDataReadPW, $uespEsoSalesDataDatabase;

		if ($this->dbReadInitialized) return true;

		$this->db = new mysqli($uespEsoSalesDataReadDBHost, $uespEsoSalesDataReadUser, $uespEsoSalesDataReadPW, $uespEsoSalesDataDatabase);
		if ($this->db->connect_error) return $this->ReportError("Could not connect to mysql database!");

		$this->dbReadInitialized = true;
		$this->dbWriteInitialized = false;
		
		UpdateEsoPageViews("salesDataViews", $this->db);
		
		return true;
	}


	private function ParseInputParams ()
	{
		$this->ParseFormParam('text');
		$this->ParseFormParam('trait');
		$this->ParseFormParam('quality');+
		$this->ParseFormParam('itemtype');
		$this->ParseFormParam('equiptype');
		$this->ParseFormParam('armortype');
		$this->ParseFormParam('weapontype');
		$this->ParseFormParam('level');
		$this->ParseFormParam('timeperiod');
		$this->ParseFormParam('server');
		$this->ParseFormParam('saletype');
		
		if ($this->formValues['saletype'] != null)
		{
			$this->formValues['saletype'] = strtolower($this->formValues['saletype']);
		}
		
		if ($this->formValues['server'] != null)
		{
			if ($this->formValues['server'] == "NA")
				$this->server = "NA";
			elseif ($this->formValues['server'] == "EU")
				$this->server = "EU";
			elseif ($this->formValues['server'] == "PTS")
				$this->server = "PTS";
			
			$this->displayServer = $this->server;
			$this->salesData->server = $this->server; 
		}
		
		$this->finalItemQuality = $this->GetItemQualityValue($this->formValues['quality']);
		$this->finalItemLevel   = $this->GetItemLevelValue($this->formValues['level']);
		if ($this->finalItemLevel == 0) $this->finalItemLevel = -1;
		
		if (array_key_exists("viewsales", $this->inputParams))
		{
			$this->viewSalesItemId = intval($this->inputParams['viewsales']);
			$this->showForm = "ViewSales";
			$this->sortField = "lastseen";
			$this->sortOrder = 0;
		}
		
		if ($this->inputParams['output'] != null)
		{
			$this->inputParams['output'] = strtolower($this->inputParams['output']);
				
			if ($this->inputParams['output'] == 'csv')
				$this->outputType = "csv";
				else if ($this->inputParams['output'] == 'html')
					$this->outputType = "html";
		}
		
		if (array_key_exists("view", $this->inputParams))
		{
			$this->rawShowForm = strtolower($this->inputParams['view']);
			
			if ($this->rawShowForm == "guild" || $this->rawShowForm == "guilds")
			{
				$this->showForm = "ViewGuilds";
			}
		}
		
		if (array_key_exists("raw", $this->inputParams) || array_key_exists("rawdata", $this->inputParams))
		{
			$this->viewRawData = true;
		}
				
		if (array_key_exists("sort", $this->inputParams)) $this->sortField = $this->inputParams['sort'];
		if (array_key_exists("order", $this->inputParams)) $this->sortOrder = intval($this->inputParams['order']);
		
		return true;
	}
	
	
	private function ParseFormParam($name)
	{
		$id = $name;
	
		if (array_key_exists($id, $this->inputParams))
		{
			$this->formValues[$name] = $this->inputParams[$id];
			return true;
		}
	
		return false;
	}


	private function SetInputParams ()
	{
		global $argv;
		$this->inputParams = $_REQUEST;
		
		$this->hasSearchData = false;
		if (count($this->inputParams) > 0) $this->hasSearchData = true;
	}


	private function OutputHtmlHeader()
	{
		ob_start("ob_gzhandler");
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Content-Type: text/html");
		
		$origin = $_SERVER['HTTP_ORIGIN'];
		
		if (substr($origin, -8) == "uesp.net")
		{
			header("Access-Control-Allow-Origin: $origin");
		}
	}
	
	
	private function OutputCsvHeader()
	{
		ob_start("ob_gzhandler");
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Content-Type: text/plain");
	
		$origin = $_SERVER['HTTP_ORIGIN'];
	
		if (substr($origin, -8) == "uesp.net")
		{
			header("Access-Control-Allow-Origin: $origin");
		}
	}


	public function LoadTemplate()
	{
		$templateFile = "";
		
		if ($this->showForm == "ViewSales")
		{
			if ($this->isEmbedded)
				$templateFile = $this->ESOVSD_HTML_SALES_TEMPLATE_EMBED;
			else
				$templateFile = $this->ESOVSD_HTML_SALES_TEMPLATE;
		}
		elseif ($this->showForm == "ViewGuilds")
		{
			if ($this->isEmbedded)
				$templateFile = $this->ESOVSD_HTML_GUILDS_TEMPLATE_EMBED;
			else
				$templateFile = $this->ESOVSD_HTML_GUILDS_TEMPLATE;
		}
		else
		{
			if ($this->isEmbedded)
				$templateFile = $this->ESOVSD_HTML_TEMPLATE_EMBED;
			else
				$templateFile = $this->ESOVSD_HTML_TEMPLATE;
		}
		
		$this->htmlTemplate = file_get_contents($templateFile);
	}
	
	
	public function GetFormValue($id, $default = "")
	{
		if (!array_key_exists($id, $this->formValues)) return $default;
		return $this->Escape($this->formValues[$id]);
	}
	
	
	public function GetGeneralListHtml($listArray, $formName, $useKeyAsValue = false)
	{
		$output = "";
		$selectedValue = $this->GetFormValue($formName);
		
		if ($selectedValue == "" && $formName = "server")
		{
			$selectedValue = $this->server;
		}
	
		foreach ($listArray as $key => $value)
		{
			if ($value == $selectedValue)
				$selected = "selected";
			elseif ($useKeyAsValue && $key == $selectedValue)
				$selected = "selected";
			else
				$selected = "";
	
			if ($useKeyAsValue)
				$output .= "<option value='$key' $selected>$value</option>";
			else
				$output .= "<option value='$value' $selected>$value</option>";
		}
	
		return $output;
	}
	
	
	public function GetItemTraitValue($text)
	{
		global $ESO_ITEMTRAIT_TEXTS, $ESO_ITEMTRAIT10_TEXTS;
	
		$value = array_search($text, $ESO_ITEMTRAIT10_TEXTS);
	
		if ($value === FALSE) return -1;
		return $value;
	}
	
	
	public function GetItemQualityValue($text)
	{
		global $ESO_ITEMQUALITY_TEXTS;
	
		$value = array_search($text, $ESO_ITEMQUALITY_TEXTS);
		if ($value === FALSE) return -1;
	
		return $value;
	}
	
	
	public function GetItemLevelValue($text)
	{
		$result = preg_match("/v[0-9]+/i", $text);
		$result1 = preg_match("/cp[0-9]+/i", $text);
		$level = 0;
	
		if ($result)
		{
			$level = intval(substr($text, 1)) + 50;
		}
		elseif ($result1)
		{
			$level = floor(intval(substr($text, 2))/10) + 50;
		}
		else
		{
			$level = intval($text);
		}
	
		if ($level < 0)
			$level = 0;
		elseif ($level > 66)
			$level = 66;
					
		return $level;
	}
	
		
	public function GetItemTypeValue($text)
	{
		global $ESO_ITEMTYPE_TEXTS;
	
		$value = array_search($text, $ESO_ITEMTYPE_TEXTS);
		if ($value === FALSE) return -1;
	
		return $value;
	}
	
	
	public function GetEquipTypeValue($text)
	{
		global $ESO_ITEMEQUIPTYPE_TEXTS;
	
		$value = array_search($text, $ESO_ITEMEQUIPTYPE_TEXTS);
		if ($value === FALSE) return -1;
	
		return $value;
	}
	
	
	public function GetWeaponTypeValue($text)
	{
		global $ESO_ITEMWEAPONTYPE_TEXTS;
	
		$value = array_search($text, $ESO_ITEMWEAPONTYPE_TEXTS);
		if ($value === FALSE) return -1;
	
		return $value;
	}
	
	
	public function GetArmorTypeValue($text)
	{
		global $ESO_ITEMARMORTYPE_TEXTS;
	
		$value = array_search($text, $ESO_ITEMARMORTYPE_TEXTS);
		if ($value === FALSE) return -1;
	
		return $value;
	}
	
	
	public function GetOutputFormLevel()
	{
		$level = $this->GetItemLevelValue($this->formValues['level']);
		if ($level <= 0) return "";
	
		if ($level > 50)
		{
			if (UESP_SHOWCPLEVEL) return "CP" . (($level - 50)*10);
			return "v" . ($level - 50);
		}
	
		return (string) $level;
	}
	
	
	public function GetIconUrl($icon)
	{
		if ($icon == null || $icon == "") $icon = self::ESOVSD_ICON_UNKNOWN;
	
		$icon = preg_replace('/dds$/', 'png', $icon);
		$icon = preg_replace('/^\//', '', $icon);
	
		$iconLink = self::ESOVSD_ICON_URL . '/' . $icon;
		return $iconLink;
	}
	
	
	public function GetItemSearchResultsHtml()
	{
		if (!$this->hasSearchData) return "";
		
		$output  = "<table id='esovsd_searchresults_table' class='esovsd_itemresults'>";
		$output .= "<tr>";
		$output .= "<th>Item</th>";
		$output .= "<th>Level</th>";
		$output .= "<th>Trait</th>";
		$output .= "<th>Type</th>";
		$output .= "<th>Equip</th>";
		$output .= "<th>Armor</th>";
		$output .= "<th>Weapon</th>";
		$output .= "<th>Set Name</th>";
		$output .= "<th>#</th>";
		$output .= "<th>Avg Price</th>";
		$output .= "<th></th>";
		$output .= "</tr>";
				
		foreach ($this->itemSortedKeys as $itemId)
		{
			$item = $this->itemResults[$itemId];
			
			$iconURL = $this->Escape($this->GetIconUrl($item['icon']));
				
			$trait = $item['trait'];
			$itemName = $this->Escape($item['name']);
			$levelText = GetEsoItemLevelText($item['level']);
			$itemType = GetEsoItemTypeText($item['itemType']);
			$weaponType = GetEsoItemWeaponTypeText($item['weaponType']);
			$armorType = GetEsoItemArmorTypeText($item['armorType']);
			$equipType = GetEsoItemEquipTypeText($item['equipType']);
			$setName = $this->Escape($item['setName']);
			$totalSales = floatval($item['countSales']);
			$totalPurchases = floatval($item['countPurchases']);
			$sumSales = floatval($item['sumSales']);
			$sumPurchases = floatval($item['sumPurchases']);
			$totalCount = $totalSales + $totalPurchases;
			$totalItems = floatval($item['countItemPurchases']) + floatval($item['countItemSales']);
			$avgPrice = ($sumSales + $sumPurchases) / $totalItems;
			$goodPrice = floatval($item['goodPrice']);
			$extraQuery = "";
			
			if ($this->formValues['saletype'] == "sold")
			{
				$totalCount = $totalPurchases;
				$totalItems = floatval($item['countItemPurchases']);
				$avgPrice = $sumPurchases / $totalItems;
				$extraQuery .= "&saletype=sold";
				$goodPrice = floatval($item['goodSoldPrice']);
			}
			elseif ($this->formValues['saletype'] == "listed")
			{
				$totalCount = $totalSales;
				$totalItems = floatval($item['countItemSales']);
				$avgPrice = $sumSales / $totalItems;
				$extraQuery .= "&saletype=listed";
				$goodPrice = floatval($item['goodListPrice']);
			}
			
			if ($goodPrice > 0) $avgPrice = $goodPrice;
			
			if ($this->formValues['timeperiod'] > 0)
			{
				$extraQuery .= "&timeperiod=".intval($this->formValues['timeperiod']);
			}
						
			$totalCountText = $totalCount;
			if ($totalItems > $totalCount) $totalCountText = "$totalCount ($totalItems)"; 
			
			$avgPrice = $this->FormatPrice($avgPrice);
				
			$traitText = "";
			if ($trait > 0) $traitText = GetEsoItemTraitText($trait);
			
			$copyPriceTooltip = $this->CreateItemCopyPriceTooltip($avgPrice, $totalSales, $totalPurchases, 0, $totalItems, $item);
			$copyPriceHtml = "<div class='esovsd_copyprice esovsd_copypricesmall' copydata='$copyPriceTooltip'>Copy</div>";
			
			$extraData = $this->Escape($item['extraData']);
			
			$output .= "<tr>";
			$output .= "<td><div class='esovsd_itemlink eso_item_link eso_item_link_q{$item['quality']}' itemid='{$item['itemId']}' intlevel='{$item['internalLevel']}' inttype='{$item['internalSubType']}' potiondata='{$item['potionData']}' extradata='$extraData'>";
			$output .= "<img src='$iconURL' class='esovsd_itemicon'>$itemName</div></td>";
			$output .= "<td>$levelText</td>";
			$output .= "<td>$traitText</td>";
			$output .= "<td>$itemType</td>";
			$output .= "<td>$equipType</td>";
			$output .= "<td>$armorType</td>";
			$output .= "<td>$weaponType</td>";
			$output .= "<td>$setName</td>";
			$output .= "<td>$totalCountText</td>";
			$output .= "<td>$avgPrice gp$copyPriceHtml</td>";
			$output .= "<td><a href='?viewsales=$itemId$extraQuery'>View Item Sales</a></td>";
			$output .= "</tr>\n";
		}
		
		$output .= "</table>";
		return $output;
	}
	
	
	
	public function FormatTimeStamp ($timestamp)
	{
		if ($timestamp == null || $timestamp == "") return "";
		
		$tsValue = intval($timestamp);
		if ($tsValue <= 0) return "";
		
		if ($this->viewRawData) return "" . $timestamp;
		
		$now = time();
		$diff = $now - $tsValue;
		
		$days = floor($diff / 86400);
		$hourSeconds = $diff % 86400;
		$hours = floor($hourSeconds / 3600);
		$minuteSeconds = $hourSeconds % 3600;
		$minutes = floor ($hourSeconds / 60);
		$seconds = $minuteSeconds % 60;
		
		if ($days > 1)
			return "$days days ago";
		elseif ($days > 0)
			return "1 day ago";
		elseif ($hours > 1)
			return "$hours hours ago";
		elseif ($hours > 0)
			return "1 hour ago";
		elseif ($minutes > 1)
			return "$minutes mins ago";
		else
			return "1 minute ago";
		
	}
	
	
	public function GetSalesSearchResultsHtml()
	{
		$output  = "<table id='esovsd_searchresults_table' class='esovsd_salesresults'>";
		$output .= "<tr>";
		$output .= "<th>Guild</th>";
		$output .= "<th>Kiosk Location</th>";
		if (!$this->OMIT_SELLER_INFO) $output .= "<th>Seller</th>";
		if (!$this->OMIT_BUYER_INFO) $output .= "<th>Buyer</th>";
		$output .= "<th>Listed / Sold</th>";
		$output .= "<th>Last Seen</th>";
		$output .= "<th>Price</th>";
		$output .= "<th>Qnt</th>";
		$output .= "<th>Unit Price</th>";
		$output .= "</tr>";
		
		$outputCount = 0;
		
		foreach ($this->searchResults as $row)
		{
			++$outputCount;
			
			if ($outputCount > $this->MAX_SALES_RECORD_DISPLAYED) 
			{
				$moreRows = count($this->searchResults) - $outputCount; 
				$output .= "<tr class='esovsdMoreSalesHidden'><td colspan='7'>";
				$output .= "Not displaying $moreRows more sales for performance reasons...";
				$output .= "</td></tr>";
				break;
			}
			
			$guild = $this->guildData[$row['guildId']];
			$item = $this->itemResults[$row['itemId']];
			$buyDate = $this->FormatTimeStamp($row['buyTimestamp']);
			$listDate = $this->FormatTimeStamp($row['listTimestamp']);
			$lastSeen = $this->FormatTimeStamp($row['lastSeen']);
			$iconURL = $this->GetIconUrl($item['icon']);
			$unitPrice = number_format(floatval($row['price']) / floatval($row['qnt']), 2, ".", '');
			
			$kiosk = $guild['storeLocation'];
			if ($kiosk == "") $kiosk = "None";
	
			$lastStoreLocTime = $this->FormatTimeStamp($guild['lastStoreLocTime']);
			if ($lastStoreLocTime != "") $kiosk .= "<br/><small>updated " . $lastStoreLocTime . "</small>";
			
			$extraClass = "";
			if ($row['outlier'] === true) $extraClass = "esovsd_outlier";
			
			$output .= "<tr class='$extraClass'>";
			$output .= "<td>{$guild['name']}</td>";
			$output .= "<td>$kiosk</td>";
			if (!$this->OMIT_SELLER_INFO) $output .= "<td>{$row['sellerName']}</td>";
			if (!$this->OMIT_BUYER_INFO) $output .= "<td>{$row['buyerName']}</td>";
			
			if ($listDate != "")
				$output .= "<td>Listed {$listDate}</td>";
			else
				$output .= "<td>Sold {$buyDate}</td>";

			$output .= "<td>$lastSeen</td>";
			$output .= "<td>{$row['price']} gp</td>";
			$output .= "<td>{$row['qnt']}</td>";
			$output .= "<td>{$unitPrice} gp</td>";
			$output .= "</tr>\n";
		}
	
		$output .= "</table>";
		return $output;
	}
	
	
	public function GetSearchResultsHtml()
	{
		if ($this->viewSalesItemId > 0) return $this->GetSalesSearchResultsHtml();
		return $this->GetItemSearchResultsHtml();
	}
	
	
	public function GetFindItemLinkQuery($itemLinkData)
	{
		$itemId = intval($itemLinkData['itemId']);
		$intSubtype = intval($itemLinkData['subtype']);
		$intLevel = intval($itemLinkData['level']);
		$potionData = intval($itemLinkData['potionData']);
		$server = $this->db->real_escape_string($this->server);
		$extraData = "";
		
		if ($itemLinkData['writ1'] > 0)
		{
			$extraData = "{$itemLinkData['writ1']}:{$itemLinkData['writ2']}:{$itemLinkData['writ3']}:{$itemLinkData['writ4']}:{$itemLinkData['writ5']}:{$itemLinkData['writ6']}";
			$extraData = $this->db->real_escape_string($extraData);
		}
		
		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM items ";
		$where = array();
		
		$where[] = "server='$server'";
		$where[] = "itemId='$itemId'";
		$where[] = "internalLevel='$intLevel'";
		$where[] = "internalSubtype='$intSubtype'";		
		$where[] = "potionData='$potionData'";		
		$where[] = "extraData='$extraData'";
				
		if (count($where) > 0) $query .= "WHERE " . implode(" AND ", $where);
		$query .= " LIMIT " . $this->searchItemIdsLimit . ";";
		
		$this->itemQuery = $query;
		return $query;
	}
	
	
	public function GetSecondFindItemLinkQuery($itemLinkData)
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
		
		$itemId = intval($itemLinkData['itemId']);
		$intSubtype = intval($itemLinkData['subtype']);
		$intLevel = intval($itemLinkData['level']);
		$potionData = intval($itemLinkData['potionData']);
		$server = $this->db->real_escape_string($this->server);
		$extraData = "";
		
		if ($itemLinkData['writ1'] > 0)
		{
			$extraData = "{$itemLinkData['writ1']}:{$itemLinkData['writ2']}:{$itemLinkData['writ3']}:{$itemLinkData['writ4']}:{$itemLinkData['writ5']}:{$itemLinkData['writ6']}";
			$extraData = $this->db->real_escape_string($extraData);
		}
		
		$query = "SELECT level, quality FROM $uespEsoLogDatabase.minedItemSummary WHERE itemId='$itemId';";
		$result = $this->db->query($query);
		
		$level = null;
		$quality = null;
		
		if ($result !== false && $result->num_rows > 0)
		{
			$row = $result->fetch_assoc();
			$level = $row['level'];
			$quality = $row['quality'];
			
			if (strpos($level, "-") !== false || strpos($quality, "-") !== false) 
			{
				$level = null;
				$quality = null;
			}
		}
		
		if ($level === null || $quality === null)
		{
			$quality = GetEsoQualityFromIntType($intSubtype);
			$level = GetEsoLevelFromIntType($intSubtype);
			if ($level == 1) $level = $intLevel;
		}
		
		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM items ";
		$where = array();
	
		$where[] = "server='$server'";
		$where[] = "itemId='$itemId'";
		$where[] = "level='$level'";
		$where[] = "quality='$quality'";
		$where[] = "potionData='$potionData'";
		$where[] = "extraData='$extraData'";
	
		if (count($where) > 0) $query .= "WHERE " . implode(" AND ", $where);
		$query .= " LIMIT " . $this->searchItemIdsLimit . ";";
		
		$this->itemQuery = $query;
		return $query;
	}
	
	
	public function GetSecondFindItemQuery()
	{
		$itemLinkData = $this->ParseItemLink($this->formValues['text']);
		if ($itemLinkData) return $this->GetSecondFindItemLinkQuery($itemLinkData);
		
		return "";
	}
	
	
	public function GetFindItemQuery()
	{
		$itemLinkData = $this->ParseItemLink($this->formValues['text']);
		if ($itemLinkData) return $this->GetFindItemLinkQuery($itemLinkData);
		
		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM items ";
		$where = array();

		$where[] = "server='".$this->server."'";
		
		$itemIdValue = intval($this->formValues['text']);
		if ($itemIdValue > 0) $where[] = "itemId='$itemIdValue'";
		
		if ($this->finalItemLevel   >= 1) $where[] = "level='{$this->finalItemLevel}'";
		if ($this->finalItemQuality >= 0) $where[] = "quality='{$this->finalItemQuality}'";
		
		$traitValue = $this->GetItemTraitValue($this->formValues['trait']);
		
		if ($traitValue > 0) 
		{
			if ($traitValue == 20 || $traitValue == 9)
				$where[] = "(trait=9 OR trait=20)";
			elseif ($traitValue == 19 || $traitValue == 24 || $traitValue == 10)
				$where[] = "(trait=10 OR trait=19 or trait=24)";
			elseif ($traitValue == 6 || $traitValue == 15)
				$where[] = "(trait=6 OR trait=15)";
			elseif ($traitValue == 25 || $traitValue == 26)
				$where[] = "(trait=25 OR trait=26)";
			else
			{
				$safeValue = $this->db->real_escape_string($traitValue);
				$where[] = "trait='$safeValue'";
			}
		}
		
		$equipTypeValue = $this->GetEquipTypeValue($this->formValues['equiptype']);
		if ($equipTypeValue > 0) $where[] = "equipType='$equipTypeValue'";
		
		$itemTypeValue = $this->GetItemTypeValue($this->formValues['itemtype']);
		if ($itemTypeValue > 0) $where[] = "itemType='$itemTypeValue'";
		
		$weaponTypeValue = $this->GetWeaponTypeValue($this->formValues['weapontype']);
		if ($weaponTypeValue > 0) $where[] = "weaponType='$weaponTypeValue'";
		
		$armorTypeValue = $this->GetArmorTypeValue($this->formValues['armortype']);
		if ($armorTypeValue > 0) $where[] = "armorType='$armorTypeValue'";
		
		if ($this->formValues['text'] != "" && $itemIdValue <= 0)
		{
			$safeText = $this->db->real_escape_string($this->formValues['text']);
			//$where[] = "MATCH(name, setName) AGAINST ('$safeText' in BOOLEAN MODE)";
			$where[] = "(name LIKE '%$safeText%' or setName LIKE '%$safeText%')";
		}
		
		$timePeriod = intval($this->formValues['timeperiod']);
		$timestamp = time() - $timePeriod;
				
		if ($this->formValues['saletype'] == "sold")
		{
			$where[] = "countPurchases > 0";
			if ($timePeriod > 0) $where[] = "lastPurchaseTimestamp >= $timestamp";
		}
		elseif ($this->formValues['saletype'] == "listed")
		{
			$where[] = "countSales > 0";
			$minTimestamp = $timestamp - $this->ESOVSD_MAX_LISTING_TIME;
			if ($timePeriod > 0) $where[] = "(lastSaleTimestamp > $minTimestamp AND lastSeen >= $timestamp)";
		}
		elseif ($timePeriod > 0)
		{
			$minTimestamp = $timestamp - $this->ESOVSD_MAX_LISTING_TIME;
			$where[] = "(lastPurchaseTimestamp >= $timestamp OR (lastSaleTimestamp > $minTimestamp AND lastSeen >= $timestamp))";
		}
		
		if (count($where) > 0) $query .= "WHERE " . implode(" AND ", $where);
		$query .= " LIMIT " . $this->searchItemIdsLimit . ";";
		
		$this->itemQuery = $query;
		return $query;
	}
	
	
	public function LoadTotalRecordCounts()
	{
		$this->lastQuery = "SELECT COUNT(*) as count FROM items;";
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return false;
		
		$row = $result->fetch_assoc();
		$this->allItemsCount = $row['count'];
		
		$this->lastQuery = "SELECT COUNT(*) as count FROM sales;";
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return false;
		
		$row = $result->fetch_assoc();
		$this->allSalesCount = $row['count'];
		
		return true;
	}
	
	
	public function LoadItemData()
	{		
		$this->totalItemCount = 0;
		$this->totalListedCount = 0;
		$this->totalSoldCount = 0;
		$this->totalSalesCount = 0;
		$this->itemCount = 0;
		
		if (!$this->hasSearchData) return $this->LoadTotalRecordCounts();
		
		$this->lastQuery = $this->GetFindItemQuery();
		
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return false;
		
		if ($result->num_rows == 0)
		{
			$secondQuery = $this->GetSecondFindItemQuery();
			
			if ($secondQuery != "")
			{
				$this->lastQuery = $secondQuery;
				
				$result = $this->db->query($this->lastQuery);
				if ($result === false) return false;
				
				if ($result->num_rows == 0)
				{
					//$this->errorMessages[] = "No items found matching input search!";
					return false;
				}
			}
		}
		
		$this->totalItemCount = $result->num_rows;
		$this->itemCount = $result->num_rows;
		
		while (($row = $result->fetch_assoc()))
		{
			$this->itemResults[$row['id']] = $row;
			$this->itemIds[] = $row['id'];
			
			$this->totalListedCount += $row['countSales'];
			$this->totalSoldCount   += $row['countPurchases'];
		}
		
		$this->totalSalesCount = $this->totalListedCount + $this->totalSoldCount;
		
		if ($result->num_rows >= $this->searchItemIdsLimit)
		{
			$result = $this->db->query("SELECT FOUND_ROWS() as rowCount;");
			
			if ($result)
			{
				$row = $result->fetch_assoc();
				$totalItems = $row['rowCount'];
				$this->totalItemCount = $totalItems;
				//$this->errorMessages[] = "Found $totalItems matching items which exceeds the maximum of {$this->searchItemIdsLimit}.";
			}
		}
		
		$this->SortItemSearchResults();
		return true;
	}
	
	
	public function LoadGuilds()
	{
		$this->lastQuery = "SELECT * FROM guilds;";
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->ReportError("Failed to load guild data!");

		while (($row = $result->fetch_assoc()))
		{
			$this->guildData[$row['id']] = $row;
		}
		
		return true;
	}
	
	
	public function GetSearchQuery()
	{
		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM sales ";
		$where = array();
		
		$where[] = "server='".$this->server."'";
		
		if (count($this->itemIds) <= 0) 
			$where[] = "0"; 
		else
			$where[] = "itemId IN (" . implode(",", $this->itemIds) . ")";
		
		$timePeriod = intval($this->formValues['timeperiod']);
		
		if ($timePeriod > 0) 
		{
			$timestamp = time() - $timePeriod;
			$minTimestamp = time() - $this->ESOVSD_MAX_LISTING_TIME;
			$where[] = "(buyTimestamp >= $timestamp OR (listTimestamp > $minTimestamp AND lastSeen >= $timestamp))";
		}
				
		if ($this->formValues['saletype'] == "sold")
		{
			$where[] = "buyTimestamp > 0";
		}
		elseif ($this->formValues['saletype'] == "listed")
		{
			$where[] = "listTimestamp > 0";
		}
		
		if (count($where) > 0)
		{
			$query .= " WHERE " . implode(" AND ", $where);
		}
		
		$query .= " LIMIT {$this->searchLimitCount};";
		
		$this->salesQuery = $query;
		return $query;
	}
	
	
	public function GetSingleSearchQuery()
	{
		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM sales ";
		$where = array();
	
		$where[] = "itemId='{$this->viewSalesItemId}'";
		
		$timePeriod = intval($this->formValues['timeperiod']);

		if ($timePeriod > 0)
		{
			$timestamp = time() - $timePeriod;
			$minTimestamp = time() - $this->ESOVSD_MAX_LISTING_TIME;
			$where[] = "(buyTimestamp >= $timestamp or (listTimestamp > $minTimestamp AND lastSeen >= $timestamp))";
		}
		
		if ($this->formValues['saletype'] == "sold")
		{
			$where[] = "buyTimestamp > 0";
		}
		elseif ($this->formValues['saletype'] == "listed")
		{
			$where[] = "listTimestamp > 0";
		}

		if (count($where) > 0)
		{
			$query .= " WHERE " . implode(" AND ", $where);
		}

		//$query .= " LIMIT {$this->searchLimitCount};";
		//$query .= " ORDER BY buyTimestamp DESC;";
		$query .= ";";

		$this->salesQuery = $query;
		return $query;
	}
		
	
	public function LoadSalesSearchResults($loadSingle = false)
	{
		if ($loadSingle)
			$this->lastQuery = $this->GetSingleSearchQuery();
		else
			$this->lastQuery = $this->GetSearchQuery();
		
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->ReportError("Failed to search for sales data!");
		
		$this->searchCount = $result->num_rows;
		$this->totalSalesCount = $this->searchCount;
		
		if ($this->searchCount == 0)
		{
			$this->errorMessages[] = "No matching sales found!";			
			return true;
		}
		
		while (($row = $result->fetch_assoc()))
		{
			$row['unitPrice'] = $row['price'] / $row['qnt'];
			if ($row['buyTimestamp']  > 0) $row['timestamp'] = $row['buyTimestamp'];
			if ($row['listTimestamp'] > 0) $row['timestamp'] = $row['listTimestamp'];
				
			$row['itemName'] = $this->itemResults[$row['itemId']]['name'];
			$this->searchResults[] = $row;
			
			$this->server = $row['server'];
		}
		
		$this->displayServer = $this->server;
		$this->salesData->server = $this->server;
		
		if ($result->num_rows >= $this->searchLimitCount)
		{
			$result = $this->db->query("SELECT FOUND_ROWS() as rowCount;");
				
			if ($result)
			{
				$row = $result->fetch_assoc();
				$totalSales = $row['rowCount'];
				$this->totalSalesCount = $totalSales;
				$this->errorMessages[] = "Displaying {$this->searchLimitCount} out of $totalSales matching sales for {$this->itemCount} items.";
			}
		}
		else
		{
			$this->errorMessages[] = "Found {$this->totalSalesCount} matching sales for {$this->itemCount} items.";
		}
				
		$this->SortSalesSearchResults();
		return true;
	}
	
		
	public function LoadSingleItemData()
	{
		$this->lastQuery = "SELECT * FROM items WHERE id={$this->viewSalesItemId};";
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->ReportError("Failed to load single item data!");
		
		if ($result->num_rows == 0) return $this->ReportError("Failed to load single item data (ID = {$this->viewSalesItemId})!");
		$row = $result->fetch_assoc();
		
		$this->itemResults[$row['id']] = $row;
		$this->itemIds[] = $row['id'];
		$this->singleItemData = $row;
		
		$this->goodPriceAll = $row['goodPrice'];
		$this->goodPriceListed = $row['goodListPrice'];
		$this->goodPriceSold = $row['goodSoldPrice'];
		
		$this->server = $row['server'];
		$this->displayServer = $this->server;
		$this->salesData->server = $this->server;
		
		$this->itemCount = 1;
		
		return true;
	}
	
	
	public function ComputeSaleStatistics()
	{
		$priceSumAll = 0;
		$countAll = 0;
		$countSold = 0;
		$countListed = 0;
		$countItemsAll = 0;
		$countItemsSold = 0;
		$countItemsListed = 0;
		$priceSumSold = 0;
		$priceSumListed = 0;
		$lastListTimestamp = time();
		$lastSoldTimestamp = $lastListTimestamp;
		
		$weightSumAll = 0;
		$weightSumSold = 0;
		$weightSumListed = 0;
		$weightItemsAll = 0;
		$weightItemsSold = 0;
		$weightItemsListed = 0;
		$currentTimestamp = time();
		
		foreach ($this->searchResults as $result)
		{
			$price = intval($result['price']);
			$qnt = intval($result['qnt']);
			
			if ($result['buyTimestamp'] > 0)
			{
				$priceSumSold += $price;
				$countItemsSold += $qnt;
				++$countSold;
				
				if ($result['buyTimestamp'] < $lastSoldTimestamp) $lastSoldTimestamp = intval($result['buyTimestamp']);
				
				$days = ($currentTimestamp - $result['buyTimestamp'])/86400;
				$weight = self::ESOVSD_WEIGHTED_CONSTANT / $days;
				if ($weight > 1) $weight = 1;
				if ($weight < 0) $weight = 0;
				
				$weightSumSold += $price * $weight;
				$weightItemsSold += $qnt * $weight;
				
				$weightSumAll += $price * $weight;
				$weightItemsAll += $qnt * $weight;
			}
			
			if ($result['listTimestamp'] > 0)
			{
				$priceSumListed += $price;
				$countItemsListed += $qnt;
				++$countListed;
				
				if ($result['listTimestamp'] < $lastListTimestamp) $lastListTimestamp = intval($result['listTimestamp']);
				
				$days = ($currentTimestamp - $result['listTimestamp'])/86400;
				$weight = self::ESOVSD_WEIGHTED_CONSTANT / $days;
				if ($weight > 1) $weight = 1;
				if ($weight < 0) $weight = 0;
				
				$weightSumListed += $price * $weight;
				$weightItemsListed += $qnt * $weight;
				
				$weightSumAll += $price * $weight;
				$weightItemsAll += $qnt * $weight;
			}
			
			$priceSumAll += $price;
			$countItemsAll += $qnt;
			++$countAll;
			
		}
		
		$this->salePriceAverageAll = 0;
		$this->salePriceAverageCountAll = 0;
		$this->salePriceAverageItemsAll = 0;
		
		$this->salePriceAverageSold = 0;
		$this->salePriceAverageCountSold = 0;
		$this->salePriceAverageItemsSold = 0;
		
		$this->salePriceAverageListed = 0;
		$this->salePriceAverageCountListed = 0;
		$this->salePriceAverageItemsListed = 0;
		
		$this->salePriceWeightAll = 0;
		$this->salePriceWeightItemsAll = 0;
		$this->salePriceWeightSold = 0;
		$this->salePriceWeightItemsSold = 0;
		$this->salePriceWeightListed = 0;
		$this->salePriceWeightItemsListed = 0;
		
		if ($countItemsAll > 0)
		{
			$this->salePriceAverageAll = floatval($priceSumAll) / $countItemsAll;
			$this->salePriceAverageCountAll = $countAll;
			$this->salePriceAverageItemsAll = $countItemsAll;
			
			$this->salePriceWeightAll = floatval($weightSumAll) / $weightItemsAll;
			$this->salePriceWeightItemsAll = $weightItemsAll;
		}
		
		if ($countItemsSold > 0)
		{
			$this->salePriceAverageSold = floatval($priceSumSold) / $countItemsSold;
			$this->salePriceAverageCountSold = $countSold;
			$this->salePriceAverageItemsSold = $countItemsSold;
			
			$this->salePriceWeightSold = floatval($weightSumSold) / $weightItemsSold;
			$this->salePriceWeightItemsSold = $weightItemsSold;
		}
		
		if ($countItemsListed > 0)
		{
			$this->salePriceAverageListed = floatval($priceSumListed) / $countItemsListed;
			$this->salePriceAverageCountListed = $countListed;
			$this->salePriceAverageItemsListed = $countItemsListed;
			
			$this->salePriceWeightListed = floatval($weightSumListed) / $weightItemsListed;
			$this->salePriceWeightItemsListed = $weightItemsListed;
		}
		
		$this->salePriceAverageLastTimestampListed = $lastListTimestamp;
		$this->salePriceAverageLastTimestampSold = $lastSoldTimestamp;
		$this->salePriceAverageLastTimestampAll = min($lastSoldTimestamp, $lastListTimestamp);
		
		$this->ComputeSaleAdvancedStatistics();
		$this->ComputeItemGoodPrice();
	}	
	
	
	public function ComputeSaleAdvancedStatistics()
	{
		$currentTimestamp = time();
		$sumSquareAll = 0;
		$sumSquareListed = 0;
		$sumSquareSold = 0;
		$sumWeightAll = 0;
		$sumWeightListed = 0;
		$sumWeightSold = 0;
		$sumWeightCountAll = 0;
		$sumWeightCountListed = 0;
		$sumWeightCountSold = 0;
		
		foreach ($this->searchResults as $result)
		{
			$price = intval($result['price']);
			$qnt = intval($result['qnt']);
			$unitPrice = $price / (float) $qnt;
			
			$sumSquareAll += pow($unitPrice - $this->salePriceAverageAll, 2);
			
			if ($result['buyTimestamp']  > 0) 
			{
				$sumSquareSold += pow($unitPrice - $this->salePriceAverageSold, 2);
				
				$days = ($currentTimestamp - $result['buyTimestamp'])/86400;
				$weight = self::ESOVSD_WEIGHTED_CONSTANT / $days;
				if ($weight > 1) $weight = 1;
				if ($weight < 0) $weight = 0;
	
				$sumWeightAll += pow($unitPrice - $this->salePriceWeightAll, 2) * $weight;
				$sumWeightSold += pow($unitPrice - $this->salePriceWeightSold, 2) * $weight;
				$sumWeightCountAll += 1 * $weight;
				$sumWeightCountSold += 1 * $weight;
			}
			
			if ($result['listTimestamp'] > 0) 
			{
				$sumSquareListed += pow($unitPrice - $this->salePriceAverageListed, 2);
				
				$days = ($currentTimestamp - $result['listTimestamp'])/86400;
				$weight = self::ESOVSD_WEIGHTED_CONSTANT / $days;
				if ($weight > 1) $weight = 1;
				if ($weight < 0) $weight = 0;
				
				$sumWeightAll += pow($unitPrice - $this->salePriceWeightAll, 2) * $weight;
				$sumWeightListed += pow($unitPrice - $this->salePriceWeightListed, 2) * $weight;
				$sumWeightCountAll += 1 * $weight;
				$sumWeightCountListed += 1 * $weight;
			}
		}
		
		$this->salePriceStdDevAll = 0;
		$this->salePriceStdDevListed = 0;
		$this->salePriceStdDevSold = 0;
		$this->salePriceStdDevWeightAll = 0;
		$this->salePriceStdDevWeightListed = 0;
		$this->salePriceStdDevWeightSold = 0;
		
		if ($this->salePriceAverageCountAll > 0) 
		{
			$this->salePriceStdDevAll = sqrt($sumSquareAll / floatval($this->salePriceAverageCountAll));
			$this->salePriceStdDevWeightAll = sqrt($sumWeightAll / $sumWeightCountAll);
		}
		
		if ($this->salePriceAverageCountSold > 0) 
		{
			$this->salePriceStdDevSold = sqrt($sumSquareSold / floatval($this->salePriceAverageCountSold));
			$this->salePriceStdDevWeightSold = sqrt($sumWeightSold / $sumWeightCountSold);
		}
		
		if ($this->salePriceAverageCountListed > 0)	
		{
			$this->salePriceStdDevListed = sqrt($sumSquareListed / floatval($this->salePriceAverageCountListed));
			$this->salePriceStdDevWeightListed = sqrt($sumWeightListed / $sumWeightCountListed);
		}
		
		$sumAdjAll = 0;
		$sumAdjListed = 0;
		$sumAdjSold = 0;
		$countAdjAll = 0;
		$countAdjListed = 0;
		$countAdjSold = 0;
		$itemsAdjAll = 0;
		$itemsAdjListed = 0;
		$itemsAdjSold = 0;
		$zScoreAll = 1;
		$zScoreSold = 1;
		$zScoreListed = 1;
		$zScoreWeightAll = 1;
		$zScoreWeightSold = 1;
		$zScoreWeightListed = 1;
		$sumAdjWeightAll = 0;
		$sumAdjWeightListed = 0;
		$sumAdjWeightSold = 0;
		$countAdjWeightAll = 0;
		$countAdjWeightListed = 0;
		$countAdjWeightSold = 0;
		$itemsAdjWeightAll = 0;
		$itemsAdjWeightListed = 0;
		$itemsAdjWeightSold = 0;
				
		foreach ($this->searchResults as &$result)
		{
			$price = intval($result['price']);
			$qnt = intval($result['qnt']);
			$unitPrice = $price / (float) $qnt;
			
			if ($this->salePriceStdDevAll != 0) $zScoreAll = abs(($unitPrice - $this->salePriceAverageAll) / $this->salePriceStdDevAll);
			if ($this->salePriceStdDevWeightAll != 0) $zScoreWeightAll = abs(($unitPrice - $this->salePriceWeightAll) / $this->salePriceStdDevWeightAll);

			if ($zScoreAll <= self::ESOVSD_MAXZSCORE)
			{
				$sumAdjAll += $price;
				$itemsAdjAll += $qnt;
				++$countAdjAll;
			}			
			else
			{
				$result['outlier'] = true;
			}
						
			if ($result['buyTimestamp']  > 0) 
			{
				if ($this->salePriceStdDevSold != 0) $zScoreSold = abs(($unitPrice - $this->salePriceAverageSold) / $this->salePriceStdDevSold);
				if ($this->salePriceStdDevWeightSold != 0) $zScoreWeightSold = abs(($unitPrice - $this->salePriceWeightSold) / $this->salePriceStdDevWeightSold);
				
				$days = ($currentTimestamp - $result['buyTimestamp'])/86400;
				$weight = self::ESOVSD_WEIGHTED_CONSTANT / $days;
				if ($weight > 1) $weight = 1;
				if ($weight < 0) $weight = 0;
				
				if ($zScoreSold <= self::ESOVSD_MAXZSCORE)
				{
					$sumAdjSold += $price;
					$itemsAdjSold += $qnt;
					++$countAdjSold;
				}
				else
				{
					$result['outlier'] = true;
				}
				
				if ($zScoreWeightAll <= self::ESOVSD_MAXZSCORE_WEIGHTED)
				{
					$sumAdjWeightAll += $price * $weight;
					$itemsAdjWeightAll += $qnt * $weight;
					++$countAdjWeightAll;
				}
				else
				{
					$result['outlier'] = true;
				}
				
				if ($zScoreWeightSold <= self::ESOVSD_MAXZSCORE_WEIGHTED)
				{
					$sumAdjWeightSold += $price * $weight;
					$itemsAdjWeightSold += $qnt * $weight;
					++$countAdjWeightSold;
				}
				else
				{
					$result['outlier'] = true;
				}
					
			}
			
			if ($result['listTimestamp'] > 0) 
			{
				if ($this->salePriceStdDevListed != 0) $zScoreList = abs(($unitPrice - $this->salePriceAverageListed) / $this->salePriceStdDevListed);
				if ($this->salePriceStdDevWeightListed != 0) $zScoreWeightListed = abs(($unitPrice - $this->salePriceWeightListed) / $this->salePriceStdDevWeightListed);
				
				$days = ($currentTimestamp - $result['listTimestamp'])/86400;
				$weight = self::ESOVSD_WEIGHTED_CONSTANT / $days;
				if ($weight > 1) $weight = 1;
				if ($weight < 0) $weight = 0;
				
				if ($zScoreList <= self::ESOVSD_MAXZSCORE)
				{
					$sumAdjListed += $price;
					$itemsAdjListed += $qnt;
					++$countAdjListed;
				}
				else
				{
					$result['outlier'] = true;
				}
				
				if ($zScoreWeightAll <= self::ESOVSD_MAXZSCORE_WEIGHTED)
				{
					$sumAdjWeightAll += $price * $weight;
					$itemsAdjWeightAll += $qnt * $weight;
					++$countAdjWeightAll;
				}
				else
				{
					$result['outlier'] = true;
				}
				
				if ($zScoreWeightListed <= self::ESOVSD_MAXZSCORE_WEIGHTED)
				{
					$sumAdjWeightListed += $price * $weight;
					$itemsAdjWeightListed += $qnt * $weight;
					++$countAdjWeightListed;
				}
				else
				{
					$result['outlier'] = true;
				}
			}
		}
		
		$this->salePriceAdjCountAll = 0;
		$this->salePriceAdjCountSold = 0;
		$this->salePriceAdjCountListed = 0;
		$this->salePriceAdjItemsAll = 0;
		$this->salePriceAdjItemsSold = 0;
		$this->salePriceAdjItemsListed = 0;
		$this->salePriceAdjAll = 0;
		$this->salePriceAdjSold = 0;
		$this->salePriceAdjListed = 0;
		$this->salePriceAdjWeightCountAll = 0;
		$this->salePriceAdjWeightCountSold = 0;
		$this->salePriceAdjWeightCountListed = 0;
		$this->salePriceAdjWeightItemsAll = 0;
		$this->salePriceAdjWeightItemsSold = 0;
		$this->salePriceAdjWeightItemsListed = 0;
		$this->salePriceAdjWeightAll = 0;
		$this->salePriceAdjWeightSold = 0;
		$this->salePriceAdjWeightListed = 0;
		
		if ($countAdjAll > 0)
		{
			$this->salePriceAdjAll = $sumAdjAll / (float) $itemsAdjAll;
			$this->salePriceAdjItemsAll = $itemsAdjAll;
			$this->salePriceAdjCountAll = $countAdjAll;
			
			$this->salePriceAdjWeightAll = $sumAdjWeightAll / (float) $itemsAdjWeightAll;
			$this->salePriceAdjWeightItemsAll = $itemsAdjWeightAll;
			$this->salePriceAdjWeightCountAll = $countAdjWeightAll;
		}
		
		if ($countAdjSold > 0)
		{
			$this->salePriceAdjSold = $sumAdjSold / (float) $itemsAdjSold;
			$this->salePriceAdjItemsSold = $itemsAdjSold;
			$this->salePriceAdjCountSold = $countAdjSold;
			
			$this->salePriceAdjWeightSold = $sumAdjWeightSold / (float) $itemsAdjWeightSold;
			$this->salePriceAdjWeightItemsSold = $itemsAdjWeightSold;
			$this->salePriceAdjWeightCountSold = $countAdjWeightSold;
		}
		
		if ($countAdjListed > 0)
		{
			$this->salePriceAdjListed = $sumAdjListed / (float) $itemsAdjListed;
			$this->salePriceAdjItemsListed = $itemsAdjListed;
			$this->salePriceAdjCountListed = $countAdjListed;
			
			$this->salePriceAdjWeightListed = $sumAdjWeightListed / (float) $itemsAdjWeightListed;
			$this->salePriceAdjWeightItemsListed = $itemsAdjWeightListed;
			$this->salePriceAdjWeightCountListed = $countAdjWeightListed;
		}
		
	}
	
	
	public function SalesDataSortTimestamp($a, $b)
	{
		return $b['timestamp'] - $a['timestamp'];
	}
	
	
	public function SalesDataSortListTimestamp($a, $b)
	{
		return $b['listTimestamp'] - $a['listTimestamp'];
	}
	
	
	public function SalesDataSortSoldTimestamp($a, $b)
	{
		return $b['buyTimestamp'] - $a['buyTimestamp'];
	}
	
	
	public function ComputeItemGoodPrice()
	{
		$soldData = array();
		$listData = array();
		$validSalesData = array();
	
		foreach ($this->searchResults as $sale)
		{
			if ($sale['outlier'] === true) continue;
	
			$validSalesData[] = $sale;
			if ($sale['listTimestamp'] > 0) $listData[] = $sale;
			if ($sale['buyTimestamp'] > 0) $soldData[] = $sale;
		}
		
		if (count($validSalesData) == 0)
		{
			foreach ($this->searchResults as $sale)
			{
				$validSalesData[] = $sale;
				if ($sale['listTimestamp'] > 0) $listData[] = $sale;
				if ($sale['buyTimestamp'] > 0) $soldData[] = $sale;
			}
		}
	
		usort($validSalesData, array('EsoViewSalesData', 'SalesDataSortTimestamp'));
		$this->singleItemData['goodPrice'] = $this->ComputeWeightedAverage($validSalesData);
			
		usort($soldData, array('EsoViewSalesData', 'SalesDataSortSoldTimestamp'));
		$this->singleItemData['goodSoldPrice'] = $this->ComputeWeightedAverage($soldData);
	
		usort($listData, array('EsoViewSalesData', 'SalesDataSortListTimestamp'));
		$this->singleItemData['goodListPrice'] = $this->ComputeWeightedAverage($listData);
		
		$this->goodPriceAll = $this->singleItemData['goodPrice'];
		$this->goodPriceListed = $this->singleItemData['goodListPrice'];
		$this->goodPriceSold = $this->singleItemData['goodSoldPrice'];
	
		return true;
	}
	
	
	public function ComputeWeightedAverage($salesData)
	{
		$numPoints = intval(count($salesData) / self::WEIGHTED_AVERAGE_BUCKETS);
		if ($numPoints < self::MIN_WEIGHTED_AVERAGE_INTERVAL) $numPoints = self::MIN_WEIGHTED_AVERAGE_INTERVAL;
	
		$sum = 0;
		$count = 0;
		$i = 0;
	
		while ($i < count($salesData) && $count < $numPoints)
		{
			$data = $salesData[$i];
			++$i;
				
			$sum += $data['unitPrice'];
				
			$day = round((time() - $data['timestamp']) / 86400, 2);
			$day = $data['timestamp'];
	
			++$count;
		}
	
		if ($count == 0) return 0;
		return $sum / $count;
	}
	
	
	public function LoadSearchResults()
	{
		
		if ($this->viewSalesItemId > 0)
		{
			$this->LoadSingleItemData();
			$this->LoadSalesSearchResults(true);
			$this->ComputeSaleStatistics();
		}
		elseif ($this->showForm == "ViewGuilds")
		{
			
		}
		elseif ($this->showForm == "ItemSearch")
		{
			$this->LoadItemData();
			
			if ($this->itemCount == 1)
			{
				$this->showForm = "ViewSales";
				$this->viewSalesItemId = $this->itemIds[0];
				$this->singleItemData = $this->itemResults[$this->viewSalesItemId];
				
				$this->LoadSalesSearchResults(true);
				$this->ComputeSaleStatistics();
				$this->LoadTemplate();
			}
		}
		else
		{
			$this->LoadTotalRecordCounts();
		}
		
	}
	
	
	public function SortItemSearchResults()
	{
		global $g_EsoSalesDataSortOrder, $g_EsoItemData;
		
		$g_EsoItemData = $this->itemResults;
		$g_EsoSalesDataSortOrder = $this->sortOrder;
		
		$this->itemSortedKeys = array_keys($this->itemResults);
	
		if ($this->sortField == "") return true;
	
		if ($this->sortField == "itemname")
		{
			usort($this->itemSortedKeys, "EsoItemDataCompareItemName");
		}
		
		return true;
	}
	
	
	public function SortSalesSearchResults()
	{
		global $g_EsoSalesDataSortOrder;
		
		$g_EsoSalesDataSortOrder = $this->sortOrder;
		if ($this->sortField == "") return true;
		
		if ($this->sortField == "itemname")
			usort($this->searchResults, "EsoSalesDataCompareItemName");
		elseif ($this->sortField == "buydate")
			usort($this->searchResults, "EsoSalesDataCompareBuyDate");
		elseif ($this->sortField == "listdate")
			usort($this->searchResults, "EsoSalesDataCompareListDate");
		elseif ($this->sortField == "price")
			usort($this->searchResults, "EsoSalesDataComparePrice");
		elseif ($this->sortField == "qnt")
			usort($this->searchResults, "EsoSalesDataCompareQnt");
		elseif ($this->sortField == "unitprice")
			usort($this->searchResults, "EsoSalesDataCompareUnitPrice");
		elseif ($this->sortField == "lastseen")
			usort($this->searchResults, "EsoSalesDataCompareLastSeen");
		
		return true;
	}
		
	
	public function GetErrorMessagesHtml()
	{
		if (count($this->errorMessages) <= 0) return "";
		$output = "<div id='esovsd_errormessages'>";
		
		foreach ($this->errorMessages as $errorMsg)
		{
			$errorMsg = $this->Escape($errorMsg);
			$output .= "<div class='esovsd_errormsg'>$errorMsg</div>";	
		}		
		
		$output .= "</div>";
		return $output;
	}
	
	
	public function ParseItemLink ($itemLink)
	{
		return ParseEsoItemLink($itemLink);
	}
	
	
	public function GetSalesItemLinkHtml()
	{
		$output = "";
		if ($this->singleItemData == null) return "";
		
		$item = $this->singleItemData;
		$iconURL = $this->GetIconUrl($item['icon']);
		
		$quality = intval($item['quality']);
		$itemId = intval($item['itemId']);
		$internalLevel = intval($item['internalLevel']);	
		$internalSubType = intval($item['internalSubType']);
		$potionData = intval($item['potionData']);
		$extraData = $this->Escape($item['extraData']);
		$itemName = $this->Escape($item['name']);
		
		$output .= "<div class='esovsd_itemlink eso_item_link eso_item_link_q{$quality}' itemid='{$itemId}' intlevel='{$internalLevel}' inttype='{$internalSubType}' potiondata='{$potionData}' extradata='{$extraData}'>";
		$output .= "<img src='$iconURL' class='esovsd_itemicon'>{$itemName}</div>";
		
		return $output;
	}
	
	
	public function GetServerHtml()
	{
		return $this->displayServer;
	}
	
	
	public function GetGuildResultsHtml()
	{
		$output  = "<table id='esovsd_guildresults_table'>";
		$output .= "<tr>";
		$output .= "<th>Guild</th>";
		$output .= "<th>Server</th>";
		$output .= "<th>Members</th>";
		$output .= "<th>Founder</th>";
		$output .= "<th>Founded Date</th>";
		$output .= "<th>Kiosk Location</th>";
		$output .= "<th>Listed / Sold</th>";
		$output .= "</tr>";
	
		foreach ($this->guildData as $key => $guild)
		{
			$name = $this->Escape($guild['name']);
			$server = $this->Escape($guild['server']);
			$numMembers = $this->Escape($guild['numMembers']);
			$founder = $this->Escape($guild['leader']);
			$numSales = $this->Escape($guild['totalSales']);
			$numPurchases = $this->Escape($guild['totalPurchases']);
			
			if ($guild['foundedDate'] <= 0) 
				$foundedDate = "";
			else
				$foundedDate = date('Y-m-d', $guild['foundedDate']);
			
			$storeLoc = $this->Escape($guild['storeLocation']);
			if ($storeLoc == "") $storeLoc = "None";
			
			$lastStoreLocTime = $this->FormatTimeStamp($guild['lastStoreLocTime']);
			if ($lastStoreLocTime != "") $lastStoreLocTime = "<small>updated " . $lastStoreLocTime . "</small>";
						
			$output .= "<tr>";
			
			$output .= "<td>$name</td>";
			$output .= "<td>$server</td>";
			$output .= "<td>$numMembers</td>";
			$output .= "<td>$founder</td>";
			$output .= "<td>$foundedDate</td>";
			$output .= "<td>$storeLoc<br/>$lastStoreLocTime</td>";
			$output .= "<td>$numSales / $numPurchases</td>";
			
			$output .= "</tr>\n";
		}
		
		$output .= "</table>";
				
		return $output;
	}
	
	
	public function CreateItemCopyPriceTooltip($price, $saleCount, $purchaseCount, $lastTimestamp, $totalItems, $itemData)
	{
		$copyData = "UESP price (";
		$price = $this->FormatPrice($price);
		$itemId = $itemData['itemId'];
		$itemLevel = $itemData['internalLevel'];
		$itemSubType = $itemData['internalSubType'];
		$extraData = $itemData['extraData'];
		$potionData = $itemData['potionData'];
		
		if ($extraData == "") $extraData = "0:0:0:0:0:0";
		
		if ($saleCount > 0) 
		{
			if ($saleCount >= 1000)
				$copyData .= floor($saleCount/1000) . "k listed";
			else
				$copyData .= "$saleCount listed";
			
			if ($purchaseCount > 0) $copyData .= ", ";
		}
		
		if ($purchaseCount >= 1000)
			$copyData .= floor($purchaseCount/1000) . "k sold";
		elseif ($purchaseCount > 0)
			$copyData .= "$purchaseCount sold";
	
		if ($totalItems > $saleCount + $purchaseCount)
		{
			if ($totalItems >= 1000000)
				$copyData .= ", " . floor($totalItems/1000000) . "M items";
			elseif ($totalItems >= 1000)
				$copyData .= ", " . floor($totalItems/1000) . "k items";
			else
				$copyData .= ", $totalItems items";
		}
			
		if ($lastTimestamp > 0)
		{
			$days = intval((time() - $lastTimestamp) / 86400) + 1;
			//$copyData .= ", $days days";
		}
		
		$copyData .= "): $price gp for |H1:item:$itemId:$itemSubType:$itemLevel:0:0:0:$extraData:0:0:0:0:0:0:0:0:$potionData|h|h";
		return $copyData;
	}
	
	
	public function CreateItemCopyPriceHtml($price, $saleCount, $purchaseCount, $lastTimestamp, $totalItems)
	{
		if ($this->singleItemData == null) return "";
		
		$copyData = $this->CreateItemCopyPriceTooltip($price, $saleCount, $purchaseCount, $lastTimestamp, $totalItems, $this->singleItemData);
		$output = "<div class='esovsd_copyprice' copydata='$copyData'>Copy to Clipboard</div>";
		
		return $output;
	}
	
	
	public function FormatPrice ($price)
	{
		$price = floatval($price);
		
		if ($price >= 1000)
			$fmtPrice = round($price, 0);
		elseif ($price >= 100)
			$fmtPrice = round($price, 1);
		else
			$fmtPrice = round($price, 2);
		
		return $fmtPrice;
	}
	
	
	public function GetPriceStatSaleTypeHtml($listCount, $soldCount, $itemCount, $avgPrice, $avgGoodPrice, $lastTimestamp, $label)
	{
		if ($avgGoodPrice > 0) $avgPrice = $avgGoodPrice;
		
		$output = "";
		$totalCount = $listCount + $soldCount;
		$fmtPrice = $this->FormatPrice($avgPrice);
		
		if ($totalCount <= 0)
		{
			$output .= "Not enough data to compute average price for $label!";
		}
		else
		{
			$days = intval((time() - $lastTimestamp) / 86400) + 1;
			$output .= "Average price for $label (";
			
			if ($listCount > 0) 
			{
				$output .= "$listCount listed";
				if ($soldCount > 0) $output .= ", ";
			}
			
			if ($soldCount > 0) $output .= "$soldCount sold";
			if ($itemCount > $totalCount) $output .= ", $itemCount items";
				
			$output .= "): <b>" . $fmtPrice . " gp</b>";
			$output .= $this->CreateItemCopyPriceHtml($avgPrice, $listCount, $soldCount, $lastTimestamp, $itemCount);
		}
		
		return $output;
	}
	
	
	public function GetPriceStatHtml()
	{
		$output = "";
		
		if ($this->formValues['saletype'] != "listed" && $this->formValues['saletype'] != "sold") 
		{
			$output .= $this->GetPriceStatSaleTypeHtml($this->salePriceAverageCountListed, $this->salePriceAverageCountSold, $this->salePriceAverageItemsAll, $this->salePriceAverageAll, $this->goodPriceAll, $this->salePriceAverageLastTimestampAll, "all data");
		}
		
		if ($this->formValues['saletype'] == "listed" || $this->formValues['saletype'] == "" || $this->formValues['saletype'] == "all")
		{
			if ($output != "") $output .= "<br /><br />";
			$output .= $this->GetPriceStatSaleTypeHtml($this->salePriceAverageCountListed, 0, $this->salePriceAverageItemsListed, $this->salePriceAverageListed, $this->goodPriceListed,$this->salePriceAverageLastTimestampListed, "items listed");
			
		}
		
		if ($this->formValues['saletype'] == "sold" || $this->formValues['saletype'] == "" || $this->formValues['saletype'] == "all")
		{
			if ($output != "") $output .= "<br /><br />";
			$output .= $this->GetPriceStatSaleTypeHtml(0, $this->salePriceAverageCountSold, $this->salePriceAverageItemsSold, $this->salePriceAverageSold, $this->goodPriceSold, $this->salePriceAverageLastTimestampSold, "items sold");
		}
		 
		$output .= "";
		return $output;		
	}
	
	
	public function GetItemDetailsHtml()
	{
		if ($this->singleItemData == null) return "";
		
		$traitText = "";
		if ($trait > 0) $traitText = GetEsoItemTraitText($this->singleItemData['trait']) . ",";
		
		$details = array();
		
		$details[] = GetEsoItemQualityText($this->singleItemData['quality']);
		$details[] = "Level ".GetEsoItemLevelText($this->singleItemData['level']);
		$details[] = GetEsoItemTypeText($this->singleItemData['itemType']);
		$details[] = GetEsoItemWeaponTypeText($this->singleItemData['weaponType']);
		$details[] = GetEsoItemArmorTypeText($this->singleItemData['armorType']);
		$details[] = GetEsoItemEquipTypeText($this->singleItemData['equipType']);
		$details[] = $this->Escape($item['setName']);
		
		$details = array_filter($details);
		
		$output = "Item Details: ";
		$output .= implode(", ", $details);
		$output .= "";
		
		return $output;				
	}
	
		
	public function GetSalesTypeHtml()
	{
		$output = "";
		$saleType = $this->formValues['saletype'];
		$timePeriod = intval($this->formValues['timeperiod']);
		$itemId = $this->viewSalesItemId;
		
		$output .= "View: ";
		
		if ($saleType == "all" || $saleType == "")
			$output .= " <b>All Items</b> ";
		else
			$output .= " <a href='?viewsales=$itemId&saletype=all&timeperiod=$timePeriod'>All Items</a>";
		
		$output .= " : ";
		
		if ($saleType == "sold")
			$output .= " <b>Only Sold Items</b> ";
		else
			$output .= " <a href='?viewsales=$itemId&saletype=sold&timeperiod=$timePeriod'>Only Sold Items</a>";
		
		$output .= " : ";
		
		if ($saleType == "listed")
			$output .= " <b>Only Listed Items</b> ";
		else
			$output .= " <a href='?viewsales=$itemId&saletype=listed&timeperiod=$timePeriod'>Only Listed Items</a>";
		
		$output .= " from ";
			
		if ($timePeriod <= 0)
			$output .= " <b>All Time</b> ";
		else
			$output .= " <a href='?viewsales=$itemId&saletype=$saleType&timeperiod=0'>All Time</a>";
		
		$output .= " : ";
			
		if ($timePeriod == 86400)
			$output .= " <b>Last Day</b> ";
		else
			$output .= " <a href='?viewsales=$itemId&saletype=$saleType&timeperiod=86400'>Last Day</a>";
		
		$output .= " : ";
			
		if ($timePeriod == 604800)
			$output .= " <b>Last Week</b> ";
		else
			$output .= " <a href='?viewsales=$itemId&saletype=$saleType&timeperiod=604800'>Last Week</a>";
		
		$output .= " : ";
				
		if ($timePeriod == 2678400)
			$output .= " <b>Last Month</b> ";
		else
			$output .= " <a href='?viewsales=$itemId&saletype=$saleType&timeperiod=2678400'>Last Month</a>";
		
		$output .= " : ";
		
		if ($timePeriod == 31558150)
			$output .= " <b>Last Year</b> ";
		else
			$output .= " <a href='?viewsales=$itemId&saletype=$saleType&timeperiod=31558150'>Last Year</a>";
		
		$output .= "<br/>";
		return $output;
	}
	
	
	public function GetSearchResultMessageHtml()
	{
		$output = "";
		
		if ($this->showForm == "ViewGuilds")
		{
			$count = count($this->guildData);
			$output .= "Showing data from all $count guilds.";
			$output .= "<br/><br/>";
			return $output;
		}
		elseif ($this->viewSalesItemId > 0)
		{
			$output .= "";
			return $output;
		}
		elseif (!$this->hasSearchData)
		{
			$output .= "There are {$this->allItemsCount} items with {$this->allSalesCount} listings/sales currently in the database.";
			$output .= "<br/><br/>";
			return $output;
		}
		elseif ($this->totalItemCount > $this->itemCount)
		{
			$output .= "Displaying {$this->searchItemIdsLimit} of {$this->totalItemCount} matching items with a total of {$this->totalListedCount} listings and {$this->totalSoldCount} sales.";
			$output .= "<br/><br/>";
		}
		elseif ($this->itemCount == 0)
		{
			$output .= "No items found matching input search!";
			$output .= "<br/><br/>";
		}
		else
		{
			$output .= "Found {$this->totalItemCount} matching items with a total of {$this->totalListedCount} listings and {$this->totalSoldCount} sales.";
			$output .= "<br/><br/>";
		}
		
		if ($this->formValues['timeperiod'] > 0)
		{
			$output .= "Note: The displayed sale # and average price are for all time. View the item sale details for more accurate values.";
			$output .= "<br/><br/>";
		}
		
		return $output;
	}
	
	
	public function GetSaleStatsHtml()
	{
		static $VALUES = array(
				"AvgPriceAll" => "salePriceAverageAll",
				"CountAll" => "salePriceAverageCountAll",
				"ItemsAll" => "salePriceAverageItemsAll",				
				"AvgPriceSold" => "salePriceAverageSold",
				"CountSold" => "salePriceAverageCountSold",
				"ItemsSold" => "salePriceAverageItemsSold",				
				"AvgPriceListed" => "salePriceAverageListed",
				"CountListed" => "salePriceAverageCountListed",
				"ItemsListed" => "salePriceAverageItemsListed",
				"LastTimeAll" => "salePriceAverageLastTimestampAll",
				"LastTimeListed" => "salePriceAverageLastTimestampListed",
				"LastTimeSold" => "salePriceAverageLastTimestampSold",
				"StdDevAll" => "salePriceStdDevAll",
				"StdDevSold" => "salePriceStdDevSold",
				"StdDevListed" => "salePriceStdDevListed",
				"AdjCountAll" => "salePriceAdjCountAll",
				"AdjCountSold" => "salePriceAdjCountSold",
				"AdjCountListed" => "salePriceAdjCountListed",
				"AdjItemsAll" => "salePriceAdjItemsAll",
				"AdjItemsSold" => "salePriceAdjItemsSold",
				"AdjItemsListed" => "salePriceAdjItemsListed",
				"AdjPriceAll" => "salePriceAdjAll",
				"AdjPriceSold" => "salePriceAdjSold",
				"AdjPriceListed" => "salePriceAdjListed",
				"WeightPriceAll" => "salePriceWeightAll",
				"WeightItemsAll" => "salePriceWeightItemsAll",
				"WeightPriceSold" => "salePriceWeightSold",
				"WeightItemsSold" => "salePriceWeightItemsSold",
				"WeightPriceListed" => "salePriceWeightListed",
				"WeightItemsListed" => "salePriceWeightItemsListed",
				"StdDevWeightAll" => "salePriceStdDevWeightAll",
				"StdDevWeightSold" => "salePriceStdDevWeightSold",
				"StdDevWeightListed" => "salePriceStdDevWeightListed",
				"AdjWeightCountAll" => "salePriceAdjWeightCountAll",
				"AdjWeightCountSold" => "salePriceAdjWeightCountSold",
				"AdjWeightCountListed" => "salePriceAdjWeightCountListed",
				"AdjWeightItemsAll" => "salePriceAdjWeightItemsAll",
				"AdjWeightItemsSold" => "salePriceAdjWeightItemsSold",
				"AdjWeightItemsListed" => "salePriceAdjWeightItemsListed",
				"AdjWeightPriceAll" => "salePriceAdjWeightAll",
				"AdjWeightPriceSold" => "salePriceAdjWeightSold",
				"AdjWeightPriceListed" => "salePriceAdjWeightListed",
		);
		
		$vars = get_object_vars($this);
		$output = "";
		
		foreach ($VALUES as $label => $member)
		{
			$value = $vars[$member];
			$output .= "$label = $value\n";
		}
		
		return $output;
	}
	
		
	public function GetViewType()
	{
		$type = $this->formValues['saletype'];
		
		if ($type == null || $type == "both" || $type == "" || $type == "all") return "all";
		if ($type == "listed" || $type == "list") return "list";
		if ($type == "sold") return "sold";

		return "both";
	}
	
	
	public function GetViewCsvLink()
	{
		if ($_SERVER['QUERY_STRING'] == "") return "";
		if ($this->showForm == "ItemSearch" && (!$this->hasSearchData || count($this->itemSortedKeys) == 0)) return "";
		
		$output = "<a href=\"?" . $_SERVER['QUERY_STRING'] . "&output=csv\">View Data as CSV</a>";
		return $output;
	}
		
	
	public function CreateOutputHtml()
	{
		$replacePairs = array(
				'{formText}' => $this->GetFormValue('text'),
				'{formTrait}' => $this->GetFormValue('trait'),
				'{formQuality}' => $this->GetFormValue('quality'),
				'{formItemType}' => $this->GetFormValue('itemtype'),
				'{formEquipType}' => $this->GetFormValue('equiptype'),
				'{formArmorType}' => $this->GetFormValue('armortype'),
				'{formWeaponType}' => $this->GetFormValue('weapontype'),
				'{formTimePeriod}' => $this->GetFormValue('timeperiod'),
				'{formServer}' => $this->GetFormValue('server'),
				'{formSaleType}' => $this->GetFormValue('saletype'),
				'{formLevel}' => $this->GetOutputFormLevel(),
				'{itemId}' => $this->viewSalesItemId,
				'{viewType}' => $this->GetViewType(),
				
				'{listTrait}' => $this->GetGeneralListHtml(self::$ESOVSD_TRAITS, 'trait'),
				'{listQuality}' => $this->GetGeneralListHtml(self::$ESOVSD_QUALITIES, 'quality'),
				'{listItemType}' => $this->GetGeneralListHtml(self::$ESOVSD_ITEMTYPES, 'itemtype'),
				'{listEquipType}' => $this->GetGeneralListHtml(self::$ESOVSD_EQUIPTYPES, 'equiptype'),
				'{listArmorType}' => $this->GetGeneralListHtml(self::$ESOVSD_ARMORTYPES, 'armortype'),
				'{listWeaponType}' => $this->GetGeneralListHtml(self::$ESOVSD_WEAPONTYPES, 'weapontype'),
				'{listTimePeriod}' => $this->GetGeneralListHtml(self::$ESOVSD_TIMEPERIODS, 'timeperiod', true),
				'{listServer}' => $this->GetGeneralListHtml(self::$ESOVSD_SERVERS, 'server', true),
				'{listSaleType}' => $this->GetGeneralListHtml(self::$ESOVSD_SALETYPES, 'saletype', true),
				
				'{searchResults}' => $this->GetSearchResultsHtml(),
				'{errorMessages}' => $this->GetErrorMessagesHtml(),
				'{searchResultMessage}' => $this->GetSearchResultMessageHtml(),
				'{itemQuery}' => $this->itemQuery,
				'{salesQuery}' => $this->salesQuery,
				'{salesItemLink}' => $this->GetSalesItemLinkHtml(),
				'{server}' => $this->GetServerHtml(),
				'{guildsResults}' => $this->GetGuildResultsHtml(),
				
				'{priceStats}' => $this->GetPriceStatHtml(),
				'{itemDetails}' => $this->GetItemDetailsHtml(),
				'{showSalesType}' => $this->GetSalesTypeHtml(),
				'{saleStats}' => $this->GetSaleStatsHtml(),
				
				'{viewCsvLink}' => $this->GetViewCsvLink(),
				
		);
		
		$output = strtr($this->htmlTemplate, $replacePairs);
		return $output;
	}
	
	
	public function CreateSalesOutputCsv()
	{
		$output = "";
		
		$output .= "\"ID\",";
		$output .= "\"Guild ID\",";
		$output .= "\"Guild\",";
		$output .= "\"Kiosk Location\",";
		if (!$this->OMIT_SELLER_INFO) $output .= "\"Seller\",";
		if (!$this->OMIT_BUYER_INFO) $output .= "\"Buyer\",";
		$output .= "\"List Time\",";
		$output .= "\"Sold Time\",";
		$output .= "\"Last Seen Time\",";
		$output .= "\"Price\",";
		$output .= "\"Qnt\",";
		$output .= "\"Unit Price\",";
		$output .= "\"Outlier\",";
		$output .= "\"Event ID\",";
		$output .= "\"Item Link\"";
		$output .= "\n";
		
		foreach ($this->searchResults as $row)
		{
			$guild = $this->guildData[$row['guildId']];
			$guildName = $this->CsvEscape($guild['name']);
			$item = $this->itemResults[$row['itemId']];
			$buyDate = $row['buyTimestamp'];
			$listDate = $row['listTimestamp'];
			$lastSeen = $row['lastSeen'];
			$unitPrice = number_format(floatval($row['price']) / floatval($row['qnt']), 2, ".", '');
							
			$kiosk = $this->CsvEscape($guild['storeLocation']);
			if ($kiosk == "") $kiosk = "None";
			
			$outlier = "0";
			if ($row['outlier'] === true) $outlier = "1";
			
			$output .= "\"{$row['id']}\",";
			$output .= "\"{$row['guildId']}\",";
			$output .= "\"$guildName\",";
			$output .= "\"$kiosk\",";
			if (!$this->OMIT_SELLER_INFO) $output .= "\"{$row['buyerName']}\",";
			if (!$this->OMIT_BUYER_INFO) $output .= "\"{$row['sellerName']}\",";
				
			$output .= "\"{$listDate}\",";
			$output .= "\"{$buyDate}\",";
			$output .= "\"{$lastSeen}\",";
	
			$output .= "\"{$row['price']}\",";
			$output .= "\"{$row['qnt']}\",";
			$output .= "\"{$unitPrice}\",";
			$output .= "\"$outlier\",";
			$output .= "\"{$row['eventId']}\",";
			$output .= "\"{$row['itemLink']}\"";
			$output .= "\n";
		}
		
		return $output;
	}
	
	
	public function CsvEscape($text)
	{
		return str_replace('"', "'", $text);
	}
	
	
	public function CreateGuildsOutputCsv()
	{
		$output = "";
		
		$output .= "\"ID\",\"Guild\",\"Server\",\"Members\",\"Founder\",\"Founded Date\",\"Kisok Location\",\"Last Updated\",\"Listed\",\"Sold\"\n";
		
		foreach ($this->guildData as $key => $guild)
		{
			$name = $this->CsvEscape($guild['name']);
			$server = $this->CsvEscape($guild['server']);
			$numMembers = $guild['numMembers'];
			$founder = $this->CsvEscape($guild['leader']);
			$numSales = $guild['totalSales'];
			$numPurchases = $guild['totalPurchases'];
				
			if ($guild['foundedDate'] <= 0)
				$foundedDate = "";
			else
				$foundedDate = date('Y-m-d', $guild['foundedDate']);
						
			$storeLoc = $this->CsvEscape($guild['storeLocation']);
			if ($storeLoc == "") $storeLoc = "None";
				
			$lastStoreLocTime = $this->FormatTimeStamp($guild['lastStoreLocTime']);

			$output .= "\"{$guild['id']}\",";
			$output .= "\"$name\",";
			$output .= "\"$server\",";
			$output .= "$numMembers,";
			$output .= "\"$founder\",";
			$output .= "\"$foundedDate\",";
			$output .= "\"$storeLoc\",";
			$output .= "\"$lastStoreLocTime\",";
			$output .= "$numSales,";
			$output .= "$numPurchases";
			$output .= "\n";
		}
		
		return $output;
	}
	
	
	public function CreateItemsOutputCsv()
	{
		$output = "";
		
		$output .= "\"ID\",";
		$output .= "\"Item\",";
		$output .= "\"Level\",";
		$output .= "\"Quality\",";
		$output .= "\"Item ID\",";
		$output .= "\"Internal Level\",";
		$output .= "\"Internal Subtype\",";
		$output .= "\"Item Type\",";
		$output .= "\"Equip Type\",";
		$output .= "\"Armor Type\",";
		$output .= "\"Weapon Type\",";
		$output .= "\"Set Name\",";
		$output .= "\"Extra Data\",";
		$output .= "\"Sales Count\",";
		$output .= "\"Sales Items\",";
		$output .= "\"Sales Price\",";
		$output .= "\"Last Sale Time\",";
		$output .= "\"List Count\",";
		$output .= "\"List Items\",";
		$output .= "\"List Price\",";
		$output .= "\"Last List Time\",";
		$output .= "\"Last Seen Time\",";
		$output .= "\"Good Price\",";
		$output .= "\"Good Sales Price\",";
		$output .= "\"Good List Price\"";
		$output .= "\n";
				
		if (!$this->hasSearchData) return "";
		
		foreach ($this->itemSortedKeys as $itemId)
		{
			$item = $this->itemResults[$itemId];
		
			$trait = $item['trait'];
			$itemName = $this->CsvEscape($item['name']);
			$itemType = $item['itemType'];
			$weaponType = $item['weaponType'];
			$armorType = $item['armorType'];
			$equipType = $item['equipType'];
			$setName = $this->CsvEscape($item['setName']);
			$totalSales = floatval($item['countSales']);
			$totalPurchases = floatval($item['countPurchases']);
			$sumSales = floatval($item['sumSales']);
			$sumPurchases = floatval($item['sumPurchases']);
			$totalCount = $totalSales + $totalPurchases;
			$totalItems = floatval($item['countItemPurchases']) + floatval($item['countItemSales']);
			$totalSaleItems = floatval($item['countItemPurchases']);
			$totalListItems = floatval($item['countItemSales']);
			$goodPrice = floatval($item['goodPrice']);
			$goodSalesPrice = floatval($item['goodSoldPrice']);
			$goodListPrice = floatval($item['goodListPrice']);
			$extraQuery = "";
			$avgPrice = 0;
			$avgSalePrice = 0;
			$avgListPrice = 0;
			
			if ($totalItems > 0) $avgPrice = ($sumSales + $sumPurchases) / $totalItems;
			if ($totalSaleItems > 0) $avgSalePrice = $sumPurchases / $totalSaleItems;
			if ($totalListItems > 0) $avgListPrice = $sumSales / $totalListItems;
			
			$extraData = $item['extraData'];
			if ($extraData == "") $extraData = $item['potionData']; 
			
			$output .= "\"{$item['id']}\",";
			$output .= "\"$itemName\",";
			$output .= "\"{$item['level']}\",";
			$output .= "\"{$item['quality']}\",";
			$output .= "\"{$item['itemId']}\",";
			$output .= "\"{$item['internalLevel']}\",";
			$output .= "\"{$item['internalSubType']}\",";
			$output .= "\"$itemType\",";
			$output .= "\"$equipType\",";
			$output .= "\"$armorType\",";
			$output .= "\"$weaponType\",";
			$output .= "\"$setName\",";
			$output .= "\"$extraData\",";
			$output .= "\"$totalPurchases\",";
			$output .= "\"$totalSaleItems\",";
			$output .= "\"$avgSalePrice\",";
			$output .= "\"{$item['lastPurchaseTimestamp']}\",";
			$output .= "\"$totalSales\",";
			$output .= "\"$totalListItems\",";
			$output .= "\"$avgListPrice\",";
			$output .= "\"{$item['lastSaleTimestamp']}\",";
			$output .= "\"{$item['lastSeen']}\",";
			$output .= "\"$goodPrice\",";
			$output .= "\"$goodSoldPrice\",";
			$output .= "\"$goodListPrice\"";
			$output .= "\n";
		}
		
		return $output;
	}
	
	
	public function CreateOutputCsv()
	{
		$output = "";
		
		if ($this->showForm == "ViewSales")
		{
			$output = $this->CreateSalesOutputCsv();
		}
		elseif ($this->showForm == "ViewGuilds")
		{
			$output = $this->CreateGuildsOutputCsv();
		}
		else
		{
			$output = $this->CreateItemsOutputCsv();
		}
		
		return $output;
	}
	
	
	public function GetOutputCsv()
	{
		$this->LoadGuilds();
		$this->LoadSearchResults();
		return $this->CreateOutputCsv();
	}
	
	
	public function GetOutputHtml()
	{
		$this->LoadTemplate();
		$this->LoadGuilds();
		
		$this->LoadSearchResults();

		return $this->CreateOutputHtml();
	}
	
	
	public function RenderCsv()
	{
		$this->OutputCsvHeader();
		
		$output = $this->GetOutputCsv();
		print ($output);
	}	
	
	
	public function Render()
	{
		if ($this->outputType == "csv")
		{
			$this->RenderCsv();
			return;
		}
		
		$this->OutputHtmlHeader();
		$output = $this->GetOutputHtml();
		print ($output);
	}
	
};


function EsoItemDataCompareItemName($a, $b)
{
	global $g_EsoSalesDataSortOrder, $g_EsoItemData;
	
	$result = strcmp($g_EsoItemData[$a]['name'], $g_EsoItemData[$b]['name']);

	if ($g_EsoSalesDataSortOrder == 0) return -$result;
	return $result;
}


function EsoSalesDataCompareItemName($a, $b)
{
	global $g_EsoSalesDataSortOrder;
	
	$result = strcmp($a['itemName'], $b['itemName']);
	
	if ($g_EsoSalesDataSortOrder == 0) return -$result;
	return $result;
}


function EsoSalesDataCompareBuyDate($a, $b)
{
	global $g_EsoSalesDataSortOrder;
	
	$t1 = $a['buyTimestamp'];
	$t2 = $b['buyTimestamp'];
	
	if ($t1 <= 0) $t1 = $a['listTimestamp'];
	if ($t2 <= 0) $t2 = $b['listTimestamp'];
	
	$result = $t1 - $t2;
	
	//if ($result == 0) $result = $a['listTimestamp'] - $b['listTimestamp']; 
	
	if ($g_EsoSalesDataSortOrder == 0) return -$result;
	return $result;
}


function EsoSalesDataCompareLastSeen($a, $b)
{
	global $g_EsoSalesDataSortOrder;
	
	$t1 = $a['lastSeen'];
	$t2 = $b['lastSeen'];
	
	$result = $t1 - $t2;
	
	if ($g_EsoSalesDataSortOrder == 0) return -$result;
	return $result;
}


function EsoSalesDataComparePrice($a, $b)
{
	global $g_EsoSalesDataSortOrder;
	
	$result = $a['price'] - $b['price'];
	
	if ($g_EsoSalesDataSortOrder == 0) return -$result;
	return $result;
}


function EsoSalesDataCompareQnt($a, $b)
{
	global $g_EsoSalesDataSortOrder;
	
	$result = $a['qnt'] - $b['qnt'];
	
	if ($g_EsoSalesDataSortOrder == 0) return -$result;
	return $result;
}


function EsoSalesDataCompareUnitPrice($a, $b)
{
	global $g_EsoSalesDataSortOrder;
	
	$result = $a['unitPrice'] - $b['unitPrice'];
	
	if ($g_EsoSalesDataSortOrder == 0) return -$result;
	return $result;
}


$viewSales = new EsoViewSalesData();
$viewSales->Render();


