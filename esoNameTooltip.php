<?php

/*
 * Tries to display a set/item/skill tooltip based on just a name.
 * 
 * https://esolog.uesp.net/esoNameTooltip.php?name=Roksa%20the%20Warped
 * https://esolog.uesp.net/esoNameTooltip.php?name=Resolving Vigor
 * https://esolog.uesp.net/esoNameTooltip.php?name=Order's Wrath
 * https://esolog.uesp.net/esoNameTooltip.php?name=arcanist/apocryphal-soldier/sanctum-of-the-abyssal-sea
 */


require_once("esoCommon.php");


class CEsoNameTooltip 
{
	public $inputName = "";
	public $DEBUG = false;
	
	
	public function __construct()
	{
		$this->ParseInputParams();
	}
	
	
	public function ReportError($msg)
	{
		$this->OutputHtmlHeader();
		
		http_response_code(400);
		header("X-UESP-Error: $msg");
		
		$data = [];
		$data['isError'] = 1;
		$data['type'] = 'error';
		$data['errorMsg'] = $msg;
		
		$json = json_encode($data);
		
		print($json);
		
		return false;
	}
	
	
	public function OutputHtmlHeader()
	{
		ob_start("ob_gzhandler");
		
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		header("Access-Control-Allow-Origin: *");
		header("content-type: application/json");
	}
	
	
	public function ParseInputParams()
	{
		if ($_REQUEST['name']) $this->inputName = trim($_REQUEST['name']);
	}
	
	
	public function IsSetName($name)
	{
		global $ESO_SET_INDEXES;
		
		$niceName = strtolower($name);
		
		foreach ($ESO_SET_INDEXES as $id => $setName)
		{
			$niceSetName = strtolower($setName);
			if ($niceSetName == $niceName) return true;
		}
		
		return false;
	}
	
	
	public function DisplaySetTooltip($setName)
	{
		//https://esolog.uesp.net/itemLinkImage.php?set=Roksa%20the%20Warped&version=
		
		//header("Location: https://esolog.uesp.net/itemLinkImage.php?set=$setName");
		//header("Location: https://esolog.uesp.net/itemLink.php?embed&set=$setName");
		
		$this->OutputHtmlHeader();
		
		$data = [];
		$data['isError'] = 0;
		$data['errorMsg'] = '';
		$data['type'] = 'set';
		$data['link'] = "https://esolog.uesp.net/itemLinkImage.php?set=$setName";
		
		$json = json_encode($data);
		
		print($json);
		
		return true;
	}
	
	
	public function DisplaySkillTooltip($skillName)
	{
		//https://esolog.uesp.net/skillTooltip.php?name=arcanist%2Fherald-of-the-tome%2Fthe-tide-kings-gaze&includelink=&version=&_=1704306786413
		
		//header("Location: https://esolog.uesp.net/skillTooltip.php?name=$skillName");
		
		$this->OutputHtmlHeader();
		
		$data = [];
		$data['isError'] = 0;
		$data['errorMsg'] = '';
		$data['type'] = 'skill';
		$data['link'] = "https://esolog.uesp.net/skillTooltip.php?name=$skillName";
		
		$json = json_encode($data);
		
		print($json);
		
		return true;
	}
	
	
	public function Run()
	{
		if ($this->DEBUG)
		{
			$this->OutputHtmlHeader();
			print("Input: " . $this->inputName);
		}
		
		if ($this->inputName == null || $this->inputName == '') return $this->ReportError("No name specified for tooltip!");
		
		if ($this->IsSetName($this->inputName))
		{
			return $this->DisplaySetTooltip($this->inputName);
		}
		
		return $this->DisplaySkillTooltip($this->inputName);
		return true;
	}
};

$nameTooltip = new CEsoNameTooltip();
$nameTooltip->Run();