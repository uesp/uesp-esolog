<?php 

require_once("/home/uesp/secrets/esosalesdata.secrets");
require_once("/home/uesp/secrets/esolog.secrets");
require_once(__DIR__."/esoCommon.php");
require_once(__DIR__."/esoPotionData.php");


class EsoGetSalesImage
{
	const REGULARFONT_FILE = "./resources/esofontregular-webfont.ttf";
	const BOLDFONT_FILE = "./resources/esofontbold-webfont.ttf";
	
	
	public $db = null;
	public $dbReadInitialized = false;
	public $dbWriteInitialized = false;
	public $lastQuery = "";
	
	public $inputParams = array();
	public $itemId = 0;
	public $outputWidth = 1024;
	public $outputHeight = 600;
	
	public $salesData = array();
	public $minPrice = 0;
	public $maxPrice = 100;
	public $minTime = 0;
	public $maxTime = 1;
	
	public $soldAvgPrice = 0;
	public $listAvgPrice = 0;
	public $totalAvgPrice = 0;
	public $totalSoldCount = 0;
	public $totalListCount = 0;
	public $totalItemCount = 0;
	
	public $image = null;
	
	
	public function __construct ()
	{
		$this->minTime = time() - 86400*10;
		$this->maxTime = time();
		
		$this->SetInputParams();
		$this->ParseInputParams();
		$this->InitDatabaseRead();
	}
	
	
	public function ReportError($errorMsg)
	{
		//print($errorMsg);
		error_log($errorMsg);
		return false;
	}
	
	
	private function ParseInputParams ()
	{
		if (array_key_exists('id', $this->inputParams))
		{
			$this->itemId = intval($this->inputParams['id']);
		}
			
		return true;
	}
	
	
	private function SetInputParams ()
	{
		global $argv;
		$this->inputParams = $_REQUEST;
	}
	
	
	private function InitDatabaseRead ()
	{
		global $uespEsoSalesDataReadDBHost, $uespEsoSalesDataReadUser, $uespEsoSalesDataReadPW, $uespEsoSalesDataDatabase;

		if ($this->dbReadInitialized) return true;

		$this->db = new mysqli($uespEsoSalesDataReadDBHost, $uespEsoSalesDataReadUser, $uespEsoSalesDataReadPW, $uespEsoSalesDataDatabase);
		if ($this->db->connect_error) return $this->ReportError("Could not connect to mysql database!");

		$this->dbReadInitialized = true;
		$this->dbWriteInitialized = false;
		
		return true;
	}
	
	
	public function LoadSalesData()
	{
		if ($this->itemId <= 0) return $this->ReportError("No itemId specified for sales data!");
		
		$this->lastQuery = "SELECT * FROM sales WHERE itemId='{$this->itemId}';";
		$result = $this->db->query($this->lastQuery);
		if ($result === false) return $this->ReportError("Failed to load sales data records!");
		if ($result->num_rows == 0) return $this->ReportError("No sales data found for itemId {$this->itemId}!");
		
		$this->salesData = array();
		
		while (($row = $result->fetch_assoc()))
		{
			$this->salesData[] = $row;
		}
		
		return true;
	}
	
	
	public function ComputeBasicStats()
	{
		if (count($this->salesData) < 1) return false;
		
		$listSum = 0;
		$soldSum = 0;
		$listCount = 0;
		$soldCount = 0;
		$minPrice = $this->salesData[0]['price'] / $this->salesData[0]['qnt'];
		$maxPrice = $minPrice;
		$minTime = time();
		$maxTime = 0;
		
		foreach ($this->salesData as $sale)
		{
			$price = intval($sale['price']);
			$qnt = intval($sale['qnt']);
			$unitPrice = $price / $qnt;
			$soldTime = intval($sale['buyTimestamp']);
			$listTime = intval($sale['listTimestamp']);
			
			if ($soldTime > 0)
			{
				$soldSum += $price;
				$soldCount += $qnt;
				
				if ($minPrice > $unitPrice) $minPrice = $unitPrice;
				if ($maxPrice < $unitPrice) $maxPrice = $unitPrice;
				if ($minTime > $soldTime) $minTime = $soldTime;
				if ($maxTime < $soldTime) $maxTime = $soldTime;
			}
			
			if ($listTime > 0)
			{
				$listSum += $price;
				$listCount += $qnt;
				
				if ($minPrice > $unitPrice) $minPrice = $unitPrice;
				if ($maxPrice < $unitPrice) $maxPrice = $unitPrice;
				if ($minTime > $listTime) $minTime = $listTime;
				if ($maxTime < $listTime) $maxTime = $listTime;
			}
		}
		
		
		$this->minTime = $minTime;
		$this->maxTime = $maxTime;
		$this->minPrice = $minPrice;
		$this->maxPrice = $maxPrice;
		
		$this->soldAvgPrice = 0;
		$this->listAvgPrice = 0;
		$this->totalAvgPrice = 0;

		if ($soldCount > 0) $this->soldAvgPrice = $soldSum / $soldCount;
		$this->soldItemCount = $soldCount;
		if ($listCount > 0) $this->listAvgPrice = $listSum / $listCount;
		$this->listItemCount = $listCount;
		
		$this->totalItemCount = $soldCount + $listCount;
		$this->totalAvgPrice = ($soldSum + $listSum) / $this->totalItemCount;
		
		if ($this->totalItemCount == 1)
		{
			$this->minPrice /= 2;
			$this->maxPrice *= 2;
			
			$this->minTime -= 3600;
			$this->maxTime += 3600;			
		}
		
		return true;
	}
	
	
	public function OutputHtmlHeader()
	{
		ob_start("ob_gzhandler");
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		header("content-type: image/png");
	
		$origin = $_SERVER['HTTP_ORIGIN'];
	
		if (substr($origin, -8) == "uesp.net")
		{
			header("Access-Control-Allow-Origin: $origin");
		}
	}
	
