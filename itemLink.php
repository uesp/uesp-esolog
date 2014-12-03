<?php

// Database users, passwords and other secrets
require("/home/uesp/secrets/esolog.secrets");


class CEsoItemLink
{
	const ESOIL_HTML_TEMPLATE = "templates/esoitemlink_template.txt";
	const ESOIL_ICON_PATH = "/home/uesp/www/eso/gameicons/";
	const ESOIL_ICON_URL = "http://content3.uesp.net/eso/gameicons/";
	const ESOIL_ICON_UNKNOWN = "unknown.png";
	
	public $inputParams = array();
	public $itemId = 0;
	public $itemLink = "";
	public $itemLevel = 1;		// 1-64
	public $itemQuality = 1;	// 1-5
	public $itemIntLevel = -1;	// 1-50
	public $itemIntType = -1;	// 1-400
	public $itemRecord = array();
	public $itemAllData = array();
	public $outputType = "html";
	public $showAll = false;
	public $itemErrorDesc = "";
	public $db = null;
	public $htmlTemplate = "";
	
	
	public function __construct ()
	{
		$this->SetInputParams();
		$this->ParseInputParams();
		$this->InitDatabase();
		
		$this->htmlTemplate = file_get_contents(self::ESOIL_HTML_TEMPLATE); 
	}
	
	
	public function ReportError($errorMsg)
	{
		print($errorMsg);
		error_log($errorMsg);
		return false;
	}
	
	
	public function ParseItemLink($itemLink)
	{
		//|H0:item:70:62:50:0:0:0:0:0:0:0:0:0:0:0:0:1:0:0:0:0|h[Cured%20Kwama%20Leggings]|h
		#$result = preg_match("#\|H[0-9A-Fa-f]+:item:(?<id>[0-9]+):(?<subtype>[0-9]+):(?<level>[0-9]+):(?<enchantid>[0-9]+):(?<enchanttype>[0-9]+):(?<enchantlevel>[0-9]+):(data):(?<style>[0-9]+):(?<id>[0-9]+):(?<id>[0-9]+):(?<id>[0-9]+):(?<id>[0-9]+)\|h([^\|]+)\|h#s", $itemLink);
		$matches = array();
		$result = preg_match('/\|H(?P<color>[A-Za-z0-9]*)\:item\:(?P<itemId>[0-9]*)\:(?P<subtype>[0-9]*)\:(?P<level>[0-9]*)\:(?P<enchantId>[0-9]*)\:(?P<enchantSubtype>[0-9]*)\:(?P<enchantLevel>[0-9]*)\:(.*?)\:(?P<style>[0-9]*)\:(?P<crafted>[0-9]*)\:(?P<bound>[0-9]*)\:(?P<charges>[0-9]*)\:(?P<potionData>[0-9]*)\|h\[?(?P<name>[a-zA-Z0-9 %_\(\)\'\-]*)(?P<nameCode>.*?)\]?\|h/', $itemLink, $matches);
		if (!$result) return false;
		
		$this->itemId = $matches['itemId'];
		$this->itemIntLevel = $matches['level'];
		$this->itemIntType = $matches['subtype'];
		
		return true;
	}
	
	
	private function ParseInputParams ()
	{
		if (array_key_exists('itemlink', $this->inputParams)) 
		{ 
			$this->itemLink = urldecode($this->inputParams['itemlink']);
			$this->ParseItemLink($this->itemLink);
		}
		
		if (array_key_exists('itemid', $this->inputParams)) $this->itemId = (int) $this->inputParams['itemid'];
		
		if (array_key_exists('level', $this->inputParams)) 
		{
			$level = strtolower($this->inputParams['level']);
			
			if ($level[0] == 'v')
				$this->itemLevel = (int) ltrim($level, 'v') + 49;
			else
				$this->itemLevel = (int) $level;
		}
		
		if (array_key_exists('quality', $this->inputParams)) $this->itemQuality = (int) $this->inputParams['quality'];
		if (array_key_exists('show', $this->inputParams)) $this->showAll = true;
		if (array_key_exists('intlevel', $this->inputParams)) $this->itemIntLevel = (int) $this->inputParams['intlevel'];
		if (array_key_exists('inttype', $this->inputParams)) $this->itemIntType = (int) $this->inputParams['inttype'];
		
		if (array_key_exists('output', $this->inputParams)) 
		{
			$this->inputParams['output'] = strtolower($this->inputParams['output']);
			
			switch ($this->inputParams['output'])
			{
				case "text":
				case "html":
				case "csv":
					$this->outputType = $this->inputParams['output'];
					break;
			}
		}
		
		return true;
	}
	
	
	private function SetInputParams ()
	{
		global $argv;
		$this->inputParams = $_REQUEST;
		
			// Add command line arguments to input parameters for testing
		if ($argv !== null)
		{
			$argIndex = 0;
			
			foreach ($argv as $arg)
			{
				$argIndex += 1;
				if ($argIndex <= 1) continue;
				$e = explode("=", $arg);
				
				if(count($e) == 2)
				{
					$this->inputParams[$e[0]] = $e[1];
				}
				else
				{
					$this->inputParams[$e[0]] = 1;
				}
			}
		}
	}
	
	
	private function InitDatabase()
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
		
