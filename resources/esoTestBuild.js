ESO_MAX_ATTRIBUTES = 64;

g_EsoBuildClickWallLinkElement = null;


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
	inputValues.floor = Math.floor;
	inputValues.round = Math.round;
	inputValues.ceil = Math.ceil;
	
	inputValues.Race = $("#esotbRace").val();
	inputValues.Class = $("#esotbClass").val();
	
	inputValues.Level = parseInt($("#esotbLevel").val());
	inputValues.CPLevel = 0;
	inputValues.EffectiveLevel = inputValues.Level + inputValues.CPLevel;
	
	inputValues.Attribute.Health = parseInt($("#esotbAttrHea").val());
	inputValues.Attribute.Magicka = parseInt($("#esotbAttrMag").val());
	inputValues.Attribute.Stamina = parseInt($("#esotbAttrSta").val());
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
		result.HealingDone = 0.10;
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
	var inputValues = GetEsoInputValues();
	
	for (var statId in g_EsoComputedStats)
	{
		UpdateEsoComputedStat(statId, g_EsoComputedStats[statId], inputValues);
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
		else if (computeItem == "-")
		{
			if (stack.length >= 2)
				stack.push(-stack.pop() + stack.pop());
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
		if (nextItem == "-") prefix = "-";
		if (nextItem == "+") prefix = "+";
		if (nextItem == "*") prefix = "x";
		
		$(computeElements[computeIndex]).text(prefix + itemValue);
		++computeIndex;
	}
	
	if (stack.length <= 0) error = "ERR";
	
	if (error == "")
	{
		var result = stack.pop();
		var display = stat.display;
		
		inputValues[statId] = result;
		
		if (display == "percent")
		{
			result = Math.round(result*1000)/10 + "%";
		}
		
		valueElement.text(result);
	}
	else
	{
		inputValues[statId] = error;
		valueElement.text(error);
	}
}


function CreateEsoComputedStat(statId, stat)
{
	var element;
	
	element = $("<div/>").attr("id", "esoidStat_" + statId).
		attr("statid", statId).
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
		else if (computeItem == "-")
		{
		}
		else
		{
			var prefix = "";
			if (nextItem == "+") prefix = "+";
			if (nextItem == "-") prefix = "-";
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
	
	//if (value == "")$this.val("0");
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
	
	//if (value == "")$this.val("0");
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


function OnEsoRaceChange(e)
{
	UpdateEsoComputedStatsList();
}


function OnEsoClassChange(e)
{
	UpdateEsoComputedStatsList();
}


function OnEsoMundusChange(e)
{
	UpdateEsoComputedStatsList();
}


function OnEsoClickItem(e)
{
	var $this = $(this);
	var id = $this.attr("id");
	
	SelectEsoItem($this);
}


function SelectEsoItem(element)
{
	var itemType = element.attr("itemtype");
	var equipType = element.attr("equiptype");
	var selectElement = $("#esotbItemSelect");
	var offset = element.offset();
	
	$("#esotbItemSelectTitle").text("Select Item")
		
	selectElement.offset({left: offset.left, top: offset.top - 100 });
	selectElement.show();
}


function ShowEsoBuildClickWall(parentElement)
{
	$("#esotbClickWall").show();
	g_EsoBuildClickWallLinkElement = parentElement;
}


function HideEsoBuildClickWall()
{
	$("#esotbClickWall").hide();
	
	if (g_EsoBuildClickWallLinkElement != null)
	{
		g_EsoBuildClickWallLinkElement.hide();
		g_EsoBuildClickWallLinkElement = null;
	}
}


function OnEsoClickBuildWall(e)
{
	HideEsoBuildClickWall();
}


function OnEsoClickComputeItems(e)
{
	var parent = $(this).parent(".esotbStatRow");
	var statId = parent.attr("statid");
	
	if (statId == null || statId == "") return false;
	
	return ShowEsoFormulaPopup(statId);
}


function ConvertEsoFormulaToPrefix(computeItems)
{
	var equation = "";
	var stack = [];
	var lastOperator = "";
	
	for (var key in computeItems)
	{
		var computeItem = computeItems[key];
		var operator = "";
		
		if (computeItem == "*" || computeItem == "+" || computeItem == "-")
		{
			operator = computeItem;
		}
		else
		{
			stack.push(computeItem);
			continue;
		}
		
		if (stack.length < 2)
		{
			equation += " ERR ";
			continue;
		}
		
		var op1 = stack.pop();
		var op2 = stack.pop();
		
		if (operator == "*")
		{
			if (lastOperator == "*")
				stack.push("" + op2 + "" + operator + "(" + op1 + ")");
			else
				stack.push("(" + op2 + ")" + operator + "(" + op1 + ")");
		}
		else
			stack.push("" + op2 + " " + operator + " " + op1 + "");
		
		lastOperator = operator;
	}
	
	if (stack.length != 1) return "ERR";
	return stack.pop();
}


function ShowEsoFormulaPopup(statId)
{
	var formulaPopup = $("#esotbFormulaPopup");
	var stat = g_EsoComputedStats[statId];
	
	if (stat == null) return false;

	var equation = ConvertEsoFormulaToPrefix(stat.compute);
	
	$("#esotbFormulaTitle").text("Complete Formula for " + stat.title);
	$("#esotbFormulaName").text(statId + " = ");
	$("#esotbFormula").text(equation);
	
	formulaPopup.show();
	ShowEsoBuildClickWall(formulaPopup);
	
	return true;
}


function CloseEsoFormulaPopup()
{
	$("#esotbFormulaPopup").hide();
	HideEsoBuildClickWall();
}


function OnEsoClickBuildStatTab(e)
{
	var tabId = $(this).attr("tabid");
	if (tabId == null || tabId == "") return;
	
	$(".esotbStatTabSelected").removeClass("esotbStatTabSelected");
	$(this).addClass("esotbStatTabSelected");
	
	$(".esotbStatBlock:visible").hide();
	$("#" + tabId).show();
}


function esotbOnDocReady()
{
	UpdateEsoComputedStatsList();
	
	$(".esotbInputValue").on('input', function(e) { OnEsoInputChange.call(this, e); });
	$("#esotbRace").change(OnEsoRaceChange)
	$("#esotbClass").change(OnEsoClassChange)
	$("#esotbMundus").change(OnEsoMundusChange)
	$(".esotbStatComputeButton").click(OnEsoToggleStatComputeItems);
	
	$(".esotbItem").click(OnEsoClickItem)
	$(".esotbComputeItems").click(OnEsoClickComputeItems);

	$("#esotbFormulaCloseButton").click(CloseEsoFormulaPopup);
	$("#esotbClickWall").click(OnEsoClickBuildWall);
	
	$(".esotbStatTab").click(OnEsoClickBuildStatTab);
}


$( document ).ready(esotbOnDocReady);