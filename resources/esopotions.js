window.EsoPotionItemLinkPopup = null;
window.EsoPotionNextFreeReagent = 1;
window.EsoPotionItemLinkCache = {};
window.EsoPotionLastItemLink = "";
window.EsoPotionLastPotionData = "";
window.EsoPotionAvailableHighlights = [ 3, 2, 1 ]; 


function GetEsoPotionAvailableHighlight() 
{
	if (EsoPotionAvailableHighlights.length == 0) return -1;
	
	return EsoPotionAvailableHighlights.pop();
}


function RestoreEsoPotionAvailableHighlight(index)
{
	 EsoPotionAvailableHighlights.push(index);
}


function UpdateEsoPotionTooltip(potionData, potionItemId, poisonItemId)
{
	var linkSrc = "//esoitem.uesp.net/itemLink.php?&embed";
	var solventName = $("#esopdSolventUsed").attr("solvent");
	var solventData = g_EsoPotionSolvents[solventName];
	
	var itemId = 54339;
	var intLevel = 3;
	var intType = 30;
	
	if (potionItemId) itemId = potionItemId;
	
	if (solventData)
	{
		intLevel = solventData.internalLevel;
		intType = solventData.internalType;
		
		if (solventData.isPoison)
		{
			itemId = 76827;
			if (poisonItemId) itemId = poisonItemId;
		}
	}
	
	if (potionData == null) potionData = 0;

	EsoPotionLastItemLink = "|H1:item:" + itemId + ":" + intType + ":" + intLevel + ":0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:0:" + potionData + "|h|h";
	EsoPotionLastPotionData = "" + itemId + ":" + intType + ":" + intLevel + ":" + potionData;
	
	linkSrc += "&itemid=" + itemId;
	linkSrc += "&intlevel=" + intLevel;
	linkSrc += "&inttype=" + intType;
	linkSrc += "&potiondata=" + potionData;
	if (solventData.isPoison) linkSrc += "&ispoison=1";
	
	if (potionData != 0) $("#esopdCopyItemLink").show();
	
	$.get(linkSrc, function(data) {
		EsoPotionItemLinkPopup.html(data);
		if (potionData == 0) ShowBadEsoPotion(solventData.isPoison);
	});
}


function GetEsoPotionReagentSlot(name)
{
	var slot1 = $("#esopdReagent1").attr("reagent");
	var slot2 = $("#esopdReagent2").attr("reagent");
	var slot3 = $("#esopdReagent3").attr("reagent");
	
	if (slot1 == name) return $("#esopdReagent1");
	if (slot2 == name) return $("#esopdReagent2");
	if (slot3 == name) return $("#esopdReagent3");
	
	return null;
}


function GetEsoPotionFreeSlotIndex()
{
	var slot1 = $("#esopdReagent1");
	var slot2 = $("#esopdReagent2");
	var slot3 = $("#esopdReagent3");
	var slotIndex = EsoPotionNextFreeReagent;
	
	if (slot1.attr("reagent") == "") return 1;
	if (slot2.attr("reagent") == "") return 2;
	if (slot3.attr("reagent") == "") return 3;
	
	++EsoPotionNextFreeReagent;
	if (EsoPotionNextFreeReagent > 3) EsoPotionNextFreeReagent = 1;
	
	return slotIndex;
}


function UpdateEsoPotionReagent(reagentSlot, reagentName, reagentIcon)
{
	var effectSlots = reagentSlot.children(".esopdReagentEffects");
	var reagentData = g_EsoPotionReagents[reagentName];
	
	if (reagentIcon == "" || reagentIcon == null)
	{
		if (reagentName == "")
			reagentIcon = "resources/alchemy_emptyslot_reagent.png";
		else if (reagentData === null)
			reagentIcon = "resources/blank.gif";
		else
			reagentIcon = reagentData.icon;
	}
	
	reagentSlot.children("img").attr("src", reagentIcon);
	reagentSlot.attr("reagent", reagentName);
	reagentSlot.children(".esopdWorkAreaName").text(reagentName);
	
	if (reagentName == "" || reagentData == null)
	{
		effectSlots.children("img").attr("src", "resources/blank.gif").attr("title", "");
		return;
	}

	var effect1 = GetEsoPotionEffectData(reagentData.effects[0]);
	var effect2 = GetEsoPotionEffectData(reagentData.effects[1]);
	var effect3 = GetEsoPotionEffectData(reagentData.effects[2]);
	var effect4 = GetEsoPotionEffectData(reagentData.effects[3]);
	
	var effectSlot1 = $(effectSlots.get(0));
	var effectSlot2 = $(effectSlots.get(2));
	var effectSlot3 = $(effectSlots.get(1));
	var effectSlot4 = $(effectSlots.get(3));
	
	var effectImg1 = effectSlot1.children("img");
	var effectImg2 = effectSlot2.children("img");
	var effectImg3 = effectSlot3.children("img");
	var effectImg4 = effectSlot4.children("img");
	
	effectImg1.attr("src", effect1.icon);
	effectImg2.attr("src", effect2.icon);
	effectImg3.attr("src", effect3.icon);
	effectImg4.attr("src", effect4.icon);
	
	effectImg1.attr("title", effect1.name);
	effectImg2.attr("title", effect2.name);
	effectImg3.attr("title", effect3.name);
	effectImg4.attr("title", effect4.name);
}


