<?php

// Database users, passwords and other secrets
require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");


class CEsoItemLinkImage
{
	const ESOIL_ICON_PATH = "/home/uesp/www/eso/gameicons/";
	const ESOIL_ICON_URL = "http://content3.uesp.net/eso/gameicons/";
	const ESOIL_ICON_UNKNOWN = "unknown.png";
	const ESOIL_IMAGE_WIDTH = 400;
	const ESOIL_IMAGE_MAXHEIGHT = 600;
	const ESOIL_REGULARFONT_FILE = "./resources/EsoFontRegular.ttf";
	const ESOIL_BOLDFONT_FILE = "./resources/EsoFontBold.ttf";
	
	public $inputParams = array();
	public $itemId = 0;
	public $itemLink = "";
	public $itemLevel = 1;		// 1-64
	public $itemQuality = 1;	// 1-5
	public $itemIntLevel = -1;	// 1-50
	public $itemIntType = -1;	// 1-400
	public $itemRecord = array();
	public $db = null;
	
	public $background;
	public $black;
	public $white;
	public $textColor;
	public $nameColor;
	public $qualityColors = array();
	
	public $bigFontSize = 18;
	public $medFontSize = 12;
	public $smallFontSize = 11;
	public $tinyFontSize = 9;
	
	public $topMargin = 24;
	public $borderMargin = 5;
	public $bigFontLineHeight = 22;
	public $medFontLineHeight = 18;
	public $smallFontLineHeight = 15;
	public $tinyFontLineHeight = 10;
	public $dataBlockMargin = 32;
	public $blockMargin = 14;
	public $borderWidth = 7;
	
	
	public function __construct ()
	{
		$this->SetInputParams();
		$this->ParseInputParams();
		$this->InitDatabase();
	}
	
	
	public function ReportError($errorMsg)
	{
		print($errorMsg);
		error_log($errorMsg);
		return false;
	}
	
	
	public function ParseItemLink($itemLink)
	{
		$matches = array();
		$result = preg_match('/\|H(?P<color>[A-Za-z0-9]*)\:item\:(?P<itemId>[0-9]*)\:(?P<subtype>[0-9]*)\:(?P<level>[0-9]*)\:(?P<enchantId>[0-9]*)\:(?P<enchantSubtype>[0-9]*)\:(?P<enchantLevel>[0-9]*)\:(.*?)\:(?P<style>[0-9]*)\:(?P<crafted>[0-9]*)\:(?P<bound>[0-9]*)\:(?P<charges>[0-9]*)\:(?P<potionData>[0-9]*)\|h\[?(?P<name>[a-zA-Z0-9 %_\(\)\'\-]*)(?P<nameCode>.*?)\]?\|h/', $itemLink, $matches);
		if (!$result) return false;
		
		$this->itemId = $matches['itemId'];
		$this->itemIntLevel = $matches['level'];
		$this->itemIntType = $matches['subtype'];
		
		return true;
	}
	
	
	private function ParseInputParams ()
	{
		if (array_key_exists('itemlink', $this->inputParams))
		{
			$this->itemLink = urldecode($this->inputParams['itemlink']);
			$this->ParseItemLink($this->itemLink);
		}
		
		if (array_key_exists('itemid', $this->inputParams)) $this->itemId = (int) $this->inputParams['itemid'];
		
		if (array_key_exists('level', $this->inputParams))
		{
			$level = strtolower($this->inputParams['level']);
				
			if ($level[0] == 'v')
				$this->itemLevel = (int) ltrim($level, 'v') + 49;
			else
				$this->itemLevel = (int) $level;
		}
		
		if (array_key_exists('quality', $this->inputParams)) $this->itemQuality = (int) $this->inputParams['quality'];
		if (array_key_exists('intlevel', $this->inputParams)) $this->itemIntLevel = (int) $this->inputParams['intlevel'];
		if (array_key_exists('inttype', $this->inputParams)) $this->itemIntType = (int) $this->inputParams['inttype'];
		
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
	
	
	private function InitDatabase()
	{
		global $uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase;
		
		$this->db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
		if ($this->db->connect_error) return $this->ReportError("ERROR: Could not connect to mysql database!");
		
		return true;
	}
	
	
	private function LoadItemRecord()
	{
		if ($this->itemId <= 0) return $this->ReportError("ERROR: Missing or invalid item ID specified (1-65000)!");
		$query = "";
		
		if ($this->itemIntLevel >= 1)
		{
			if ($this->itemIntType < 0) return $this->ReportError("ERROR: Missing or invalid item internal type specified (1-400)!");
			$query = "SELECT * FROM minedItem WHERE itemId={$this->itemId} AND internalLevel={$this->itemIntLevel} AND internalSubtype={$this->itemIntType} LIMIT 1;";
			$this->itemErrorDesc = "id={$this->itemId}, Internal Level={$this->itemIntLevel}, Internal Type={$this->itemIntType}";
		}
		else
		{
			if ($this->itemLevel <= 0) return $this->ReportError("ERROR: Missing or invalid item Level specified (1-64)!");
			if ($this->itemQuality <= 0) return $this->ReportError("ERROR: Missing or invalid item Quality specified (1-5)!");
			$query = "SELECT * FROM minedItem WHERE itemId={$this->itemId} AND level={$this->itemLevel} AND quality={$this->itemQuality} LIMIT 1;";
			$this->itemErrorDesc = "id={$this->itemId}, Level={$this->itemLevel}, Quality={$this->itemQuality}";
		}
		
		$result = $this->db->query($query);
		if (!$result) return $this->ReportError("ERROR: Database query error! " . $this->db->error);
		if ($result->num_rows === 0) return $this->ReportError("ERROR: No item found matching {$this->itemErrorDesc}!");
		
		$result->data_seek(0);
		$row = $result->fetch_assoc();
		if (!$row) $this->ReportError("ERROR: No item found matching {$this->itemErrorDesc}!");
		
		if ($this->itemLevel <= 0) $this->itemLevel = (int) $row['level'];
		if ($this->itemQuality <= 0) $this->itemQuality = (int) $row['quality'];
		
		// TODO: Temporary fix for setMaxEquipCount
		if (array_key_exists('setMaxEquipCount', $row) && $row['setMaxEquipCount'] == -1)
		{
			$highestSetDesc = "";
			$row['setMaxEquipCount'] = 0;
				
			if (array_key_exists('setBonusDesc1', $row) && $row['setBonusDesc1'] != "") $highestSetDesc = $row['setBonusDesc1'];
			if (array_key_exists('setBonusDesc2', $row) && $row['setBonusDesc2'] != "") $highestSetDesc = $row['setBonusDesc2'];
			if (array_key_exists('setBonusDesc3', $row) && $row['setBonusDesc3'] != "") $highestSetDesc = $row['setBonusDesc3'];
			if (array_key_exists('setBonusDesc4', $row) && $row['setBonusDesc4'] != "") $highestSetDesc = $row['setBonusDesc4'];
			if (array_key_exists('setBonusDesc5', $row) && $row['setBonusDesc5'] != "") $highestSetDesc = $row['setBonusDesc5'];
		
			if ($highestSetDesc != "")
			{
				$row['setMaxEquipCount'] = 1;
				$matches = array();
				$matchResult = preg_match("/\(([0-9]+) items\)/", $highestSetDesc, $matches);
				if ($matchResult) $row['setMaxEquipCount'] = (int) $matches[1];
			}
		}
		
		return $row;
	}
	
	
	private function OutputHtmlHeader()
	{
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		header("content-type: image/png");
	}
	
	
	public function PrintTextAA($image, $fontSize, $x, $y, $color, $font, $text)
	{
		$colorAA = imagecolorallocatealpha($image, $color & 0xff, ($color >> 8) & 0xff, ($color >> 16) & 0xff, 110);
		$delta = 1;
		
		imagettftext($image, $fontSize, 0, $x+$delta, $y+$delta, $colorAA, $font, $text);
		imagettftext($image, $fontSize, 0, $x-$delta, $y-$delta, $colorAA, $font, $text);
		imagettftext($image, $fontSize, 0, $x+$delta, $y-$delta, $colorAA, $font, $text);
		imagettftext($image, $fontSize, 0, $x-$delta, $y+$delta, $colorAA, $font, $text);
		
		imagettftext($image, $fontSize, 0, $x, $y, $color, $font, $text);
	}
	
	
	public function PrintCenterText($image, $fontSize, $y, $color, $font, $text)
	{
		$box = imagettfbbox($fontSize, 0, $font, $text);
		$x = (self::ESOIL_IMAGE_WIDTH - $box[4] + $box[0]) / 2;
		imagettftext($image, $fontSize, 0, $x, $y, $color, $font, $text);
		//$this->PrintTextAA($image, $fontSize, $x, $y, $color, $font, $text);
	}
	
	
	public function PrintRightText($image, $fontSize, $x, $y, $color, $font, $text)
	{
		$box = imagettfbbox($fontSize, 0, $font, $text);
		$newX = $x - ($box[4] - $box[0]);
		imagettftext($image, $fontSize, 0, $newX, $y, $color, $font, $text);
		//$this->PrintTextAA($image, $fontSize, $newX, $y, $color, $font, $text);
	}
	
	
	public function OutputCenterImage($image, $filename, $y)
	{
		$hrImage = imagecreatefrompng($filename);
		if ($hrImage == null) return false;
		imagealphablending($hrImage, true);
		imagesavealpha($hrImage, true);
		
		$imageWidth = imagesx($hrImage);
		$imageHeight = imagesy($hrImage);
		$x = (self::ESOIL_IMAGE_WIDTH - $imageWidth) / 2;
		
		imagecopy($image, $hrImage, $x, $y, 0, 0, $imageWidth, $imageHeight);
		
		imagedestroy($hrImage);
		return true;
	}
	
	
	private function MakeItemBindTypeText()
	{
		$bindType = $this->itemRecord['bindType'];
	
		if ($bindType <= 0) return "";
		return GetEsoItemBindTypeText($bindType);
	}
	
	
	private function MakeItemTypeText()
	{
		switch ($this->itemRecord['type'])
		{
			case 1:
			case 2:
				return GetEsoItemEquipTypeText($this->itemRecord['equipType']);
			case 4:
				return "Food";
			default:
				return GetEsoItemTypeText($this->itemRecord['type']);
		}
	}
	
	
	private function MakeItemSubTypeText()
	{
		$type = $this->itemRecord['type'];
		if ($type <= 0) return "";
	
		if ($type == 2) //armor
		{
			return "(" . GetEsoItemArmorTypeText($this->itemRecord['armorType']) . ")";
		}
		elseif ($type == 1) //weapon
		{
			return "(" . GetEsoItemWeaponTypeText($this->itemRecord['weaponType']) . ")";
		}
	
		return "";
	}
	
	
	private function MakeItemIconImageFilename()
	{
		$icon = $this->itemRecord['icon'];
		if ($icon == null || $icon == "") $icon = self::ESOIL_ICON_UNKNOWN;
	
		$icon = preg_replace('/dds$/', 'png', $icon);
		$icon = preg_replace('/^\//', '', $icon);
	
		$iconLink = self::ESOIL_ICON_PATH . $icon;
		return $iconLink;
	}
	
	
	public function OutputItemLevelBlock($image, $y)
	{
		$level = $this->itemRecord['level'];
		$levelImageWidth = 0;
		$levelImage = null;
		
		if ($level >= 50)
		{
			$imageFile = "./resources/eso_item_veteranicon.png";
			$label = "RANK ";
			$levelText = $level - 49;
			
			$levelImage = imagecreatefrompng($imageFile);
			
			if ($levelImage != null)
			{
				imageantialias($levelImage, true);
				imagealphablending($levelImage, true);
				imagesavealpha($levelImage, true);
				$levelImageWidth = imagesx($levelImage) + 2;
			}
		}
		else
		{
			$imageFile = "";
			$label = "LEVEL ";
			$levelText = $level;
		}
		
		$box1 = imagettfbbox($this->medFontSize, 0, self::ESOIL_BOLDFONT_FILE, $label);
		$box2 = imagettfbbox($this->bigFontSize, 0, self::ESOIL_BOLDFONT_FILE, $levelText);
		$totalWidth = $levelImageWidth + $box1[4] - $box1[0] + $box2[4] - $box2[0];
		$x = (self::ESOIL_IMAGE_WIDTH - $totalWidth ) / 2;
		
		if ($levelImage)
		{
			imagecopy($image, $levelImage, $x, $y, 0, 0, imagesx($levelImage), imagesy($levelImage));
			imagedestroy($levelImage);
			$x += $levelImageWidth;
		}
		
		imagettftext($image, $this->medFontSize, 0, $x, $y + $box2[1] - $box2[5] + 4, $this->textColor, self::ESOIL_BOLDFONT_FILE, $label);
		$x += $box1[4] - $box1[0];
		imagettftext($image, $this->bigFontSize, 0, $x, $y + $box2[1] - $box2[5] + 4, $this->white, self::ESOIL_BOLDFONT_FILE, $levelText);
	}
	
	
	public function OutputItemValueBlock($image, $y)
	{
		$value = $this->itemRecord['value'];
		if ($value <= 0) return;
		
		$label = "VALUE ";
		$valueText = $value;
		
		$box1 = imagettfbbox($this->medFontSize, 0, self::ESOIL_BOLDFONT_FILE, $label);
		$box2 = imagettfbbox($this->bigFontSize, 0, self::ESOIL_BOLDFONT_FILE, $valueText);
		$totalWidth = $box1[4] - $box1[0] + $box2[4] - $box2[0];
		$x = self::ESOIL_IMAGE_WIDTH - $totalWidth - $this->dataBlockMargin;
		
		imagettftext($image, $this->medFontSize, 0, $x, $y + $box2[1] - $box2[5] + 4, $this->textColor, self::ESOIL_BOLDFONT_FILE, $label);
		$x += $box1[4] - $box1[0];
		imagettftext($image, $this->bigFontSize, 0, $x, $y + $box2[1] - $box2[5] + 4, $this->white, self::ESOIL_BOLDFONT_FILE, $valueText);
	}
	
	
	public function OutputItemLeftBlock($image, $y)
	{
		
		switch ($this->itemRecord['type'])
		{
			case 1:
				$label = "DAMAGE ";
				$valueText = $this->itemRecord['weaponPower'];
				break;
			case 2:
				$label = "ARMOR ";
				$valueText = $this->itemRecord['armorRating'];
				break;
			default:
				return;
		}
		
		$box1 = imagettfbbox($this->medFontSize, 0, self::ESOIL_BOLDFONT_FILE, $label);
		$box2 = imagettfbbox($this->bigFontSize, 0, self::ESOIL_BOLDFONT_FILE, $valueText);
		$totalWidth = $box1[4] - $box1[0] + $box2[4] - $box2[0];
		$x = $this->dataBlockMargin;
		
		imagettftext($image, $this->medFontSize, 0, $x, $y + $box2[1] - $box2[5] + 4, $this->textColor, self::ESOIL_BOLDFONT_FILE, $label);
		$x += $box1[4] - $box1[0];
		imagettftext($image, $this->bigFontSize, 0, $x, $y + $box2[1] - $box2[5] + 4, $this->white, self::ESOIL_BOLDFONT_FILE, $valueText);
	}
	
	
	private function OutputItemBar($image, $y)
	{
		$type = $this->itemRecord['type'];
		if ($type <= 0) return 0;
		$charges = $this->itemRecord['maxCharges'];
		
		if ($type == 1 && $charges > 0) 
			$itemBarFile = "resources/eso_item_chargebar.png";
		elseif ($type == 1 || $type == 2) 
			$itemBarFile = "resources/eso_item_conditionbar.png";
		else
			return 0;
		
		if ($this->OutputCenterImage($image, $itemBarFile, $y)) return 7 + $this->blockMargin;
		return 0;
	}
	
	
	private function OutputItemEnchantBlock($image, $y)
	{
		$enchantName = strtoupper($this->itemRecord['enchantName']);
		if ($enchantName == "") return 0;
		$enchantDesc = $this->itemRecord['enchantDesc'];
		
		$y += $this->smallFontLineHeight;
		$this->PrintCenterText($image, $this->smallFontSize, $y, $this->white, self::ESOIL_BOLDFONT_FILE, $enchantName);
		$y += $this->medFontLineHeight;
		$this->PrintCenterText($image, $this->medFontSize, $y, $this->textColor, self::ESOIL_BOLDFONT_FILE, $enchantDesc);
		
		return $this->smallFontLineHeight + $this->medFontLineHeight + $this->blockMargin;
	}
	
	
	private function OutputItemAbilityBlock($image, $y)
	{
		$ability = strtoupper($this->itemRecord['abilityName']);
		if ($ability == "") return 0;
		
		$abilityDesc = $this->itemRecord['abilityDesc'];
		$cooldown = ((int) $this->itemRecord['abilityCooldown']) / 1000;
		$abilityDesc .= " (" . $cooldown . " second cooldown)";
		
		$y += $this->smallFontLineHeight;
		$this->PrintCenterText($image, $this->smallFontSize, $y, $this->white, self::ESOIL_BOLDFONT_FILE, $ability);
		$y += $this->medFontLineHeight;
		$this->PrintCenterText($image, $this->medFontSize, $y, $this->textColor, self::ESOIL_BOLDFONT_FILE, $abilityDesc);
	
		return $this->smallFontLineHeight + $this->medFontLineHeight + $this->blockMargin;
	}
	
	
	private function OutputItemTraitBlock($image, $y)
	{
		$trait = $this->itemRecord['trait'];
		if ($trait <= 0) return 0;
		
		$traitDesc = $this->itemRecord['traitDesc'];
		$traitName = strtoupper(GetEsoItemTraitText($trait));
		
		$y += $this->smallFontLineHeight;
		$this->PrintCenterText($image, $this->smallFontSize, $y, $this->white, self::ESOIL_BOLDFONT_FILE, $traitName);
		$y += $this->medFontLineHeight;
		$this->PrintCenterText($image, $this->medFontSize, $y, $this->textColor, self::ESOIL_BOLDFONT_FILE, $traitDesc);
		
		return $this->smallFontLineHeight + $this->medFontLineHeight + $this->blockMargin;
	}
	
	
	private function OutputItemTraitAbilityBlock($image, $y)
	{
		$abilityDesc = strtoupper($this->itemRecord['traitAbilityDesc']);
		$cooldown = ((int) $this->itemRecord['traitCooldown']) / 1000;
		if ($abilityDesc == "") return 0;
		$abilityDesc .= " (" . $cooldown . " second cooldown)";
		
		$y += $this->medFontLineHeight;
		$this->PrintCenterText($image, $this->medFontSize, $y, $this->textColor, self::ESOIL_BOLDFONT_FILE, $abilityDesc);
		
		return $this->medFontLineHeight + $this->blockMargin;
	}
	
	
	private function OutputItemSetBlock($image, $y)
	{
		$setName = strtoupper($this->itemRecord['setName']);
		if ($setName == "") return "";
		
		$setMaxEquipCount = $this->itemRecord['setMaxEquipCount'];
		$setBonusCount = (int) $this->itemRecord['setBonusCount'];
		$setLabel = "PART OF THE $setName SET ($setMaxEquipCount/$setMaxEquipCount ITEMS)";
		
		$deltaY = $this->smallFontLineHeight; 
		$this->PrintCenterText($image, $this->smallFontSize, $y + $deltaY, $this->white, self::ESOIL_BOLDFONT_FILE, $setLabel);
		
		for ($i = 1; $i <= $setBonusCount && $i <= 5; $i += 1)
		{
			$setCount = $this->itemRecord['setBonusCount' . $i];
			$setDesc = $this->itemRecord['setBonusDesc' . $i];
			$deltaY += $this->medFontLineHeight;
			$this->PrintCenterText($image, $this->medFontSize, $y + $deltaY, $this->textColor, self::ESOIL_BOLDFONT_FILE, $setDesc);
		}
		
		return $deltaY + $this->blockMargin;
	}
	
	
	private function OutputItemDescription($image, $y)
	{
		$desc = $this->itemRecord['description'];
		if ($desc == "") return 0;
		
		$x = $this->borderMargin + 1;
		imagettftext($image, $this->tinyFontSize, 0, $x, $y, $this->textColor, self::ESOIL_BOLDFONT_FILE, $desc);
		return $this->tinyFontLineHeight + $this->blockMargin;
	}
	
	
	public function OutputBorder ($image)
	{
		$borderImage = imagecreatefrompng("resources/eso_item_border.png");
		if ($borderImage == null) return false;
		
		$topBorderImage    = imagecreatetruecolor(imagesx($borderImage) - $this->borderWidth*2, $this->borderWidth);
		$bottomBorderImage = imagecreatetruecolor(imagesx($borderImage) - $this->borderWidth*2, $this->borderWidth);
		$leftBorderImage   = imagecreatetruecolor($this->borderWidth, imagesy($borderImage) - $this->borderWidth*2);
		$rightBorderImage  = imagecreatetruecolor($this->borderWidth, imagesy($borderImage) - $this->borderWidth*2);
		$cornerImageNE = imagecreatetruecolor($this->borderWidth, $this->borderWidth);
		$cornerImageNW = imagecreatetruecolor($this->borderWidth, $this->borderWidth);
		$cornerImageSE = imagecreatetruecolor($this->borderWidth, $this->borderWidth);
		$cornerImageSW = imagecreatetruecolor($this->borderWidth, $this->borderWidth);
		
		imagecopy($topBorderImage, $borderImage, 0, 0, $this->borderWidth, 0, imagesx($borderImage) - $this->borderWidth*2, $this->borderWidth);
		imagecopy($bottomBorderImage, $borderImage, 0, 0, $this->borderWidth, imagesy($borderImage) - $this->borderWidth, imagesx($borderImage) - $this->borderWidth*2, $this->borderWidth);
		imagecopy($leftBorderImage, $borderImage, 0, 0, 0, $this->borderWidth, $this->borderWidth, imagesy($borderImage) - $this->borderWidth*2);
		imagecopy($rightBorderImage, $borderImage, 0, 0, imagesx($borderImage) - $this->borderWidth, $this->borderWidth, $this->borderWidth, imagesy($borderImage) - $this->borderWidth*2);
		
		imagecopy($cornerImageNE, $borderImage, 0, 0, imagesx($borderImage) - $this->borderWidth, 0, $this->borderWidth, $this->borderWidth);
		imagecopy($cornerImageNW, $borderImage, 0, 0, 0, 0, $this->borderWidth, $this->borderWidth);
		imagecopy($cornerImageSE, $borderImage, 0, 0, imagesx($borderImage) - $this->borderWidth, imagesy($borderImage) - $this->borderWidth, $this->borderWidth, $this->borderWidth);
		imagecopy($cornerImageSW, $borderImage, 0, 0, 0, imagesy($borderImage) - $this->borderWidth, $this->borderWidth, $this->borderWidth);
		
		imagecopyresized($image, $topBorderImage,    $this->borderWidth, $this->topMargin, 0, 0, imagesx($image) - $this->borderWidth*2, $this->borderWidth, imagesx($topBorderImage), $this->borderWidth);
		imagecopyresized($image, $bottomBorderImage, $this->borderWidth, imagesy($image) - $this->borderWidth, 0, 0, imagesx($image) - $this->borderWidth*2, $this->borderWidth, imagesx($bottomBorderImage), $this->borderWidth);
		imagecopyresized($image, $leftBorderImage,   0, $this->topMargin + $this->borderWidth, 0, 0, $this->borderWidth, imagesy($image) - $this->borderWidth*2 - $this->topMargin, $this->borderWidth, imagesy($leftBorderImage));
		imagecopyresized($image, $rightBorderImage,  imagesx($image) - $this->borderWidth, $this->borderWidth + $this->topMargin, 0, 0, $this->borderWidth, imagesy($image) - $this->borderWidth*2 - $this->topMargin, $this->borderWidth, imagesy($rightBorderImage));
		
		imagecopy($image, $cornerImageNW, 0, $this->topMargin, 0, 0, $this->borderWidth, $this->borderWidth);
		imagecopy($image, $cornerImageNE, imagesx($image) - $this->borderWidth, $this->topMargin, 0, 0, $this->borderWidth, $this->borderWidth);
		imagecopy($image, $cornerImageSW, 0, imagesy($image) - $this->borderWidth, 0, 0, $this->borderWidth, $this->borderWidth);
		imagecopy($image, $cornerImageSE, imagesx($image) - $this->borderWidth, imagesy($image) - $this->borderWidth, 0, 0, $this->borderWidth, $this->borderWidth);
		
		return true;
	}
	
	
	public function OutputImage()
	{
		$image = imagecreatetruecolor(self::ESOIL_IMAGE_WIDTH, self::ESOIL_IMAGE_MAXHEIGHT);
		if ($image == null) return false;
		
		imageantialias($image, true);
		imagealphablending($image, true);
		imagesavealpha($image, true);
		
		$itemData = $this->itemRecord;
		
		$this->qualityColors = array(
				imagecolorallocate($image, 0xff, 0xff, 0xff),
				imagecolorallocate($image, 0x2d, 0xc5, 0x0e),
				imagecolorallocate($image, 0x3a, 0x92, 0xff),
				imagecolorallocate($image, 0xa0, 0x2e, 0xf7),
				imagecolorallocate($image, 0xff, 0xff, 0x33),
		);
		
		$this->background = imagecolorallocatealpha($image, 0, 0, 0, 127);
		$this->black =  imagecolorallocate($image, 0, 0, 0);
		$this->white =  imagecolorallocate($image, 255, 255, 255);
		$this->textColor =  imagecolorallocate($image, 0xC5, 0xC2, 0x9E);
		
		imagefill($image, 0, 0, $this->background);
		imagefilledrectangle ($image, $this->borderWidth, $this->topMargin + $this->borderWidth, self::ESOIL_IMAGE_WIDTH - $this->borderWidth, self::ESOIL_IMAGE_MAXHEIGHT - $this->borderWidth, $this->black);
		
		$itemName = strtoupper($itemData['name']);
		$quality = $itemData['quality'];
		$this->nameColor = $this->qualityColors[$quality - 1];
		if ($this->nameColor == null) $this->nameColor = $white;
		
		$y = $this->topMargin + $this->borderMargin + $this->medFontLineHeight;
		imagettftext($image, $this->smallFontSize, 0, 10, $y, $this->textColor, self::ESOIL_BOLDFONT_FILE, $this->MakeItemTypeText());
		
		$y += $this->medFontLineHeight;
		$this->PrintRightText($image, $this->smallFontSize, 390, $y, $this->textColor, self::ESOIL_BOLDFONT_FILE, $this->MakeItemBindTypeText());
		imagettftext($image, $this->smallFontSize, 0, 10, $y, $this->textColor, self::ESOIL_BOLDFONT_FILE, $this->MakeItemSubTypeText());
		
		$y += $this->bigFontLineHeight + 16;
		$this->PrintCenterText($image, $this->bigFontSize, $y, $this->nameColor, self::ESOIL_BOLDFONT_FILE, $itemName);
		
		$y += 6;
		$this->OutputCenterImage($image, "./resources/eso_item_hr.png", $y);
		
		$y += 6;
		$this->OutputItemLeftBlock($image, $y);
		$this->OutputItemLevelBlock($image, $y);
		$this->OutputItemValueBlock($image, $y);
		
		$y += 40;
		$y += $this->OutputItemBar($image, $y);
		
		$y += $this->OutputItemEnchantBlock($image, $y);
		$y += $this->OutputItemAbilityBlock($image, $y);
		$y += $this->OutputItemTraitBlock($image, $y);
		$y += $this->OutputItemTraitAbilityBlock($image, $y);
		$y += $this->OutputItemSetBlock($image, $y);
		
		$y += $this->OutputItemDescription($image, $y);
		
		$imageHeight = $y + 1;
		
		$croppedImage = imagecreatetruecolor(self::ESOIL_IMAGE_WIDTH, $imageHeight);
		if ($image == null) return false;
		imageantialias($croppedImage, true);
		imagealphablending($croppedImage, true);
		imagesavealpha($croppedImage, true);
		imagefill($croppedImage, 0, 0, $this->background);
		$this->OutputBorder($croppedImage);
		imagecopy($croppedImage, $image, 0, 0, 0, 0, imagesx($image), $imageHeight - $this->borderWidth);
		$this->OutputCenterImage($croppedImage, $this->MakeItemIconImageFilename(), 1);
		
		imagepng($croppedImage);
		imagedestroy($croppedImage);
		imagedestroy($image);
	}
	
	
	public function MakeImage()
	{
		$this->OutputHtmlHeader();
		
		$this->itemRecord = $this->LoadItemRecord();
		if (!$this->itemRecord) return false;
		
		$this->OutputImage();
		return true;
	}
	
};

$g_EsoItemLinkImage = new CEsoItemLinkImage();
$g_EsoItemLinkImage->MakeImage();

?>
