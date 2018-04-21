<?php

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require_once("/home/uesp/secrets/esosalesdata.secrets");


class CEsoSalesMakePriceList
{
	public $VERSION = "1";
	public $OUTPUT_BASEPATH = "prices";
	public $OUTPUT_LUAFILENAME = "uespSalesPrices.lua";
	public $OUTPUT_PHPFILENAME = "uespSalesPrices.php";
	public $OUTPUT_PRICELISTINDEX = "priceIndex.html";
	
	public $db = null;
	
	public $indexData = array();
	public $updateTime = 0;
	public $outputPath = "";
	
	public $MAXITEMSPERFUNCTION = 10000;
	
	
	public function __construct()
	{
		global $argc, $argv;
		
		$this->updateTime = time();
		$this->InitDatabase();
		
		if ($argc > 1)
		{
			$this->outputPath = $argv[1];
		}
	}	
	
	
	public function InitDatabase()
	{
		global $uespEsoSalesDataReadDBHost, $uespEsoSalesDataReadUser, $uespEsoSalesDataReadPW, $uespEsoSalesDataDatabase;
		
		$this->db = new mysqli($uespEsoSalesDataReadDBHost, $uespEsoSalesDataReadUser, $uespEsoSalesDataReadPW, $uespEsoSalesDataDatabase);
		if ($this->db->connect_error) die("Could not connect to mysql database!");
	}
	
	
	public function LoadItems($server)
	{
		$query = "SELECT * FROM items WHERE server='{$server}';";
		$result = $this->db->query($query);
		if ($result === false) die("Failed to load items: $query");
		
		print("Loaded {$result->num_rows} items for server $server...\n");
		$itemData = array();
		$totalSales = 0;
		$totalPurchases = 0;
				
		while (($row = $result->fetch_assoc()))
		{
			$itemId = $row['itemId'];
			$level = $row['level'];
			$quality = $row['quality'];
			$trait = $row['trait'];
			$potionData = $row['potionData'];
			$extraData = $row['extraData'];
			$goodPrice = $row['goodPrice'];
			$goodSoldPrice = $row['goodSoldPrice'];
			$goodListPrice = $row['goodListPrice'];
			
			if ($extraData != "") $potionData = $extraData;
		
			if (!isset($itemData[$itemId])) $itemData[$itemId] = array();
			if (!isset($itemData[$itemId][$level])) $itemData[$itemId][$level] = array();
			if (!isset($itemData[$itemId][$level][$quality])) $itemData[$itemId][$level][$quality] = array();
			if (!isset($itemData[$itemId][$level][$quality][$trait])) $itemData[$itemId][$level][$quality][$trait] = array();
			if (!isset($itemData[$itemId][$level][$quality][$trait][$potionData])) $itemData[$itemId][$level][$quality][$trait][$potionData] = array();
		
			$saleData = &$itemData[$itemId][$level][$quality][$trait][$potionData];
		
			$count = intval($row['countPurchases']) + intval($row['countSales']);
			$itemCount = intval($row['countItemPurchases']) + intval($row['countItemSales']);
			if ($itemCount == 0) continue;
			
			$sumPrice = floatval($row['sumSales']) + floatval($row['sumPurchases']);
			$avgPrice = $sumPrice / $itemCount;
			if ($goodPrice > 0) $avgPrice = $goodPrice;
		
			if ($avgPrice >= 1000)
				$avgPrice = round($avgPrice, 0);
			else if ($avgPrice >= 100)
				$avgPrice = round($avgPrice, 1);
			else
				$avgPrice = round($avgPrice, 2);
		
			$avgPurchasePrice = 0;
			$avgSalePrice = 0;

			if ($row['countPurchases'] > 0)$avgPurchasePrice = floatval($row['sumPurchases']) / intval($row['countItemPurchases']);
			if ($row['countSales'] > 0) $avgSalePrice = floatval($row['sumSales']) / intval($row['countItemSales']);
			if ($goodSoldPrice > 0) $avgPurchasePrice = $goodSoldPrice;
			if ($goodListPrice > 0) $avgSalePrice = $goodListPrice;

			if ($avgPurchasePrice >= 1000)
				$avgPurchasePrice = round($avgPurchasePrice, 0);
			else if ($avgPurchasePrice >= 100)
				$avgPurchasePrice = round($avgPurchasePrice, 1);
			else
				$avgPurchasePrice = round($avgPurchasePrice, 2);

			if ($avgSalePrice >= 1000)
				$avgSalePrice = round($avgSalePrice, 0);
			else if ($avgPrice >= 100)
				$avgSalePrice = round($avgSalePrice, 1);
			else
				$avgSalePrice = round($avgSalePrice, 2);

			$saleData[0] = $avgPrice;
			$saleData[1] = $avgPurchasePrice;
			$saleData[2] = $avgSalePrice;
			$saleData[3] = $row['countPurchases'];
			$saleData[4] = $row['countSales'];
			$saleData[5] = $row['countItemPurchases'];
			$saleData[6] = $row['countItemSales'];
			if ($extraData != "") $saleData[7] = $row['potionData'];
			
			$totalSales += $row['countSales'];
			$totalPurchases += $row['countPurchases'];
		}
		
		$this->indexData[$server]['numItems'] = count($itemData);
		$this->indexData[$server]['numListed'] = countSales;
		$this->indexData[$server]['numSold'] = countPurchases;
		
		return $itemData;
	}
	
	
	public function MakeLuaData($itemData, $server)
	{
		$output = "";
		$dataCount = 0;
		$itemOutputCount = 0;
		$updateDate = date("Y-m-d H:i:s");
		
		$output .= "uespLog = uespLog or {}\n";
		$output .= "function uespLog.InitSalesPrices()\n";
		$output .= "uespLog.SalesPricesServer = '$server'\n";
		$output .= "uespLog.SalesPricesVersion = {$this->VERSION}\n";
		$output .= "uespLog.SalesPricesLastUpdate = '$updateDate'\n";
		$output .= "uespLog.SalesPrices = {}\n";
		$output .= "local SetPriceFunc = function(a)\n";		
		
		foreach ($itemData as $itemId => $levelData)
		{
			$itemOutputCount++;
				
			if ($itemOutputCount >= $this->MAXITEMSPERFUNCTION)
			{
				$itemOutputCount = 0;
				$output .= "end\n";
				$output .= "SetPriceFunc(uespLog.SalesPrices)\n";
				$output .= "SetPriceFunc = function(a)\n";
			}
			
			$output .= "a[$itemId]={\n";
			
			foreach ($levelData as $level => $qualityData)
			{
				$output .= "[$level]={";
		
				foreach ($qualityData as $quality => $traitData)
				{
					$output .= "[$quality]={";
						
					foreach ($traitData as $trait => $potionData)
					{
						$output .= "[$trait]={";
						$outputCount = 0;
		
						foreach ($potionData as $potion => $salesData)
						{
							if (is_string($potion))
								$output .= "[\"$potion\"]=";
							else 
								$output .= "[$potion]=";
								
							$output .= "{{$salesData[0]},{$salesData[1]},{$salesData[2]},{$salesData[3]},{$salesData[4]},{$salesData[5]},{$salesData[6]}},";
							++$dataCount;
							++$outputCount;
							
							if ($outputCount > 40)
							{
								$outputCount = 0;
								$output .= "\n";
							}
						}
		
						$output .= "},\n";
					}
						
					$output .= "},";
				}
		
				$output .= "},";
			}
		
			$output .= "}\n";
		}
		
		$output .= "end\n";
		$output .= "SetPriceFunc(uespLog.SalesPrices)\n";
		
		$output .= "uespLog.SalesPricesDataCount = $dataCount\n";
		$output .= "end\n";
		
		$this->indexData[$server]['dataCount'] = $dataCount;
		
		return $output;
	}
	
	
	public function MakePhpData($itemData, $server)
	{
		$output = array();
		$dataCount = 0;
		$updateDate = date("Y-m-d H:i:s");
		
		$output['SalesPricesServer'] = $server;
		$output['SalesPricesVersion'] = $this->VERSION;
		$output['SalesPricesLastUpdate'] = $updateDate;
				
		foreach ($itemData as $itemId => $levelData)
		{
			$output[$itemId] = array();
		
			foreach ($levelData as $level => $qualityData)
			{
				$output[$itemId][$level] = array();
		
				foreach ($qualityData as $quality => $traitData)
				{
					$output[$itemId][$level][$quality] = array();
		
					foreach ($traitData as $trait => $potionData)
					{
						$output[$itemId][$level][$quality][$trait] = array();
		
						foreach ($potionData as $potion => $salesData)
						{
							$output[$itemId][$level][$quality][$trait][$potion] = array($salesData[0], $salesData[1], $salesData[2], $salesData[3], $salesData[4], $salesData[5], $salesData[6], $salesData[7]);
							if ($salesData[7] > 0) $output[$itemId][$level][$quality][$trait][$potion][7] = $salesData[7];  
							++$dataCount;
						}
					}
				}
			}
		}
		
		$output['SalesPricesDataCount'] = $dataCount;
		
		return '<?php' . "\n" . '$uespSalesPrices = ' . var_export($output, true) . ";\n";
	}
	
	
	public function OutputPriceList($server, $luaData, $baseFilename)
	{
		$outputFile = $this->outputPath . $this->OUTPUT_BASEPATH . "$server/$baseFilename";
		$outputZip  = $this->outputPath . $this->OUTPUT_BASEPATH . "$server/$baseFilename.zip";
		$outputGzip = $this->outputPath . $this->OUTPUT_BASEPATH . "$server/$baseFilename.gz";
		
		$tmpFile = $outputFile . ".tmp";
		print("Saving $server price list to '$outputFile'...\n");
		
		if (file_put_contents($tmpFile, $luaData) === false)
		{
			print("Error: Failed to write file data!\n");
			return false;
		}
		
		rename($tmpFile, $outputFile);
		
		$this->indexData[$server]['fileSize'] = strlen($luaData);
		$this->indexData[$server]['updateDate'] = date('Y-m-d H:i:s', $this->updateTime);
		
		$zip = new ZipArchive();
		
		if ($zip->open($outputZip, ZipArchive::OVERWRITE)!== TRUE) return $this->ReportError("Cannot create ZIP file $outputZip!");
    	$zip->addFile($outputFile, $baseFilename);
		$zip->close();
		
		$gz = gzopen($outputGzip, 'w9');
		gzwrite($gz, $luaData);
		gzclose($gz);
		
		return true;
	}
	
	
	public function CreatePriceListIndex()
	{
		$output = "";
		
		foreach ($this->indexData as $server => $indexData)
		{
			$size = round($indexData['fileSize'] / 1000000, 1);
			$date = $indexData['updateDate'];
			$priceCount = $indexData['dataCount'];
			
			$output .= "<li>";
			$output .= "<a href='{$this->OUTPUT_BASEPATH}$server/{$this->OUTPUT_LUAFILENAME}'><b>$server {$this->OUTPUT_LUAFILENAME}</b></a> -- ";
			$output .= "$priceCount Prices, Last Updated $date EST, $size MB";
			$output .= "</li>\n";
		}
		
		if (file_put_contents($this->outputPath . $this->OUTPUT_PRICELISTINDEX, $output) === false)
		{
			print("Error: Failed to write price index data!\n");
			return false;
		}
		
		return true;
	}
	
	
	public function CreatePriceList($server)
	{
		$this->indexData[$server] = array();
		
		$itemData = $this->LoadItems($server);
		
		$luaData = $this->MakeLuaData($itemData, $server);
		$phpData = $this->MakePhpData($itemData, $server);
		
		$this->OutputPriceList($server, $luaData, $this->OUTPUT_LUAFILENAME);
		$this->OutputPriceList($server, $phpData, $this->OUTPUT_PHPFILENAME);
		
		return true;
	}
	
		
	public function MakePrices()
	{
		$this->CreatePriceList("NA");
		$this->CreatePriceList("EU");
		$this->CreatePriceList("PTS");
		$this->CreatePriceList("Other");
		
		$this->CreatePriceListIndex();
	}
	
};


$priceList = new CEsoSalesMakePriceList();
$priceList->MakePrices();











