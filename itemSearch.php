<?php 

	/* Database users, passwords and other secrets */
require("/home/uesp/secrets/esolog.secrets");

	/* Common library */
require("esoCommon.php");


class EsoItemSearcher
{
	const PRINT_DB_ERRORS = true;
	const PRINT_DB_QUERY = false;
	
	const ESOIS_RESULT_LIMIT = 500;
	
	const ESOIS_HTML_TEMPLATE = "templates/esoitemsearch_template.txt";
	
	const ESOIS_ICON_URL = "http://esoicons.uesp.net/";
	const ESOIS_ICON_UNKNOWN = "unknown.png";
	const ESOIS_ICON_WIDTH = 24;
	
	static public $ESOIS_EFFECT_DATA = array(
			"" => array(),
			"Damage Shield" => array("%Adds % Damage Shield%"),
			"Resurrection Speed" => array("%decrease time to resurrect%"),
			"Max Health" => array("%Adds % Maximum Health%"),
			"Max Magicka" => array("%Adds % Maximum Magicka%"),
			"Max Stamina" => array("%Adds % Maximum Stamina%"),
			"Restore Health" => array("%Restore % Health%"),
			"Restore Magicka" => array("%Restore % Magicka%"),
			"Restore Stamina" => array("%Restore % Stamina%"),
			"Health Recovery" => array("%Adds % Health Recovery%"),
			"Magicka Recovery" => array("%Adds % Magicka Recovery%"),
			"Stamina Recovery" => array("%Adds % Stamina Recovery%"),
			"Physical Resistance" => array("%Adds % Physical Resistance%"),
			"Critical Resistance" => array("%Adds % Critical Resistance%"),
			"Spell Damage" => array("%Increase Spell Damage%"),
			"Weapon Damage" => array("%Increase Weapon Damage%"),
			"Spell Critical" => array("%Adds % Spell Critical%"),
			"Weapon Critical" => array("%Adds % Weapon Critical%"),
		);
	
	static public $ESOIS_EFFECT_COLUMNS = array(
			"setBonusDesc1",
			"setBonusDesc2",
			"setBonusDesc3",
			"setBonusDesc4",
			"setBonusDesc5",
			"enchantDesc",
			"traitDesc",
			"traitAbilityDesc",
			"abilityDesc",			
		);
	
	static public $ESOIS_ENCHANTS = array(
			"",
			"Absorb Magicka",
			"Absorb Stamina",
			"Alchemical Acceleration",
			"Alchemical Amplification",
			"Arms of Infernace",
			"Bashing",
			"Befouled Weapon",
			"Charged Weapon",
			"Cold Resistance",
			"Cruel Flurry",
			"Crusher",
			"Crushing Wall",
			"Decrease Health",
			"Disease Resistance",
			"Enchantment",
			"Fiery Weapon",
			"Flame Resistance",
			"Frozen Weapon",
			"Hardening",
			"Health Recovery",
			"Life Drain",
			"Magicka Recovery",
			"Maximum Health",
			"Maximum Magicka",
			"Maximum Stamina",
			"Merciless Charge",
			"Multi-Effect",
			"Physical Resistance",
			"Poison Resistance",
			"Poisoned Weapon",
			"Rampaging Slash",
			"Reduce Spell Cost",
			"Reduce Stamina Cost",
			"Shield-play",
			"Shock Resistance",
			"Spell Damage",
			"Spell Resistance",
			"Stamina Recovery",
			"Thunderous Volley",
			"Weakening",
			"Weapon Damage",
		);
	
	static public $ESOIS_SEARCH_FIELDS = array(
			'name', 
			'description', 
			'abilityName', 
			'abilityDesc', 
			'enchantName', 
			'enchantDesc', 
			'traitDesc', 
			'setName', 
			'setBonusDesc1',
			'setBonusDesc2', 
			'setBonusDesc3', 
			'setBonusDesc4', 
			'setBonusDesc5',
	);
	
	static public $ESOIS_TRAITS = array();
	static public $ESOIS_STYLES = array();
	static public $ESOIS_QUALITIES = array();
	static public $ESOIS_ITEMTYPES = array();
	static public $ESOIS_EQUIPTYPES = array();
	static public $ESOIS_ARMORTYPES = array();
	static public $ESOIS_WEAPONTYPES = array();
	static public $ESOIS_EFFECTS = array();
	
	public $db = null;
	public $dbReadInitialized = false;
	public $totalRowCount = 0;
	public $lastQuery = "";
	
	public $hasSearch = false;
	public $searchResults = array();
	public $searchCount = 0;
	