function UpdateEsoPotionSolvent(solventName, solventIcon)
{
	var solventData = g_EsoPotionSolvents[solventName];
	var solventSlot = $("#esopdSolventUsed");
	var displayName = solventName;
	
	if (solventIcon == "" || solventIcon == null)
	{
		if (solventName == "")
			solventIcon = "resources/alchemy_emptyslot_solvent.png";
		else if (solventData === null)
			solventIcon = "resources/blank.gif";
		else
			solventIcon = solventData.icon;
	}
	
	if (solventData != null)
	{
		var level = solventData.level;
		
		if (level > 50)
			displayName += "<br/>CP" + ((level - 50)*10);
		else
			displayName += "<br/>Level " + level;
	}
	
	solventSlot.children("img").attr("src", solventIcon);
	solventSlot.attr("solvent", solventName);
	solventSlot.children(".esopdWorkAreaName").html(displayName);	
}


function GetEsoReagentEffects(reagentSlot)
{
	var reagentName = reagentSlot.attr("reagent");
	var reagentData = g_EsoPotionReagents[reagentName];
	
	if (reagentName == "" || reagentData == null) return 0;
	
	return reagentData.effects.slice();
}


function CombineEsoReagentEffects(effects1, effects2, effects3)
{
	var combinedEffects = [];
	var effectCounts = {};
	var i;
	
	for (i = 0; i < effects1.length; i++) 
	{
		var effectId = parseInt(effects1[i]);
		if (effectCounts[effectId] == null) effectCounts[effectId] = 0;
		++effectCounts[effectId];
	}
	
	for (i = 0; i < effects2.length; i++) 
	{
		var effectId = parseInt(effects2[i]);
		if (effectCounts[effectId] == null) effectCounts[effectId] = 0;
		++effectCounts[effectId];
	}
	
	for (i = 0; i < effects3.length; i++) 
	{
		var effectId = parseInt(effects3[i]);
		if (effectCounts[effectId] == null) effectCounts[effectId] = 0;
		++effectCounts[effectId];
	}
	
	for (var effectId in effectCounts) 
	{
		effectId = parseInt(effectId);
		
		var effectData = g_EsoPotionEffects[effectId];
		var count = effectCounts[effectId];
		
		if (effectData && effectData.oppositeId > 0 && effectCounts[effectData.oppositeId] > 0)
		{
			count -= effectCounts[effectData.oppositeId];
		}
		
		if (count > 1) combinedEffects.push(effectId);
	}
	
	return combinedEffects.sort(SortEffectByIndex);
	//return combinedEffects.sort((a, b) => a - b);
	//return combinedEffects;
}


function SortEffectByIndex(a, b)
{
	var eff1 = g_EsoPotionEffects[a];
	var eff2 = g_EsoPotionEffects[b];
	
	if (eff1 == null || eff2 == null) return a - b;
	return eff1.index - eff2.index;
}


function UpdateEsoPotion()
{
	var effects1 = GetEsoReagentEffects($("#esopdReagent1"));
	var effects2 = GetEsoReagentEffects($("#esopdReagent2"));
	var effects3 = GetEsoReagentEffects($("#esopdReagent3"));
	
	var combinedEffects = CombineEsoReagentEffects(effects1, effects2, effects3);
	var potionData = 0;
	var potionBaseId = 0;
	var poisonBaseId = 0;
	
	for (var i = 0; i < combinedEffects.length; i++) 
	{
		potionData = potionData * 256 + combinedEffects[i];
	}
	
	if (combinedEffects[0] != null && g_EsoPotionEffects[combinedEffects[0]] != null)
	{
		var effectData = g_EsoPotionEffects[combinedEffects[0]];
		potionBaseId = effectData.potionBaseId;
		poisonBaseId = effectData.poisonBaseId;
	}
	
	UpdateEsoPotionTooltip(potionData, potionBaseId, poisonBaseId);
	UpdateEsoPotionLink();
	UpdateEsoPotionDataText();
}


