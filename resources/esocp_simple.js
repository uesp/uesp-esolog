function OnDisciplineClick(e)
{
	var id = $(this).attr('id');
	
	$(".esovcpDiscHighlight").removeClass("esovcpDiscHighlight");
	$(this).addClass("esovcpDiscHighlight");
	
	$(".esovcpDiscSkills").hide();
	$("#skills_" + id).show();
	
	UpdateCPLink();
}


function OnPlusButtonClick(e)
{
	var skillId = $(this).attr('skillid');
	var inputControl = $(".esovcpPointInput[skillid='" + skillId + "']");
	
	if (inputControl.length == 0) return;
	
	var value = (parseInt(inputControl.val()) || 0) + 1;
	var disciplineId = $(this).closest(".esovcpDiscSkills").attr("disciplineid");
	
	if (value > 100) value = 100;
	inputControl.val(value);
	
	UpdateSkillDesc(skillId, value);
	UpdateDiscPoints(disciplineId);
}


function OnMinusButtonClick(e)
{
	var skillId = $(this).attr('skillid');
	var inputControl = $(".esovcpPointInput[skillid='" + skillId + "']");
	
	if (inputControl.length == 0) return;
	
	var value = (parseInt(inputControl.val()) || 0) - 1;
	var disciplineId = $(this).closest(".esovcpDiscSkills").attr("disciplineid");
	
	if (value < 0) value = 0;
	inputControl.val(value);
	
	UpdateSkillDesc(skillId, value);
	UpdateDiscPoints(disciplineId);
}


function OnPointInputChange(e)
{
	var skillId = $(this).attr('skillid');
	var value = parseInt($(this).val()) || 0;
	var disciplineId = $(this).closest(".esovcpDiscSkills").attr("disciplineid");
	
	if (value < 0) value = 0;
	if (value > 100) value = 100;
	
	UpdateSkillDesc(skillId, value);
	UpdateDiscPoints(disciplineId);
}


function OnPointInputScrollUp(e)
{
	var skillId = $(this).attr('skillid');
	var inputControl = $(this).find("input");
	
	if (inputControl.length == 0) return;
	
	var value = (parseInt(inputControl.val()) || 0) + 1;
	var disciplineId = $(this).closest(".esovcpDiscSkills").attr("disciplineid");
	
	if (value < 0) value = 0;
	if (value > 100) value = 100;
	
	inputControl.val(value);
	UpdateSkillDesc(skillId, value);
	UpdateDiscPoints(disciplineId);
}


function OnPointInputScrollDown(e)
{
	var skillId = $(this).attr('skillid');
	var inputControl = $(this).find("input");
	
	if (inputControl.length == 0) return;
	
	var value = (parseInt(inputControl.val()) || 0) - 1;
	var disciplineId = $(this).closest(".esovcpDiscSkills").attr("disciplineid");
	
	if (value < 0) value = 0;
	if (value > 100) value = 100;
	
	inputControl.val(value);
	UpdateSkillDesc(skillId, value);
	UpdateDiscPoints(disciplineId);
}


function UpdateSkillDesc(skillId, points)
{
	var descControl = $("#descskill_" + skillId);
	var desc = g_SkillDesc[skillId][points];
	
	descControl.html(desc);
}


function UpdateDiscPoints(discId)
{
	var skillInputs = $("#skills_" + discId + " .esovcpPointInput");
	var totalPoints = 0;
	var attributeIndex = $("#" + discId).parent().attr("attributeindex");
	
	skillInputs.each(function() {
		totalPoints += parseInt($(this).val()) || 0;
    });
	
	$("#skills_" + discId + " .esovcpDiscTitlePoints").text(totalPoints);
	$("#" + discId + " .esovcpDiscPoints").text(totalPoints);	
	
	UpdateDiscAttrPoints(attributeIndex);
	UpdateUnlockLevels(discId);
}


function UpdateDiscAttrPoints(attributeIndex)
{
	var discPoints = $(".esovcpDiscAttrGroup[attributeindex='" + attributeIndex + "'] .esovcpDiscPoints");
	var totalPoints = 0;
	
	discPoints.each(function() {
		totalPoints += parseInt($(this).text()) || 0;
    });
	
	$(".esovcpDiscAttrPoints[attributeindex='" + attributeIndex + "']").text(totalPoints);
	
	UpdateTotalCPPoints();
}


function UpdateTotalCPPoints()
{	var attrPoints = $(".esovcpDiscAttrPoints");
	var totalPoints = 0;
	
	attrPoints.each(function() {
		totalPoints += parseInt($(this).text()) || 0;
	});

	$(".esovcpTotalPoints").text(totalPoints + " CP");
	
	UpdateCPLink();
}


function EncodeSkillData64(skillData)
{
	var rawData = new Uint8Array(skillData);
	var result = btoa(String.fromCharCode.apply(null, rawData));
	
	return encodeURIComponent(result);
}


