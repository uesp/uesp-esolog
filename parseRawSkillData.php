<?php

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require("skillTooltips.class.php");

$TABLE_SUFFIX = "31";

$skillTooltips = new CEsoSkillTooltips($TABLE_SUFFIX);
$skillTooltips->UpdateAllSkillRawData();