	public $inputParams = array();
	public $outputFormat = "HTML";
	public $version = "";
	
	public $formValues = array();
	public $htmlTemplate = "";
	
	
	public function __construct ()
	{
		global $ESO_ITEMTRAIT_TEXTS, $ESO_ITEMTYPE_TEXTS, $ESO_ITEMEQUIPTYPE_TEXTS;
		global $ESO_ITEMARMORTYPE_TEXTS, $ESO_ITEMWEAPONTYPE_TEXTS, $ESO_ITEMQUALITY_TEXTS;
		global $ESO_ITEMSTYLE_TEXTS;
		
		self::$ESOIS_STYLES = self::MakeUniqueArray($ESO_ITEMSTYLE_TEXTS);
		self::$ESOIS_TRAITS = self::MakeUniqueArray($ESO_ITEMTRAIT_TEXTS);
		self::$ESOIS_QUALITIES = self::MakeUniqueArray($ESO_ITEMQUALITY_TEXTS, true);
		self::$ESOIS_ITEMTYPES = self::MakeUniqueArray($ESO_ITEMTYPE_TEXTS);
		self::$ESOIS_EQUIPTYPES = self::MakeUniqueArray($ESO_ITEMEQUIPTYPE_TEXTS);
		self::$ESOIS_ARMORTYPES = self::MakeUniqueArray($ESO_ITEMARMORTYPE_TEXTS);
		self::$ESOIS_WEAPONTYPES = self::MakeUniqueArray($ESO_ITEMWEAPONTYPE_TEXTS);
		
		self::MakeEffectArray();
		
		$this->InitDatabase();
		$this->SetInputParams();
		$this->ParseInputParams();
		
		$this->LoadHtmlTemplate();		
	}
	
	
	private static function MakeUniqueArray($src, $noSort = false)
	{
		$newArray = array_unique($src);
		if (!$noSort) sort($newArray);
		return $newArray;
	}
	
	
	private static function MakeEffectArray()
	{
		self::$ESOIS_EFFECTS = array();
		
		foreach (self::$ESOIS_EFFECT_DATA as $effectName => $effectData)
		{
			self::$ESOIS_EFFECTS[] = $effectName;
		}
		
		sort(self::$ESOIS_EFFECTS);
	}
	
	
	private function InitDatabase ()
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
	
		if ($this->dbReadInitialized) return true;
	
		$this->db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($db->connect_error) return $this->ReportError("Could not connect to mysql database!");
	
