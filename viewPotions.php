<?php 


require_once("/home/uesp/secrets/esolog.secrets");
require_once(__DIR__."/esoCommon.php");
require_once(__DIR__."/esoPotionData.php");


class CEsoViewPotions
{
	public $ESOPD_HTML_TEMPLATE = "";
	
	public $inputParams = array();
	
	public $inputSolvent = "";
	public $inputIsPoison = false;
	public $inputReagents = array("", "", "");
	
	
	public function __construct()
	{
		$this->ESOPD_HTML_TEMPLATE = __DIR__."/templates/esopotions_template.txt";
		
		$this->SetInputParams();
		$this->ParseInputParams();
		
		$this->LoadTemplate();
		
		UpdateEsoPageViews("potionViews");
	}
	
	
	public function ReportError($errorMsg)
	{
		error_log($errorMsg);
		return false;
	}
	
	
	private function ParseInputParams ()
	{
		global $ESO_SOLVENT_DATA, $ESO_REAGENT_DATA;
		
		if (array_key_exists("s", $this->inputParams)) $this->inputSolvent = $this->inputParams['s'];
		if (array_key_exists("solvent", $this->inputParams)) $this->inputSolvent = $this->inputParams['solvent'];
		
		if (!array_key_exists($this->inputSolvent, $ESO_SOLVENT_DATA)) $this->inputSolvent = "Lorkhan's Tears";
		if (array_key_exists($this->inputSolvent, $ESO_SOLVENT_DATA) && $ESO_SOLVENT_DATA[$this->inputSolvent]['isPoison']) $this->inputIsPoison = true;
				
		if (array_key_exists("r1", $this->inputParams)) $this->inputReagents[0] = $this->inputParams['r1'];
		if (array_key_exists("r2", $this->inputParams)) $this->inputReagents[1] = $this->inputParams['r2'];
		if (array_key_exists("r3", $this->inputParams)) $this->inputReagents[2] = $this->inputParams['r3'];
		
		if (array_key_exists("reagent1", $this->inputParams)) $this->inputReagents[0] = $this->inputParams['reagent1'];
		if (array_key_exists("reagent2", $this->inputParams)) $this->inputReagents[1] = $this->inputParams['reagent2'];
		if (array_key_exists("reagent3", $this->inputParams)) $this->inputReagents[2] = $this->inputParams['reagent3'];
		
		if ($this->inputReagents[0] && $ESO_REAGENT_DATA[$this->inputReagents[0]] == null) $this->inputReagents[0] = "";
		if ($this->inputReagents[1] && $ESO_REAGENT_DATA[$this->inputReagents[1]] == null) $this->inputReagents[1] = "";
		if ($this->inputReagents[2] && $ESO_REAGENT_DATA[$this->inputReagents[2]] == null) $this->inputReagents[2] = "";
		
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
		header("Content-Type: text/html");
	
		header("Access-Control-Allow-Origin: *");
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
			
			$output .= "<div class=\"esopdReagent\" reagent=\"$name\">";
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
		usort($sortedEffects, 'EsoSortPotionDataName');
				
		foreach ($sortedEffects as $effect)
		{
			$effectIndex = $effect['id'];
			$icon = $effect['icon'];
			$name = $effect['name'];
			$name2 = $effect['name2'];
			$isPositive = $effect['isPositive'];
			
			$extraClass = "esopdEffectPositive";
			if (!$isPositive) $extraClass = "esopdEffectNegative";
			
			if ($this->inputIsPoison && $name2) $name = $name2;
			
			$output .= "<div class=\"esopdEffect $extraClass\" effectindex=\"$effectIndex\">";
			$output .= "<img src='$icon'> ";
			$output .= "<div class=\"esopdEffectName\">$name</div>";
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
	
	
	public function GetSolventsHtml()
	{
		global $ESO_SOLVENT_DATA;
		
		$output = "";
		$sortedSolvents = $ESO_SOLVENT_DATA;
		//usort($sortedSolvents, EsoSortSolventData);
		
		foreach ($sortedSolvents as $solvent)
		{
			$iconUrl = $solvent['icon'];
			$name = $solvent['name'];
			$level = $solvent['level'];
			$isPoison = $solvent['isPoison'];
			
			if ($level > 50)
				$level = "CP" . (($level - 50) * 10);
			else
				$level = "Level $level";
			
			$output .= "<div class=\"esopdSolvent\" solvent=\"$name\">";
			$output .= "<img class=\"esopdSolventIcon\" src=\"$iconUrl\"><br /><div class=\"esopdSolventName\">$name<br/>$level</div><br />";
			$output .= "</div>";		
		}
		
		return $output;
	}
	
	
	public function GetSolventJson()
	{
		global $ESO_SOLVENT_DATA;
		return json_encode($ESO_SOLVENT_DATA);
	}
	
	
	public function GetInputSolventName()
	{
		return htmlspecialchars($this->inputSolvent);
	}
	
	
	public function GetInputSolventLevel()
	{
		global $ESO_SOLVENT_DATA;
		
		$solventData = $ESO_SOLVENT_DATA[$this->inputSolvent];
		if ($solventData == null) return "Level 1";
		
		$level = $solventData['level'];
		
		if ($level > 50)
			$level = "CP". (($level-50)*10);
		else
			$level = "Level $level";
			
		return $level;
	}
	
	
	public function GetInputSolventIcon()
	{
		global $ESO_SOLVENT_DATA;
		
		$solventData = $ESO_SOLVENT_DATA[$this->inputSolvent];
		if ($solventData == null) return "resources/alchemy_emptyslot_solvent.png";
		
		return $solventData['icon'];
	}
	
	
	public function GetInputReagentName($i)
	{
		if ($this->inputReagents[$i] == null) return "";
		return htmlspecialchars($this->inputReagents[$i]);
	}
	
	
	public function GetInputReagentIcon($i)
	{
		global $ESO_REAGENT_DATA;
		
		$reagent = $this->inputReagents[$i];
		
		if ($reagent == null || $ESO_REAGENT_DATA[$reagent] == null) return "resources/alchemy_emptyslot_reagent.png";
		
		return $ESO_REAGENT_DATA[$reagent]['icon'];
	}
	
	
	public function GetInputReagentEffectName($i, $effectIndex)
	{
		global $ESO_REAGENT_DATA, $ESO_POTIONEFFECT_DATA;
	
		$reagent = $this->inputReagents[$i];
		if ($reagent == null) return "";
		if ($ESO_REAGENT_DATA[$reagent] == null) return "";
	
		$effect = $ESO_REAGENT_DATA[$reagent]['effects'][$effectIndex];
		if ($effect == null) return "";
	
		$effectData = $ESO_POTIONEFFECT_DATA[$effect];
		if ($effectData == null) return "";
	
		return htmlspecialchars($effectData['name']);
	}
	
	
	public function GetInputReagentEffectIcon($i, $effectIndex)
	{
		global $ESO_REAGENT_DATA, $ESO_POTIONEFFECT_DATA;
		
		$reagent = $this->inputReagents[$i];
		if ($reagent == null) return "/resources/blank.gif";
		if ($ESO_REAGENT_DATA[$reagent] == null) return "/resources/blank.gif";
		
		$effect = $ESO_REAGENT_DATA[$reagent]['effects'][$effectIndex];
		if ($effect == null) return "/resources/blank.gif";
		
		$effectData = $ESO_POTIONEFFECT_DATA[$effect];
		if ($effectData == null) return "/resources/blank.gif";
		
		return $effectData['icon'];
	}
	
	
	public function CreateOutputHtml()
	{
		$replacePairs = array(
				'{reagents}' => $this->GetReagentsHtml(),
				'{solvents}' => $this->GetSolventsHtml(),
				'{effects}' => $this->GetEffectsHtml(),
				'{reagentJS}' => $this->GetReagentJson(),
				'{solventJS}' => $this->GetSolventJson(),
				'{effectJS}' => $this->GetEffectJson(),
				'{unknownEffectJS}' => $this->GetUnknownEffectJson(),
				
				'{solventName}' => $this->GetInputSolventName(),
				'{solventLevel}' => $this->GetInputSolventLevel(),
				'{solventIcon}' => $this->GetInputSolventIcon(),
				
				'{reagentName1}' => $this->GetInputReagentName(0),
				'{reagentIcon1}' => $this->GetInputReagentIcon(0),
				
				'{reagentName2}' => $this->GetInputReagentName(1),
				'{reagentIcon2}' => $this->GetInputReagentIcon(1),
				
				'{reagentName3}' => $this->GetInputReagentName(2),
				'{reagentIcon3}' => $this->GetInputReagentIcon(2),
				
				'{reagentEffectTitle11}' => $this->GetInputReagentEffectName(0, 0),
				'{reagentEffectTitle12}' => $this->GetInputReagentEffectName(0, 1),
				'{reagentEffectTitle13}' => $this->GetInputReagentEffectName(0, 2),
				'{reagentEffectTitle14}' => $this->GetInputReagentEffectName(0, 3),
				'{reagentEffectIcon11}' => $this->GetInputReagentEffectIcon(0, 0),
				'{reagentEffectIcon12}' => $this->GetInputReagentEffectIcon(0, 1),
				'{reagentEffectIcon13}' => $this->GetInputReagentEffectIcon(0, 2),
				'{reagentEffectIcon14}' => $this->GetInputReagentEffectIcon(0, 3),
				
				'{reagentEffectTitle21}' => $this->GetInputReagentEffectName(1, 0),
				'{reagentEffectTitle22}' => $this->GetInputReagentEffectName(1, 1),
				'{reagentEffectTitle23}' => $this->GetInputReagentEffectName(1, 2),
				'{reagentEffectTitle24}' => $this->GetInputReagentEffectName(1, 3),
				'{reagentEffectIcon21}' => $this->GetInputReagentEffectIcon(1, 0),
				'{reagentEffectIcon22}' => $this->GetInputReagentEffectIcon(1, 1),
				'{reagentEffectIcon23}' => $this->GetInputReagentEffectIcon(1, 2),
				'{reagentEffectIcon24}' => $this->GetInputReagentEffectIcon(1, 3),
				
				'{reagentEffectTitle31}' => $this->GetInputReagentEffectName(2, 0),
				'{reagentEffectTitle32}' => $this->GetInputReagentEffectName(2, 1),
				'{reagentEffectTitle33}' => $this->GetInputReagentEffectName(2, 2),
				'{reagentEffectTitle34}' => $this->GetInputReagentEffectName(2, 3),
				'{reagentEffectIcon31}' => $this->GetInputReagentEffectIcon(2, 0),
				'{reagentEffectIcon32}' => $this->GetInputReagentEffectIcon(2, 1),
				'{reagentEffectIcon33}' => $this->GetInputReagentEffectIcon(2, 2),
				'{reagentEffectIcon34}' => $this->GetInputReagentEffectIcon(2, 3),
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
