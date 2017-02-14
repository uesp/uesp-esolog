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
	
	
	public function GetRegeantsHtml()
	{
		global $ESO_REAGENT_DATA, $ESO_POTION_DATA, $ESO_UNKNOWN_POTION_EFFECT;
		
		$output = "";
		
		foreach ($ESO_REAGENT_DATA as $reagent)
		{
			$iconUrl = $reagent['icon'];
			$name = $reagent['name'];
			
			$trait1 = $ESO_POTION_DATA[$reagent['effects'][0]] ?: $ESO_UNKNOWN_POTION_EFFECT;
			$trait2 = $ESO_POTION_DATA[$reagent['effects'][1]] ?: $ESO_UNKNOWN_POTION_EFFECT;
			$trait3 = $ESO_POTION_DATA[$reagent['effects'][2]] ?: $ESO_UNKNOWN_POTION_EFFECT;
			$trait4 = $ESO_POTION_DATA[$reagent['effects'][3]] ?: $ESO_UNKNOWN_POTION_EFFECT;
			
			$traitIcon1 = $trait1['icon'];
			$traitIcon2 = $trait2['icon'];
			$traitIcon3 = $trait3['icon'];
			$traitIcon4 = $trait4['icon'];
			
			$output .= "<div class='esopdReagent'>";
			$output .= "<img class=esopdReagentIcon' src='$iconUrl'> <div class='esopdReagentName'>$name</div>";
			$output .= "<div class='esopdTraits'>";
			$output .= "<img src='$traitIcon1'>";
			$output .= "<img src='$traitIcon2'>";
			$output .= "<img src='$traitIcon3'>";
			$output .= "<img src='$traitIcon4'>";
			$output .= "</div>";
			$output .= "</div>";
		}
		
		
		return $output;
	}
	
	
	public function CreateOutputHtml()
	{
		$replacePairs = array(
				'{reagents}' => $this->GetRegeantsHtml(),
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


$viewPotions = new CEsoViewPotions();
$viewPotions->Render();