function GetEsoPotionEffectData(effectIndex)
{
	var data = g_EsoPotionEffects[effectIndex];
	if (data == null) return g_EsoUnknownPotionEffect;
	return data;
}


function OnClickEsoReagent(e)
{
	var $this = $(this);
	var reagent = $this.parent();
	var slotIndex = GetEsoPotionFreeSlotIndex();
	var slotName = "#esopdReagent" + slotIndex; 
	var reagentName = reagent.attr("reagent");
	var currentSlot = GetEsoPotionReagentSlot(reagentName)
		
	if (currentSlot != null) 
	{
		UpdateEsoPotionReagent(currentSlot, "", "");
	}
	else
	{
		UpdateEsoPotionReagent($(slotName), reagentName, $this.attr("src"));
	}
	
	
	UpdateEsoPotion();
}


function OnClickEsoSolvent(e)
{
	var $this = $(this);
	var solvent = $this.parent();
	var solventName = solvent.attr("solvent");
	var solventData = g_EsoPotionSolvents[solventName];
	
	if (solventData) ChangeEsoPotionEffectNames(solventData.isPoison);
	
	UpdateEsoPotionSolvent(solventName, $this.attr("src"));
	UpdateEsoPotion();
}


function OnClickEsoReagentSlot(e)
{
	UpdateEsoPotionReagent($(this), "", "");
	UpdateEsoPotion();
}


function OnClickEsoSolventSlot(e)
{
	//UpdateEsoPotionSolvent(solventName, $this.attr("src"));
	//UpdateEsoPotion();
}


function HasArrayCommonValues(a1, a2)
{
	if (a1 == null || a2 == null) return false;
	
	for (var i = 0; i < a1.length; ++i)
	{
		for (var j = 0; j < a2.length; ++j)
		{
			if (a1[i] == a2[j]) return true;
		}
	}
	
	return false;
}


function EnableEsoReagentEffect(reagentSlot, effects, highlightIndexes)
{
	var reagentName = reagentSlot.attr("reagent");
	var reagentData = g_EsoPotionReagents[reagentName];
	
	if (reagentName == "" || reagentData == null) return;
	
	if (effects.length == 0) {
		reagentSlot.removeClass("esopdReagentDisable");
		reagentSlot.removeClass("esopdReagentHighlight1");
		reagentSlot.removeClass("esopdReagentHighlight2");
		reagentSlot.removeClass("esopdReagentHighlight3");
	}
	else if (HasArrayCommonValues(reagentData.effects, effects))
	{
		var hasEffect1 = HasArrayCommonValues(reagentData.effects, [effects[0]]);
		var hasEffect2 = HasArrayCommonValues(reagentData.effects, [effects[1]]);
		var hasEffect3 = HasArrayCommonValues(reagentData.effects, [effects[2]]);
		
		for (var i = 0; i < EsoPotionAvailableHighlights.length; ++i) 
		{
			var highlightIndex = EsoPotionAvailableHighlights[i];
			reagentSlot.removeClass("esopdReagentHighlight" + highlightIndex);
		}
		
		if (hasEffect1) {
			var highlightIndex = highlightIndexes[effects[0]];
			reagentSlot.removeClass("esopdReagentDisable");
			reagentSlot.addClass("esopdReagentHighlight" + highlightIndex);
		}
		
		if (hasEffect2) {
			var highlightIndex = highlightIndexes[effects[1]];
			reagentSlot.removeClass("esopdReagentDisable");
			reagentSlot.addClass("esopdReagentHighlight" + highlightIndex);
		}
		
		if (hasEffect3) {
			var highlightIndex = highlightIndexes[effects[2]];
			reagentSlot.removeClass("esopdReagentDisable");
			reagentSlot.addClass("esopdReagentHighlight" + highlightIndex);
		}
		
		reagentSlot.removeClass("esopdReagentDisable");
	}
	else
	{
		reagentSlot.addClass("esopdReagentDisable");
		reagentSlot.removeClass("esopdReagentHighlight1");
		reagentSlot.removeClass("esopdReagentHighlight2");
		reagentSlot.removeClass("esopdReagentHighlight3");
	}
	

}


