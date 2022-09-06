<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>UESP:ESO Golden Vendor Items</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<meta charset="utf-8" />
	<link rel="stylesheet" href="//esolog-static.uesp.net/viewlog.css" />
	<script type="text/javascript" src="//esolog-static.uesp.net/resources/jquery-1.10.2.js"></script>
	<script type="text/javascript" src="//esolog-static.uesp.net/viewlog.js"></script>
</head>
<body>
<a href="/viewlog.php">Back to Home</a>
<h1>UESP:ESO Golden Vendor Items</h1>
Showing all recorded golden vendor items.
<?php
	require_once("goldenVendor.class.php");
	$golden = new CEsoGetGoldenVendorHtml();
	$golden->showAll = true;
	$golden->showLatestInHistory = true;
	print($golden->GetHtml());
?>
<hr>
<div class='elvLicense'>Most content here is available under the same Attribute-ShareAlike 2.5 License as the UESP wiki. See <a href='https://en.uesp.net/wiki/UESPWiki:Copyright_and_Ownership'>Copyright and Ownership</a> for more information. Some data is extracted directly from the ESO game data files and copyright is owned by Zenimax Online Studios.</div></body>
</html>
	