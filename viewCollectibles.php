<?php

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");
require("esoCollectibleData.php");

class CEsoViewCollectibles
{
	const ESOVCOL_HTML_TEMPLATE = "templates/esovcol_template.txt";
	
	public $inputParams = array();
	public $htmlTemplate = "";
	
	public $viewCategory = "";
	public $viewSubcategory = "";
	public $viewSearch = "";
	public $extraQueryString = "";
	public $version = "";
	
	public $allCollectibles = array();
	public $collectibles = array();
	public $categories = array();
	
	
	public function __construct()
	{
		$this->SetInputParams();
		$this->ParseInputParams();
		$this->InitDatabase();
	
		$this->htmlTemplate = file_get_contents(self::ESOVCOL_HTML_TEMPLATE);
	}
	
	
	public function ReportError($errorMsg)
	{
		print($errorMsg);
		error_log($errorMsg);
		return false;
	}
	
	
	private function ParseInputParams ()
	{
		if (array_key_exists('category', $this->inputParams)) $this->viewCategory = $this->inputParams['category'];
		if (array_key_exists('subcategory', $this->inputParams)) $this->viewSubcategory = $this->inputParams['subcategory'];
		if (array_key_exists('search', $this->inputParams)) $this->viewSearch = $this->inputParams['search'];
		if (array_key_exists('version', $this->inputParams)) $this->version = $this->inputParams['version'];
		
		$this->tableSuffix = GetEsoItemTableSuffix($this->version);
		$this->fullTableName = $this->tableName . $this->tableSuffix;
		
		if ($this->version != '') 
		{
			$this->extraQueryString = '&version='.$this->version;
		}
	}
	
	
	private function SetInputParams ()
	{
		global $argv;
		$this->inputParams = $_REQUEST;
	}
	
	
	private function InitDatabase()
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
	
		$this->db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($this->db->connect_error) return $this->ReportError("ERROR: Could not connect to mysql database!");
		
		UpdateEsoPageViews("minedItemViews");
	
