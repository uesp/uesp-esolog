<?php 

require_once("/home/uesp/secrets/esolog.secrets");
require_once("/home/uesp/esolog.static/esoCommon.php");


class CEsoViewFurnishings 
{
	
	public $db = null;
	public $lastQuery = null; 
	
	public $recipes = array();
	public $furnishings = array();
	
	
	public function __construct()
	{
		$this->InitDatabase();	
	}
	
	
	public function ReportError($errorMsg)
	{
		error_log($errorMsg);
		print($errorMsg);
		
		if ($this->db && $this->db->error)
		{
			error_log($this->db->error);
			print($this->db->error);
		}
		
		return false;
	}
	
	
	public function InitDatabase()
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
	
		$this->db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($this->db->connect_error) return $this->ReportError("ERROR: Could not connect to mysql database!");
	
		return true;
	}
		
	
	public function OutputTextHeader()
	{
		ob_start("ob_gzhandler");
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		header("content-type: text/plain");
	
		$origin = $_SERVER['HTTP_ORIGIN'];
	
		if (substr($origin, -8) == "uesp.net")
		{
			header("Access-Control-Allow-Origin: $origin");
		}
	}
	
	
	public function LoadData()
	{
		$this->lastQuery = "SELECT * FROM minedItemSummary where type=29 or type=61;";
		$result = $this->db->query($this->lastQuery);
		if (!$result) return $this->ReportError("Failed to load recipe/furnishing data!");
		
		$this->recipes = array();
		$this->furnishings = array();
		
		while ($row = $result->fetch_assoc())
		{
			if ($row['specialType'] == 170) continue;
			if ($row['specialType'] == 171) continue;
			
			$name = $row['name'];
			$itemId = $row['itemId'];
			
			if ($this->recipes[$name] != null || $this->furnishings[$name] != null)
			{
				//print("Duplicate item name '$name' found!\n");
			}
			
			if ($row['type'] == 29) 
				$this->recipes[$itemId] = $row;
			else
				$this->furnishings[$itemId] = $row;
			
		}
		
		$this->LoadRecipeData();
		return true;
	}
	
	
	public function LoadRecipeData()
	{
		$newRecipes = array();
		
		foreach ($this->recipes as $recipeId => $recipe)
		{
			$itemId = $recipe['itemId'];
			
			$item = LoadEsoMinedItemExact($this->db, $itemId, 1, 1);
			
			if (!$item)
			{
				$this->ReportError("Failed to load extra recipe data!");
				$newRecipes[$itemId] = $recipe;
			}
			else
			{
				$newRecipes[$itemId] = $item;
				
				$linkMatches = ParseEsoItemLink($newRecipes[$itemId]['resultItemLink']);
				
				if ($linkMatches)
				{
					$newRecipes[$itemId]['resultItemId'] = $linkMatches['itemId'];
					$newRecipes[$itemId]['resultIntLevel'] = $linkMatches['level'];
					$newRecipes[$itemId]['resultIntSubtype'] = $linkMatches['subtype'];
				}
				
			}
		}
		
		$this->recipes = $newRecipes;
	}
	
	
	public function SortRecipeByName($a, $b)
	{
		return strcasecmp($a['name'], $b['name']);
	}
		
		
	public function PrintData()
	{
		uasort($this->recipes, array("CEsoViewFurnishings", "SortRecipeByName"));
		uasort($this->furnishings, array("CEsoViewFurnishings", "SortRecipeByName"));
		
		print("#, Furnishing Name, Quality, Item Link, Recipe Name, Recipe Item Link\n");
		
		$index = 1;
		
		foreach ($this->recipes as $recipeId => $recipe)
		{
			$name = $recipe['name'];
			$furnishing = $this->furnishings[$recipe['resultItemId']];
			$recipeLink = "|H0:item:$recipeId:1:1:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0|h|h";
			
			if ($furnishing)
			{
				print("$index, \"{$furnishing['name']}\", {$furnishing['quality']}, {$recipe['resultItemLink']}, \"{$recipe['name']}\", $recipeLink\n");
				$this->furnishings[$recipe['resultItemId']]['__found'] = 1; 
			}
			else
			{
				print("$index, ?, ?, {$recipe['resultItemLink']}, \"$name\", $recipeLink\n");
			}
			
			++$index;
		}
		
		foreach ($this->furnishings as $furnishId => $furnish)
		{
			$furnishLink = "|H0:item:$furnishId:1:1:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0|h|h";
			print("$index, \"{$furnish['name']}\", {$furnish['quality']}, $furnishLink, ?, ?\n");
			++$index;
		}
	}
	
	
	public function Render()
	{
		$this->OutputTextHeader();
		$this->LoadData();
		$this->PrintData();
	}
	
};


$viewFurnishings = new CEsoViewFurnishings();
$viewFurnishings->Render();