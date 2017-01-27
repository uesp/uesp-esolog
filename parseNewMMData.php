<?php

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require_once("parseSalesData.php");


class CEsoParseNewMMData 
{
	const ESO_MMDATA_PATH = "/home/uesp/esolog/mm/";
	const ESO_MMINDEX_FILENAME = "mmparse.index";
	
	public $salesData = null;

	
	public function __construct ()
	{
		$this->salesData = new EsoSalesDataParser();
	}
	
	
	public function ReadIndexFile($path)
	{
		$index = file_get_contents($path . self::ESO_MMINDEX_FILENAME);
	
		if ($index === False)
		{
			$this->writeIndexFile($path, 0);
			return 0;
		}
	
		$value = (int) $index;
		if ($value < 0) $value = 0;
	
		return $value;
	}
	
	
	public function WriteIndexFile($path, $value)
	{
		$filename = $path . self::ESO_MMINDEX_FILENAME;
		
		if (file_put_contents($filename, (string)$value) === FALSE)
		{
			error_log("Failed to write the log index file: $filename");
			return False;
		}
	
		return True;
	}
	
	
	public function ParseMMFiles($inputPath)
	{
		print("Parsing all MM data in '$inputPath'...\n");
		
		$dir = new DirectoryIterator($inputPath);
		
		foreach ($dir as $fileinfo)
		{
			if ($fileinfo->isFile())
			{
				$filename = $inputPath . $fileinfo->getFilename();
				$this->salesData->LoadMMFile($filename);
			}
		}
		
		$returnValue = true;
		
		$returnValue &= $this->salesData->ParseAllMMData();
		
		$returnValue &= $this->salesData->SaveUpdatedGuilds();
		$returnValue &= $this->salesData->SaveUpdatedItems();
		
		$this->salesData->ClearMMData();
		
		return $returnValue;
	}
	
	
	public function ParseServer($server)
	{
		$inputPath = self::ESO_MMDATA_PATH . strtolower($server) . "/";
		$parseIndex = $this->ReadIndexFile($inputPath);
		$maxDirIndex = $parseIndex;
		
		print("Parsing server $server directories greater than $maxDirIndex...\n");
		
		$dir = new DirectoryIterator($inputPath);
		
		foreach ($dir as $fileinfo) 
		{
			if (!$fileinfo->isDot() && $fileinfo->isDir()) 
			{
				$dirIndex = intval($fileinfo->getFilename());
				
				if ($dirIndex > $parseIndex)
				{
					$this->ParseMMFiles($inputPath . $fileinfo->getFilename() . "/");
					if ($dirIndex > $maxDirIndex) $maxDirIndex = $dirIndex;
				}
			}
		}
		
		$this->WriteIndexFile($inputPath, $maxDirIndex);
		return true;
	}
	
	
	public function ParseNewData()
	{
		$this->ParseServer("NA");
		$this->ParseServer("EU");
		$this->ParseServer("PTS");
		$this->ParseServer("Other");
		
		return true;
	}
};


$parser = new CEsoParseNewMMData();
$parser->ParseNewData();