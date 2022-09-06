<?php
/*
 * getGoldenVendorHtml.php -- by Dave Humphrey (dave@uesp.net), May 2022
 * 
 * Returns HTML fragments of Golden Vendor items.
 * 
 * TODO:
 *
 */


require_once("goldenVendor.class.php");


$golden = new CEsoGetGoldenVendorHtml();
$golden->Render();
