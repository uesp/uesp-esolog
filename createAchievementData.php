<?php

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("/home/uesp/secrets/esolog.secrets");
require("esoCommon.php");

$startTime = microtime(true);
$CURRENT_CATEGORY = "";

function SortByIndex($a, $b)
{
	return $a['index'] - $b['index'];
}

function SortByCategoryName($a, $b)
{
	global $categoryData;
	
	$cat1 = $categoryData[$a];
	$cat2 = $categoryData[$b];
	
	if ($cat1 == null || $cat2 == null)	return 0;
	
	return $cat1['index'] - $cat2['index'];
}


function SortBySubCategoryName($a, $b)
{
	global $categoryData, $CURRENT_CATEGORY;
	
	$cat1 = $categoryData["$CURRENT_CATEGORY::$a"];
	$cat2 = $categoryData["$CURRENT_CATEGORY::$b"];
	
	if ($cat1 == null || $cat2 == null)	return 0;
	
	return $cat1['subIndex'] - $cat2['subIndex'];
}


$db = new mysqli($uespEsoLogReadDBHost, $uespEsoLogReadUser, $uespEsoLogReadPW, $uespEsoLogDatabase);
if ($db->connect_error) exit("Could not connect to mysql database!");

$query = "SELECT * FROM achievementCategories;";
$result = $db->query($query);
if (!$result) exit("Failed to load achievementCategories!");

$categories = array();

while (($row = $result->fetch_assoc()))
{
	$categories[] = $row;
}

$query = "SELECT * FROM achievements;";
$result = $db->query($query);
if (!$result) exit("Failed to load achievements!");

$achievements = array();

while (($row = $result->fetch_assoc()))
{
	$row['criteria'] = array();
	$id = intval($row['id']);
	$achievements[$id] = $row;
}

$query = "SELECT * FROM achievementCriteria;";
$result = $db->query($query);
if (!$result) exit("Failed to load achievementCriteria!");

$criteria = array();

while (($row = $result->fetch_assoc()))
{
	$criteria[] = $row;
	
	$id = intval($row['achievementId']);
	if ($achievements[$id] == null) continue;
	
	$index = intval($row['criteriaIndex']);
	$achievements[$id]['criteria'][$index] = $row;
}

//$outputData = array();
$categoryData = array();
$treeData = array();

foreach ($categories as $category)
{
	$catName = $category['categoryName'];
	$subCatName = $category['subCategoryName'];
		
	$newCate = array();
	$newCate['name'] = $catName;
	$newCate['subName'] = $subCatName;
	$newCate['index'] = intval($category['categoryIndex']);
	$newCate['subIndex'] = intval($category['subCategoryIndex']);
	if ($newCate['subIndex'] < 0) $newCate['subIndex'] = 0;
	$newCate['points'] = intval($category['points']);
	$newCate['icon'] = $category['icon'];
	
	$categoryData[$category['name']] = $newCate;
	if ($category['subCategoryIndex'] == -1) $categoryData[$catName] = $newCate;
	
	if ($category['numAchievements'] == 0) continue;
	
	if ($treeData[$catName] == null) $treeData[$catName] = array();
	$treeData[$catName][$subCatName] = array();
	
	/*
	if ($category['subCategoryIndex'] != -1) continue;
	$name = $category['categoryName'];
	
	$outputData[$name] = array();
	$outputData[$name]['name'] = $name;
	$outputData[$name]['index'] = $category['categoryIndex'];
	$outputData[$name]['points'] = $category['points'];
	$outputData[$name]['icon'] = $category['icon'];
	$outputData[$name]['categories'] = array();
	
	$newData = array();
	$newData['name'] = "General";
	$newData['index'] = 0;
	$newData['points'] = $category['points'];
	$outputData[$name]['categories']['General'] = $newData; */
}

uksort($treeData, SortByCategoryName);

foreach ($treeData as $catName => $data)
{
	$CURRENT_CATEGORY = $catName;
	uksort($treeData[$catName], SortBySubCategoryName);
}

