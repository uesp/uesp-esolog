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
	public $itemLevel = 1;		// 1-64
	public $itemQuality = 1;  	//1-5
	public $outputType = "html";
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
	
	
	private function ParseInputParams ()
	{
		if (array_key_exists('itemid', $this->inputParams)) $this->itemId = (int) $this->inputParams['itemid'];
		if (array_key_exists('level', $this->inputParams)) $this->itemLevel = (int) $this->inputParams['level'];
		if (array_key_exists('quality', $this->inputParams)) $this->itemQuality = (int) $this->inputParams['quality'];
		
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
		
		$this->itemErrorDesc = "id={$this->itemId}, Level={$this->itemLevel}, Quality={$this->itemQuality}";
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
	
	
	private function LoadItemRecord()
	{
		if ($this->itemId <= 0) return $this->ReportError("ERROR: Missing or invalid item ID specified (1-65000)!");
		if ($this->itemLevel <= 0) return $this->ReportError("ERROR: Missing or invalid item Level specified (1-64)!");
		if ($this->itemQuality <= 0) return $this->ReportError("ERROR: Missing or invalid item Quality specified (1-5)!");
		
		$query = "SELECT * FROM minedItem WHERE itemId={$this->itemId} AND level={$this->itemLevel} AND quality={$this->itemQuality} LIMIT 1;";
		
		$result = $this->db->query($query);
		if (!$result) return false;
		if ($result->num_rows === 0) return $this->ReportError("ERROR: No item found matching {$this->itemErrorDesc}!");
		
		$result->data_seek(0);
		$row = $result->fetch_assoc();
		if (!$row) $this->ReportError("ERROR: No item found matching {$this->itemErrorDesc}!");
		
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
				22 => "Jewelry Arcmne",
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
	
	
	public function GetItemQualityText ($value)
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
				11 => "additive",
				33 => "alchemy_base",
				2 => "armor",
				24 => "armor_booster",
				45 => "armor_trait",
				47 => "ava_repair",
				41 => "blacksmithing_booster",
				36 => "blacksmithing_material",
				35 => "blacksmithing_raw_material",
				43 => "clothier_booster",
				40 => "clothier_material",
				39 => "clothier_raw_material",
				34 => "collectible",
				18 => "container",
				13 => "costume",
				14 => "disguise",
				12 => "drink",
				32 => "enchanting_rune",
				25 => "enchantment_booster",
				28 => "flavoring",
				4 => "food",
				21 => "glyph_armor",
				26 => "glyph_jewelry",
				20 => "glyph_weapon",
				10 => "ingredient",
				22 => "lockpick",
				16 => "lure",
				0 => "none",
				3 => "plug",
				30 => "poison",
				7 => "potion",
				17 => "raw_material",
				31 => "reagent",
				29 => "recipe",
				8 => "scroll",
				6 => "siege",
				19 => "soul_gem",
				27 => "spice",
				44 => "style_material",
				15 => "tabard",
				9 => "tool",
				48 => "trash",
				5 => "trophy",
				1 => "weapon",
				23 => "weapon_booster",
				46 => "weapon_trait",
				42 => "woodworking_booster",
				38 => "woodworking_material",
				37 => "woodworking_raw_material",
				49 => "spellcrafting_tablet",
				50 => "mount",
				51 => "potency_rune",
				52 => "aspect_rune",
				53 => "essence_rune",
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

	
	private function MakeItemRawDataList()
	{	
		$output = "";
		
		foreach ($this->itemRecord as $key => $value)
		{
			if ($key == 'id' || $key == 'logId' || $value == "" || $value == '-1' || $value == '0') continue;
			
			if ($key == "icon")
				$output .= "\t<tr><td>$key</td><td><img class='esoil_icon' src='{$this->MakeItemIconImageLink()}' /> $value</td></tr>\n";
			else
				$output .= "\t<tr><td>$key</td><td>$value</td></tr>\n";
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
	
	
	private function MakeItemLevelString()
	{
		$level = $this->itemRecord['level'];
		if ($level <= 0) return "?";
		
		if ($level >= 50) 
		{
			$level -= 49;
			return "<img src='resources/eso_item_veteranicon.png' /> Rank <div class='esoil_itemlevel'>$level</div>";
		}
		
		return "Level <div class='esoil_itemlevel'>$level</div>";
	}
	
	
	private function MakeItemLeftBlock()
	{
		$type = $this->itemRecord['type'];
		
		if ($type == 2) //armor 
		{
			return "Armor <div class='esoil_itemleft'>{$this->itemRecord['armorRating']}</div>";
		}
		elseif ($type == 1) //weapon 
		{
			return "Damage <div class='esoil_itemleft'>{$this->itemRecord['weaponPower']}</div>";
		}
		
		return "";
	}
	
	
	private function MakeItemBindTypeText()
	{
		$bindType = $this->itemRecord['bindType'];
		
		if ($bindType > 0) return "Bound";
		return "";
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
		$enchantName = $this->itemRecord['enchantName'];
		$enchantDesc = $this->itemRecord['enchantDesc'];
		
		if ($enchantName == "") return "";
		return "<div class='esoil_swhite'>$enchantName</div><br/>$enchantDesc";
	}
	
	
	private function MakeItemTraitBlock()
	{
		return "";
	}
	
	
	private function MakeItemSetBlock()
	{
		return "";
	}
	
	
	private function OutputHtml()
	{
		$replacePairs = array(
				'{itemName}' => $this->itemRecord['name'],
				'{itemDesc}' => $this->itemRecord['description'],
				'{itemLink}' => $this->itemRecord['link'],
				'{itemId}' => $this->itemRecord['id'],
				'{itemType1}' => $this->GetItemEquipTypeText(),
				'{itemType2}' => $this->MakeItemSubTypeText(),
				'{itemBindType}' => $this->MakeItemBindTypeText(),
				'{itemValue}' => $this->itemRecord['value'],
				'{itemLevel}' => $this->itemRecord['level'],
				'{itemLevelBlock}' => $this->MakeItemLevelString(),
				'{itemQuality}' => $this->GetItemQualityText(),
				'{itemRawDataList}' => $this->MakeItemRawDataList(),
				'{iconLink}' => $this->MakeItemIconImageLink(),
				'{itemLeftBlock}' => $this->MakeItemLeftBlock(),
				'{itemBar}' => $this->MakeItemBarLink(),
				'{itemEnchantBlock}' => $this->MakeItemEnchantBlock(),
				'{itemTraitBlock}' => $this->MakeItemTraitBlock(),
				'{itemSetBlock}' => $this->MakeItemSetBlock(),
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