		return true;
	}
	
	
	private function OutputHtmlHeader()
	{
		ob_start("ob_gzhandler");
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		header("content-type: text/html");
	}
	
	
	public function EscapeHtml($value)
	{
		return htmlspecialchars($value, ENT_NOQUOTES);
	}
	
	
	public function EscapeUri($value)
	{
		return urlencode($value);
	}
	
	
	public function LoadCollectibles()
	{
		global $ESO_COLLECTIBLE_DATA;
		
		foreach ($ESO_COLLECTIBLE_DATA as $categoryIndex => $categoryData)
		{
			$categoryName = "";
			
			foreach ($categoryData as $subCategoryIndex => $subCategoryData)
			{
				$subCategoryName = "";
				
				if ($subCategoryIndex == 0)
				{
					$this->categories[$categoryIndex] = $subCategoryData;
					$categoryName = $subCategoryData['name'];
					$subCategoryData['isCategory'] = true;
				}
				else 
				{
					$subCategoryName = $subCategoryData['name'];
					$subCategoryData['category'] = $categoryName;
					$subCategoryData['categoryIndex'] = $categoryIndex;
					$subCategoryData['isSubcategory'] = true;
					$this->categories[$categoryIndex][$subCategoryIndex] = $subCategoryData;
				}
				
				$id = $categoryName . ":" . $subCategoryName;
				if ($this->doesCollectibleMatch($subCategoryData)) $this->collectibles[$id] = $subCategoryData;
				
				foreach ($subCategoryData['collectibles'] as $index => $collectible)
				{
					$collectible['category'] = $categoryName;
					$collectible['categoryIndex'] = $categoryIndex;
					$collectible['subCategory'] = $subCategoryName;
					$collectible['subCategoryIndex'] = $subCategoryIndex;
					$id = $collectible['id'];
					
					$this->allCollectibles[$id] = $collectible;
					
					if ($this->doesCollectibleMatch($collectible)) $this->collectibles[$id] = $collectible;
				}
			}
		}
		
		uasort($this->collectibles, array($this, 'SortCollectible'));
		return true;
	}
	
	
	public function SortCollectible($a, $b)
	{
		if ($a['isCategory'] != $b['isCategory']) return $b['isCategory'] - $a['isCategory'];
		if ($a['isSubcategory'] != $b['isSubcategory']) return $b['isSubcategory'] - $a['isSubcategory'];
		return strcasecmp($a['name'], $b['name']);
	}
	
	
	public function doesCollectibleMatch($collectible)
	{
			//TODO: Search?
		
		if ($this->viewCategory ==  "") 
		{
			return $collectible['isCategory'];
		}
		
		if ($this->viewSubcategory == "")
		{
			if ($collectible['isSubcategory'] && strcasecmp($collectible['category'], $this->viewCategory) == 0) return true;
			if ($collectible['subCategory'] == "" && strcasecmp($collectible['category'], $this->viewCategory) == 0) return true;
			return false;
		}
		
		return (strcasecmp($collectible['category'], $this->viewCategory) == 0) && (strcasecmp($collectible['subCategory'], $this->viewSubcategory) == 0);
	}
	
	
	public function MakeCollectiblesBlock()
	{
		$output = "<ol id='esovmi_itemlist'>\n";
		
		foreach ($this->collectibles as $item)
		{
			$itemId = $item['id'];
			$name = $item['name'];
			if ($name == "") $name = "[blank]";
			$name = preg_replace('/[\^]+.*/', "", $name);
			$safeName = $this->EscapeHtml($name);
			
			if ($item['isCategory'])
			{
				$safeName2 = $this->EscapeUri($name);
				$output .= "<li><a href='viewCollectibles.php?category=$safeName2' class='esovmiCategoryItem'>$safeName</a></li>";
			}
			else if ($item['isSubcategory'])
			{
				$safeName2 = $this->EscapeUri($name);
				$safeCategory = $this->EscapeUri($item['category']);
				$output .= "<li><a href='viewCollectibles.php?category=$safeCategory&subcategory=$safeName2' class='esovmiCategoryItem'>$safeName</a></li>";
			}
			else
			{
				//$output .= "<li><a href='viewlog.php?action=view&record=collectibles&id=$itemId{$this->extraQueryString}'>$safeName</a></li>";
				$output .= "<li><a href='itemLink.php?collectid=$itemId{$this->extraQueryString}'>$safeName</a></li>";
			}
		}
		
		if (count($this->collectibles) == 0) $output .= "No collectibles found!";
		
		$output .= "</ol>\n";
		return $output;
	}
	
	
	public function MakeSearchBlock()
	{
		//TODO
		//if (!$this->LoadSearchRecords()) return "";
		return $this->MakeCollectiblesBlock();
	}	
	
	
	public function MakeContentBlock()
	{
		if ($this->viewSearch != "")
			return $this->MakeSearchBlock();
		elseif ($this->viewCategory != "" && $this->viewSubcategory != "")
			return $this->MakeSubcategoryBlock();
		elseif ($this->viewCategory != "")
			return $this->MakeCategoryBlock();
		else
			return $this->MakeTopLevelBlock();
	}
	
	
	public function MakeSubcategoryBlock()
	{
		if (!$this->LoadCollectibles()) return "";
		return $this->MakeCollectiblesBlock();
	}
	
	
	public function MakeCategoryBlock()
	{
		if (!$this->LoadCollectibles()) return "";
		return $this->MakeCollectiblesBlock();
	}
	
	public function MakeTopLevelBlock()
	{
		if (!$this->LoadCollectibles()) return "";
		return $this->MakeCollectiblesBlock();
	}
	
	
	public function MakeTitleString()
	{
		if ($this->viewSearch != "")
		{
			return "Search Results For: " . $this->viewSearch;
		}
		elseif ($this->viewCategory != "" && $this->viewSubcategory != "")
		{
			$safeCategory = $this->EscapeHtml($this->viewCategory);
			$safeSubcategory = $this->EscapeHtml($this->viewSubcategory);
			return "$safeCategory:$safeSubcategory Collectibles";
		}
		elseif ($this->viewCategory != "")
		{
			$safeCategory = $this->EscapeHtml($this->viewCategory);
			return "$safeCategory Collectibles";
		}
		else
		{
			return "All Collectibles";
		}
	}
	
	
	public function MakeBreadCrumbBlock()
	{
		$output = "&lt; <a href='?{$this->extraQueryString}'>All Collectibles</a>";
		
		if ($this->viewSearch != "") return $output;
		if ($this->viewCategory == "") return "";
		
		if ($this->viewCategory != "")
		{
			$safeCategory1 = $this->EscapeHtml($this->viewCategory);
			$safeCategory2 = $this->EscapeUri($this->viewCategory);
			
			if ($this->viewSubcategory == "")
				$output .= ": $safeCategory1";
			else
				$output .= ": <a href='?category=$safeCategory2{$this->extraQueryString}'>$safeCategory1</a>";
		}
		
		if ($this->viewSubcategory != "")
		{
			$safeCategory2 = $this->EscapeUri($this->viewCategory);
			$safeSubcategory1 = $this->EscapeHtml($this->viewSubcategory);
			$safeSuccategory2 = $this->EscapeUri($this->viewSubcategory);
			$output .= ": $safeSubcategory1";
		}
		
		return $output;
	}
	
	
	public function GetQueryString()
	{
		$query = array();
		
		if ($this->viewCategory != "") {
			$query['category'] = $this->EscapeUri($this->viewCategory);
		}
		
		if ($this->$viewSubcategory >= 0) {
			$query['subcategory'] = $this->EscapeUri($this->$viewSubcategory);
		}
		$queryString = "";
		
		foreach ($query as $k => $v) {
			$queryString .= "$k=$v&";
		}
		
		return $queryString;
	}
	
	
	public function GetCurrentVersion() 
	{
		return GetEsoDisplayVersion($this->version);
	}
	
	
	public function ViewItems()
	{
		$this->OutputHtmlHeader();
		
		$replacePairs = array(
				'{title}' => $this->MakeTitleString(),
				'{content}' => $this->MakeContentBlock(),
				'{search}' => $this->viewSearch,
				'{breadCrumb}' => $this->MakeBreadCrumbBlock(),
				'{version}' => $this->GetCurrentVersion(),
				'{versionList}' => "",
		);
		
		if (!CanViewEsoLogVersion($this->version))
		{
			return $this->ViewErrorItems();
		}
		
		$output = strtr($this->htmlTemplate, $replacePairs);
		print($output);
	}
	
	
	public function ViewErrorItems()
	{
		$this->OutputHtmlHeader();
	
		$replacePairs = array(
				'{title}' => $this->MakeTitleString(),
				'{content}' => "Permission Denied!",
				'{search}' => $this->viewSearch,
				'{breadCrumb}' => $this->MakeBreadCrumbBlock(),
		);
	
		$output = strtr($this->htmlTemplate, $replacePairs);
		print($output);
		
		return true;
	}
	
};


$g_EsoViewCollectibles = new CEsoViewCollectibles();
$g_EsoViewCollectibles->ViewItems();