//uasort($outputData, SortByIndex);
/*
foreach ($categories as $category)
{
	if ($category['subCategoryIndex'] == -1) continue;
	
	$name = $category['categoryName'];
	$subName = $category['subCategoryName'];

	$newData = array();
	$newData['name'] = $name;
	$newData['index'] = $category['subCategoryIndex'];
	$newData['points'] = $category['points'];
	$newData['achievements'] = array();
	
	$outputData[$name]['categories'][$subName] = $newData;
	$outputData[$name]['categories']['General']['points'] -= $category['points'];
}*/

$achData = array();

foreach ($achievements as $achievement)
{
	$categoryNames = explode("::", $achievement['categoryName']);
	$cateName1 = $categoryNames[0];
	$cateName2 = $categoryNames[1];
	
	//if ($outputData[$cateName1] == null) continue;
	//if ($outputData[$cateName1]['categories'][$cateName2] == null) continue;

	$newData = array();
	$newData['id'] = intval($achievement['id']);
	$newData['name'] = $achievement['name'];
	$newData['desc'] = $achievement['description'];
	$newData['icon'] = $achievement['icon'];
	$newData['points'] = intval($achievement['points']);
	$newData['index'] = intval($achievement['achievementIndex']);
	$newData['criteria'] = array();
		
	if ($achievement['dyeName'] != "")
	{
		$newData['dyeName'] = $achievement['dyeName'];
		$newData['dyeColor'] = $achievement['dyeColor'];
	}
	
	if ($achievement['title'] != "")
	{
		$newData['title'] = $achievement['title'];
	}
	
	if ($achievement['collectibleId'] != "")
	{
		$newData['collectId'] = $achievement['collectibleId'];
	}
	
	if ($achievement['itemName'] != "")
	{
		$newData['itemName'] = $achievement['itemName'];
		$newData['itemLink'] = $achievement['itemLink'];
		$newData['itemIcon'] = $achievement['itemIcon'];
	}
	
	foreach ($achievement['criteria'] as $index => $criteria)
	{
		$newCrit = array();
		//$newCrit['name'] = $criteria['name'];
		$newCrit['name'] = $criteria['description'];
		$newCrit['value'] = intval($criteria['numRequired']);
		$newCrit['index'] = intval($criteria['criteriaIndex']);
		
		$newData['criteria'][$index] = $newCrit;	
	}
	
	$achData[$achievement['id']] = $newData;
	//$outputData[$cateName1]['categories'][$cateName2]['achievements'][] = $newData;
	
	if ($treeData[$cateName1] === null) 
	{
		print("Unknown category '$cateName1' found!\n");
		continue;
	}
	
	if ($treeData[$cateName1][$cateName2] === null) 
	{
		print("Unknown category '$cateName1::$cateName2' found!\n");
		continue;
	}
	
	$achIndex = intval($achievement['achievementIndex']);
	
	if ($treeData[$cateName1][$cateName2][$achIndex] == null) $treeData[$cateName1][$cateName2][$achIndex] = array();
	$treeData[$cateName1][$cateName2][$achIndex][] = intval($achievement['id']);
}

/*
foreach ($outputData as $name => $category)
{
	uasort($outputData[$name]['categories'], SortByIndex);
	
	foreach ($category['categories'] as $subName => $subCategory)
	{
		uasort($outputData[$name]['categories'][$subName]['achievements'], SortByIndex);
	}
}*/

$deltaTime = (microtime(true) - $startTime) * 1000.0;
print("Achievement creation took $deltaTime ms\n");

//$output = "$ESO_ACHIEVEMENT_DATA = " . var_export($outputData, true) . ";\n";
$output .= "<?php\n\$ESO_ACHIEVEMENT_CATEGORIES = " . var_export($categoryData, true) . ";\n";
$output .= "\$ESO_ACHIEVEMENT_DATA = " . var_export($achData, true) . ";\n";
$output .= "\$ESO_ACHIEVEMENT_TREE = " . var_export($treeData, true) . ";\n";
file_put_contents("esoAchievementData.php", $output);