	const BORDER_LEFT_MARGIN = 100;
	const BORDER_RIGHT_MARGIN = 10;
	const BORDER_TOP_MARGIN = 10;
	const BORDER_BOTTOM_MARGIN = 50;
	const TICK_LENGTH = 5;
	const TICK_FONT_SIZE = 10;
	
	
	public function ConvertGraphToPixelX($x)
	{
		$graphRange = $this->maxTime - $this->minTime;
		if ($graphRange == 0) $graphRange = 3600;
		$pixelRange = $this->outputWidth - self::BORDER_LEFT_MARGIN - self::BORDER_RIGHT_MARGIN;
		
		return intval(($x - $this->minTime) / $graphRange * $pixelRange) + self::BORDER_LEFT_MARGIN;
	}
	
	
	public function ConvertGraphToPixelY($y)
	{
		$graphRange = $this->maxPrice - $this->minPrice;
		if ($graphRange == 0) $graphRange = 1;
		$pixelRange = $this->outputHeight - self::BORDER_TOP_MARGIN - self::BORDER_BOTTOM_MARGIN;
	
		return intval(($this->maxPrice - $y) / $graphRange * $pixelRange) + self::BORDER_TOP_MARGIN;
	}
	
	
	public function CreateImageBorders(&$image)
	{
		$x1 = self::BORDER_LEFT_MARGIN;
		$y1 = self::BORDER_TOP_MARGIN;
		$x2 = $this->outputWidth - self::BORDER_RIGHT_MARGIN;
		$y2 = $this->outputHeight - self::BORDER_BOTTOM_MARGIN;
		
		imagerectangle($image, $x1, $y1, $x2, $y2, $this->borderColor);		
	}
	
	
	public function CreateImageTicks(&$image)
	{
		$this->CreateImageTicksX($image);	
		$this->CreateImageTicksY($image);
	}
	
	
	public function CreateImageTicksX(&$image)
	{
		$currentTime = time();
		$y1 = $this->outputHeight - self::BORDER_BOTTOM_MARGIN;
		$y2 = $y1 + self::TICK_LENGTH;
		$tickRange = $this->GetNiceTickRange($this->minTime, $this->maxTime, 0);
		$startValue = $this->minTime + $tickRange - fmod($this->minTime, $tickRange);
		
		for ($value = $startValue; $value < $this->maxTime; $value += $tickRange)
		{
			$days = intval(($currentTime - $value) / 86400);			
			$x = $this->ConvertGraphToPixelX($value);
			imageline($image, $x, $y1, $x, $y2, $this->borderColor);
			imagefttext($image, self::TICK_FONT_SIZE, 0, $x, $y2 + 20, $this->borderColor, self::REGULARFONT_FILE, $days);
		}
		
	}
	
	
	public function CreateImageTicksY(&$image)
	{
		$x2 = self::BORDER_LEFT_MARGIN;
		$x1 = $x2 - self::TICK_LENGTH;
		$tickRange = $this->GetNiceTickRange($this->minPrice, $this->maxPrice);
		$startValue = $this->minPrice + $tickRange - fmod($this->minPrice, $tickRange);
		
		for ($value = $startValue; $value < $this->maxPrice; $value += $tickRange)
		{
			$y = $this->ConvertGraphToPixelY($value);
			imageline($image, $x1, $y, $x2, $y, $this->borderColor);
			imagefttext($image, self::TICK_FONT_SIZE, 0, $x1 - 50, $y, $this->borderColor, self::REGULARFONT_FILE, $value);
		}
	}
	
	
	public function CreateImageSalesData(&$image)
	{
		foreach ($this->salesData as $sale)
		{
			$price = intval($sale['price']);
			$qnt = intval($sale['qnt']);
			$unitPrice = $price / $qnt;
			$soldTime = intval($sale['buyTimestamp']);
			$listTime = intval($sale['listTimestamp']);
			
			$color = $this->saleColor;
			$time = $soldTime;
			
			if ($listTime > 0) 
			{
				$color = $this->listColor;
				$time = $listTime;
			}
			
			$x = $this->ConvertGraphToPixelX($time);
			$y = $this->ConvertGraphToPixelY($unitPrice);
			
			imagefilledellipse($image, $x, $y, 2, 2, $color);
		}
	}
	
	
	public function GetNiceTickRange(&$min, &$max, $nearestMultiple = 0, $numTicks = 5)
	{
		$range = $max - $min;
		$tempStep = $range / $numTicks;
		
		$mag = floor(log10($tempStep));
		$magPow = pow(10, $mag);
		
		$magMsd = intval($tempStep/$magPow + 0.5);
		
		if ($magMsd > 5.0)
			$magMsd = 10.0;
		else if ($magMsd > 2.0)
			$magMsd = 5.0;
		else if ($magMsd > 1.0)
			$magMsd = 2.0;
		
		$tickRange =  $magMsd * $magPow;
		
		if ($nearestMultiple > 0)
		{
			$remainder = $tickRange % $nearestMultiple;
			if ($remainder != 0) $tickRange = $tickRange + $nearestMultiple - $remainder;
		}
		
		return $tickRange;
	}

	
	public function CreateImage()
	{
		$image = imagecreatetruecolor($this->outputWidth, $this->outputHeight);
		$this->image = $image;
		if ($image == null) return false;
		
		imageantialias($image, true);
		imagealphablending($image, true);
		imagesavealpha($image, true);
				
		$this->invis = imagecolorallocatealpha($image, 0, 0, 0, 0);
		$this->black = imagecolorallocate($image, 0, 0, 0);
		$this->white = imagecolorallocate($image, 255, 255, 255);
		$this->textColor = imagecolorallocate($image, 0xC5, 0xC2, 0x9E);
		$this->darkGray = imagecolorallocate($image, 0x55, 0x55, 0x55);
		
		$this->backgroundColor = imagecolorallocate($image, 0, 0, 0);
		$this->borderColor = imagecolorallocate($image, 0xf0, 0xf0, 0xf0);
		$this->saleColor = imagecolorallocate($image, 0x00, 0xff, 0x00);
		$this->listColor = imagecolorallocate($image, 0xff, 0x33, 0x33);
		
		imagefilledrectangle($image, 0, 0, $this->outputWidth, $this->outputHeight, $this->backgroundColor);
		
		$this->CreateImageBorders($image);
		$this->CreateImageTicks($image);
		$this->CreateImageSalesData($image);
		
		return true;
	}
	
	
	public function OutputImage()
	{
		$this->OutputHtmlHeader();
		
		$this->LoadSalesData();
		$this->ComputeBasicStats();
		
		$this->CreateImage();
		
		imagepng($this->image);
	}
};


$image = new EsoGetSalesImage();
$image->OutputImage();