function UpdateEnabledEsoReagentEffects()
{
	var effects = GetEsoPotionSelectedEffects();
	var highlightIndexes = GetEsoPotionSelectedEffectsWithHighlightIndex();
	var reagents = $(".esopdReagent");
	
	reagents.each(function(i) {
		EnableEsoReagentEffect($(this), effects, highlightIndexes);
	});
	
	if (effects.length == 0)
	{
		$(".esopdEffect").removeClass("esopdEffectDisable");
	}
	else
	{
		$(".esopdEffect").addClass("esopdEffectDisable");
		$(".esopdEffect.esopdEffectHighlight1").removeClass("esopdEffectDisable");
		$(".esopdEffect.esopdEffectHighlight2").removeClass("esopdEffectDisable");
		$(".esopdEffect.esopdEffectHighlight3").removeClass("esopdEffectDisable");
	}
}


function OnEsoPotionEffectReset()
{
	$(".esopdEffectHighlight1").removeClass("esopdEffectHighlight1");
	$(".esopdEffectHighlight2").removeClass("esopdEffectHighlight2");
	$(".esopdEffectHighlight3").removeClass("esopdEffectHighlight3");
	
	RestoreEsoPotionAvailableHighlight(3);
	RestoreEsoPotionAvailableHighlight(2);
	RestoreEsoPotionAvailableHighlight(1);
	
	UpdateEnabledEsoReagentEffects();
}


function GetEsoPotionSelectedEffects()
{
	var enabledEffects = $(".esopdEffectHighlight1, .esopdEffectHighlight3, .esopdEffectHighlight2");
	var effects = [];
	
	enabledEffects.each(function() {
		effects.push(parseInt($(this).attr("effectindex")));
	});
	
	return effects;
}


function GetEsoPotionSelectedEffectsWithHighlightIndex()
{
	var enabledEffects = $(".esopdEffectHighlight1, .esopdEffectHighlight3, .esopdEffectHighlight2");
	var effect1 = $(".esopdEffectHighlight1");
	var effect2 = $(".esopdEffectHighlight2");
	var effect3 = $(".esopdEffectHighlight3");
	var effects = {};
	
	if (effect1.length) effects[parseInt(effect1.attr("effectindex"))] = 1;
	if (effect2.length) effects[parseInt(effect2.attr("effectindex"))] = 2;
	if (effect3.length) effects[parseInt(effect3.attr("effectindex"))] = 3;
	
	return effects;
}


function FindEsoMatchingReagents(effects)
{
	var reagents = [];
	
	for (var name in g_EsoPotionReagents)
	{
		var reagent = g_EsoPotionReagents[name];
		
		for (var i in reagent.effects)
		{
			if ($.inArray(reagent.effects[i], effects) >= 0)
			{
				reagents.push(name);
				break;
			}
		}		
	}
	
	return reagents;
}


function FindEsoPotionCombinations(effects)
{
	var reagents = FindEsoMatchingReagents(effects);
	var allReagents = Object.keys(g_EsoPotionReagents);
	var potions = [];
	var potionKeys = {};
		
	if (reagents.length == 0) return potions;
	
	for (var i in reagents)
	{
		var name1 = reagents[i];
		
		for (var j in reagents)
		{
			var name2 = reagents[j];
			if (name1 == name2) continue;
			
			var reagent1 = g_EsoPotionReagents[name1];
			var reagent2 = g_EsoPotionReagents[name2];
			
			if (reagent1 == null || reagent2 == null) continue;
			
			var combineEffects1 = CombineEsoReagentEffects(reagent1.effects, reagent2.effects, []);
			var matched = true;
			
			for (var m in effects)
			{
				if ($.inArray(effects[m], combineEffects1) < 0)
				{
					matched = false;
					break;
				}
			}
			
			var key1 = name1 + ":" + name2;
			var key2 = name2 + ":" + name1;
			
			if (matched && !(key1 in potionKeys) && !(key2 in potionKeys)) 
			{
				potionKeys[key1] = 1;
				
				var result = {};
				result.name1 = name1;
				result.name2 = name2;
				result.name3 = "";
				result.effects = combineEffects1;
				
				potions.push(result);
			}
			
			for (var k in allReagents)
			{
				var name3 = allReagents[k];
				if (name1 == name3 || name2 == name3) continue;
				
				var reagent3 = g_EsoPotionReagents[name3];
				if (reagent3 == null) continue;
				
				var combineEffects2 = CombineEsoReagentEffects(reagent1.effects, reagent2.effects, reagent3.effects);
				var matched = true;
				
				for (var m in effects)
				{
					if ($.inArray(effects[m], combineEffects2) < 0)
					{
						matched = false;
						break;
					}
				}
				
				var key1 = name1 + ":" + name2 + ":" + name3;
				var key2 = name1 + ":" + name3 + ":" + name2;
				var key3 = name2 + ":" + name1 + ":" + name3;
				var key4 = name2 + ":" + name3 + ":" + name1;
				var key5 = name3 + ":" + name2 + ":" + name1;
				var key6 = name3 + ":" + name1 + ":" + name2;
				
				if (matched && !(key1 in potionKeys) && !(key2 in potionKeys) && !(key3 in potionKeys)
						    && !(key4 in potionKeys) && !(key5 in potionKeys) && !(key6 in potionKeys))
				{
					potionKeys[key1] = 1;
					
					var result = {};
					result.name1 = name1;
					result.name2 = name2;
					result.name3 = name3;
					result.effects = combineEffects2;
					
					potions.push(result);
				}
			}
		}
	}
	
	return potions;
}


