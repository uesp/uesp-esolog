<?php

require_once(__DIR__."/esoCommon.php");
require_once(__DIR__."/esoPotionData.php");

if (php_sapi_name() != "cli") die("Can only be run from command line!");


$USE_THREE_REAGENTS = true;
$USE_TWO_REAGENT_UPTO = 36;
//$USE_TWO_REAGENT_UPTO = 0.5;


function GetCombinedEffects($reagent1, $reagent2, $reagent3, $known1, $known2, $known3)
{
	global $ESO_POTIONEFFECT_DATA, $USE_THREE_REAGENTS;
	
	$combinedEffects = array();
	$effectCounts = array();
	$knownEffects = array();
	
	$effects1 = array();
	$effects2 = array();
	$effects3 = array();
	
	if ($reagent1) $effects1 = $reagent1['effects'];
	if ($reagent2) $effects2 = $reagent2['effects'];
	if ($reagent3) $effects3 = $reagent3['effects'];
	
	foreach ($effects1 as $i => $effectId) 
	{
		if ($knownEffects[$effectId] === null) $knownEffects[$effectId] = true;
		$knownEffects[$effectId] &= $known1[$i];
		
		if ($effectCounts[$effectId] == null) $effectCounts[$effectId] = 0;
		++$effectCounts[$effectId];
	}
	
	foreach ($effects2 as $i => $effectId) 
	{
		if ($knownEffects[$effectId] === null) $knownEffects[$effectId] = true;
		$knownEffects[$effectId] &= $known2[$i];
		
		if ($effectCounts[$effectId] == null) $effectCounts[$effectId] = 0;
		++$effectCounts[$effectId];
	}
	
	foreach ($effects3 as $i => $effectId)
	{
		if ($knownEffects[$effectId] === null) $knownEffects[$effectId] = true;
		$knownEffects[$effectId] &= $known3[$i];
		
		if ($effectCounts[$effectId] == null) $effectCounts[$effectId] = 0;
		++$effectCounts[$effectId];
	}
	
	foreach ($effectCounts as $effectId => $effectCount) 
	{
		$effectId = intval($effectId);
		
		$effectData = $ESO_POTIONEFFECT_DATA[$effectId];
		$count = $effectCounts[$effectId];
		
		if ($effectData && $effectData['oppositeId'] > 0 && $effectCounts[$effectData['oppositeId']] > 0)
		{
			$count -= $effectCounts[$effectData['oppositeId']];
		}
		
		if ($knownEffects[$effectId]) continue;
		
		if ($count > 1) $combinedEffects[] = $effectId;
	}
	
	return $combinedEffects;
}


function shuffle_assoc($list) {
	if (!is_array($list)) return $list;

	$keys = array_keys($list);
	shuffle($keys);
	$random = array();
	foreach ($keys as $key) {
		$random[$key] = $list[$key];
	}
	return $random;
}


function CreateReagents()
{
	global $ESO_REAGENT_DATA;
	
	$reagents = array();

	foreach ($ESO_REAGENT_DATA as $name => $reagent)
	{
		$reagents[$name] = $reagent;
		$reagents[$name]['knownEffects'] = array( false, false, false, false);
		$reagents[$name]['knownEffectCount'] = 0;
	}
	
	return $reagents;
}


