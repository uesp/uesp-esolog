var EsoPotionItemLinkPopup = null;
var EsoPotionNextFreeReagent = 1;
var EsoPotionItemLinkCache = {};
var EsoPotionLastItemLink = "";
var EsoPotionLastPotionData = "";


function UpdateEsoPotionTooltip(potionData, potionItemId, poisonItemId)
{
	var linkSrc = "http://esoitem.uesp.net/itemLink.php?&embed";
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
		
	$.get(linkSrc, function(data) {
		EsoPotionItemLinkPopup.html(data);
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
	
	return combinedEffects;
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
		
		if (i == 0 && g_EsoPotionEffects[combinedEffects[i]] != null)
		{
			var effectData = g_EsoPotionEffects[combinedEffects[i]];
			potionBaseId = effectData.potionBaseId;
			poisonBaseId = effectData.poisonBaseId;
		}
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
	for (var i = 0; i < a1.length; ++i)
	{
		for (var j = 0; j < a2.length; ++j)
		{
			if (a1[i] == a2[j]) return true;
		}
	}
	
	return false;
}


function EnableEsoReagentEffect(reagentSlot, effects)
{
	var reagentName = reagentSlot.attr("reagent");
	var reagentData = g_EsoPotionReagents[reagentName];
	
	if (reagentName == "" || reagentData == null) return;
	
	if (effects.length == 0) {
		reagentSlot.removeClass("esopdReagentDisable");
		reagentSlot.removeClass("esopdReagentHighlight");
	}
	else if (HasArrayCommonValues(reagentData.effects, effects))
	{
		reagentSlot.removeClass("esopdReagentDisable");
		reagentSlot.addClass("esopdReagentHighlight");		
	}
	else
	{
		reagentSlot.addClass("esopdReagentDisable");
		reagentSlot.removeClass("esopdReagentHighlight");
	}

}


function UpdateEnabledEsoReagentEffects()
{
	var effects = GetEsoPotionSelectedEffects();
	var reagents = $(".esopdReagent");
	
	reagents.each(function(i) {
		EnableEsoReagentEffect($(this), effects);
	});
	
	if (effects.length == 0)
	{
		$(".esopdEffect").removeClass("esopdEffectDisable");
	}
	else
	{
		$(".esopdEffect.esopdEffectHighlighted").removeClass("esopdEffectDisable");
		$(".esopdEffect").not(".esopdEffectHighlighted").addClass("esopdEffectDisable");
	}
}


function OnEsoPotionEffectReset()
{
	$(".esopdEffectHighlighted").removeClass("esopdEffectHighlighted");
	UpdateEnabledEsoReagentEffects();
}


function GetEsoPotionSelectedEffects()
{
	var enabledEffects = $(".esopdEffectHighlighted");
	var effects = [];
	
	enabledEffects.each(function() {
		effects.push(parseInt($(this).attr("effectindex")));
	});
	
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
	
	if (solventData && solventData.isPoison) isPoison = true;
	
	output += " reagent1=\"" + result.name1 + "\"";
	output += " reagent2=\"" + result.name2 + "\"";
	output += " reagent3=\"" + result.name3 + "\"";
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
		
	OnClickEsoReagentTab.call($("#esopdFindPotionTab"));
}


function OnClickEsoEffect(e)
{
	var $this = $(this);
	var effectIndex = $this.attr("effectindex");
	var isHighlighted = $this.hasClass("esopdEffectHighlighted");
	
	if (isHighlighted)
	{
		$this.removeClass("esopdEffectHighlighted");
	}
	else
	{
		$this.addClass("esopdEffectHighlighted");
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
	
	$("#" + tabid).show();
}


function OnClickCopyEsoPotionItemLink()
{
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
	
	$("#esopdPotionLink").attr("href", link);
}


function UpdateEsoPotionDataText()
{
	$("#esopdPotionData").text("Potion Data: " + EsoPotionLastPotionData);
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
	
	UpdateEsoPotion();
}


$( document ).ready(esopdOnDocReady);