function CreateEsoFindPotionResultHtml(result)
{
	var output = "<li class=\"esopdPotionResult\"";
	var reagent1 = g_EsoPotionReagents[result.name1];
	var reagent2 = g_EsoPotionReagents[result.name2];
	var reagent3 = g_EsoPotionReagents[result.name3];
	var isPoison = false;
	var solventData = g_EsoPotionSolvents[$("#esopdSolventUsed").attr("solvent")];
	var numReagents = 2;
	var numEffects = result.effects.length;
	
	if (solventData && solventData.isPoison) isPoison = true;
	if (reagent1 != null && reagent2 != null && reagent3 != null) numReagents = 3;
	
	output += " reagent1=\"" + result.name1 + "\"";
	output += " reagent2=\"" + result.name2 + "\"";
	output += " reagent3=\"" + result.name3 + "\"";
	output += " numreagents=\"" + numReagents + "\"";
	output += " numeffects=\"" + numEffects + "\"";
	output += ">";
	
	output += "<div class=\"esopdPotionResultName\">";
	if (reagent1 != null) output += "<img src=\"" + reagent1.icon + "\"> "
	output += result.name1 + " + ";
	if (reagent2 != null) output += "<img src=\"" + reagent2.icon + "\"> "
	output += result.name2
	
	if (result.name3 != "") 
	{
		output += " + ";
		if (reagent3 != null) output += "<img src=\"" + reagent3.icon + "\"> "
		output += result.name3;
	}
	
	output += "</div>";
	
	output += " = ";
	output += "<div class=\"esopdPotionResultEffects\">";
	
	for (var i in result.effects)
	{
		var effectData = g_EsoPotionEffects[result.effects[i]];
		if (effectData == null) continue;
		
		if (i > 0) output += " + ";
		output += "<img src=\"" + effectData.icon + "\"> ";
		
		if (isPoison && effectData.name2)
			output +=  effectData.name2;
		else
			output +=  effectData.name;
	}
	
	output += "</div>";
	output += "</li>";
	return output;
}


function CreateEsoFindPotionResults(results)
{
	var output = "<ol class=\"esopdPotionResultsList\">";
	
	for (var i in results)
	{
		output += CreateEsoFindPotionResultHtml(results[i]);
	}
	
	output += "</ol>";
	
	$("#esopdFindPotionResults").html(output);
	
	$(".esopdPotionResult").click(OnEsoPotionResultClick);
}


function OnEsoPotionResultClick(e)
{
	var $this = $(this);
	var reagentName1 = $this.attr("reagent1");
	var reagentName2 = $this.attr("reagent2");
	var reagentName3 = $this.attr("reagent3");
	
	UpdateEsoPotionReagent($("#esopdReagent1"), reagentName1, null);
	UpdateEsoPotionReagent($("#esopdReagent2"), reagentName2, null);
	UpdateEsoPotionReagent($("#esopdReagent3"), reagentName3, null);
	
	UpdateEsoPotion();
}


