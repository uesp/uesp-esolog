<?php 


require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");


class CEsoViewNpcLoot 
{
	public $USE_CONSTANT_WRITVOUCHER_PRICE = true; 
	
	public $db = null;
	public $dbReadInitialized = false;
	public $lastQuery = "";
	
	public $htmlTemplate = "";
	
	public $searchResults = array();
	public $npcRecord = array();
	public $zoneQntTotals = array();
	public $itemQntTotals = array();
	public $zoneCountTotals = array();
	public $itemCountTotals = array();
	public $itemLinkData = array();
	
	public $salesPrices = null;
	
	public $viewNpcName = "";
	public $viewItemName = "";
	public $viewGroup = "";
	public $viewExtra = "";
	public $salesPriceServer = "";
	public $output = "html";
	
	
	public function __construct ()
	{
		//error_reporting(E_ALL);
		
		$this->InitDatabase();
		$this->SetInputParams();
		$this->ParseInputParams();
		
		$this->htmlTemplate = file_get_contents(__DIR__."/templates/esoviewnpcloot_template.txt");
	}
	
	
	private function InitDatabase ()
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
	
		if ($this->dbReadInitialized) return true;
	
		$this->db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($this->db->connect_error) return $this->ReportError("Could not connect to mysql database!");
	
		UpdateEsoPageViews("logViews");
	
