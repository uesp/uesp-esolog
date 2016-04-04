<?php

/*
 * viewSkills.php -- by Dave Humphrey (dave@uesp.net), March 2016
 * 
 * Outputs a HTML page containing an ESO skill tree similar to the game UI.
 * 
 * TODO:
 *
 */

require_once("viewSkills.class.php");


$g_EsoViewSkills = new CEsoViewSkills();
$g_EsoViewSkills->Render();