function OnEsoPotionEffectFind()
{
	var effects = GetEsoPotionSelectedEffects();
	var results = FindEsoPotionCombinations(effects);
	var effectNames = "";
	var isPoison = false;
	var solventData = g_EsoPotionSolvents[$("#esopdSolventUsed").attr("solvent")];
	
	if (solventData && solventData.isPoison) isPoison = true;
	
	for (var i in effects)
	{
		var effectData = g_EsoPotionEffects[effects[i]];
		if (effectData == null) continue;
		
		if (i > 0) effectNames += " + ";
		
		if (isPoison && effectData.name2)
			effectNames += effectData.name2;
		else
			effectNames += effectData.name;
	}
	
	if (results.length == 0)
		$("#esopdFindPotionMsg").html("No potions with the effects of <b>" + effectNames + "</b> were found!");
	else
		$("#esopdFindPotionMsg").html("Found <b>" + results.length + "</b> potions with the effects of <b>"+ effectNames + "</b>!");
	
	CreateEsoFindPotionResults(results);
	ShowFindResults();
		
	OnClickEsoReagentTab.call($("#esopdFindPotionTab"));
}


function OnClickEsoEffect(e)
{
	var $this = $(this);
	var effectIndex = $this.attr("effectindex");
	var hasHighlight1 = $this.hasClass("esopdEffectHighlight1");
	var hasHighlight2 = $this.hasClass("esopdEffectHighlight2");
	var hasHighlight3 = $this.hasClass("esopdEffectHighlight3");
	var isHighlighted = hasHighlight1 || hasHighlight2 || hasHighlight3;
	
	if (isHighlighted) 
	{
		var highlightIndex = -1;
		if (hasHighlight1) highlightIndex = 1;
		if (hasHighlight2) highlightIndex = 2;
		if (hasHighlight3) highlightIndex = 3;
		
		$this.removeClass("esopdEffectHighlight" + highlightIndex);
		RestoreEsoPotionAvailableHighlight(highlightIndex);
	}
	else 
	{
		var highlightIndex = GetEsoPotionAvailableHighlight();
		if (highlightIndex <= 0) return;
		
		$this.addClass("esopdEffectHighlight" + highlightIndex);
	}
	
	UpdateEnabledEsoReagentEffects();
}


function OnClickEsoReagentTab(e)
{
	var $this = $(this);
	var tabid = $this.attr("tabid");
	
	if (tabid == null || tabid == "") return;
	
	$(".esopdReagentTabSelected").removeClass("esopdReagentTabSelected");
	$this.addClass("esopdReagentTabSelected");
	
	$("#esopdReagents").hide();
	$("#esopdSolvents").hide();
	$("#esopdFindPotions").hide();
	$("#esopdLearn").hide();
	
	$("#" + tabid).show();
}


function OnClickCopyEsoPotionItemLink(e)
{
	var textToCopy = EsoPotionLastItemLink;
	
	$("body")
		.append($('<input type="text" name="fname" class="textToCopyInput" style="opacity: 0; position: absolute;" />' )
				.val(textToCopy))
		.find(".textToCopyInput")
		.select();
	
	try 
	{
		var successful = document.execCommand('copy');
		var msg = successful ? 'successful' : 'unsuccessful';
		//alert('Text copied to clipboard!');
    }
	catch (err) 
    {
    	window.prompt("To copy the text to clipboard: Ctrl+C, Enter", textToCopy);
    }
	
	$(".textToCopyInput").remove();	
}


function ChangeEsoPotionEffectNames(usePoison)
{
	$(".esopdEffect").each(function(){
		var effectindex = parseInt($(this).attr("effectindex"));
		var effectData = g_EsoPotionEffects[effectindex];
		
		if (effectData == null) return;
		
		var newName = effectData.name;
		if (usePoison && effectData.name2) newName = effectData.name2;
		
		$(this).children(".esopdEffectName").text(newName);
	});
}


function UpdateEsoPotionLink()
{
	var solvent = $("#esopdSolventUsed").attr("solvent");
	var reagent1 = $("#esopdReagent1").attr("reagent");
	var reagent2 = $("#esopdReagent2").attr("reagent");
	var reagent3 = $("#esopdReagent3").attr("reagent");
	
	var link = "?s=" + solvent + "&r1=" + reagent1 + "&r2=" + reagent2 + "&r3=" + reagent3;
	
	$("#esopdPotionLink").attr("href", link).show();
}



function UpdateEsoPotionDataText()
{
	$("#esopdPotionData").text("Potion Data: " + EsoPotionLastPotionData).show();
}