		$this->dbReadInitialized = true;
		return true;
	}
	
	
	public function ReportError ($errorMsg)
	{
		error_log($errorMsg);
		error_log("Last Query: " . $this->lastQuery);
		error_log("DB Error: " . $this->db->error);
	
		return false;
	}
	
	
	public function SetInputParams ()
	{
		$this->inputParams = $_REQUEST;
	}
	
	
	public function ParseInputParams ()
	{
		if ($this->inputParams['npc'] != null)
		{
			$this->viewNpcName = $this->inputParams['npc'];
		}
		
		if ($this->inputParams['item'] != null)
		{
			$this->viewItemName = $this->inputParams['item'];
		}
		
		if ($this->inputParams['group'] != null)
		{
			$group = strtolower($this->inputParams['group']);
			
			if ($group == "reagent")
				$this->viewGroup = "Reagent";
			else if ($group == "solvent")
				$this->viewGroup = "Solvent";
			else if ($group == "ore")
				$this->viewGroup = "Ore";
			else if ($group == "silk")
				$this->viewGroup = "Silk";
			else if ($group == "scraps")
				$this->viewGroup = "Scraps";
			else if ($group == "wood")
				$this->viewGroup = "Wood";
			else if ($group == "container")
				$this->viewGroup = "Container";
			else if ($group == "provision")
				$this->viewGroup = "Provisioning Container";
			else if ($group == "alchemysurvey")
				$this->viewGroup = "Alchemy Survey";
				
		}
		
		if ($this->inputParams['extra'] != null)
		{
			$this->viewExtra = strtolower($this->inputParams['extra']);
		}
		
		if ($this->inputParams['prices'] != null)
		{
			$server = strtoupper($this->inputParams['prices']);
			
			if ($server == "NA")
				$this->salesPriceServer = "NA";
			else if ($server == "EU")
				$this->salesPriceServer = "EU";
			else if ($server == "PTS")
				$this->salesPriceServer = "PTS";
			else if ($server == "OTHER")
				$this->salesPriceServer = "Other";
		}
				
		if ($this->inputParams['output'] != null)
		{
			$output = strtolower($this->inputParams['output']);
			
			if ($output == "html")
				$this->output = "html";
			else if ($output == "csv")
				$this->output = "csv";
		}
		
		if ($this->inputParams['voucher'] == 'constant')
		{
			$this->USE_CONSTANT_WRITVOUCHER_PRICE = true;	
		}
		else if ($this->inputParams['voucher'] == 'variable')
		{
			$this->USE_CONSTANT_WRITVOUCHER_PRICE = false;	
		}
	}
	
	
	public function WriteHtmlHeaders()
	{
		ob_start("ob_gzhandler");
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		header("content-type: text/html");
	}
	
	
	public function WriteCsvHeaders()
	{
		ob_start("ob_gzhandler");
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		header("content-type: text/plain");
	}
	
	
	public function LoadSalesPrices()
	{
		if ($this->salesPriceServer == "") return true;
		
		if ($this->salesPriceServer == "NA")
		{
			include "pricesNA/uespSalesPrices.php";
		}
		else if ($this->salesPriceServer == "EU")
		{
			include "pricesEU/uespSalesPrices.php";
		}
		else if ($this->salesPriceServer == "PTS")
		{
			include "pricesPTS/uespSalesPrices.php";
		}
		else if ($this->salesPriceServer == "Other")
		{
			include "pricesOther/uespSalesPrices.php";
		}
		else
		{
			return false;
		}
		
		if ($uespSalesPrices == null) return false;
		
		$this->salesPrices = &$uespSalesPrices;		
		
		$this->UpdateWritVoucherValue();
		return true;
	}
	
	
	public $MODIFY_PRICE_DATA = array(
			"Blacksmith Survey:" => array(
					'itemId' => array(71198),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(129),
					'extraValue' => 120,
			),
			"Clothier Survey:" => array(
					'itemId' => array(71200, 71239),
					'level' => array(1, 1),
					'quality' => array(1, 1),
					'extraQnt' => array(88, 73),
					'extraValue' => 150,
			),
			"Woodworker Survey:" => array(
					'itemId' => array(71199),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(118),
					'extraValue' => 100,
			),
			"Alchemist Survey:" => array(
					'itemId' => array(30157, 30160, 30164, 30161, 30162, 30158, 30163, 30159, 42871, 42869),
					'level' => array(1, 1, 1, 1, 1, 1, 1, 1, 1, 1),
					'quality' => array(2, 2, 2, 2, 2, 2, 2, 2, 1, 1),
					'extraQnt' => array(3.75, 3.75, 3.75, 3.75, 3.75, 3.75, 3.75, 3.75, 1.2, 1.2),
			),
			"Enchanter Survey:" => array(
					'value' => 3200,
			),
			"Shipment of Calcinium Ingots" => array(
					'itemId' => array(46127),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Dwarven Ingots" => array(
					'itemId' => array(6000),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Ebony Ingots" => array(
					'itemId' => array(6001),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Galatite Ingots" => array(
					'itemId' => array(46128),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Iron Ingots" => array(
					'itemId' => array(5413),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Orichalcum" => array(
					'itemId' => array(23107),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Quicksilver Ingots" => array(
					'itemId' => array(46129),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Steel Ingots" => array(
					'itemId' => array(4487),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Voidstone Ingots" => array(
					'itemId' => array(46130),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Cotton Cloth" => array(
					'itemId' => array(23125),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Ebonthread Cloth" => array(
					'itemId' => array(23127),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Flax Cloth" => array(
					'itemId' => array(4463),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Ironthread Cloth" => array(
					'itemId' => array(46132),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Jute Cloth" => array(
					'itemId' => array(811),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Kresh Cloth" => array(
					'itemId' => array(46131),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Silverweave Cloth" => array(
					'itemId' => array(46133),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Spidersilk Cloth" => array(
					'itemId' => array(23126),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Spidersilk Cloth" => array(
					'itemId' => array(23126),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Voidcloth" => array(
					'itemId' => array(46134),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Fell Hide" => array(
					'itemId' => array(23101),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Hide" => array(
					'itemId' => array(4447),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Iron Hide" => array(
					'itemId' => array(46136),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Leather" => array(
					'itemId' => array(23099),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Rawhide" => array(
					'itemId' => array(794),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Shadowhide" => array(
					'itemId' => array(46138),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Superb Hide" => array(
					'itemId' => array(46137),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Thick Leather" => array(
					'itemId' => array(23100),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Topgrain Hide" => array(
					'itemId' => array(46135),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Ash" => array(
					'itemId' => array(46140),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Beech" => array(
					'itemId' => array(23121),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Birch" => array(
					'itemId' => array(46139),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Hickory" => array(
					'itemId' => array(23122),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Mahogany" => array(
					'itemId' => array(46141),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Maple" => array(
					'itemId' => array(803),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Nightwood" => array(
					'itemId' => array(46142),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Oak" => array(
					'itemId' => array(533),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Shipment of Yew" => array(
					'itemId' => array(23123),
					'level' => array(1),
					'quality' => array(1),
					'extraQnt' => array(25),
			),
			"Writ Voucher" => array(
					'value' => 750,
			),
	);
	
	
	public $WRITVOUCHER_UPDATE_ITEMIDS = array(
			119696,	// Alchemy
			119698,
			119699,
			119700,
			119701,
			119702,
			119703,
			119704,
			119705,
			119818,
			119820,
			119564,	// Enchanting
			121528,
			119693,	// Provisioning
	);
	
	
	public function UpdateWritVoucherValue()
	{
		if ($this->salesPrices == null) return false;
		
		$sumPrices = 0;
		$sumVouchers = 0;
		$totalPrices = 0;
		
		foreach ($this->WRITVOUCHER_UPDATE_ITEMIDS as $itemId)
		{
			$data1 = $this->salesPrices[$itemId];
			if ($data1 == null) continue;
		
			$data2 = $data1[30];
			
			if ($data2 == null) 
			{
				$data2 = $data1[1];
				if ($data2 == null) continue;
			}
		
			$data3 = $data2[5];
			if ($data2 == null) continue;
		
			$data4 = $data3[0];
			if ($data4 == null) continue;
		
			foreach ($data4 as $potionData => $data5)
			{
				if ($data5[0] == null || $data5[0] <= 0) continue;
				if ($data5[7] == null || $data5[7] <= 0) continue;
			
				$sumPrices += $data5[0];
				$sumVouchers += $data5[7];
				$totalPrices += 1;
			}
		}
		
		$newPrice = 0;
		
		if ($totalPrices > 0)
		{
			$numVouchers = $sumVouchers / 10000 / $totalPrices;
			$newPrice = $sumPrices / $totalPrices / $numVouchers;
			$this->MODIFY_PRICE_DATA['Writ Voucher']['value'] = $newPrice; 
		}
			
		//error_log("Updating Writ Voucher: $sumPrices, $sumVouchers, $totalPrices, $newPrice");
		
		return true;
	}
	
	
	public function FindModifySalesPriceMatch($itemName, $itemType)
	{
		if ($this->USE_CONSTANT_WRITVOUCHER_PRICE && $itemType == 60) return $this->MODIFY_PRICE_DATA['Writ Voucher'];
		
		foreach ($this->MODIFY_PRICE_DATA as $matchName => $matchData)
		{
			if (strpos($itemName, $matchName) !== false) return $matchData;
		}
		
		return null;
	}
	
	
	public function FindModifySalesPrice($itemName, $itemId, $level, $quality, $trait, $potionData, $itemType, $extraData)
	{
		$matchData = $this->FindModifySalesPriceMatch($itemName, $itemType);
		if (!$matchData) return -1;
		
		if ($matchData['value'] != null) 
		{
			if ($itemType == 60 && $potionData > 0) return floor($matchData['value'] * $potionData / 10000); 
			return $matchData['value'];
		}
		
		$price = 0;
		if ($matchData['extraValue'] != null) $price = $matchData['extraValue'];
		
		foreach ($matchData['itemId'] as $i => $itemId)
		{
			$level = $matchData['level'][$i];
			$quality = $matchData['quality'][$i];
			
			$newPrice = $this->FindSalesPriceRaw($itemId, $level, $quality, $trait, $potionData, $extraData);
			
			if ($newPrice > 0) 
			{
				$qnt = $matchData['extraQnt'][$i];
				$price += $newPrice * $qnt;
				//$this->ReportError("ModifySalesPrice: Found price $newPrice * $qnt for item $itemId:$level:$quality:$trait:$potionData!");
			}
			else
			{
				//$this->ReportError("ModifySalesPrice: Did not find price for item $itemId:$level:$quality:$trait:$potionData!");
			}
		}
		
		return $price;
	}
	
	
	public function FindSalesPrice($itemLink, $quality, $trait, $itemName, $itemType)
	{
		if ($this->salesPrices == null) return -1;
		
		$matches = ParseEsoItemLink($itemLink);
		if (!$matches) return -1;
		
		$itemId = intVal($matches['itemId']);
		$intLevel = intVal($matches['level']);
		$intSubtype = intVal($matches['subtype']);
		$potionData = intVal($matches['potionData']);
		$writ1 = intVal($matches['writ1']);
		$writ2 = intVal($matches['writ2']);
		$writ3 = intVal($matches['writ3']);
		$writ4 = intVal($matches['writ4']);
		$writ5 = intVal($matches['writ5']);
		$writ6 = intVal($matches['writ6']);
		$quality = intVal($quality);
		$trait = intVal($trait);
		if ($trait < 0) $trait = 0;
		if ($quality < 0) $quality = 0;
		$extraQnt = 1;
		
		$extraData = "";
		if ($writ1 > 0) $extraData = "$writ1:$writ2:$writ3:$writ4:$writ5:$writ6";
		
		$level = GetEsoLevelFromIntType($intSubtype, $intLevel);
		
		$price = $this->FindModifySalesPrice($itemName, $itemId, $level, $quality, $trait, $potionData, $itemType, $extraData);
		if ($price > 0) return $price;
		
		$price = $this->FindSalesPriceRaw($itemId, $level, $quality, $trait, $potionData, $extraData);
		if ($price > 0) return $price;
		
		if ($itemType == 60 && $potionData > 0)
		{
			$priceData = $this->MODIFY_PRICE_DATA['Writ Voucher'];
			 return floor($priceData['value'] * $potionData / 10000);
		}
		
		return -1;
	}
	
	
	public function FindSalesPriceRaw($itemId, $level, $quality, $trait, $potionData, $extraData)
	{
		$data1 = $this->salesPrices[$itemId];
		if ($data1 == null) return -2;
		
		$data2 = $data1[$level];
		
		if ($data2 == null)
		{
			$data2 = $data1[1];
			if ($data2 == null) return -3;
		}
		
		$data3 = $data2[$quality];
		
		if ($data3 == null) 
		{
			$data3 = $data2[1];
			if ($data2 == null) return -4;
		}
		
		$data4 = $data3[$trait];
		if ($data4 == null) return -5;
		
		if ($extraData != "")
			$data5 = $data4[$extraData];
		else
			$data5 = $data4[$potionData];
		
		if ($data5 == null) return -6;
		
		if ($data5[0] == null) return -7;
		
		return $data5[0];
	}
	
	
	public function FindAllSalesPrice()
	{
		if ($this->salesPrices == null) return;
		
		foreach ($this->searchResults as $i => $result)
		{
			if ($result['itemName'] == "Gold" || $result['itemName'] == "__gold")
			{
				$this->searchResults[$i]['salePrice'] = 1;
				$this->searchResults[$i]['totalPrice'] = $result['qnt'];
				continue;
			}
			
			if (substr($result['itemLink'], 0, 2) != "|H") continue;
				
			$salesPrice = $this->FindSalesPrice($result['itemLink'], $result['quality'], $result['trait'], $result['itemName'], $result['itemType']);
			
			if ($salesPrice > 0) 
			{
				if ($salesPrice < $result['value']) $salesPrice = $result['value'];
				
				$this->searchResults[$i]['salesPrice'] = $salesPrice;
				$this->searchResults[$i]['totalPrice'] = $salesPrice * $result['qnt'];
				//$this->ReportError("{$result['itemName']} found sales price data ($salesPrice)");
			}
			else
			{
				if ($result['value'] > 0)
				{
					$this->searchResults[$i]['salesPrice'] = $result['value'];
					$this->searchResults[$i]['totalPrice'] = $result['value'] * $result['qnt'];
				}
				
				$this->ReportError("{$result['itemName']} no sales price data found ($salesPrice) {$result['itemLink']}!");
			}
		}
		
	}
	
	
	public function GetNpcSearchQuery()
	{
		$safeName = $this->db->real_escape_string($this->viewNpcName);
		$this->lastQuery = "SELECT * FROM npc WHERE name='$safeName';";
		$result = $this->db->query($this->lastQuery);
		
		if (!$result || $result->num_rows <= 0) 
		{ 
			$this->ReportError("Failed to find NPC matching '{$this->viewNpcName}'!");
			return "";
		}
				
		$this->npcRecord = $result->fetch_assoc();
		$npcId = $this->npcRecord['id']; 
		
		$query = "SELECT * FROM npcLoot WHERE npcId=$npcId;";
		return $query;
	}
	
	
	public function GetNpcGroupAlchemyReagentQuery()
	{
		$query = "SELECT id from npc WHERE name='Beetle Scuttle' or name='Blessed Thistle' or name='Bugloss' or name='Butterfly Wing' or ";
		$query .= "name='Columbine' or name='Corn Flower' or name='Dragonthorn' or name='Emetic Russula' or name='Fleshfly Larva' or ";
		$query .= "name='Imp Stool' or name='Lady''s Smock' or name='Luminous Russula' or name='Mudcrab Chitin' or name='Namira''s Rot' or ";
		$query .= "name='Nightshade' or name='Nirnroot' or name='Scrib Jelly' or name='Spider Egg' or name='Stinkhorn' or name='Torchbug Thorax' or ";
		$query .= "name='Violet Coprinus' or name='Water Hyacinth' or name='White Cap' or name='Wormwood' or name='Torchbug' or name='Butterfly';";
		
		//$query = "SELECT id from npc WHERE name='Columbine' or name='Bugloss';";
		return $query;
	}
	
	
	public function GetNpcGroupAlchemySolventQuery()
	{
		$query = "SELECT id from npc WHERE name='Water Skin' or name='Pure Water';";
	
		return $query;
	}
	
	public function GetNpcGroupOreQuery()
	{
		$query = "SELECT id from npc WHERE name LIKE '% Ore' AND name NOT LIKE 'Rich %';";

		return $query;
	}
	
	
	public function GetNpcGroupScrapsQuery()
	{
		$query = "SELECT npcId as id FROM npcLoot where itemName LIKE '% Scraps' GROUP BY npcId;";
	
		return $query;
	}
	
	
	public function GetNpcGroupSilkQuery()
	{
		$query = "SELECT id from npc WHERE name='Ancestor Silk' or name='Cotton' or name='Ebonthread' or name='Flax' or name='Ironthread' or ";
		$query .= "name='Jute' or name='Kresh' or name='Silverweave' or name='Spidersilk' or name='Void Cloth';";
		
		return $query;
	}

	
	public function GetNpcGroupWoodQuery()
	{
		$query = "SELECT id from npc WHERE name='Ruby Ash Wood' or name='Ash' or name='Beech' or name='Birch' or name='Hickory' or ";
		$query .= "name='Mahogany' or name='Maple' or name='Nightwood' or name='Oak' or name='Yew';";
	
		return $query;
	}
	
	
	public function GetNpcGroupContainerQuery()
	{
		$query = "SELECT id FROM npc where ";
		$query .= "name='Backpack' or name='Trunk' or ";
		$query .= "name='Urn' or name='Wardrobe' or name='Dwemer Jug' or name='Dwemer Pot' or ";
		$query .= "name='Dresser' or name='Large Dwemer Jug' or ";
		$query .= "name='Large Dwemer Pot' or name='Nightstand' or name='Tomb Urn';";
	
		return $query;
	}
	
	
	public function GetNpcGroupProvisioningQuery()
	{
		$query = "SELECT id FROM npc where name='Barrel' or name='Barrels' or name='Crate' or name='Crates' or ";
		$query .= "name='Bag' or name='Sack' or name='Jug' or name='Basket' or ";
		$query .= "name='Burnt Barrel' or ";
		$query .= "name='Burnt Barrels' or name='Burnt Crate' or name='Burnt Crates' or ";
		$query .= "name='Saltrice Sack' or name='Apple Basket' or ";
		$query .= "name='Corn Basket' or name='Flour Sack' or name='Millet Sack';";
	
		return $query;
	}
	
	
	public function GetNpcGroupAlchemySurveyQuery()
	{
		$query = "SELECT id FROM npc WHERE ";
		$query .= "name='Lush Columbine' or ";
		$query .= "name='Lush Mountain Flower' or ";
		$query .= "name='Lush Corn Flower' or ";
		$query .= "name='Lush Dragonthorn' or ";
		$query .= "name='Lush Bugloss' or ";
		$query .= "name='Lush Lady''s Smock' or ";
		$query .= "name='Lush Wormwood' or ";
		$query .= "name='Lush Blessed Thistle';";
	
		return $query;
	}	
	
	
	public function GetNpcGroupSearchQuery()
	{
		if ($this->viewGroup == "Reagent") 
			$this->lastQuery = $this->GetNpcGroupAlchemyReagentQuery();
		else if ($this->viewGroup == "Solvent")
			$this->lastQuery = $this->GetNpcGroupAlchemySolventQuery();
		else if ($this->viewGroup == "Ore")
			$this->lastQuery = $this->GetNpcGroupOreQuery();
		else if ($this->viewGroup == "Scraps")
			$this->lastQuery = $this->GetNpcGroupScrapsQuery();
		else if ($this->viewGroup == "Silk")
			$this->lastQuery = $this->GetNpcGroupSilkQuery();
		else if ($this->viewGroup == "Wood")
			$this->lastQuery = $this->GetNpcGroupWoodQuery();
		else if ($this->viewGroup == "Container")
			$this->lastQuery = $this->GetNpcGroupContainerQuery();
		else if ($this->viewGroup == "Provisioning Container")
			$this->lastQuery = $this->GetNpcGroupProvisioningQuery();
		else if ($this->viewGroup == "Alchemy Survey")
			$this->lastQuery = $this->GetNpcGroupAlchemySurveyQuery();
		
		if ($this->lastQuery == "") return $this->ReportError("No valid NPC group specified!");
		
		$result = $this->db->query($this->lastQuery);
		
		if (!$result || $result->num_rows <= 0)
		{
			$this->ReportError("Failed to find any records for NPC Group '{$this->viewGroup}'!");
			return "";
		}
		
		$npcIds = array();
		
		while ($row = $result->fetch_assoc())
		{
			$npcIds[] = $row['id'];
		}
		
		$npcIds = implode(",", $npcIds);
		
		$query = "SELECT npcLoot.*, npc.name FROM npcLoot LEFT JOIN npc on npcId=npc.id WHERE npcId in ($npcIds);";
		return $query;
	}
	
	
	public function GetItemSearchQuery()
	{
		$safeName = $this->db->real_escape_string($this->viewItemName);
		
		$query = "SELECT npcLoot.*, npc.name FROM npcLoot LEFT JOIN npc on npcId=npc.id WHERE itemName LIKE '%$safeName%';";
		return $query;
	}
	
	
	public function GetSearchQuery()
	{
		if ($this->viewGroup != "") return $this->GetNpcGroupSearchQuery();
		if ($this->viewNpcName != "") return $this->GetNpcSearchQuery();
		if ($this->viewItemName != "") return $this->GetItemSearchQuery();
		
		return "";
	}
	
	
	public function LoadResults()
	{
		$this->lastQuery = $this->GetSearchQuery();
		if ($this->lastQuery == "") return false;
			
		$result = $this->db->query($this->lastQuery);
		if (!$result) return $this->ReportError("Failed to load search results!");
		
		$this->searchResults = array();
		
		while (($row = $result->fetch_assoc()))
		{
			$row['itemName'] = MakeEsoTitleCaseName($row['itemName']);
			$this->searchResults[] = $row;
		}
		
		$this->FindAllSalesPrice();
		//$this->LoadItemData();
		
		return true;
	}
	
	
	public function LoadItemData()
	{
		$this->itemLinkData["__gold"] = array(
				'icon' => '/esoui/art/currency/currency_gold_32.dds',
				'quality' => '1',
		);
		
		$this->itemLinkData["__telvar"] = array(
				'icon' => '/esoui/art/currency/currency_telvar_32.dds',
				'quality' => '1',
		);
		
		foreach ($this->searchResults as $i => $result)
		{
			$itemLink = $result['itemLink'];
			if ($itemLink == null || $itemLink == "") continue;
			
			$itemData = &$this->itemLinkData[$itemLink];
			
			if ($itemData != null)
			{
				$this->searchResults[$i]['itemData'] = $itemData;
				continue;				
			}
				
			$matches = ParseEsoItemLink($itemLink);
			if ($matches === false) continue;
			
			$itemId = intval($matches['itemId']);
			$intLevel = intval($matches['level']);
			$intSubtype = intval($matches['subtype']);
			
			$this->lastQuery = "SELECT * FROM minedItem WHERE itemId=$itemId AND internalLevel=$intLevel AND internalSubtype=$intSubtype;";
			$result = $this->db->query($this->lastQuery);
			if (!$result) continue;
			
			if ($result->num_rows == 0)
			{
				$this->lastQuery = "SELECT * FROM minedItem WHERE itemId=$itemId AND internalLevel=1 AND internalSubtype=1;";
				$result = $this->db->query($this->lastQuery);
				if (!$result) continue;
				if ($result->num_rows == 0) continue;
			}
			
			$itemData = $result->fetch_assoc();
			$this->itemLinkData[$itemLink] = $itemData;
			$this->searchResults[$i]['itemData'] = $itemData;				
		}
		
		return true;
	}
	
	
	public function ParseNpcItemResults()
	{
		$totalCount = 1;
		$totalQnt = 1;
		$this->zoneQntTotals = array();
		$this->itemQntTotals = array();
		$this->zoneCountTotals = array();
		$this->itemCountTotals = array();
		$this->itemSummary = array();
		
		if (count($this->searchResults) <= 0) return false;
		
		$this->zoneCountTotals['All'] = 0;
		$this->zoneQntTotals['All'] = 0;
		$this->zoneCountTotals['Summary'] = 0;
		$this->zoneQntTotals['Summary'] = 0;
		$this->zoneCountTotals['Writ Summary'] = 0;
		$this->zoneQntTotals['Writ Summary'] = 0;
		$this->zoneCountTotals['Hireling Summary'] = 0;
		$this->zoneQntTotals['Hireling Summary'] = 0;
		$this->zoneCountTotals['Item Summary'] = 0;
		$this->zoneQntTotals['Item Summary'] = 0;
		$this->zoneCountTotals['Survey Summary'] = 0;
		$this->zoneQntTotals['Survey Summary'] = 0;
		$this->zoneCountTotals['Motif Summary'] = 0;
		$this->zoneQntTotals['Motif Summary'] = 0;
		
		foreach ($this->searchResults as $i => $result)
		{
			$itemName = $result['itemName'];
			$itemLink = $result['itemLink'];
			
			if ($itemName == "__totalCount")
			{
				if ($this->zoneCountTotals[$result['zone']] == null) $this->zoneCountTotals[$result['zone']] = 0;
				if ($this->zoneQntTotals[$result['zone']] == null) $this->zoneQntTotals[$result['zone']] = 0;
				
				$this->zoneCountTotals[$result['zone']] += $result['count'];
				$this->zoneQntTotals[$result['zone']] += $result['qnt'];
				
				if ($result['zone'] == "")
				{
					$this->zoneCountTotals['All'] += $result['count'];
					$this->zoneQntTotals['All'] += $result['qnt'];
					$this->zoneCountTotals['Summary'] += $result['count'];
					$this->zoneQntTotals['Summary'] += $result['qnt'];
					$this->zoneCountTotals['Writ Summary'] += $result['count'];
					$this->zoneQntTotals['Writ Summary'] += $result['qnt'];
					$this->zoneCountTotals['Hireling Summary'] += $result['count'];
					$this->zoneQntTotals['Hireling Summary'] += $result['qnt'];
					$this->zoneCountTotals['Item Summary'] += $result['count'];
					$this->zoneQntTotals['Item Summary'] += $result['qnt'];
					$this->zoneCountTotals['Survey Summary'] += $result['count'];
					$this->zoneQntTotals['Survey Summary'] += $result['qnt'];
					$this->zoneCountTotals['Motif Summary'] += $result['count'];
					$this->zoneQntTotals['Motif Summary'] += $result['qnt'];
				}
				
				continue;
			}
			elseif ($itemName == "__gold")
			{
				$this->searchResults[$i]['itemName'] = "Gold";
				$itemName = "Gold";
			}
			elseif ($itemName == "__telvar")
			{
				$this->searchResults[$i]['itemName'] = "Telvar";
				$itemName = "Telvar";
			}
			
			if ($this->itemQntTotals[$itemName] == null) 
			{
				$this->itemQntTotals[$itemName] = array();
				$this->itemQntTotals[$itemName]['count'] = 0;
				$this->itemQntTotals[$itemName]['totalPrice'] = 0;
				$this->itemQntTotals[$itemName]['salesPrice'] = $result['salesPrice'];
				$this->itemQntTotals[$itemName]['itemName'] = $itemName;
				$this->itemQntTotals[$itemName]['itemLink'] = $result['itemLink'];
				$this->itemQntTotals[$itemName]['icon'] = $result['icon'];
				$this->itemQntTotals[$itemName]['itemType'] = $result['itemType'];
				$this->itemQntTotals[$itemName]['quality'] = $result['quality'];
				$this->itemQntTotals[$itemName]['trait'] = $result['trait'];
			}
			
			if ($this->itemCountTotals[$itemName] == null) 
			{
				$this->itemCountTotals[$itemName] = array();
				$this->itemCountTotals[$itemName]['count'] = 0;
				$this->itemCountTotals[$itemName]['totalPrice'] = 0;
				$this->itemCountTotals[$itemName]['salesPrice'] = $result['salesPrice'];
				$this->itemCountTotals[$itemName]['itemName'] = $itemName;
				$this->itemCountTotals[$itemName]['itemLink'] = $result['itemLink'];
				$this->itemCountTotals[$itemName]['icon'] = $result['icon'];
				$this->itemCountTotals[$itemName]['itemType'] = $result['itemType'];
				$this->itemCountTotals[$itemName]['quality'] = $result['quality'];
				$this->itemCountTotals[$itemName]['trait'] = $result['trait'];
			}
			
			$this->itemQntTotals[$itemName]['count']   += $result['qnt'];
			$this->itemCountTotals[$itemName]['count'] += $result['count'];
			
			if ($result['totalPrice'])
			{
				$this->itemQntTotals[$itemName]['totalPrice']   += $result['totalPrice'];
				$this->itemCountTotals[$itemName]['totalPrice'] += $result['totalPrice'];
			}			
				
			if ($result['itemType'] < 0) continue;
			if ($result['quality'] < 0) continue;
			
			$itemType = $result['itemType'];
			$quality = $result['quality'];
			$trait = $result['trait'];
			
			if ($this->itemSummary[$itemType] == null) $this->itemSummary[$itemType] = array();
			if ($this->itemSummary[$itemType]["all"] == null) $this->itemSummary[$itemType]["all"] = array( 'count' => 0, 'qnt' => 0, 'numItems' => 0, 'totalPrice' => 0);
			
			if ($quality >= 0)
			{
				if ($this->itemSummary[$itemType][$quality] == null) $this->itemSummary[$itemType][$quality] = array( 'count' => 0, 'qnt' => 0, 'numItems' => 0, 'totalPrice' => 0);
				$this->itemSummary[$itemType][$quality]['count'] += $result['count'];
				$this->itemSummary[$itemType][$quality]['qnt'] += $result['qnt'];
				$this->itemSummary[$itemType][$quality]['numItems'] += 1;
				
				if ($result['totalPrice'])
				{
					$this->itemSummary[$itemType][$quality]['totalPrice'] += $result['totalPrice'];
				}
			}
			
			if ($trait > 0)
			{				
				$traitName = GetEsoItemTraitText($trait);
				$trait = $trait + 1000;
				
				if ($this->itemSummary[$itemType][$trait] == null) $this->itemSummary[$itemType][$trait] = array( 'count' => 0, 'qnt' => 0, 'numItems' => 0, 'totalPrice' => 0);
				$this->itemSummary[$itemType][$trait]['count'] += $result['count'];
				$this->itemSummary[$itemType][$trait]['qnt'] += $result['qnt'];
				$this->itemSummary[$itemType][$trait]['numItems'] += 1;

				if ($this->itemSummary[$traitName] == null) $this->itemSummary[$traitName] = array();
				if ($this->itemSummary[$traitName]["all"] == null) $this->itemSummary[$traitName]["all"] = array( 'count' => 0, 'qnt' => 0, 'numItems' => 0, 'totalPrice' => 0);
				
				$this->itemSummary[$traitName]["all"]['trait'] = $result['trait'];
				$this->itemSummary[$traitName]["all"]['count'] += $result['count'];
				$this->itemSummary[$traitName]["all"]['qnt'] += $result['qnt'];
				$this->itemSummary[$traitName]["all"]['numItems'] += 1;
				
				if ($result['totalPrice'])
				{
					$this->itemSummary[$itemType][$trait]['totalPrice'] += $result['qnt'];
					$this->itemSummary[$traitName]["all"]['totalPrice'] += $result['totalPrice'];
				}
			}
			
			$this->itemSummary[$itemType]["all"]['count'] += $result['count'];
			$this->itemSummary[$itemType]["all"]['qnt'] += $result['qnt'];
			$this->itemSummary[$itemType]["all"]['numItems'] += 1;
			
			if ($result['totalPrice'])
			{
				$this->itemSummary[$itemType]["all"]['totalPrice'] += $result['totalPrice'];
			}
		}
		
		if ($this->zoneQntTotals['']   != null) $totalQnt   = $this->zoneQntTotals[''];
		if ($this->zoneCountTotals[''] != null) $totalCount = $this->zoneCountTotals[''];
		if ($totalQnt <= 0) $totalQnt = 1;
		if ($totalCount <= 0) $totalCount = 1;
		
		foreach ($this->searchResults as $i => $result)
		{
			if ($result['itemName'] != "__totalCount")
			{
				$zoneQnt = $this->zoneCountTotals[$result['zone']];
				$this->searchResults[$i]['dropZoneRatio'] = $result['qnt'] / $zoneQnt;
				$this->searchResults[$i]['dropRatio'] = $result['qnt'] / $totalCount;
			}
		}

		foreach ($this->itemQntTotals as $itemName => $qntData)
		{
			$countData = $this->itemCountTotals[$itemName];
			
			$newResult = array();
			$newResult['zone'] = "All";
			$newResult['itemName'] = $qntData['itemName'];
			$newResult['itemLink'] = $qntData['itemLink'];
			
			$newResult['itemType'] = $qntData['itemType'];
			$newResult['quality'] = $qntData['quality'];
			$newResult['trait'] = $qntData['trait'];
			$newResult['icon'] = $qntData['icon'];
			$newResult['salesPrice'] = $qntData['salesPrice'];
			$newResult['totalPrice'] = $qntData['totalPrice'];
			
			$newResult['qnt'] = $qntData['count'];
			$newResult['count'] = $countData['count'];
			$newResult['dropZoneRatio'] = $qntData['count'] / $totalCount;
			$newResult['dropRatio'] = $newResult['dropZoneRatio'];
			$newResult['totalQnt'] = $totalCount;
			$newResult['totalCount'] = $totalCount;
			
			$this->searchResults[] = $newResult;
		}
		
		foreach ($this->itemSummary as $itemType => $data1)
		{
			$typeName = GetEsoItemTypeText($itemType);
			if ($typeName == "") $typeName = "None";
			
			foreach ($data1 as $dataValue => $data2)
			{
				if ($dataValue === "all")
				{
					if (!is_numeric($itemType))
					{
						$itemSuffix = " (All)";
						$trait = $data2['trait'];
						$typeName = $itemType;
						$itemType = -1;
						$qualityValue = -1;
					}
					else
					{
						$qualityValue = $dataValue;
						$itemSuffix = " (All)";
						$trait = -1;
					}
				}
				else if ($dataValue > 900)
				{
					$qualityValue = -1;
					$trait = $dataValue - 1000;
					$itemSuffix = " (".GetEsoItemTraitText($trait).")";
				}
				else
				{
					$qualityValue = $dataValue;
					$itemSuffix = " (".GetEsoItemQualityText($dataValue).")";
					$trait = -1;
				}
				
				$newResult = array();
				
				$newResult['zone'] = "Summary";
				$newResult['itemName'] = "$typeName$itemSuffix";
				$newResult['itemLink'] = "";
								
				$newResult['itemType'] = $itemType;
				$newResult['quality'] = $qualityValue;
				$newResult['trait'] = $trait;
				$newResult['icon'] = "";
				//$newResult['salesPrice'] = $data2['salesPrice'];
				$newResult['totalPrice'] = $data2['totalPrice'];
				
				$newResult['qnt'] = $data2['qnt'];
				$newResult['count'] = $data2['count'];
				$newResult['dropZoneRatio'] = $data2['qnt'] / $totalCount;
				$newResult['dropRatio'] = $data2['qnt'] / $totalCount;
				$newResult['totalQnt'] = $totalQnt;
				$newResult['totalCount'] = $totalCount;
				
				$this->searchResults[] = $newResult;				
			}			
		}
		
		if ($this->viewExtra == "writ")
		{
			$this->searchResults[] = $this->MakeNpcSummaryResult("Writ Summary", "Master Writ", array(60), array('all'), $this->itemSummary, $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResult("Writ Summary", "Survey Map", array(5), array(3), $this->itemSummary, $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResult("Writ Summary", "Fragment", array(5), array(4), $this->itemSummary, $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResult("Writ Summary", "Gold Temper", array(41, 43, 42), array(5, 5, 5), $this->itemSummary, $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResult("Writ Summary", "Material Shipment", array(18), array('all'), $this->itemSummary, $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResult("Writ Summary", "Ornate Item", array('Ornate'), array('all'), $this->itemSummary, $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResult("Writ Summary", "Intricate Item", array('Intricate'), array('all'), $this->itemSummary, $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResult("Writ Summary", "Repair Kit", array(9), array(2), $this->itemSummary, $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResult("Writ Summary", "Trait Stone", array(45, 46), array('all', 'all'), $this->itemSummary, $totalQnt, $totalCount);
			
			$this->searchResults[] = $this->MakeNpcSummaryResult("Writ Summary", "Solvent", array(33, 58), array('all', 'all'), $this->itemSummary, $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResult("Writ Summary", "Reagent", array(31), array('all'), $this->itemSummary, $totalQnt, $totalCount);
			
			$this->searchResults[] = $this->MakeNpcSummaryResult("Writ Summary", "Recipe (Epic)", array(29), array(4), $this->itemSummary, $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResult("Writ Summary", "Recipe (Superior)", array(29), array(3), $this->itemSummary, $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResult("Writ Summary", "Recipe (Fine)", array(29), array(2), $this->itemSummary, $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResult("Writ Summary", "Ingredient (Normal)", array(10), array(1), $this->itemSummary, $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResult("Writ Summary", "Ingredient (Epic)", array(10), array(4), $this->itemSummary, $totalQnt, $totalCount);
			
			$this->searchResults[] = $this->MakeNpcSummaryResult("Writ Summary", "Glyph", array(21, 26, 20), array('all', 'all', 'all'), $this->itemSummary, $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResult("Writ Summary", "Soul Gem", array(19), array('all'), $this->itemSummary, $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResult("Writ Summary", "Kuta", array(52), array(5), $this->itemSummary, $totalQnt, $totalCount);
			
			if ($this->salesPriceServer != "")
			{
				$this->searchResults[] = $this->MakeNpcSummaryResultAll("Writ Summary", "All", $this->itemSummary, $totalQnt, $totalCount);
			}
		}
		else if ($this->viewExtra == "hireling")
		{
			$this->searchResults[] = $this->MakeNpcSummaryResult("Hireling Summary", "Style Material", array(44), array('all'), $this->itemSummary, $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResult("Hireling Summary", "Trait Stone", array(45, 46), array('all', 'all'), $this->itemSummary, $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResult("Hireling Summary", "Refined Material", array(38, 36, 40), array('all', 'all', 'all'), $this->itemSummary, $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResult("Hireling Summary", "Raw Material", array(35, 39, 37), array('all', 'all', 'all'), $this->itemSummary, $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResult("Hireling Summary", "Improvement Material (All)", array(41, 43, 42), array('all', 'all', 'all'), $this->itemSummary, $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResult("Hireling Summary", "Improvement Material (Fine)", array(41, 43, 42), array(2, 2, 2), $this->itemSummary, $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResult("Hireling Summary", "Improvement Material (Superior)", array(41, 43, 42), array(3 , 3, 3), $this->itemSummary, $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResult("Hireling Summary", "Improvement Material (Epic)", array(41, 43, 42), array(4, 4, 4), $this->itemSummary, $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResult("Hireling Summary", "Improvement Material (Legendary)", array(41, 43, 42), array(5, 5, 5), $this->itemSummary, $totalQnt, $totalCount);
			
			
			if ($this->salesPriceServer != "")
			{
				$this->searchResults[] = $this->MakeNpcSummaryResultAll("Hireling Summary", "All", $this->itemSummary, $totalQnt, $totalCount);
			}
		}
		else if ($this->viewExtra == "item")
		{
			$this->searchResults[] = $this->MakeNpcSummaryResult("Item Summary", "Armor / Weapons (All)", array(1, 2), array('all', 'all'), $this->itemSummary, $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResult("Item Summary", "Armor / Weapons (Normal)", array(1, 2), array(1, 1), $this->itemSummary, $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResult("Item Summary", "Armor / Weapons (Fine)", array(1, 2), array(2, 2), $this->itemSummary, $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResult("Item Summary", "Armor / Weapons (Superior)", array(1, 2), array(3, 3), $this->itemSummary, $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResult("Item Summary", "Armor / Weapons (Epic)", array(1, 2), array(4, 4), $this->itemSummary, $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResult("Item Summary", "Armor / Weapons (Legendary)", array(1, 2), array(5, 5), $this->itemSummary, $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResult("Item Summary", "Paintings", array(61), array(4), $this->itemSummary, $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResult("Item Summary", "Treasure Maps", array(5), array(3), $this->itemSummary, $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResult("Item Summary", "Glyphs (All)", array(21, 26, 20), array('all', 'all', 'all'), $this->itemSummary, $totalQnt, $totalCount);
			//$this->searchResults[] = $this->MakeNpcSummaryResult("Item Summary", "Gold", array(21, 26, 20), array('all', 'all', 'all'), $this->itemSummary, $totalQnt, $totalCount);
			
			if ($this->salesPriceServer != "")
			{
				$this->searchResults[] = $this->MakeNpcSummaryResultAll("Item Summary", "All", $this->itemSummary, $totalQnt, $totalCount);
			}				
		}
		else if ($this->viewExtra == "alchemysurvey")
		{
			$this->searchResults[] = $this->MakeNpcSummaryResultName("Survey Summary", "Columbine", $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResultName("Survey Summary", "Mountain Flower",  $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResultName("Survey Summary", "Corn Flower", $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResultName("Survey Summary", "Dragonthorn", $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResultName("Survey Summary", "Bugloss", $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResultName("Survey Summary", "Lady's Smock", $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResultName("Survey Summary", "Wormwood", $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResultName("Survey Summary", "Blessed Thistle", $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResultName("Survey Summary", "Worms", $totalQnt, $totalCount);
			$this->searchResults[] = $this->MakeNpcSummaryResultName("Survey Summary", "Crawlers", $totalQnt, $totalCount);
			
			if ($this->salesPriceServer != "")
			{
				$this->searchResults[] = $this->MakeNpcSummaryResultAll("Survey Summary", "All", $this->itemSummary, $totalQnt, $totalCount);
			}
		}
		else if ($this->viewExtra == "motif")
		{
			for ($i = 15; $i < 100; ++$i)
			{
				//$motifResult = $this->MakeNpcSummaryResultNamePrefix("Motif Summary", "Crafting Motif $i:", $totalQnt, $totalCount);
				$motifResult = $this->MakeNpcSummaryResultNameMatch("Motif Summary", "/(Crafting Motif $i: .*)\b\w+/", $totalQnt, $totalCount);
				if ($motifResult) $this->searchResults[] = $motifResult;
			}			
			
			if ($this->salesPriceServer != "")
			{
				//$this->searchResults[] = $this->MakeNpcSummaryResultAll("Motif Summary", "All", $this->itemSummary, $totalQnt, $totalCount);
				$this->searchResults[] = $this->MakeNpcSummaryResultAll("Summary", "All", $this->itemSummary, $totalQnt, $totalCount);
			}
		}
		else if ($this->salesPriceServer != "")
		{
			$this->searchResults[] = $this->MakeNpcSummaryResultAll("Summary", "All", $this->itemSummary, $totalQnt, $totalCount);
		}
		
		usort($this->searchResults, array('CEsoViewNpcLoot', 'SortNpcItemSearchResults'));
	}
	
	
	public function MakeNpcSummaryResultName($zone, $itemName, $totalQnt, $totalCount)
	{
		$qntData = $this->itemQntTotals[$itemName];
		$countData = $this->itemCountTotals[$itemName];
		
		if ($qntData == null || $countData == null) return null;
		
		$qnt = $qntData['count'];
		$count = $countData['count'];
		
		$newResult = array();
		
		$newResult['zone'] = $zone;
		$newResult['itemName'] = $itemName;
		$newResult['itemLink'] = "";
		
		$newResult['itemType'] = -1;
		$newResult['quality'] = -1;
		$newResult['trait'] = -1;
		$newResult['icon'] = "";
		$newResult['salesPrice'] = $qntData['salesPrice'];
		$newResult['totalPrice'] = $qntData['totalPrice'];
		
		$newResult['qnt'] = $qnt;
		$newResult['count'] = $count;
		$newResult['dropZoneRatio'] = $qnt / $totalCount;
		$newResult['dropRatio'] = $qnt / $totalCount;
		$newResult['totalQnt'] = $totalQnt;
		$newResult['totalCount'] = $totalCount;
		
		return $newResult;
	}
	
	
	public function MakeNpcSummaryResultNamePrefix($zone, $namePrefix, $totalQnt, $totalCount)
	{
		$totalPrefixQnt = 0;
		$totalPrefixCount = 0;
		$totalMotifSalesPrice = 0;
		$totalMotifPrice = 0;
		$motifCount = 0;
		
		foreach ($this->itemQntTotals as $itemName => $qntData)
		{
			if (strncmp($itemName, $namePrefix, strlen($namePrefix)) !== 0) continue;
				
			$countData = $this->itemCountTotals[$itemName];
			if ($countData == null) continue;
			
			++$motifCount;
			
			$totalPrefixQnt += $qntData['count'];
			$totalPrefixCount += $countData['count'];
			
			$totalMotifSalesPrice += $qntData['salesPrice'];
			$totalMotifPrice += $qntData['totalPrice'];
		}
		
		if ($motifCount == 0) return null;
						
		$newResult = array();
		
		$newResult['zone'] = $zone;
		$newResult['itemName'] = $namePrefix;
		$newResult['itemLink'] = "";
		
		$newResult['itemType'] = -1;
		$newResult['quality'] = -1;
		$newResult['trait'] = -1;
		$newResult['icon'] = "";
		$newResult['salesPrice'] = $totalMotifSalesPrice / $motifCount;
		$newResult['totalPrice'] = $totalMotifPrice;
		
		$newResult['qnt'] = $totalPrefixQnt;
		$newResult['count'] = $totalPrefixCount;
		$newResult['dropZoneRatio'] = $totalPrefixQnt / $totalCount;
		$newResult['dropRatio'] = $totalPrefixQnt / $totalCount;
		$newResult['totalQnt'] = $totalQnt;
		$newResult['totalCount'] = $totalCount;
		
		return $newResult;
	}
	
	
	public function MakeNpcSummaryResultNameMatch($zone, $nameRegex, $totalQnt, $totalCount)
	{
		$totalPrefixQnt = 0;
		$totalPrefixCount = 0;
		$totalMotifSalesPrice = 0;
		$totalMotifPrice = 0;
		$motifCount = 0;
		$matchName = $nameRegex;
		
		foreach ($this->itemQntTotals as $itemName => $qntData)
		{
			//if (strncmp($itemName, $namePrefix, strlen($namePrefix)) !== 0) continue;
			$result = preg_match($nameRegex, $itemName, $matches);
			if (!$result) continue;
			if ($matches[1]) $matchName = $matches[1]; 
				
			$countData = $this->itemCountTotals[$itemName];
			if ($countData == null) continue;
			
			++$motifCount;
			
			$totalPrefixQnt += $qntData['count'];
			$totalPrefixCount += $countData['count'];
			
			$totalMotifSalesPrice += $qntData['salesPrice'];
			$totalMotifPrice += $qntData['totalPrice'];
		}
		
		if ($motifCount == 0) return null;
						
		$newResult = array();
		
		$newResult['zone'] = $zone;
		$newResult['itemName'] = $matchName;
		$newResult['itemLink'] = "";
		
		$newResult['itemType'] = -1;
		$newResult['quality'] = -1;
		$newResult['trait'] = -1;
		$newResult['icon'] = "";
		$newResult['salesPrice'] = $totalMotifSalesPrice / $motifCount;
		$newResult['totalPrice'] = $totalMotifPrice;
		
		$newResult['qnt'] = $totalPrefixQnt;
		$newResult['count'] = $totalPrefixCount;
		$newResult['dropZoneRatio'] = $totalPrefixQnt / $totalCount;
		$newResult['dropRatio'] = $totalPrefixQnt / $totalCount;
		$newResult['totalQnt'] = $totalQnt;
		$newResult['totalCount'] = $totalCount;
		
		return $newResult;
	}
	
	
	public function MakeNpcSummaryResult($zone, $itemName, $itemType, $quality, $itemSummary, $totalQnt, $totalCount)
	{
		$newResult = array();
		$qnt = 0;
		$count = 0;
		$totalPrice = 0 ;
		
		for ($i = 0; $i < count($itemType); ++$i)
		{
			if ($i > count($quality)) break;
			
			$sumData1 = $itemSummary[$itemType[$i]];
			if ($sumData1 == null) continue;
		
			$sumData2 = $sumData1[$quality[$i]];
			if ($sumData2 == null) continue;
			
			if ($sumData2['totalPrice']) $totalPrice += $sumData2['totalPrice']; 
		
			$qnt += $sumData2['qnt'];
			$count += $sumData2['count'];
		}
		
		if ($qnt == 0 || $count == 0) return null;
		 
		$newResult['zone'] = $zone;
		$newResult['itemName'] = $itemName;
		$newResult['itemLink'] = "";
		
		$newResult['itemType'] = -1;
		$newResult['quality'] = -1;
		$newResult['trait'] = -1;
		$newResult['icon'] = "";
		$newResult['totalPrice'] = $totalPrice;		
		
		$newResult['qnt'] = $qnt;
		$newResult['count'] = $count;
		$newResult['dropZoneRatio'] = $qnt / $totalCount;
		$newResult['dropRatio'] = $qnt / $totalCount;
		$newResult['totalQnt'] = $totalQnt;
		$newResult['totalCount'] = $totalCount;
		
		return $newResult;
	}
	
	
	public function MakeNpcSummaryResultAll($zone, $itemName, $itemSummary, $totalQnt, $totalCount)
	{
		$newResult = array();
		$qnt = 0;
		$count = 0;
		$totalPrice = 0 ;
	
		foreach ($itemSummary as $itemType => $sumData1)
		{
			if (!is_numeric($itemType)) continue;
			
			$sumData2 = $sumData1['all'];
			if ($sumData2 == null) continue;
				
			if ($sumData2['totalPrice']) $totalPrice += $sumData2['totalPrice'];
	
			$qnt += $sumData2['qnt'];
			$count += $sumData2['count'];
		}
	
		if ($qnt == 0 || $count == 0) return null;
			
		$newResult['zone'] = $zone;
		$newResult['itemName'] = $itemName;
		$newResult['itemLink'] = "";
	
		$newResult['itemType'] = -1;
		$newResult['quality'] = -1;
		$newResult['trait'] = -1;
		$newResult['icon'] = "";
		$newResult['totalPrice'] = $totalPrice;
	
		$newResult['qnt'] = $qnt;
		$newResult['count'] = $count;
		$newResult['dropZoneRatio'] = 0;
		$newResult['dropRatio'] = 0;
		$newResult['totalQnt'] = -1;
		$newResult['totalCount'] = $totalCount;
	
		return $newResult;
	}
	
	
	public function SortNpcItemSearchResults($a, $b)
	{
		$zone1 = $a['zone'];
		$zone2 = $b['zone'];
		
		if ($zone1 == "") $zone1 = "ZZZZ";
		if ($zone2 == "") $zone2 = "ZZZZ";
		
		if ($zone1 == "All") $zone1 = " 3";
		if ($zone2 == "All") $zone2 = " 3";
		if ($zone1 == "Summary") $zone1 = " 2";
		if ($zone2 == "Summary") $zone2 = " 2";
		if ($zone1 == "Writ Summary") $zone1 = " 1";
		if ($zone2 == "Writ Summary") $zone2 = " 1";
		if ($zone1 == "Hireling Summary") $zone1 = " 1";
		if ($zone2 == "Hireling Summary") $zone2 = " 1";
		if ($zone1 == "Item Summary") $zone1 = " 1";
		if ($zone2 == "Item Summary") $zone2 = " 1";
		if ($zone1 == "Survey Summary") $zone1 = " 1";
		if ($zone2 == "Survey Summary") $zone2 = " 1";
		if ($zone1 == "Motif Summary") $zone1 = " 1";
		if ($zone2 == "Motif Summary") $zone2 = " 1";
		
		$compare = strcasecmp($zone1, $zone2);
		if ($compare != 0) return $compare;
		
		return strcasecmp($a['itemName'], $b['itemName']);
	}
	
	
	public function GetNpcResultsCsv()
	{
		$this->ParseNpcItemResults();
		$npcName = $this->npcRecord['name'];
		
		$output = "\"Zone\",\"NPC\",\"Item Link\",\"Item Name\",\"Item Type\",\"Quality\",\"Trait\",\"Count\",\"Qnt\",\"Stack Size\",\"Total\",\"Drop Chance\",\"Stack Chance\"";
		
		if ($this->salesPriceServer != "") 
		{
			$output .= ",\"Unit Value\",";
			$output .= "\"Total Value\",";
			$output .= "\"Avg Value\"";
		}
		
		$output .= "\n";
		
		foreach ($this->searchResults as $result)
		{
			//if ($result['itemName'] == "__totalCount") continue;
			
			if ($result['name'] != null) $npcName = $result['name'];
				
			$zone = $this->EscapeStringCsv($result['zone']);
			$itemLink = $this->EscapeStringCsv($result['itemLink']);
			$itemName = $this->EscapeStringCsv($result['itemName']);
			$qnt = $result['qnt'];
			$count = $result['count'];
			$totalZoneQnt = $this->zoneCountTotals[$zone];
			$dropChance = round($result['dropZoneRatio'] * 100, 4);
			$itemType = GetEsoItemTypeText($result['itemType']);
			if ($itemType == "") $itemType = "None";
			$quality = $result['quality'];
			if ($quality < 0) $quality = "";
			$trait = GetEsoItemTraitText($result['trait']);
			$totalPrice = $result['totalPrice'];
			
			$stackSize = round($qnt/$count, 2);
			//if ($quality == "all") $stackSize = "";
			$stackChance = round($count / $totalZoneQnt * 100, 4);
			
			$output .= "\"$zone\",\"$npcName\"\"$itemLink\",\"$itemName\",\"$itemType\",\"$quality\",\"$trait\",$count,$qnt,\"$stackSize\",$totalZoneQnt,$dropChance%,$stackChance%";
			
			if ($this->salesPriceServer != "")
			{
				if ($totalPrice == null)
				{
					$output .= ",0,";
					$output .= "0,";
					$output .= "0";
				}
				else
				{
					$avgPrice = number_format($totalPrice / $totalZoneQnt, 2);
					$totalPrice = number_format($totalPrice);
					
					$unitPrice = "";
					if ($result['salesPrice'] > 0)	$unitPrice = number_format($result['salesPrice'], 2) . "";
					$output .= ",$unitPrice,";
					$output .= "$totalPrice,";
					$output .= "$avgPrice";
				}
			}
			
			$output .= "\n";
		}
		
		return $output;
	}
	
	
	public function GetNpcResultsHtml()
	{
		$this->ParseNpcItemResults();
		
		if ($this->viewGroup != "")
			$npcName = "<b>Group {$this->viewGroup}</b>";
		else
			$npcName = "<b>{$this->viewNpcName}</b>";
		
		if (count($this->searchResults) <= 0)
		{
			$output = "No loot data found for $npcName!<p>";
			return $output;
		}
		
		$output = "Showing NPC loot drop data for $npcName:<p>";
		$output .= "<table id='esonplResultsTable'>";
		
		$output .= "<tr>";
		$output .= "<th>Zone</th>";
		if ($this->viewGroup != "") $output .= "<th>NPC</th>";
		$output .= "<th>Item Name</th>";
		$output .= "<th>Item Type</th>";
		$output .= "<th>Trait</th>";
		$output .= "<th>Count</th>";
		$output .= "<th>Qnt</th>";
		$output .= "<th>Total</th>";
		$output .= "<th>Drop Chance</th>";
		
		if ($this->salesPriceServer != "") 
		{
			$output .= "<th>Unit Value</th>";
			$output .= "<th>Total Value</th>";
			$output .= "<th>Avg Value</th>";
		}
		
		$output .= "</tr>";
		
		$totalQnt = 0;
		if ($this->zoneCountTotals[''] != null) $totalQnt = $this->zoneCountTotals[''];
		$lastZone = "";
		$npcName = $this->viewNpcName;
				
		foreach ($this->searchResults as $result)
		{
			if ($result['itemName'] == "__totalCount" || $result['itemName'] == "") continue;
			
			if ($result['name'] != null) $npcName = $result['name'];
			
			$zone = $result['zone'];
			$itemLink = $result['itemLink'];
			$itemName = $result['itemName'];
			$count = $result['count'];
			$qnt = $result['qnt'];
			$totalZoneQnt = $this->zoneCountTotals[$zone];
			$dropChance = "";
			if ($result['dropZoneRatio'] > 0) $dropChance = "" . round($result['dropZoneRatio'] * 100, 1) . "%";
			$iconUrl = "";
			if ($result['icon']) $iconUrl = MakeEsoIconLink($result['icon']);
			$itemType = GetEsoItemTypeText($result['itemType']);
			if ($itemType == "") $itemType = "None";
			$quality = $result['quality'];
			$trait = GetEsoItemTraitText($result['trait']);
			$totalPrice = $result['totalPrice'];
			
			if ($lastZone != $zone && ($lastZone == "All" || $lastZone == "Summary" || $lastZone == "Writ Summary" || $lastZone == "Hireling Summary" || $lastZone == "Item Summary" || $lastZone == "Survey Summary" || $lastZone == "Motif Summary"))
			{
				$output .= "<tr>";
				$output .= "<td colspan='6'></td>";
				$output .= "</tr>";
			}
			
			$output .= "<tr>";
			$output .= "<td>$zone</td>";
			if ($this->viewGroup != "") $output .= "<td>$npcName</td>";
			
			if ($iconUrl == "")
				$output .= "<td><div class='esonplItemLink eso_item_link_q$quality' itemlink='$itemLink'>$itemName</div></td>";
			elseif (substr($itemLink, 0, 2) != "|H")
				$output .= "<td><div class='esonplItemLink eso_item_link_q$quality' itemlink='$itemLink'><img src='$iconUrl' class='esonplItemIcon'>$itemName</div></td>";
			else
				$output .= "<td><div class='eso_item_link esonplItemLink eso_item_link_q$quality' itemlink='$itemLink'><img src='$iconUrl' class='esonplItemIcon'>$itemName</div></td>";
			
			$output .= "<td>$itemType</td>";
			$output .= "<td>$trait</td>";
			$output .= "<td>$count</td>";
			$output .= "<td>$qnt</td>";
			$output .= "<td>$totalZoneQnt</td>";
			$output .= "<td>$dropChance</td>";
			
			if ($this->salesPriceServer != "")
			{
				if ($totalPrice == null)
				{
					$output .= "<td></td>";
					$output .= "<td></td>";
					$output .= "<td></td>";
				}
				else
				{
					$avgPrice = number_format($totalPrice / $totalZoneQnt, 2);
					$totalPrice = number_format($totalPrice);
					
					$unitPrice = "";
					if ($result['salesPrice'] > 0)	$unitPrice = number_format($result['salesPrice'], 2) . " gp";
					$output .= "<td>$unitPrice</td>";
					$output .= "<td>$totalPrice gp</td>";
					$output .= "<td>$avgPrice gp</td>";
				}
			}
			
			$output .= "</tr>";
			
			$lastZone = $zone;
		}
		
		$output .= "</table>";
		return $output;
	}
	
	
	public function SortItemSearchResults($a, $b)
	{
		$compare = strcasecmp($a['name'], $b['name']);
		if ($compare != 0) return $compare;
		
		$compare = strcasecmp($a['zone'], $b['zone']);
		if ($compare != 0) return $compare;
		
		$compare = strcasecmp($a['itemName'], $b['itemName']);
		return $compare;
	}
	
	
	public function GetItemResultsCsv()
	{
		usort($this->searchResults, array('CEsoViewNpcLoot', 'SortItemSearchResults'));
		
		$output = "\"NPC\",\"Zone\",\"Item Link\",\"Item Name\",\"Item Type\",\"Quality\",\"Trait\",\"Qnt\",\"Count\",\"Zone Qnt\"\n";
	
		foreach ($this->searchResults as $result)
		{
			$npc = $this->EscapeStringCsv($result['name']);
			$zone = $this->EscapeStringCsv($result['zone']);
			$itemLink = $this->EscapeStringCsv($result['itemLink']);
			$itemName = $this->EscapeStringCsv($result['itemName']);
			$qnt = $result['qnt'];
			$count = $result['count'];
			$totalZoneQnt = $this->zoneCountTotals[$zone];
			$itemType = GetEsoItemTypeText($result['itemType']);
			if ($itemType == "") $itemType = "None";
			$quality = $result['quality'];
			$trait = GetEsoItemTraitText($result['trait']);
	
			$output .= "\"$npc\",\"$zone\",\"$itemLink\",\"$itemName\",\"$itemType\",$quality,\"$trait\",$qnt,$count,$totalZoneQnt\n";
		}
	
		return $output;
	}
	
	
	public function EscapeStringCsv($value)
	{
		$newValue = str_replace("\\", "\\\\", $value);
		$newValue = str_replace("\"", "\\\"", $value);
		return $newValue;
	}
	
	
	public function GetItemResultsHtml() 
	{
		usort($this->searchResults, array('CEsoViewNpcLoot', 'SortItemSearchResults'));
		
		$output = "Showing NPC loot drop data for item search string '<b>{$this->viewItemName}</b>':<p>";
		
		$output .= "<table id='esonplResultsTable'>";
		$output .= "<tr>";
		$output .= "<th>NPC</th>";
		$output .= "<th>Zone</th>";
		$output .= "<th>Item</th>";
		$output .= "<th>Item Type</th>";
		$output .= "<th>Trait</th>";
		$output .= "<th>Qnt</th>";
		$output .= "<th></th>";
		$output .= "</tr>";
		
		foreach ($this->searchResults as $result)
		{
			$npcName = $result['name'];
			$safeName = urlencode($npcName);
			$npcId = $result['npcId'];
			$zone = $result['zone'];
			$itemName = $result['itemName'];
			$itemLink = $result['itemLink'];
			$itemType = GetEsoItemTypeText($result['itemType']);
			if ($itemType == "") $itemType = "None";
			$qnt = $result['qnt'];
			$quality = $result['quality'];
			$trait = GetEsoItemTraitText($result['trait']);
			
			$iconUrl = "";
			if ($result['icon']) $iconUrl = MakeEsoIconLink($result['icon']);
			
			$links  = "<a href='/viewlog.php?action=view&record=npc&id=$npcId'>View NPC</a>";
			$links .= " <a href='/viewNpcLoot.php?npc=$safeName'>View NPC Loots</a>";
			
			$output .= "<tr>";
			$output .= "<td>$npcName</td>";
			$output .= "<td>$zone</td>";
			
			if ($iconUrl == "")
				$output .= "<td><div class='esonplItemLink eso_item_link_q$quality' itemlink='$itemLink'>$itemName</div></td>";
			elseif (substr($itemLink, 0, 2) != "|H")
				$output .= "<td><div class='esonplItemLink eso_item_link_q$quality' itemlink='$itemLink'><img src='$iconUrl' class='esonplItemIcon'>$itemName</div></td>";
			else
				$output .= "<td><div class='eso_item_link esonplItemLink eso_item_link_q$quality' itemlink='$itemLink'><img src='$iconUrl' class='esonplItemIcon'>$itemName</div></td>";
				
			$output .= "<td>$itemType</td>";
			$output .= "<td>$trait</td>";
			$output .= "<td>$qnt</td>";
			$output .= "<td>$links</td>";
			$output .= "</tr>";
		}
		
		$output .= "</table>";
		return $output;
	}
	
	
	public function GetResultsHtml()
	{
		if ($this->viewNpcName != "" || $this->viewGroup != "") return $this->GetNpcResultsHtml();
		if ($this->viewItemName != "") return $this->GetItemResultsHtml();
		
		return "No NPC or item specified!";
	}
	
	
	public function GetOutputHtml()
	{
		$replacePairs = array(
				'{npcLootResults}' => $this->GetResultsHtml(),
		);
	
		$output = strtr($this->htmlTemplate, $replacePairs);

		return $output;
	}
	
	
	public function GetOutputCsv()
	{
		if ($this->viewNpcName != "" || $this->viewGroup != "") return $this->GetNpcResultsCsv();
		if ($this->viewItemName != "") return $this->GetItemResultsCsv();
		
		return "No NPC or item specified!";
	}
	
		
	public function Render()
	{
		$this->LoadSalesPrices();
		$this->LoadResults();
		
		if ($this->output == "csv")
		{
			$this->WriteCsvHeaders();
			print($this->GetOutputCsv());
		}
		else 
		{
			$this->WriteHtmlHeaders();
			print($this->GetOutputHtml());
		}
		
	}
	
};


$viewNpcLoot = new CEsoViewNpcLoot();
$viewNpcLoot->Render();