		$this->dbReadInitialized = true;
		return true;
	}
	
	
	public function LoadHtmlTemplate()
	{
		$this->htmlTemplate = file_get_contents(self::ESOIS_HTML_TEMPLATE);
	}
	
	
	public function ReportError ($errorMsg)
	{
		print($errorMsg);
	
		if (self::PRINT_DB_ERRORS && $this->db != null && $this->db->error)
		{
			print("<p />DB Error:" . $this->db->error . "<p />");
			print("<p />Last Query:" . $this->lastQuery . "<p />");
		}
	
		return FALSE;
	}
	
	
	public function GetFormValue($id, $default = "")
	{
		if (!array_key_exists($id, $this->formValues)) return $default;
		return htmlspecialchars($this->formValues[$id]);
	}
	
	
	public function IsOutputHTML()
	{
		return $this->outputFormat == "HTML";
	}
	
	
	public function IsOutputCSV()
	{
		return $this->outputFormat == "CSV";
	}
	
	
	public function GetTopMenuHtml()
	{
		if (!$this->IsOutputHTML()) return "";
	
		$output = "<a href='viewlog.php'>Back to Home</a><br />\n";
	
		return $output;
	}
	

	public function GetGeneralListHtml($listArray, $formName)
	{
		$output = "";
		$selectedValue = $this->GetFormValue($formName);
	
		foreach ($listArray as $value)
		{
			if ($value == $selectedValue)
				$selected = "selected";
			else
				$selected = "";
						
			$output .= "<option value='$value' $selected>$value</option>";
		}
	
		return $output;
	}
	
	
	public function GetItemTraitValue($text)
	{
		global $ESO_ITEMTRAIT_TEXTS;
	
		$value = array_search($text, $ESO_ITEMTRAIT_TEXTS);
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
	
	
	public function GetItemStyleValue($text)
	{
		global $ESO_ITEMSTYLE_TEXTS;
	
		$value = array_search($text, $ESO_ITEMSTYLE_TEXTS);
		if ($value === FALSE) return -1;
	
		return $value;
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
	
	
	private function MakeSqlQuery()
	{
		$where = array();
		$query = "SELECT * FROM minedItemSummary" . GetEsoItemTableSuffix($this->version) . " ";
		
		if ($this->formValues['text'] != "") 
		{
			$searchText = $this->db->real_escape_string($this->formValues['text']);
			$searchFields = implode(",", self::$ESOIS_SEARCH_FIELDS);
			$tmpWhere = "MATCH($searchFields) AGAINST ('$searchText' in BOOLEAN MODE)";
			$intVal = intval($this->formValues['text']);
			
			if (is_numeric($this->formValues['text']) && $intVal > 0 && $intVal < 100000) 
			{
				$tmpWhere = "(" . $tmpWhere . " OR itemId=$intVal" . ")";	
			}
			
			$where[] = $tmpWhere;
		}
		
		if ($this->formValues['trait'] != "")
		{
			$value = $this->GetItemTraitValue($this->formValues['trait']);
			$where[] = "trait = $value";
		}
		
		if ($this->formValues['quality'] != "")
		{
			$value = $this->GetItemQualityValue($this->formValues['quality']);
			
			if ($value == 0)
				$where[] = "(quality = '$value' or quality='0-5')";
			else
				$where[] = "(quality = '$value' or quality='1-5')";
		}
		
		if ($this->formValues['style'] != "")
		{
			$value = $this->GetItemStyleValue($this->formValues['style']);
			$where[] = "style = $value";
		}
		
		
		if ($this->formValues['itemtype'] != "")
		{
			$value = $this->GetItemTypeValue($this->formValues['itemtype']);
			$where[] = "type = $value";
		}
		
		if ($this->formValues['equiptype'] != "")
		{
			$value = $this->GetEquipTypeValue($this->formValues['equiptype']);
			$where[] = "equipType = $value";
		}
		
		if ($this->formValues['armortype'] != "")
		{
			$value = $this->GetArmorTypeValue($this->formValues['armortype']);
			$where[] = "armorType = $value";
		}
		
		if ($this->formValues['weapontype'] != "")
		{
			$value = $this->GetWeaponTypeValue($this->formValues['weapontype']);
			$where[] = "weaponType = $value";
		}
		
		if ($this->formValues['enchant'] != "")
		{
			$name = $this->db->real_escape_string($this->formValues['enchant']);
			$where[] = "enchantName LIKE '$name%'";
		}
		
		if ($this->formValues['effect'] != "")
		{
			$effectData = self::$ESOIS_EFFECT_DATA[$this->formValues['effect']];
			
			if ($effectData != null)
			{
				$name = $effectData[0];
				//$tmpWhere = "MATCH($searchFields) AGAINST ('$searchText' in BOOLEAN MODE)";
				//$where[] = "enchantName LIKE '$name%'";
				$tmpWhere = array();
				
				foreach (self::$ESOIS_EFFECT_COLUMNS as $column)
				{
					$tmpWhere[] = "$column LIKE '$name'";
				}
				
				$where[] = "(" . implode(" OR ", $tmpWhere) . ")";				
			}
		}		
		
		$this->hasSearch = false;
		
		if (count($where) > 0)
		{
			$this->hasSearch = true;
			$query .= "WHERE " . implode(" AND ", $where);
		}
		
		$query .= " ORDER BY name LIMIT ". self::ESOIS_RESULT_LIMIT .";";
		
		return $query;
	}
	
	
	public function GetIconUrl($icon)
	{
		if ($icon == null || $icon == "") $icon = self::ESOIS_ICON_UNKNOWN;
		
		$icon = preg_replace('/dds$/', 'png', $icon);
		$icon = preg_replace('/^\//', '', $icon);
		
		$iconLink = self::ESOIS_ICON_URL . $icon;
		return $iconLink;
	}

	
	private function GetSearchResultRowHtml($result)
	{
		$output = "";
		
		$itemName = $result['name'];
		$itemLink = $result['link'];
		$itemId = $result['itemId'];
		$quality = $result['quality'];
		$icon = $result['icon'];
		$iconUrl = $this->GetIconUrl($icon);
		$slotText = "";
		$desc = FormatEsoItemDescriptionText($result['description']);
		$trait = $result['trait'];
		
		$enchantDesc = FormatRemoveEsoItemDescriptionText($result['enchantDesc']);
		$traitDesc = FormatRemoveEsoItemDescriptionText($result['traitDesc']);
		$abilityDesc = FormatRemoveEsoItemDescriptionText($result['abilityDesc']);
		$traitAbilityDesc = FormatRemoveEsoItemDescriptionText($result['traitAbilityDesc']);
		$setBonusDesc1 = FormatRemoveEsoItemDescriptionText($result['setBonusDesc1']);
		$setBonusDesc2 = FormatRemoveEsoItemDescriptionText($result['setBonusDesc2']);
		$setBonusDesc3 = FormatRemoveEsoItemDescriptionText($result['setBonusDesc3']);
		$setBonusDesc4 = FormatRemoveEsoItemDescriptionText($result['setBonusDesc4']);
		$setBonusDesc5 = FormatRemoveEsoItemDescriptionText($result['setBonusDesc5']);
		
		if ($result['type'] == 1)
		{
			$slotText = GetEsoItemEquipTypeText($result['equipType']) . " (" . GetEsoItemWeaponTypeText($result['weaponType']) . ")";
		}
		else if ($result['type'] == 2)
		{
			if ($result['armorType'] > 0)
				$slotText = GetEsoItemEquipTypeText($result['equipType']) . " (" . GetEsoItemArmorTypeText($result['armorType']) . ")";
			else
				$slotText = GetEsoItemEquipTypeText($result['equipType']);
		}
		else
		{
			$slotText = GetEsoItemTypeText($result['type']);
		}
		
		$linkToItem = "http://esoitem.uesp.net/itemLink.php?itemid=$itemId&summary";
		
		$output .= "<tr class='esois_resultrow'><td>\n";
		$output .= "<a class='esois_itemlink eso_item_link' href='$linkToItem' itemid='$itemId' summary='1' quality='$quality'><img class='esois_itemicon' src='$iconUrl' width='" . self::ESOIS_ICON_WIDTH . "' /> $itemName</a>";
		$output .= "<div class='esois_itemdata'>";
		$output .= "  $slotText, ";
		
		if (is_numeric($quality))
		{
			$output .= GetEsoItemQualityText($quality) . " Quality, ";
		}
		
		if ($traitDesc != "")
		{
			$output .= GetEsoItemTraitText($result['trait']) . ": " . $traitDesc . ", ";
		}
		else if ($trait > 0)
		{
			$output .= GetEsoItemTraitText($result['trait']) . ", ";
		}
		
		if ($enchantDesc != "" && $result['enchantName'] != "")
		{
			$output .= $result['enchantName'] . ": " . $enchantDesc . ", ";
		}
		else if ($result['enchantName'] != "")
		{
			$output .= $result['enchantName'] . ", ";
		}
		else if ($enchantDesc != "")
		{
			$output .= $enchantDesc . ", ";
		}
		
		if ($result['style'] > 0)
		{
			$output .= GetEsoItemStyleText($result['style']) . " Style, ";
		}
				
		if ($result['setName'] != "")
		{
			$output .= "Part of the " . $result['setName'] . " Set";
			//if ($setBonusDesc1 != "") $output .= $setBonusDesc1 . " ";
			//if ($setBonusDesc2 != "") $output .= $setBonusDesc2 . " ";
			//if ($setBonusDesc3 != "") $output .= $setBonusDesc3 . " ";
			//if ($setBonusDesc4 != "") $output .= $setBonusDesc4 . " ";
			//if ($setBonusDesc5 != "") $output .= $setBonusDesc5 . " ";
			$output .= ", ";
		}
		
		if ($result['abilityName'] != "" && $abilityDesc != "")
		{
			$output .= $result['abilityName'] . ": " . $abilityDesc . ", ";
		}
		else if ($result['abilityName'] != "")
		{
			$output .= $result['abilityName'] . ", ";
		}
		else if ($abilityDesc != "")
		{
			$output .= $abilityDesc . ", ";
		}
		
		if ($traitAbilityDesc != "")
		{
			$output .= $traitAbilityDesc . ", ";
		}
		
		if ($result['bindType'] > 0)
		{
			$output .= GetEsoItemBindTypeText($result['bindType']) . ", ";
		}
						
		$output .= "<div class='esois_itemdesc'>$desc</div>";
		$output .= "</div>";
		$output .= "</td></tr>\n";
		
		return $output;
	}
	
	
	private function GetSearchResultsHtml()
	{
		$output = "";
		
		foreach ($this->searchResults as $result)
		{
			$output .= $this->GetSearchResultRowHtml($result);
		}
		
		return $output;
	}
	
	
	private function GetDbQueryHtml()
	{
		if (self::PRINT_DB_QUERY) return htmlspecialchars($this->lastQuery);
		return "";
	}
	
	
	private function OutputHtml()
	{
		$replacePairs = array(
				'{topMenu}' => $this->GetTopMenuHtml(),
				'{formVersion}' => $this->version,
				'{dbQuery}' => $this->GetDbQueryHtml(),
				
				'{formText}' => $this->GetFormValue('text'),
				'{formTrait}' => $this->GetFormValue('trait'),
				'{formQuality}' => $this->GetFormValue('quality'),
				'{formItemType}' => $this->GetFormValue('itemtype'),
				'{formEquipType}' => $this->GetFormValue('equiptype'),
				'{formArmorType}' => $this->GetFormValue('armortype'),
				'{formWeaponType}' => $this->GetFormValue('weapontype'),
				'{formEnchant}' => $this->GetFormValue('enchant'),
				'{formStyle}' => $this->GetFormValue('stlye'),
				'{formEffect}' => $this->GetFormValue('effect'),
				
				'{listTrait}' => $this->GetGeneralListHtml(self::$ESOIS_TRAITS, 'trait'),
				'{listQuality}' => $this->GetGeneralListHtml(self::$ESOIS_QUALITIES, 'quality'),
				'{listItemType}' => $this->GetGeneralListHtml(self::$ESOIS_ITEMTYPES, 'itemtype'),
				'{listEquipType}' => $this->GetGeneralListHtml(self::$ESOIS_EQUIPTYPES, 'equiptype'),
				'{listArmorType}' => $this->GetGeneralListHtml(self::$ESOIS_ARMORTYPES, 'armortype'),
				'{listWeaponType}' => $this->GetGeneralListHtml(self::$ESOIS_WEAPONTYPES, 'weapontype'),
				'{listEnchant}' => $this->GetGeneralListHtml(self::$ESOIS_ENCHANTS, 'enchant'),
				'{listStyle}' => $this->GetGeneralListHtml(self::$ESOIS_STYLES, 'style'),
				'{listEffect}' => $this->GetGeneralListHtml(self::$ESOIS_EFFECTS, 'effect'),
				
				'{searchResults}' => $this->GetSearchResultsHtml(),
				'{searchResultDisplay}' => $this->hasSearch ? "block" : "none",
				'{searchCount}' => $this->searchCount,
			);
	
		$output = strtr($this->htmlTemplate, $replacePairs);
	
		print ($output);
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
	
	
	private function ParseInputParams()
	{
		if (array_key_exists('format', $this->inputParams)) $this->outputFormat = strtoupper($this->inputParams['format']);
		
		if (array_key_exists('version', $this->inputParams)) $this->version = urldecode($this->inputParams['version']);
		if (array_key_exists('v', $this->inputParams)) $this->version = urldecode($this->inputParams['v']);
		
		$this->ParseFormParam("text");		
		$this->ParseFormParam("trait");
		$this->ParseFormParam("style");
		$this->ParseFormParam("quality");
		$this->ParseFormParam("enchant");
		$this->ParseFormParam("itemtype");
		$this->ParseFormParam("equiptype");
		$this->ParseFormParam("armortype");
		$this->ParseFormParam("weapontype");
		$this->ParseFormParam("effect");
	}
	
	
	private function SetInputParams()
	{
		global $argv;
		$this->inputParams = $_REQUEST;
	
			// Add command line arguments to input parameters for testing
		if ($argv !== null)
		{
			foreach ($argv as $arg)
			{
				$e = explode("=", $arg);
	
				if(count($e) == 2)
					$this->inputParams[$e[0]] = $e[1];
				else
					$this->inputParams[$e[0]] = 0;
			}
		}
	}
	
	
	public function WriteHeaders()
	{
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
	
		if ($this->IsOutputCSV())
			header("content-type: text/plain");
		else
			header("content-type: text/html");
	}
	
	
	public function DoSearch()
	{
		$this->lastQuery = $this->MakeSqlQuery();
		if (!$this->hasSearch) return false;
		
		$result = $this->db->query($this->lastQuery);
		
		if ($result === FALSE) return $this->ReportError("Failed to perform search!");
		$result->data_seek(0);
		$this->searchCount = $result->num_rows;
		
		while ( ($row = $result->fetch_assoc()) )
		{
			$this->searchResults[] = $row;
		}
			
		return true;
	}
	
	
		/* Main entrance */
	public function Start()
	{
		$this->WriteHeaders();
		
		$this->DoSearch();
		
		$this->OutputHtml();
		
	}
	
};


$g_EsoItemSearcher = new EsoItemSearcher();
$g_EsoItemSearcher->Start();