function ShowFindResults()
{
	var numEffects = 0;
	var numReagents = 0;
	
	if ($('#esopdFindOneEffect').is(':checked')) numEffects = 1;
	if ($('#esopdFindTwoEffects').is(':checked')) numEffects = 2;
	if ($('#esopdFindThreeEffects').is(':checked')) numEffects = 3;
	
	if ($('#esopdFindTwoReagents').is(':checked')) numReagents = 2;
	if ($('#esopdFindThreeReagents').is(':checked')) numReagents = 3;
	
	if (numEffects == 0 && numReagents == 0)
	{
		$(".esopdPotionResult").show();
		return;
	}
	
	if (numReagents == 0)
	{
		$(".esopdPotionResult").hide();
		$(".esopdPotionResult[numeffects='" + numEffects + "']").show();
		return;
	}
	
	if (numEffects == 0)
	{
		$(".esopdPotionResult").hide();
		$(".esopdPotionResult[numreagents='" + numReagents + "']").show();
		return;
	}
	
	$(".esopdPotionResult").hide();
	$(".esopdPotionResult[numreagents='" + numReagents + "'][numeffects='" + numEffects + "']").show();
}


function OnEsoFindTwoReagants(e)
{
	$('#esopdFindThreeReagents').attr('checked', false);
	ShowFindResults();
}


function OnEsoFindThreeReagants(e)
{
	$('#esopdFindTwoReagents').attr('checked', false);
	ShowFindResults();
}


function OnEsoFindOneEffect(e)
{
	$('#esopdFindTwoEffects').attr('checked', false);
	$('#esopdFindThreeEffects').attr('checked', false);
	ShowFindResults();
}


function OnEsoFindTwoEffects(e)
{
	$('#esopdFindOneEffect').attr('checked', false);
	$('#esopdFindThreeEffects').attr('checked', false);
	ShowFindResults();
}


function OnEsoFindThreeEffects(e)
{
	$('#esopdFindOneEffect').attr('checked', false);
	$('#esopdFindTwoEffects').attr('checked', false);
	ShowFindResults();
}

function OnFindItemLinkKeyDown()
{
	if(event.keyCode == 13) OnEsoPotionFindItemLinkButton();    
}


function ParseEsoItemLink(itemLink)
{
	//|H1:item:Id:SubType:InternalLevel:EnchantId:EnchantSubType:EnchantLevel:Writ1:Writ2:Writ3:Writ4:Writ5:Writ6:0:0:0:Style:Crafted:Bound:Stolen::Charges:PotionEffect/WritReward|hName|h
	var result = itemLink.match(/\|H([0-9a-zA-Z]+):(.*?):([0-9]+):([0-9]+):([0-9]+):([0-9]+):([0-9]+):([0-9]+):([0-9]+):([0-9]+):([0-9]+):([0-9]+):([0-9]+):([0-9]+):([0-9]+):([0-9]+):([0-9]+):([0-9]+):([0-9]+):([0-9]+):([0-9]+):([0-9]+):([0-9]+)\|h(.*?)\|h/);
	if (result == null) return false;
	
	result.linkType = result[1];
	result.linkName = result[2];
	result.itemId = result[3];
	result.internalSubtype = result[4];
	result.internalLevel = result[5];
	result.enchantId = result[6];
	result.enchantSubtype = result[7];
	result.enchantLevel = result[8];
	result.writ1 = result[9];
	result.writ2 = result[10];
	result.writ3 = result[11];
	result.writ4 = result[12];
	result.writ5 = result[13];
	result.writ6 = result[14];
	result.zero1 = result[15];
	result.zero2 = result[16];
	result.zero3 = result[17];
	result.style = result[18];
	result.crafted = result[19];
	result.bound = result[20];
	result.stolen = result[21];
	result.charges = result[22];
	result.potionData = result[23];
	result.name = result[24];	
	
	return result;
}


function OnEsoPotionFindItemLinkButton()
{
	var itemLink = $("#esopdFindItemLink").val();
	FindPotionItemLinkEffects(itemLink);
}


