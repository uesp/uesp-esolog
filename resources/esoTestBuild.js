
function GetEsoInputValues()
{
	var inputValues = {};
	
	inputValues.Level = $("#esotbLevel").val();
	inputValues.AttributeHealth = $("#esotbAttrHea").val();
	inputValues.AttributeMagicka = $("#esotbAttrMag").val();
	inputValues.AttributeStamina = $("#esotbAttrSta").val();
	
	return inputValues;
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
	if (inputValues == null) inputValues = GetEsoInputValues();
	
	var element = $("#esoidStat_" + statId);
	if (element.length == 0) element = CreateEsoComputedStat(statId, stat);
	
	var valueElement = element.children(".esotbStatValue");
	var computeElements = element.find(".esotbStatComputeValue");
	var totalValue = 0;
	
	for (var i = 0; i < stat.compute.length; ++i)
	{
		var computeItem = stat.compute[i];
		var itemValue = 0;
		
		with(inputValues)
		{
			itemValue = eval(computeItem);
			totalValue += itemValue;
		}
		
		$(computeElements[i]).text((itemValue < 0 ? "" : "+") + itemValue);
	}
	
	valueElement.text(totalValue);
}


function CreateEsoComputedStat(statId, stat)
{
	var element;
	
	element = $("<div/>").attr("id", "esoidStat_" + statId)
		.addClass("esotbStatRow")
		.appendTo("#esotbStatList");

	$("<div/>").addClass("esotbStatName")
		.text(stat.title)
		.appendTo(element);
	
	$("<div/>").addClass("esotbStatValue")
		.text("?")
		.appendTo(element);
	
	var computeElement = $("<div/>").addClass("esotbComputeItems").
										appendTo(element);
		
	for (var i = 0; i < stat.compute.length; ++i)
	{
		var computeItem = stat.compute[i];
		
		$("<div/>").addClass("esotbStatComputeValue")
			.text("+0")
			.appendTo(computeElement);
		
		$("<div/>").addClass("esotbStatComputeItem")
			.text("(" + computeItem + ")")
			.appendTo(computeElement);
	}
	
	return element;
}


function esotbOnDocReady()
{
	UpdateEsoComputedStatsList();
}


$( document ).ready(esotbOnDocReady);