ESO_MAX_ATTRIBUTES = 64;


function GetEsoInputValues()
{
	var inputValues = {};
	
	for (var key in g_EsoInputStats)
	{
		var object = g_EsoInputStats[key];
		
		if (typeof(object) == "object")
		{
			inputValues[key] = {};
			
			for (var key1 in object)
			{
				inputValues[key][key1] = 0;
			}
		}
		else
		{
			inputValues[key] = 0;	
		}		
	}
	
	inputValues.pow = Math.pow;
	
	inputValues.Level = $("#esotbLevel").val();
	inputValues.Attribute.Health = $("#esotbAttrHea").val();
	inputValues.Attribute.Magicka = $("#esotbAttrMag").val();
	inputValues.Attribute.Stamina = $("#esotbAttrSta").val();
	inputValues.Attribute.TotalPoints = inputValues.Attribute.Health + inputValues.Attribute.Magicka + inputValues.Attribute.Stamina;
	
	$.extend(inputValues.Mundus, GetEsoInputMundusValues());
	
	return inputValues;
}


function GetEsoInputMundusValues()
{
	var result = {}
	
	result.Name = $("#esotbMundus").val();
	
	if (result.Name == "The Lady")
	{
		result.PhysicalResist = 1280;
	}
	else if (result.Name == "The Lover")
	{
		result.SpellResist = 1280;
	}
	else if (result.Name == "The Lord")
	{
		result.Health = 1280;
	}
	else if (result.Name == "The Mage")
	{
		result.Magicka = 1280;
	}
	else if (result.Name == "The Tower")
	{
		result.Stamina = 1280;
	}
	else if (result.Name == "The Atronach")
	{
		result.MagickaRegen = 210;
	}
	else if (result.Name == "The Serpent")
	{
		result.StaminaRegen = 210;
	}
	else if (result.Name == "The Shadow")
	{
		result.CritDamage = 0.12;
	}
	else if (result.Name == "The Ritual")
	{
		result.HealingGiven = 0.10;
	}
	else if (result.Name == "The Thief")
	{
		result.SpellCrit = 0.11;	//TODO: Absolute values?
		result.WeaponCrit = 0.11;
	}
	else if (result.Name == "The Warrior")
	{
		result.WeaponDamage = 166;
	}
	else if (result.Name == "The Apprentice")
	{
		result.SpellDamage = 166;
	}
	else if (result.Name == "The Steed")
	{
		result.HealthRegen = 210;
		result.RunSpeed = 0.05;
	}
	
	return result;
}


function UpdateEsoComputedStatsList()
{
	
	for (var statId in g_EsoComputedStats)
	{
		UpdateEsoComputedStat(statId, g_EsoComputedStats[statId]);
	}
	
}


function UpdateEsoComputedStat(statId, stat, inputValues)
{
	var stack = [];
	var error = "";
	var computeIndex = 0;
	
	if (inputValues == null) inputValues = GetEsoInputValues();
	
	var element = $("#esoidStat_" + statId);
	if (element.length == 0) element = CreateEsoComputedStat(statId, stat);
	
	var valueElement = element.children(".esotbStatValue");
	var computeElements = element.find(".esotbStatComputeValue");
	
	for (var i = 0; i < stat.compute.length; ++i)
	{
		var computeItem = stat.compute[i];
		var nextItem = stat.compute[i+1];
		var itemValue = 0;
		
		if (computeItem == "*")
		{
			if (stack.length >= 2)
				stack.push(stack.pop() * stack.pop());
			else
				error = "ERR";

			continue;
		}
		else if (computeItem == "+")
		{
			if (stack.length >= 2)
				stack.push(stack.pop() + stack.pop());
			else
				error = "ERR";
			
			continue;
		}
		
		with(inputValues)
		{
			try {
				itemValue = eval(computeItem); 
			} catch (e) {
			    itemValue = "ERR";
			}
			
			stack.push(itemValue);
		}
		
		var prefix = "";
		if (nextItem == "+") prefix = "+";
		if (nextItem == "*") prefix = "x";
		
		$(computeElements[computeIndex]).text(prefix + itemValue);
		++computeIndex;
	}
	
	if (stack.length <= 0) error = "ERR";
	
	if (error == "")
		valueElement.text(Math.floor(stack.pop()));
	else
		valueElement.text(error);
}


