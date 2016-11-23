<?php 


require("parseSalesData.php");


$salesData = new EsoSalesDataParser();

$salesData->LoadMMFile('../MM00Data.lua');
$salesData->LoadMMFile('../MM01Data.lua');
$salesData->LoadMMFile('../MM02Data.lua');
$salesData->LoadMMFile('../MM03Data.lua');
$salesData->LoadMMFile('../MM04Data.lua');
$salesData->LoadMMFile('../MM05Data.lua');
$salesData->LoadMMFile('../MM06Data.lua');
$salesData->LoadMMFile('../MM07Data.lua');
$salesData->LoadMMFile('../MM08Data.lua');
$salesData->LoadMMFile('../MM09Data.lua');
$salesData->LoadMMFile('../MM10Data.lua');
$salesData->LoadMMFile('../MM11Data.lua');
$salesData->LoadMMFile('../MM12Data.lua');
$salesData->LoadMMFile('../MM13Data.lua');
$salesData->LoadMMFile('../MM14Data.lua');
$salesData->LoadMMFile('../MM15Data.lua');
$salesData->ParseAllMMData();