function FindPotionItemLinkEffects(itemLink)
{
	OnEsoPotionEffectReset();
	
	var itemData = ParseEsoItemLink(itemLink);
	
	if (itemData === false) 
	{
		OnEsoPotionEffectFind();
		return false;
	}
	
	var effect1 = 0;
	var effect2 = 0;
	var effect3 = 0;
	
	if (itemData.writ1 == 239 || itemData.writ1 == 199)
	{
		effect1 = itemData.writ2;
		effect2 = itemData.writ3;
		effect3 = itemData.writ4;
	}
	else if (itemData.potionData > 0)
	{
		effect1 = (itemData.potionData & 255);
		effect2 = ((itemData.potionData >> 8) & 255);
		effect3 = ((itemData.potionData >> 16) & 255);
	}
	else
	{
		OnEsoPotionEffectFind();
		return false;
	}
	
	if (effect1 > 0) {
		var highlightIndex = GetEsoPotionAvailableHighlight();
		$(".esopdEffect[effectindex='" + effect1 + "']").addClass("esopdEffectHighlight" + highlightIndex);
	}
	
	if (effect2 > 0) {
		var highlightIndex = GetEsoPotionAvailableHighlight();
		$(".esopdEffect[effectindex='" + effect2 + "']").addClass("esopdEffectHighlight" + highlightIndex);
	}
	
	if (effect3 > 0) {
		var highlightIndex = GetEsoPotionAvailableHighlight();
		$(".esopdEffect[effectindex='" + effect3 + "']").addClass("esopdEffectHighlight" + highlightIndex);
	}
	
	$(".esopdEffect").addClass("esopdEffectDisable");
	$(".esopdEffectHighlight1").removeClass("esopdEffectDisable");
	$(".esopdEffectHighlight2").removeClass("esopdEffectDisable");
	$(".esopdEffectHighlight3").removeClass("esopdEffectDisable");
	
	if (itemData.writ1 == 239)
	{
		UpdateEsoPotionSolvent("Alkahest");
	}
	else if(itemData.writ1 == 199)
	{
		UpdateEsoPotionSolvent("Lorkhan's Tears");
	}
	
	UpdateEnabledEsoReagentEffects();
	OnEsoPotionEffectFind();
	return true;
}


function ShowBadEsoPotion(isPoison)
{
	if (isPoison === true)
		$("#esoil_itemname").text("UNKNOWN POISON");
	else
		$("#esoil_itemname").text("UNKNOWN POTION");
	
	$("#esoil_itemicon").attr("src", "//esoicons.uesp.net/unknown.png");
	$("#esoil_itemtraitabilityblock").hide();
	
	$("#esopdPotionData").hide();
	$("#esopdCopyItemLink").hide();
}


function OnClickEsoSortByName()
{
	var effects = $('#esopdEffects .esopdEffect').get();
	
	effects.sort(function(a, b) {
		var name1 = $(a).children(".esopdEffectName");
		var name2 = $(b).children(".esopdEffectName");
		return name1.text().toUpperCase().localeCompare(name2.text().toUpperCase());
	});
	
	$.each(effects, function(idx, itm) { $("#esopdEffects").append(itm); });
}


function OnClickEsoSortByID()
{
	var effects = $('#esopdEffects .esopdEffect').get();
	
	effects.sort(function(a, b) {
		var id1 = parseInt($(a).attr("effectindex"));
		var id2 = parseInt($(b).attr("effectindex"));
		return id1 - id2;
	});
	
	$.each(effects, function(idx, itm) { $("#esopdEffects").append(itm); });
}


function esopdOnDocReady()
{
	EsoPotionItemLinkPopup = $("#esopdTooltip");
	
	$(".esopdReagentIcon").click(OnClickEsoReagent);
	$(".esopdSolventIcon").click(OnClickEsoSolvent);
	$("#esopdSolventUsed").click(OnClickEsoSolventSlot)
	$("#esopdReagentIcon").click(OnClickEsoReagentSlot)
	$(".esopdEffect").click(OnClickEsoEffect);
	$(".esopdReagentTab").click(OnClickEsoReagentTab);
	$("#esopdCopyItemLink").click(OnClickCopyEsoPotionItemLink);
	$(".esopdLearnList li").click(OnEsoPotionResultClick)
	$("#esopdFindTwoReagents").change(OnEsoFindTwoReagants)
	$("#esopdFindThreeReagents").change(OnEsoFindThreeReagants)
	$("#esopdFindOneEffect").change(OnEsoFindOneEffect)
	$("#esopdFindTwoEffects").change(OnEsoFindTwoEffects)
	$("#esopdFindThreeEffects").change(OnEsoFindThreeEffects)
	$("#esopdButtonSortByName").click(OnClickEsoSortByName);
	$("#esopdButtonSortByID").click(OnClickEsoSortByID);
	
	UpdateEsoPotion();
}


$( document ).ready(esopdOnDocReady);