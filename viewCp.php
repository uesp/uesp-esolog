<?php 

// Database users, passwords and other secrets
require_once("/home/uesp/secrets/esolog.secrets");
require_once("esoCommon.php");


class CEsoViewCP
{
	const ESOVCP_HTML_TEMPLATE = "templates/esocp_template.txt";
	const ESOVCP_HTML_TEMPLATE_EMBED = "templates/esocp_template.txt";
	
	public $htmlTemplate = "";
	public $isEmbedded = false;
	public $baseUrl = "";
	public $basePath = "";
	public $baseResource = "";
	
	public $version = "";
			

	public function __construct ($isEmbedded = false)
	{
		$this->isEmbedded = $isEmbedded;
	
		$this->SetInputParams();
		$this->ParseInputParams();
		$this->InitDatabase();
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
	
		return true;
	}
	
	
	private function OutputHtmlHeader()
	{
		ob_start("ob_gzhandler");
		
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . "");
	}
	
	
	public function LoadTemplate()
	{
		$templateFile = $this->basePath;
	
		if ($this->isEmbedded)
			$templateFile .= self::ESOVCP_HTML_TEMPLATE_EMBED;
		else
			$templateFile .= self::ESOVCP_HTML_TEMPLATE;
					
		$this->htmlTemplate = file_get_contents($templateFile);
	}
	

	private function ParseInputParams ()
	{
		if (array_key_exists('version', $this->inputParams)) $this->version = urldecode($this->inputParams['version']);
	
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
	
	
	private function GetTableSuffix()
	{
		return GetEsoItemTableSuffix($this->version);
	}
	
	
	public function GetUpdateDate()
	{
		$query = "SELECT * FROM logInfo WHERE id='lastCPUpdate';";
		$result = $this->db->query($query);
		if (!$result) return "";
	
		$row = $result->fetch_assoc();
		$updateDate = $row['value'];
	
		return $updateDate;
	}
	
	
	public function GetVersionTitle()
	{
		if ($this->GetTableSuffix() == "") return "";
		return " v" . $this->version . "";
	}
		
	
	public function CreateOutputHtml()
	{
		$this->LoadTemplate();
		
		$replacePairs = array(
				'{version}' => $this->version,
				'{versionTitle}' => $this->GetVersionTitle(),
				'{updateDate}' => $this->GetUpdateDate(),
		);
	
		$output = strtr($this->htmlTemplate, $replacePairs);
		return $output;
	}
	
	
	
	public function Render()
	{
		$this->OutputHtmlHeader();
		
		$output = $this->CreateOutputHtml();
		print ($output);
	}
	
};


$g_EsoViewCp = new CEsoViewCP(false);
$g_EsoViewCp->Render();