		$this->db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($this->db->connect_error) return $this->ReportError("ERROR: Could not connect to mysql database!");
		
		return true;
	}
	
	
	private function ReduceAllItemData()
	{
		if (count($this->itemAllData) == 0) return;
		
		$firstItem = $this->itemAllData[0];
		
		foreach ($this->itemAllData as $index => &$item)
		{
			if ($firstItem == $item) continue;
			$delItems = array("link");
			
			foreach ($item as $key => $value)
			{
				if ($key == 'level' || $key == 'quality') continue;
				
				if (array_key_exists($key, $firstItem) && $firstItem[$key] == $value)
				{
					$delItems[] = $key;
				}
			}
			
			foreach ($delItems as $key => $value)
			{
				unset($item[$value]);
			}
		}
	}
	
	
	private function LoadAllItemData()
	{
		$this->itemAllData = array();
		if ($this->itemId <= 0) return false;
		
		$query = "SELECT * FROM minedItem WHERE itemId={$this->itemId} ORDER BY level, quality;";
		
		$result = $this->db->query($query);
		if (!$result) return false;
		if ($result->num_rows === 0) return false;
		
		$result->data_seek(0);
		
		while (($row = $result->fetch_assoc()))
		{
					// TODO: Temporary fix for setMaxEquipCount
			if (array_key_exists('setMaxEquipCount', $row) && $row['setMaxEquipCount'] == -1)
			{
				$highestSetDesc = "";
					
				if (array_key_exists('setBonusDesc1', $row) && $row['setBonusDesc1'] != "") $highestSetDesc = $row['setBonusDesc1'];
				if (array_key_exists('setBonusDesc2', $row) && $row['setBonusDesc2'] != "") $highestSetDesc = $row['setBonusDesc2'];
				if (array_key_exists('setBonusDesc3', $row) && $row['setBonusDesc3'] != "") $highestSetDesc = $row['setBonusDesc3'];
				if (array_key_exists('setBonusDesc4', $row) && $row['setBonusDesc4'] != "") $highestSetDesc = $row['setBonusDesc4'];
				if (array_key_exists('setBonusDesc5', $row) && $row['setBonusDesc5'] != "") $highestSetDesc = $row['setBonusDesc5'];
					
				if ($highestSetDesc != "")
				{
					$matches = array();
					$matchResult = preg_match("/\(([0-9]+) items\)/", $highestSetDesc, $matches);
					if ($matchResult) $row['setMaxEquipCount'] = (int) $matches[1];
				}
			}
			
			$this->itemAllData[] = $row;
		}
		
		$this->ReduceAllItemData();
		return true;
	}
	
	
	private function LoadItemRecord()
	{
		if ($this->itemId <= 0) return $this->ReportError("ERROR: Missing or invalid item ID specified (1-65000)!");
		$query = "";
		
		if ($this->itemIntLevel >= 1)
		{
			if ($this->itemIntType < 0) return $this->ReportError("ERROR: Missing or invalid item internal type specified (1-400)!");
			$query = "SELECT * FROM minedItem WHERE itemId={$this->itemId} AND internalLevel={$this->itemIntLevel} AND internalSubtype={$this->itemIntType} LIMIT 1;";
			$this->itemErrorDesc = "id={$this->itemId}, Internal Level={$this->itemIntLevel}, Internal Type={$this->itemIntType}";
		}
		else
		{
			if ($this->itemLevel <= 0) return $this->ReportError("ERROR: Missing or invalid item Level specified (1-64)!");
			if ($this->itemQuality <= 0) return $this->ReportError("ERROR: Missing or invalid item Quality specified (1-5)!");
			$query = "SELECT * FROM minedItem WHERE itemId={$this->itemId} AND level={$this->itemLevel} AND quality={$this->itemQuality} LIMIT 1;";
			$this->itemErrorDesc = "id={$this->itemId}, Level={$this->itemLevel}, Quality={$this->itemQuality}";
		}
		
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
		if ($result->num_rows === 0) return $this->ReportError("ERROR: No item found matching {$this->itemErrorDesc}!");
		
		$result->data_seek(0);
		$row = $result->fetch_assoc();
		if (!$row) $this->ReportError("ERROR: No item found matching {$this->itemErrorDesc}!");
		
		if ($this->itemLevel <= 0) $this->itemLevel = (int) $row['level'];
		if ($this->itemQuality <= 0) $this->itemQuality = (int) $row['quality'];
		
			// TODO: Temporary fix for setMaxEquipCount
		if (array_key_exists('setMaxEquipCount', $row) && $row['setMaxEquipCount'] == -1)
		{
			$highestSetDesc = "";
			$row['setMaxEquipCount'] = 0;
			
			if (array_key_exists('setBonusDesc1', $row) && $row['setBonusDesc1'] != "") $highestSetDesc = $row['setBonusDesc1'];
			if (array_key_exists('setBonusDesc2', $row) && $row['setBonusDesc2'] != "") $highestSetDesc = $row['setBonusDesc2'];
			if (array_key_exists('setBonusDesc3', $row) && $row['setBonusDesc3'] != "") $highestSetDesc = $row['setBonusDesc3'];
			if (array_key_exists('setBonusDesc4', $row) && $row['setBonusDesc4'] != "") $highestSetDesc = $row['setBonusDesc4'];
			if (array_key_exists('setBonusDesc5', $row) && $row['setBonusDesc5'] != "") $highestSetDesc = $row['setBonusDesc5'];
				
			if ($highestSetDesc != "")
			{
				$row['setMaxEquipCount'] = 1;
				$matches = array();
				$matchResult = preg_match("/\(([0-9]+) items\)/", $highestSetDesc, $matches);
				if ($matchResult) $row['setMaxEquipCount'] = (int) $matches[1];
			}
		}
		
		return $row;
	}
	
	
	private function TestAddSetData ($row)
	{ 
		$row['setMaxEquipCount'] = 5;
		$row['setBonusCount'] = 4;
		$row['setBonusCount1'] = 2;
		$row['setBonusCount2'] = 3;
		$row['setBonusCount3'] = 4;
		$row['setBonusCount4'] = 5;
		$row['setBonusDesc1'] = "Adds 139 Armor";
		$row['setBonusDesc2'] = "Adds 104 Max Health";
		$row['setBonusDesc3'] = "Adds 104 Max Health";
		$row['setBonusDesc4'] = "Death's Wind If struck by a melee attack while below 35% health, nearby enemies are knocked back and stunned for 4.0 seconds. This effect can only happen once every 30.0 seconds";
		return $row;
	}
	
	
	private function OutputHtmlHeader()
	{
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		
		if ($this->outputType == "html")
			header("content-type: text/html");
		else
			header("content-type: text/plain");
	}
	
	
	public function GetItemQualityFromSubtype($level, $subtype)
	{
		static $LEVEL_DATA = array(
		);
	}
	
	
	public function GetItemTraitText()
	{
		static $VALUES = array(
				-1 => "",
				18 => "Armor Divines",
				17 => "Armor Exploration",
				12 => "Armor Impenetrable",
				16 => "Armor Infused",
				20 => "Armor Intricate",
				19 => "Armor Ornate",
				13 => "Armor Reinforced",
				11 => "Armor Sturdy",
				15 => "Armor Training",
				14 => "Armor Well Fitted",
				22 => "Jewelry Arcane",
				21 => "Jewelry Health",
				24 => "Jewelry Ornate",
				23 => "Jewelry Robust",
				0 => "None",
				2 => "Weapon Charged",
				5 => "Weapon Defending",
				4 => "Weapon Infused",
				9 => "Weapon Intricate",
				10 => "Weapon Ornate",
				1 => "Weapon Power",
				3 => "Weapon Precise",
				7 => "Weapon Sharpened",
				6 => "Weapon Training",
				8 => "Weapon Weighted",
				25 => "Nirnhoned",
				26 => "Nirnhoned",
		);
	
		$key = (int) $this->itemRecord['trait'];
	
		if (array_key_exists($key, $VALUES)) return $VALUES[$key];
		return "Unknown ($key)";
	}
	
	
	public function GetItemStyleText()
	{
		static $VALUES = array(
				-1 => "",
				0 => "None",
				1 => "Breton",
				2 => "Redguard",
				3 => "Orc",
				4 => "Dunmer",
				5 => "Nord",
				6 => "Argonian",
				7 => "Altmer",
				8 => "Bosmer",
				9 => "Khajiit",
				10 => "Unique",
				11 => "Aldmeri Dominion",
				12 => "Ebonheart Pact",
				13 => "Daggerfall Covenant",
				14 => "Dwemer",
				15 => "Ancient Elf",
				16 => "Imperial",
				17 => "Reach",
				18 => "Bandit",
				19 => "Primitive",
				20 => "Daedric",
				21 => "Warrior Class",
				22 => "Mage Class",
				23 => "Rogue Class",
				24 => "Summoner Class",
				25 => "Marauder Class",
				26 => "Healer Class",
				27 => "Battlemage Class",
				28 => "Nightblade Class",
				29 => "Ranger Class",
				30 => "Knight Class",
				31 => "Draugr",
				32 => "Maormer",
				33 => "Akaviri",
				34 => "Imperial",
				35 => "Yokudan",
		);
		
		$key = (int) $this->itemRecord['style'];
		if (array_key_exists($key, $VALUES)) return $VALUES[$key];
		return "Unknown ($key)";
	}
	
	
	public function GetItemQualityText()
	{
		static $VALUES = array(
		-1 => "",
		0 => "Trash",
		1 => "Normal",
		2 => "Fine",
		3 => "Superior",
		4 => "Epic",
		5 => "Legendary",
		);
		
		$key = (int) $this->itemRecord['quality'];
		if (array_key_exists($key, $VALUES)) return $VALUES[$key];
		return "Unknown ($key)";
	}
	
	
	public function GetItemArmorTypeText()
	{
		static $VALUES = array(
				-1 => "",
				0 => "None",
				1 => "Light",
				2 => "Medium",
				3 => "Heavy",
		);
		
		$key = (int) $this->itemRecord['armorType'];
		if (array_key_exists($key, $VALUES)) return $VALUES[$key];
		return "Unknown ($key)";
	}
	
	
	public function GetItemWeaponTypeText()
	{
		static $VALUES = array(
				-1 => "",
				0 => "None",
				1 => "Axe",
				2 => "Hammer",
				3 => "Sword",
				4 => "Two handed Sword",
				5 => "Two handed Axe",
				6 => "Two handed Hammer",
				7 => "Prop",
				8 => "Bow",
				9 => "Healing Staff",
				10 => "Rune",
				11 => "Dagger",
				12 => "Fire Staff",
				13 => "Frost Staff",
				14 => "Shield",
				15 => "Lightning Staff",
		);
		
		$key = (int) $this->itemRecord['weaponType'];
		if (array_key_exists($key, $VALUES)) return $VALUES[$key];
		return "Unknown ($key)";
	}
	
	
	public function GetItemTypeText()
	{
		static $VALUES = array(
				-1 => "",
				11 => "Additive",
				33 => "Alchemy Base",
				2 => "Armor",
				24 => "Armor Booster",
				45 => "Armor Trait",
				47 => "Ava Repair",
				41 => "Blacksmithing Booster",
				36 => "Blacksmithing Material",
				35 => "Blacksmithing Raw Material",
				43 => "Clothier Booster",
				40 => "Clothier Material",
				39 => "Clothier Raw Material",
				34 => "Collectible",
				18 => "Container",
				13 => "Costume",
				14 => "Disguise",
				12 => "Drink",
				32 => "Enchanting Rune",
				25 => "Enchantment Booster",
				28 => "Flavoring",
				4 => "Food",
				21 => "Glyph Armor",
				26 => "Glyph Jewelry",
				20 => "Glyph Weapon",
				10 => "Ingredient",
				22 => "Lockpick",
				16 => "Lure",
				0 => "None",
				3 => "Plug",
				30 => "Poison",
				7 => "Potion",
				17 => "Raw Material",
				31 => "Reagent",
				29 => "Recipe",
				8 => "Scroll",
				6 => "Siege",
				19 => "Soul Gem",
				27 => "Spice",
				44 => "Style Material",
				15 => "Tabard",
				9 => "Tool",
				48 => "Trash",
				5 => "Trophy",
				1 => "Weapon",
				23 => "Weapon Booster",
				46 => "Weapon Trait",
				42 => "Woodworking Booster",
				38 => "Woodworking Material",
				37 => "Woodworking Raw Material",
				49 => "Spellcrafting Tablet",
				50 => "Mount",
				51 => "Potency Rune",
				52 => "Aspect Rune",
				53 => "Essence Rune",
		);
	
		$key = (int) $this->itemRecord['type'];
		if (array_key_exists($key, $VALUES)) return $VALUES[$key];
		return "Unknown ($key)";
	}
	
	public function GetItemEquipTypeText()
	{
		static $VALUES = array(
				-1 => "",
				0 => "none",
				1 => "Head",
				2 => "Neck",
				3 => "Chest",
				4 => "Shoulders",
				5 => "One Hand",
				6 => "Two Hand",
				7 => "Off Hand",
				8 => "Waist",
				9 => "Legs",
				10 => "Feet",
				11 => "Costume",
				12 => "Ring",
				13 => "Hand",
				14 => "Main Hand",
		);
		
		$key = (int) $this->itemRecord['equipType'];
		if (array_key_exists($key, $VALUES)) return $VALUES[$key];
		return "Unknown ($key)";
	}
	
	
	public function GetItemBindTypeText()
	{
		static $VALUES = array(
				-1 => "",
				0 => "",
				1 => "Bind on Pickup",
				2 => "Bind on Equip",
				3 => "Backpack Bind on Pickup",
		);
	
		$key = (int) $this->itemRecord['bindType'];
		if (array_key_exists($key, $VALUES)) return $VALUES[$key];
		return "Unknown ($key)";
	}
	
	private function MakeItemRawDataList()
	{	
		$output = "";
		
		foreach ($this->itemRecord as $key => $value)
		{
			if (!$this->showAll && ($key == 'id' || $key == 'logId' || $value == "" || $value == '-1' || $value == '0')) continue;
			$id = "esoil_rawdata_" . $key;
			
			if ($key == "icon")
				$output .= "\t<tr><td>$key</td><td id='$id'><img id='esoil_rawdata_iconimage' src='{$this->MakeItemIconImageLink()}' /> $value</td></tr>\n";
			else
				$output .= "\t<tr><td>$key</td><td id='$id'>$value</td></tr>\n";
		}
		
		return $output;
	}
	
	
	private function MakeItemIconImageLink()
	{
		$icon = $this->itemRecord['icon'];
		if ($icon == null || $icon == "") $icon = self::ESOIL_ICON_UNKNOWN;
		
		$icon = preg_replace('/dds$/', 'png', $icon);
		$icon = preg_replace('/^\//', '', $icon);
		
		$iconLink = self::ESOIL_ICON_URL . $icon;
		return $iconLink;
	}
	
	
	private function MakeItemLevelSimpleString()
	{
		$level = $this->itemRecord['level'];
		if ($level <= 0) return "Level ?";
		
		if ($level >= 50)
		{
			$level -= 49;
			return "Rank V$level";
		}
		
		return "Level $level";
	}
	
	
	private function MakeItemLevelString()
	{
		$level = $this->itemRecord['level'];
		if ($level <= 0) return "?";
		
		if ($level >= 50) 
		{
			$level -= 49;
			return "<img src='resources/eso_item_veteranicon.png' /> RANK <div id='esoil_itemlevel'>$level</div>";
		}
		
		return "LEVEL <div id='esoil_itemlevel'>$level</div>";
	}
	
	
	private function MakeItemLeftBlock()
	{
		$type = $this->itemRecord['type'];
		
		if ($type == 2) //armor 
		{
			return "ARMOR <div id='esoil_itemleft'>{$this->itemRecord['armorRating']}</div>";
		}
		elseif ($type == 1) //weapon 
		{
			return "DAMAGE <div id='esoil_itemleft'>{$this->itemRecord['weaponPower']}</div>";
		}
		
		return "";
	}
	
	
	private function MakeItemBindTypeText()
	{
		$bindType = $this->itemRecord['bindType'];
		
		if ($bindType <= 0) return "";
		return $this->GetItemBindTypeText();
	}
	
	
	private function MakeItemTypeText()
	{
		switch ($this->itemRecord['type'])
		{
			case 1:
			case 2:
				return $this->GetItemEquipTypeText();
			case 4:
				return "Food";
			default:
				return $this->GetItemTypeText();
		}
	}
	
	
	private function MakeItemSubTypeText()
	{
		$type = $this->itemRecord['type'];
		if ($type <= 0) return "";
		
		if ($type == 2) //armor
		{
			return "(" . $this->GetItemArmorTypeText() . ")";
		}
		elseif ($type == 1) //weapon
		{
			return "(" . $this->GetItemWeaponTypeText() . ")";
		}
		
		return "";
	}
	
	
	private function MakeItemBarLink()
	{
		$type = $this->itemRecord['type'];
		if ($type <= 0) return "";
		$charges = $this->itemRecord['maxCharges'];
		
		if ($type == 1 && $charges > 0) return "<img src='resources/eso_item_chargebar.png' />";
		if ($type == 1 || $type == 2) return "<img src='resources/eso_item_conditionbar.png' />";
		return "";
	}
	
	private function MakeItemEnchantBlock()
	{
		$enchantName = strtoupper($this->itemRecord['enchantName']);
		$enchantDesc = $this->FormatDescriptionText($this->itemRecord['enchantDesc']);
		
		if ($enchantName == "") return "";
		return "<div class='esoil_white esoil_small'>$enchantName</div><br/>$enchantDesc";
	}
	
	
	private function MakeItemTraitBlock()
	{
		$trait = $this->itemRecord['trait'];
		$traitDesc = $this->FormatDescriptionText($this->itemRecord['traitDesc']);
		$traitName = strtoupper($this->GetItemTraitText());
		
		if ($trait <= 0) return "";
		return "<div class='esoil_white esoil_small'>$traitName</div><br />$traitDesc";
	}
	
	
	private function FormatDescriptionText($desc)
	{
		$output = preg_replace("| by ([0-9\.]+)|s", " by <div class='esoil_white'>$1</div>", $desc);
		$output = preg_replace("|Adds ([0-9\.]+) |s", "Adds <div class='esoil_white'>$1</div> ", $output);
		$output = preg_replace("|for ([0-9\.]+)%|s", "for <div class='esoil_white'>$1</div>%", $output);
		$output = preg_replace("#\|c([0-9a-fA-F]{6})([0-9\.]+)\|r#s", "<div style='color:#$1;display:inline;'>$2</div> ", $output);
		$output = str_replace("\n", "<br />", $output);
		return $output;
	}
	
	
	private function MakeItemSetBlock()
	{
		$setName = strtoupper($this->itemRecord['setName']);
		if ($setName == "") return "";
		
		$setMaxEquipCount = $this->itemRecord['setMaxEquipCount'];
		$setBonusCount = (int) $this->itemRecord['setBonusCount'];
		$output = "<div class='esoil_white esoil_small'>PART OF THE $setName SET ($setMaxEquipCount/$setMaxEquipCount ITEMS)</div>";
		
		for ($i = 1; $i <= $setBonusCount && $i <= 5; $i += 1)
		{
			$setCount = $this->itemRecord['setBonusCount' . $i];
			$setDesc = $this->FormatDescriptionText($this->itemRecord['setBonusDesc' . $i]);
			$output .= "<br />$setDesc";
		}
		
		return $output;
	}
	
	
	private function MakeItemAbilityBlock()
	{
		$ability = strtoupper($this->itemRecord['abilityName']);
		$abilityDesc = $this->FormatDescriptionText($this->itemRecord['abilityDesc']);
		$cooldown = ((int) $this->itemRecord['abilityCooldown']) / 1000;
		
		if ($abilityDesc == "") return "";
		return "<div class='esoil_white esoil_small'>$ability</div> $abilityDesc ($cooldown second cooldown)";
	}
	
	
	private function MakeItemTraitAbilityBlock()
	{
		$abilityDesc = strtoupper($this->itemRecord['traitAbilityDesc']);
		$cooldown = ((int) $this->itemRecord['traitCooldown']) / 1000;
		
		if ($abilityDesc == "") return "";
		return "$abilityDesc ($cooldown second cooldown)";
	}
	
	
	private function GetItemLeftBlockDisplay()
	{
		
		switch ($this->itemRecord['type'])
		{
			case 2:
			case 1:
				return "inline";
		}
		
		return "none";
	}
	
	
	private function GetItemLevelBlockDisplay()
	{
		$level = $this->itemRecord['level'];
		if ($level <= 0) return "none";
		
		switch ($this->itemRecord['type'])
		{
			case 2:
			case 1:
				return "inline";
		}
		
		return "inline";
	}
	
	
	private function GetItemValueBlockDisplay()
	{
		$value = $this->itemRecord['value'];
		
		if ($value <= 0) return "none";
		return "inline";
	}
	
	
	private function GetItemDataJson()
	{
		$output = json_encode($this->itemAllData);
		return $output;
	}
	
	
	private function OutputHtml()
	{
		$replacePairs = array(
				'{itemName}' => $this->itemRecord['name'],
				'{itemNameUpper}' => strtoupper($this->itemRecord['name']),
				'{itemDesc}' => $this->itemRecord['description'],
				'{itemLink}' => $this->itemRecord['link'],
				'{itemId}' => $this->itemRecord['id'],
				'{itemType1}' => $this->MakeItemTypeText(),
				'{itemType2}' => $this->MakeItemSubTypeText(),
				'{itemBindType}' => $this->MakeItemBindTypeText(),
				'{itemValue}' => $this->itemRecord['value'],
				'{itemLevel}' => $this->MakeItemLevelSimpleString(),
				'{itemLevelRaw}' => $this->itemRecord['level'],
				'{itemQualityRaw}' => $this->itemRecord['quality'],
				'{itemLevelBlock}' => $this->MakeItemLevelString(),
				'{itemQuality}' => $this->GetItemQualityText(),
				'{itemRawDataList}' => $this->MakeItemRawDataList(),
				'{iconLink}' => $this->MakeItemIconImageLink(),
				'{itemLeftBlock}' => $this->MakeItemLeftBlock(),
				'{itemBar}' => $this->MakeItemBarLink(),
				'{itemEnchantBlock}' => $this->MakeItemEnchantBlock(),
				'{itemTraitBlock}' => $this->MakeItemTraitBlock(),
				'{itemSetBlock}' => $this->MakeItemSetBlock(),
				'{itemAbilityBlock}' => $this->MakeItemAbilityBlock(),
				'{itemTraitAbilityBlock}' => $this->MakeItemTraitAbilityBlock(),
				'{itemLeftBlockDisplay}' => $this->GetItemLeftBlockDisplay(),
				'{itemLevelBlockDisplay}' => $this->GetItemLevelBlockDisplay(),
				'{itemValueBlockDisplay}' => $this->GetItemValueBlockDisplay(),
				'{itemDataJson}' => $this->GetItemDataJson(),
			);
		
		$output = strtr($this->htmlTemplate, $replacePairs);
		
		print ($output);
	}
	
	
	public function DumpItem()
	{
		foreach ($this->itemRecord as $key => $value)
		{
			print("$key = $value\n");
		}
	}
	
	
	public function ShowItemHtml()
	{
		$this->OutputHtml();
	}
	
	
	public function ShowItem()
	{
		$this->OutputHtmlHeader();
		
		$this->itemRecord = $this->LoadItemRecord();
		if (!$this->itemRecord) return false;
		
		$this->LoadAllItemData();
		
		if ($this->outputType == "html")
			$this->ShowItemHtml();
		elseif ($this->outputType == "text")
			$this->DumpItem();
		else
			$this->ReportError("Error: Unknown output type '{$this->outputType}' specified!");
		
		return true;
	}
	
};


$g_EsoItemLink = new CEsoItemLink();
$g_EsoItemLink->ShowItem();


?>
