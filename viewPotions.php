<?php 


require_once("/home/uesp/secrets/esolog.secrets");
require_once(__DIR__."/esoCommon.php");
require_once(__DIR__."/esoPotionData.php");


class CEsoViewPotions
{
	public $ESOPD_HTML_TEMPLATE = "";
	
	
	public function __construct()
	{
		$this->ESOPD_HTML_TEMPLATE = __DIR__."/templates/esopotions_template.txt";
		
		$this->SetInputParams();
		$this->ParseInputParams();
		
		$this->LoadTemplate();
	}
	
	
	public function ReportError($errorMsg)
	{
		error_log($errorMsg);
		return false;
	}
	
	
	private function ParseInputParams ()
	{
		return true;
	}
	
	
	private function SetInputParams ()
	{
		$this->inputParams = $_REQUEST;
	}
	
	
	private function OutputHtmlHeader()
	{
		ob_start("ob_gzhandler");
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
	
		$origin = $_SERVER['HTTP_ORIGIN'];
	
		if (substr($origin, -8) == "uesp.net")
		{
			header("Access-Control-Allow-Origin: $origin");
		}
	}
	
	
	public function LoadTemplate()
	{
		$this->htmlTemplate = file_get_contents($this->ESOPD_HTML_TEMPLATE);
	}
	
	
	public function GetReagentsHtml()
	{
		global $ESO_REAGENT_DATA, $ESO_POTIONEFFECT_DATA, $ESO_UNKNOWN_POTION_EFFECT;
		
		$output = "";
		
		foreach ($ESO_REAGENT_DATA as $reagent)
		{
			$iconUrl = $reagent['icon'];
			$name = $reagent['name'];
			$effectIndex = $reagent['id'];
			
			$trait1 = $ESO_POTIONEFFECT_DATA[$reagent['effects'][0]] ?: $ESO_UNKNOWN_POTION_EFFECT;
			$trait2 = $ESO_POTIONEFFECT_DATA[$reagent['effects'][1]] ?: $ESO_UNKNOWN_POTION_EFFECT;
			$trait3 = $ESO_POTIONEFFECT_DATA[$reagent['effects'][2]] ?: $ESO_UNKNOWN_POTION_EFFECT;
			$trait4 = $ESO_POTIONEFFECT_DATA[$reagent['effects'][3]] ?: $ESO_UNKNOWN_POTION_EFFECT;
			
			$traitIcon1 = $trait1['icon'];
			$traitIcon2 = $trait2['icon'];
			$traitIcon3 = $trait3['icon'];
			$traitIcon4 = $trait4['icon'];
			
			$traitText1 = $trait1['name'];
			$traitText2 = $trait2['name'];
			$traitText3 = $trait3['name'];
			$traitText4 = $trait4['name'];
			
			$output .= "<div class=\"esopdReagent\" reagent=\"$name\" effectindex=\"$effectIndex\">";
			$output .= "<img class=\"esopdReagentIcon\" src=\"$iconUrl\"><br /><div class=\"esopdReagentName\">$name</div><br />";
			$output .= "<div class=\"esopdTraits\">";
			$output .= "<img src='$traitIcon1' title='$traitText1'>";
			$output .= "<img src='$traitIcon2' title='$traitText2'>";
			$output .= "<img src='$traitIcon3' title='$traitText3'>";
			$output .= "<img src='$traitIcon4' title='$traitText4'>";
			$output .= "</div>";
			$output .= "</div>";
		}
		
		return $output;
	}
	
	
	public function GetEffectsHtml()
	{
		global $ESO_POTIONEFFECT_DATA;
		
		$output = "";
		$sortedEffects = $ESO_POTIONEFFECT_DATA;
		usort($sortedEffects, EsoSortPotionDataName);
				
		foreach ($sortedEffects as $effect)
		{
			$effectIndex = $effect['id'];
			$icon = $effect['icon'];
			$name = $effect['name'];
			$isPositive = $effect['isPositive'];
			
			$extraClass = "esopdEffectPositive";
			if (!$isPositive) $extraClass = "esopdEffectNegative";
			
			$output .= "<div class=\"esopdEffect $extraClass\" effectindex=\"$effectIndex\">";
			$output .= "<img src='$icon'> $name";
			$output .= "</div>";
		}		
		
		return $output;
	}
	
	
	public function GetReagentJson()
	{
		global $ESO_REAGENT_DATA;
		
		$data = array();
		
		foreach ($ESO_REAGENT_DATA as $reagent)
		{
			$data[$reagent['name']] = $reagent;
		}
		
		return json_encode($data);
	}
	
	
	public function GetEffectJson()
	{
		global $ESO_POTIONEFFECT_DATA;
		return json_encode($ESO_POTIONEFFECT_DATA);
	}
	
	
	public function GetUnknownEffectJson()
	{
		global $ESO_UNKNOWN_POTION_EFFECT;
		return json_encode($ESO_UNKNOWN_POTION_EFFECT);
	}
	
	
	public function CreateOutputHtml()
	{
		$replacePairs = array(
				'{reagents}' => $this->GetReagentsHtml(),
				'{effects}' => $this->GetEffectsHtml(),
				'{reagentJS}' => $this->GetReagentJson(),
				'{effectJS}' => $this->GetEffectJson(),
				'{unknownEffectJS}' => $this->GetUnknownEffectJson(),
		);
	
		$output = strtr($this->htmlTemplate, $replacePairs);

		return $output;
	}
	
	
	public function OutputHtml()
	{
		print ($this->CreateOutputHtml());
	}
	
	
	public function Render()
	{
		$this->OutputHtmlHeader();
		$this->OutputHtml();
	}
	
};


function EsoSortPotionDataName($a, $b)
{
	return strcmp($a['name'], $b['name']);
}


$viewPotions = new CEsoViewPotions();
$viewPotions->Render();