function LearnAllTraits($use2ReagentsUpTo)
{
	global $totalCombinations, $reagents;
	global $knownReagents, $knownEffects;
	global $totalReagents, $totalEffects;
	global $USE_THREE_REAGENTS, $USE_TWO_REAGENT_UPTO;
	
	$output = "Learning all reagent traits...\n";
	
	foreach ($reagents as $name1 => $reagent1)
	{
		if ($use2ReagentsUpTo > 0 && $totalCombinations > $use2ReagentsUpTo) break;
		
		while ($reagents[$name1]['knownEffectCount'] < 4)
		{
			if ($use2ReagentsUpTo > 0 && $totalCombinations > $use2ReagentsUpTo) break;
			
			++$totalCombinations;
			$maxEffect2 = "";
			$maxEffect3 = "";
			$maxCount2 = 0;
			$maxEffects = array();
			
			foreach ($reagents as $name2 => $reagent2)
			{
				if ($name1 == $name2) continue;
				
				if ($USE_THREE_REAGENTS && $use2ReagentsUpTo == 0)
				{
					foreach ($reagents as $name3 => $reagent3)
					{
						if ($name1 == $name3) continue;
						if ($name2 == $name3) continue;
						
						$effects = GetCombinedEffects($reagent1, $reagent2, $reagent3, $reagents[$name1]['knownEffects'], $reagents[$name2]['knownEffects'], $reagents[$name3]['knownEffects']);
						if (count($effects) <= 0) continue;
						
						if (count($effects) > $maxCount2)
						{
							$maxCount2 = count($effects);
							$maxEffect2 = $name2;
							$maxEffect3 = $name3;
							$maxEffects = $effects;
						}
					}
				}
				else
				{
					$effects = GetCombinedEffects($reagent1, $reagent2, null, $reagents[$name1]['knownEffects'], $reagents[$name2]['knownEffects'], null);
					if (count($effects) <= 0) continue;
					
					if (count($effects) > $maxCount2)
					{
						//if ($use2ReagentsUpTo <= 0 || ($use2ReagentsUpTo > 0 && count($effects) > 1))
						{	
							$maxCount2 = count($effects);
							$maxEffect2 = $name2;
							$maxEffects = $effects;
						}
					}
				}
			}
			
			foreach ($maxEffects as $effectId)
			{
				foreach ($reagents[$name1]['effects'] as $i => $effectId1)
				{
					if ($effectId == $effectId1 && !$reagents[$name1]['knownEffects'][$i])
					{
						$reagents[$name1]['knownEffects'][$i] = true;
						++$reagents[$name1]['knownEffectCount'];
					}
				}
				
				foreach ($reagents[$maxEffect2]['effects'] as $i => $effectId1)
				{
					if ($effectId == $effectId1 && !$reagents[$maxEffect2]['knownEffects'][$i])
					{
						$reagents[$maxEffect2]['knownEffects'][$i] = true;
						++$reagents[$maxEffect2]['knownEffectCount'];
					}
				}
				
				if ($USE_THREE_REAGENTS && $reagents[$maxEffect3] && $use2ReagentsUpTo == 0)
				{
					foreach ($reagents[$maxEffect3]['effects'] as $i => $effectId1)
					{
						if ($effectId == $effectId1 && !$reagents[$maxEffect3]['knownEffects'][$i])
						{
							$reagents[$maxEffect3]['knownEffects'][$i] = true;
							++$reagents[$maxEffect3]['knownEffectCount'];
						}
					}
				}
			}
			
			$names = array($name1, $maxEffect2);
			if ($maxEffect3) $names[] = $maxEffect3;
			sort($names);
			
			$output .= "\t" . implode(" + ", $names) . " = $maxCount2 effects (";
			$output .= implode(", ", $maxEffects);
			$output .= ")\n";
		}
	}
	
	if ($use2ReagentsUpTo > 0 && $totalCombinations > $use2ReagentsUpTo) return $output;
	
	$output .= "$totalCombinations Combinations\n";
	
	$knownReagents = 0;
	$knownEffects = 0;
	
	foreach ($reagents as $name => $reagent)
	{
		$knownEffects += $reagent['knownEffectCount'];
	
		if ($reagent['knownEffectCount'] >= 4)
		{
			if ($reagent['knownEffectCount'] > 4)
			{
				$count = $reagent['knownEffectCount'];
				$output .= "Max count for $name is $count\n";
			}
	
			++$knownReagents;
		}
		else
		{
			$missing = 4 - $reagent['knownEffectCount'];
			$output .= "Missing $missing traits for $name\n";
		}
	}
	
	$output .= "Total Reagents Known = $knownReagents / $totalReagents\n";
	$output .= "Total Effects Known = $knownEffects / $totalEffects\n";
	
	return $output;
}


function FindMinCombinations($runCount)
{
	global $totalCombinations, $reagents;
	global $knownReagents, $knownEffects;
	global $totalReagents, $totalEffects;
	global $USE_TWO_REAGENT_UPTO;
	
	$minCombinations = 100;
	
	print("Finding minimum combinations for all reagent traits in $runCount tests...\n");
	
	for ($i = 0; $i < $runCount; ++$i)
	{
		$reagents = CreateReagents();
		$reagents = shuffle_assoc($reagents);
		
		$totalCombinations = 0;
		$totalReagents = count($reagents);
		$totalEffects = $totalReagents * 4;
		$knownEffects = 0;
		
		if ($USE_TWO_REAGENT_UPTO > 0)
		{
			$output = LearnAllTraits($USE_TWO_REAGENT_UPTO);
			$output .= LearnAllTraits(0);
		}
		else
		{
			$output = LearnAllTraits(0);
		}
		
		if ($totalCombinations < $minCombinations)
		{
			$minCombinations = $totalCombinations;
			print ($output);
		}
	}
	
	print("Minimum combinations was found to be $minCombinations in $runCount tests!\n");
}


FindMinCombinations(10000);