function CreateEsoComputedStat(statId, stat)
{
	var element;
	
	element = $("<div/>").attr("id", "esoidStat_" + statId).
		addClass("esotbStatRow").
		appendTo("#esotbStatList");

	$("<div/>").addClass("esotbStatName").
		text(stat.title).
		appendTo(element);
		
	$("<div/>").addClass("esotbStatValue").
		text("?").
		appendTo(element);
	
	$("<div/>").addClass("esotbStatComputeButton").
		text("+").
		appendTo(element);
	
	var computeElement = $("<div/>").addClass("esotbComputeItems").
										attr("style", "display: none;").
										appendTo(element);
		
	CreateEsoComputedStatItems(stat.compute, computeElement);
	
	return element;
}


function CreateEsoComputedStatItems(computeData, parentElement)
{
	
	for (var i = 0; i < computeData.length; ++i)
	{
		var computeItem = computeData[i];
		var nextItem = computeData[i+1];
		
		if (computeItem == "*")
		{
		}
		else if (computeItem == "+")
		{
		}
		else
		{
			var prefix = "";
			if (nextItem == "+") prefix = "+";
			if (nextItem == "*") prefix = "x";
			
			$("<div/>").addClass("esotbStatComputeValue").
				text(prefix + "0").
				attr("computeindex", i).
				appendTo(parentElement);
			
			$("<div/>").addClass("esotbStatComputeItem").
				text(computeItem).
				attr("computeindex", i).
				appendTo(parentElement);
		}
	}
	
}


function OnEsoInputChange(e)
{
	var id = $(this).attr("id");
	
	if (id == "esotbLevel") 
	{
		OnEsoLevelChange.call(this, e);
	}
	else if ($(this).hasClass("esotbAttributeInput"))
	{
		OnEsoAttributeChange.call(this, e);
	}
	
	UpdateEsoComputedStatsList();
}


function OnEsoAttributeChange(e)
{
	var $this = $(this);
	var value = $this.val();
	
	if (value == "")$this.val("0");
	if (value > ESO_MAX_ATTRIBUTES) $this.val(ESO_MAX_ATTRIBUTES);
	if (value < 0)  $this.val("0");
	
	var totalValue = parseInt($("#esotbAttrHea").val()) + parseInt($("#esotbAttrMag").val()) + parseInt($("#esotbAttrSta").val());
	
	if (totalValue > ESO_MAX_ATTRIBUTES) 
	{
		totalValue = ESO_MAX_ATTRIBUTES;
		$this.val(ESO_MAX_ATTRIBUTES - totalValue + parseInt(value));
	}
	
	$("#esotbAttrTotal").text(totalValue + " / " + ESO_MAX_ATTRIBUTES);
}


function OnEsoLevelChange(e)
{
	var $this = $(this);
	var value = $this.val();
	
	if (value == "")$this.val("0");
	if (value > 50) $this.val("50");
	if (value < 1)  $this.val("1");
}


function OnEsoToggleStatComputeItems(e)
{
	var computeItems = $(this).next(".esotbComputeItems");
	
	if (computeItems.is(":visible"))
		$(this).text("+");
	else
		$(this).text("-");
		
	computeItems.slideToggle();
}


function OnEsoMundusChange(e)
{
	UpdateEsoComputedStatsList();
}


function esotbOnDocReady()
{
	UpdateEsoComputedStatsList();
	
	$(".esotbInputValue").on('input', function(e) { OnEsoInputChange.call(this, e); });
	$("#esotbMundus").change(OnEsoMundusChange)
	$(".esotbStatComputeButton").click(OnEsoToggleStatComputeItems);
}


$( document ).ready(esotbOnDocReady);