<?php 

// Database users, passwords and other secrets
require_once("/home/uesp/secrets/esolog.secrets");
require_once(__DIR__."/esoCommon.php");


class CEsoViewTributeCard
{
	public $ESOVTC_HTML_TEMPLATE = "";	// Set in constructor
	public $BASE_IMAGE_URL = "https://esoicons.uesp.net/";
	
	public $CARD_WIDTH = 256;	// Should be same size as the base card images
	public $CARD_HEIGHT = 512;
	
	public $htmlTemplate = "";
	public $dataLoaded = false;
	
	public $db = null;
	public $lastQuery = "";
	public $cardData = null;
	
	public $inputCardName = "";
	public $inputCardId = -1;
	public $errorMessage = "";
	
	
	public function __construct()
	{
		$this->ESOVTC_HTML_TEMPLATE = __DIR__ . "/templates/esotributecard_template.txt";
		
		$this->SetInputParams();
		$this->ParseInputParams();
		$this->InitDatabase();
		$this->LoadTemplate();
	}
	
	
	public function ReportError($errorMsg)
	{
		error_log($errorMsg);
		return false;
	}
	
	
	private function InitDatabase()
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
		
		$this->db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($this->db->connect_error) return $this->ReportError("ERROR: Could not connect to mysql database!");
		
		UpdateEsoPageViews("tributeCardViews");
		
		return true;
	}
	
	
	private function OutputHtmlHeader()
	{
		ob_start("ob_gzhandler");
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		header("Access-Control-Allow-Origin: *");
	}
	
	
	private function LoadTemplate()
	{
		$templateFile = $this->ESOVTC_HTML_TEMPLATE;
		
		$this->htmlTemplate = file_get_contents($templateFile);
	}
	
	
	private function ParseInputParams ()
	{
		if (array_key_exists('id', $this->inputParams)) $this->inputCardId = intval($this->inputParams['id']);
		if (array_key_exists('cardid', $this->inputParams)) $this->inputCardId = intval($this->inputParams['cardid']);
		if (array_key_exists('name', $this->inputParams)) $this->inputCardName = $this->inputParams['name'];
		if (array_key_exists('cardname', $this->inputParams)) $this->inputCardName = $this->inputParams['cardname'];
		
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
	
	
	public function GetCardImageUrl()
	{
		if ($this->cardData == null) return "";
		
		$texture = $this->cardData['texture'];
		if ($texture == null || $texture == "") return "";
		
		return $this->BASE_IMAGE_URL . str_replace(".dds", ".png", $texture);
	}
	
	
	public function CreateOutputHtml()
	{
		$replacePairs = array(
				'{cardWidth}' => $this->CARD_WIDTH,
				'{cardHeight}' => $this->CARD_HEIGHT,
				'{cardImageUrl}' => $this->GetCardImageUrl(),
		);
		
		$output = strtr($this->htmlTemplate, $replacePairs);
		
		return $output;
	}
	
	
	public function CreateErrorOutputHtml()
	{
		$replacePairs = array(
				'{cardWidth}' => $this->CARD_WIDTH,
				'{cardHeight}' => $this->CARD_HEIGHT,
				'{cardImageUrl}' => "",
		);
		
		$output = strtr($this->htmlTemplate, $replacePairs);
		
		return $output;
	}
	
	
	public function LoadTributeCardById($cardId)
	{
		$safeId = intval($cardId);
		$this->lastQuery = "SELECT * FROM tributeCards WHERE id='$safeId';";
		
		$result = $this->db->query($this->lastQuery);
		if ($result === false || $result->num_rows == 0) return $this->ReportError("Error: Could not find the card #$cardId!");
		
		$row = $result->fetch_assoc();
		if ($row == null) return $this->ReportError("Error: Could not find the card #$cardId!");
		
		$this->cardData = $row;
		$this->dataLoaded = true;
		
		return true;
	}
	
	
	public function LoadTributeCardByName($cardName)
	{
		$safeName = $this->db->real_escape_string($cardName);
		$this->lastQuery = "SELECT * FROM tributeCards WHERE name='$cardName' LIMIT 1;";		//TODO Duplicate names?
		
		$result = $this->db->query($this->lastQuery);
		if ($result === false || $result->num_rows == 0) return $this->ReportError("Error: Could not find the card named '$cardName'!");
		
		$row = $result->fetch_assoc();
		if ($row == null) return $this->ReportError("Error: Could not find the card named '$cardName'!");
		
		$this->cardData = $row;
		$this->dataLoaded = true;
		return true;
	}
	
	
	public function LoadData()
	{
		$this->dataLoaded = false;
		
		if ($this->inputCardId > 0)
		{
			return $this->LoadTributeCardById($this->inputCardId);
		}
		else if ($this->inputCardName)
		{
			return $this->LoadTributeCardById($this->inputCardId);
		}
		else
		{
			$this->errorMessage = "No input card name or ID specified!";
			return false;
		}
		
		return true;
	}
	
	
	public function GetOutputHtml()
	{
		if (!$this->dataLoaded)	$this->LoadData();
		
		return $this->CreateOutputHtml();
	}
	
	
	public function OutputHtml()
	{
		$output = $this->CreateOutputHtml();
		print ($output);
	}
	
	
	public function Render()
	{
		//$this->OutputHtmlHeader();
		
		if (!$this->dataLoaded)	$this->LoadData();
		
		$this->OutputHtml();
	}
};


$cardViewer = new CEsoViewTributeCard();
$cardViewer->Render();