function DecodeSkillData64(rawData)
{
	var result = new Uint8Array(atob(decodeURIComponent(rawData)).split("").map(function(c) {
		return c.charCodeAt(0); 
	}));
	
	return result;
}


function UpdateCPLink()
{
	var link = $("#esovcpLinkBlock");
	var inputControls = $(".esovcpPointInput");
	var cpQueryData = "";
	var selectDiscId = $(".esovcpDiscHighlight").attr("id");
	var skillData = [];
	
	inputControls.each(function() {
		var disciplineIndex = parseInt($(this).attr("disciplineIndex"));
		var skillIndex = parseInt($(this).attr("skillIndex")) || 0;
		var index = (disciplineIndex - 1) * 4 + skillIndex - 1;
		
		skillData[index] = parseInt($(this).val()) || 0;
	});
	
	cpQueryData = EncodeSkillData64(skillData);
	if (selectDiscId != "") cpQueryData += "&disc=" + selectDiscId;
	
	link.attr("href", "?cp=" + cpQueryData);
}


function UpdateUnlockLevels(discId)
{
	var points = parseInt($("#skills_" + discId + " .esovcpDiscTitlePoints").text()) || 0;
	var passives = $("#skills_" + discId + " .esovcpSkillLevel");
	
	passives.each(function() {
		var unlockLevel = $(this).parent().attr("unlocklevel");
		
		if (unlockLevel <= points)
		{
			$(this).addClass("esovcpPassiveUnlocked");
		}
		else
		{
			$(this).removeClass("esovcpPassiveUnlocked");
		}
	});
}


function OnCPSearch(e)
{
	var text = $("#esovcpSearchText").val().trim();
	DoEsoCPSearch(text);
}


var g_EsoCPSearchText = "";
var g_EsoCPSearchLastIndex = -1;


function DoEsoCPSearch(text)
{
	var newSearch = false;
	
	text = text.toLowerCase();
		
	if (text != g_EsoCPSearchText) 
	{
		g_EsoCPSearchText = text;
		g_EsoCPSearchLastIndex = -1;
		newSearch = true;
	}
	
	$(".esovcpHighlightSkill").removeClass("esovcpHighlightSkill");
	
	var result = FindNextEsoCPText();
	
	if (result == null)
	{
		$("#esovcpSearchResult").text("No matches found!");
		return false;
	}
	
	$("#esovcpSearchResult").text("Found match! Search again for next match...");
	SelectCPSkillElement(result);
	
	return true;
}


function SelectCPSkillElement(element)
{
	if (element == null || element.length == 0) return;
	
	var parent = element.parent();
	var skillId = parseInt(element.attr("skillid")) || 0;
	if (skillId <= 0) return;
	
	element.addClass("esovcpHighlightSkill");
	
	if (!parent.is(':visible'))
	{
		var discId = parent.attr("disciplineid");
		$(".esovcpDiscSkills:visible").hide();
		parent.show();
		
		$(".esovcpDiscHighlight").removeClass("esovcpDiscHighlight");
		$("#" + discId).addClass("esovcpDiscHighlight");
	}
}


function FindNextEsoCPText()
{
	var searchElements = $(".esovcpSkill");
	var searchText = g_EsoCPSearchText.toLowerCase();
	
	for (var i = g_EsoCPSearchLastIndex + 1; i < searchElements.length; ++i)
	{
		var element = $(searchElements[i]);
		var index = element.text().toLowerCase().indexOf(searchText);
		
		if (index >= 0)
		{
			g_EsoCPSearchLastIndex = i;
			return element;
		}
	}
	
	g_EsoCPSearchLastIndex = -1;
	return null;
}


function esovcpOnDocReady()
{
	$(".esovcpDiscipline").click(OnDisciplineClick);
	$(".esovcpMinusButton").click(OnMinusButtonClick);
	$(".esovcpPlusButton").click(OnPlusButtonClick);
	$(".esovcpPointInput").on('input', function(e) { OnPointInputChange.call(this, e); });
		
	$(".esovcpSkill").bind('mousewheel DOMMouseScroll', function(e) { 
		if (e.originalEvent.wheelDelta > 0 || e.originalEvent.detail < 0) {
	        OnPointInputScrollUp.call(this, e);
	    }
	    else {
	    	OnPointInputScrollDown.call(this, e);
	    }
	});
	
	$("#esovcpSearchText").on("keypress", function(e) {
		if ( e.keyCode == 13 ) OnCPSearch(e); 
	});
	$("#esovcpSearchButton").click(OnCPSearch);
	
	UpdateDiscPoints('the_lord');
	UpdateDiscPoints('the_lady');
	UpdateDiscPoints('the_steed');
	UpdateDiscPoints('the_ritual');
	UpdateDiscPoints('the_atronach');
	UpdateDiscPoints('the_apprentice');
	UpdateDiscPoints('the_shadow');
	UpdateDiscPoints('the_lover');
	UpdateDiscPoints('the_tower');
}


$( document ).ready(esovcpOnDocReady);