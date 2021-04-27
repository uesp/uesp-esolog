<?php

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require_once("parseSalesData.php");

$salesData = new EsoSalesDataParser();
$salesData->UpdateTrendsForAllItems();