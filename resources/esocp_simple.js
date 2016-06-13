var g_EsoCpDisableUpdates = false;
var g_EsoCpData = {};


function UpdateEsoCpData()
{
	var cpSkills = $(".esovcpSkill");
	var cpDiscs = $(".esovcpDiscipline");
	var cpAttributes = $(".esovcpDiscAttrPoints");
	
	cpSkills.each(function(){
		var $this = $(this);
		
		var skillId = $this.attr("skillid");
		if (skillId == null || skillId == "") return;
		
		var skillName = $this.find(".esovcpSkillName").text();
		
		if (g_EsoCpData[skillId] == null) 
		{
			g_EsoCpData[skillId] = {};
			g_EsoCpData[skillId].id = skillId;
			g_EsoCpData[skillId].name = skillName;
			g_EsoCpData[skillId].discipline = $this.parent().attr("disciplineid");
			g_EsoCpData[skillId].unlockLevel = $this.attr("unlockLevel");
			
			g_EsoCpData[skillName] = g_EsoCpData[skillId];
		}
		
		g_EsoCpData[skillId].points = $this.find(".esovcpPointInput").val();
		g_EsoCpData[skillId].isUnlocked = ($this.attr("unlocked") != 0);		
	});
		
	cpDiscs.each(function(){
		var $this = $(this);
		
		var discId = $this.attr("id");
		if (discId == null || discId == "") return;
		
		if (g_EsoCpData[discId] == null)
		{
			g_EsoCpData[discId] = {};
			g_EsoCpData[discId].index = $this.attr("disciplineindex");
			g_EsoCpData[discId].attribute = $this.parent().attr("attributeindex");
		}
		
		g_EsoCpData[discId].points = $this.find(".esovcpDiscPoints").text();
	});
	
	g_EsoCpData["totalPoints"] = 0;
	
	cpAttributes.each(function() {
		var $this = $(this);
		
		var attributeIndex = $this.attr("attributeindex");
		if (attributeIndex == null || attributeIndex == "") return;
		var attributeId = "attribute" + attributeIndex;
		
		if (g_EsoCpData[attributeId] == null)
		{
			g_EsoCpData[attributeId] = {};
			g_EsoCpData[attributeId].index = attributeIndex;
		}
		
		g_EsoCpData[attributeId].points = parseInt($this.text());
		g_EsoCpData["totalPoints"] += g_EsoCpData[attributeId].points;
	});
}


function OnEsoCpDisciplineClick(e)
{
	var id = $(this).attr('id');
	
	$(".esovcpDiscHighlight").removeClass("esovcpDiscHighlight");
	$(this).addClass("esovcpDiscHighlight");
	
	$(".esovcpDiscSkills").hide();
	$("#skills_" + id).show();
	
	UpdateEsoCPLink();
	UpdateEsoCpData();
}


function OnEsoCPPlusButtonClick(e)
{
	var skillId = $(this).attr('skillid');
	var inputControl = $(".esovcpPointInput[skillid='" + skillId + "']");
	
	if (inputControl.length == 0) return;
	
	var value = (parseInt(inputControl.val()) || 0) + 1;
	var disciplineId = $(this).closest(".esovcpDiscSkills").attr("disciplineid");
	
	if (value > 100) value = 100;
	inputControl.val(value);
	
	UpdateEsoCPSkillDesc(skillId, value);
	UpdateEsoCPDiscPoints(disciplineId);
}


function OnEsoCPMinusButtonClick(e)
{
	var skillId = $(this).attr('skillid');
	var inputControl = $(".esovcpPointInput[skillid='" + skillId + "']");
	
	if (inputControl.length == 0) return;
	
	var value = (parseInt(inputControl.val()) || 0) - 1;
	var disciplineId = $(this).closest(".esovcpDiscSkills").attr("disciplineid");
	
	if (value < 0) value = 0;
	inputControl.val(value);
	
	UpdateEsoCPSkillDesc(skillId, value);
	UpdateEsoCPDiscPoints(disciplineId);
}


function OnEsoCPPointInputChange(e)
{
	var skillId = $(this).attr('skillid');
	var value = parseInt($(this).val()) || 0;
	var disciplineId = $(this).closest(".esovcpDiscSkills").attr("disciplineid");
	
	if (value < 0) value = 0;
	if (value > 100) value = 100;
	
	UpdateEsoCPSkillDesc(skillId, value);
	UpdateEsoCPDiscPoints(disciplineId);
}


function OnEsoCPPointInputScrollUp(e)
{
	var skillId = $(this).attr('skillid');
	var inputControl = $(this).find("input");
	
	if (inputControl.length == 0) return;
	
	var value = (parseInt(inputControl.val()) || 0) + 1;
	var disciplineId = $(this).closest(".esovcpDiscSkills").attr("disciplineid");
	
	if (value < 0) value = 0;
	if (value > 100) value = 100;
	
	inputControl.val(value);
	UpdateEsoCPSkillDesc(skillId, value);
	UpdateEsoCPDiscPoints(disciplineId);
	
	e.preventDefault();
}


function OnEsoCPPointInputScrollDown(e)
{
	var skillId = $(this).attr('skillid');
	var inputControl = $(this).find("input");
	
	if (inputControl.length == 0) return;
	
	var value = (parseInt(inputControl.val()) || 0) - 1;
	var disciplineId = $(this).closest(".esovcpDiscSkills").attr("disciplineid");
	
	if (value < 0) value = 0;
	if (value > 100) value = 100;
	
	inputControl.val(value);
	UpdateEsoCPSkillDesc(skillId, value);
	UpdateEsoCPDiscPoints(disciplineId);
	
	e.preventDefault();
}


function UpdateEsoCPSkillDesc(skillId, points)
{
	var descControl = $("#descskill_" + skillId);
	
	if (g_EsoCpSkillDesc[skillId] == null) return;
	var desc = g_EsoCpSkillDesc[skillId][points];
	
	descControl.html(desc);
}


function UpdateEsoCPDiscSkillDesc(discId)
{
	var discElement = $("#skills_" + discId);
	
	discElement.find(".esovcpSkill").each(function(i, element){
		var skillId = $(this).attr("skillid");
		if (skillId == null || skillId == "") return;
		var points = $(this).find(".esovcpPointInput").val();
		UpdateEsoCPSkillDesc(skillId, points);
	});
}


function UpdateEsoCPDiscPoints(discId)
{
	var skillInputs = $("#skills_" + discId + " .esovcpPointInput");
	var totalPoints = 0;
	var attributeIndex = $("#" + discId).parent().attr("attributeindex");
	
	skillInputs.each(function() {
		totalPoints += parseInt($(this).val()) || 0;
    });
	
	$("#skills_" + discId + " .esovcpDiscTitlePoints").text(totalPoints);
	$("#" + discId + " .esovcpDiscPoints").text(totalPoints);	
	
	UpdateEsoCPUnlockLevels(discId);
	UpdateEsoCPDiscAttrPoints(attributeIndex);	
}


function UpdateEsoCPDiscAttrPoints(attributeIndex)
{
	var discPoints = $(".esovcpDiscAttrGroup[attributeindex='" + attributeIndex + "'] .esovcpDiscPoints");
	var totalPoints = 0;
	
	discPoints.each(function() {
		totalPoints += parseInt($(this).text()) || 0;
    });
	
	$(".esovcpDiscAttrPoints[attributeindex='" + attributeIndex + "']").text(totalPoints);
	
	UpdateEsoCPTotalCPPoints();
}


function UpdateEsoCPTotalCPPoints()
{	var attrPoints = $(".esovcpDiscAttrPoints");
	var totalPoints = 0;
	
	attrPoints.each(function() {
		totalPoints += parseInt($(this).text()) || 0;
	});

	$(".esovcpTotalPoints").text(totalPoints + " CP");
	
	UpdateEsoCPLink();
	UpdateEsoCpData();
	
	if (!g_EsoCpDisableUpdates) $( document ).trigger("esocpUpdate");
}


function EncodeEsoCPSkillData64(skillData)
{
	var rawData = new Uint8Array(skillData);
	var result = btoa(String.fromCharCode.apply(null, rawData));
	
	return encodeURIComponent(result);
}


function DecodeEsoCPSkillData64(rawData)
{
	var result = new Uint8Array(atob(decodeURIComponent(rawData)).split("").map(function(c) {
		return c.charCodeAt(0); 
	}));
	
	return result;
}


function UpdateEsoCPLink()
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
	
	cpQueryData = EncodeEsoCPSkillData64(skillData);
	if (selectDiscId != "") cpQueryData += "&disc=" + selectDiscId;
	
	link.attr("href", "?cp=" + cpQueryData);
}


function UpdateEsoCPUnlockLevels(discId)
{
	var points = parseInt($("#skills_" + discId + " .esovcpDiscTitlePoints").text()) || 0;
	var passives = $("#skills_" + discId + " .esovcpSkillLevel");
	
	passives.each(function() {
		var unlockLevel = $(this).parent().attr("unlocklevel");
		
		if (unlockLevel <= points)
		{
			$(this).parent().attr("unlocked", "1");
			$(this).addClass("esovcpPassiveUnlocked");
		}
		else
		{
			$(this).parent().attr("unlocked", "0");
			$(this).removeClass("esovcpPassiveUnlocked");
		}
	});
}


function OnEsoCPSearch(e)
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
	SelectEsoCPSkillElement(result);
	
	return true;
}


function SelectEsoCPSkillElement(element)
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


function OnEsoCPResetAll(e)
{
	$(".esovcpPointInput").val(0);
	EsoCpUpdateAll();
}


function OnEsoCPResetDisc(e)
{
	var parent = $(this).parent();
	var discId = parent.attr("disciplineid");
	if (discId == null || discId == "") return;
	
	parent.find(".esovcpPointInput").val("0");
	
	UpdateEsoCPDiscSkillDesc(discId);
	UpdateEsoCPDiscPoints(discId);
}


function EsoCpUpdateAll()
{
	UpdateEsoCPDiscSkillDesc('the_lord');
	UpdateEsoCPDiscSkillDesc('the_lady');
	UpdateEsoCPDiscSkillDesc('the_steed');
	UpdateEsoCPDiscSkillDesc('the_ritual');
	UpdateEsoCPDiscSkillDesc('the_atronach');
	UpdateEsoCPDiscSkillDesc('the_apprentice');
	UpdateEsoCPDiscSkillDesc('the_shadow');
	UpdateEsoCPDiscSkillDesc('the_lover');
	UpdateEsoCPDiscSkillDesc('the_tower');
	
	g_EsoCpDisableUpdates = true;
	
	UpdateEsoCPDiscPoints('the_lord');
	UpdateEsoCPDiscPoints('the_lady');
	UpdateEsoCPDiscPoints('the_steed');
	UpdateEsoCPDiscPoints('the_ritual');
	UpdateEsoCPDiscPoints('the_atronach');
	UpdateEsoCPDiscPoints('the_apprentice');
	UpdateEsoCPDiscPoints('the_shadow');
	UpdateEsoCPDiscPoints('the_lover');
	
	g_EsoCpDisableUpdates = false;
	UpdateEsoCPDiscPoints('the_tower');
}


function esovcpOnDocReady()
{
	$(".esovcpDiscipline").click(OnEsoCpDisciplineClick);
	$(".esovcpMinusButton").click(OnEsoCPMinusButtonClick);
	$(".esovcpPlusButton").click(OnEsoCPPlusButtonClick);
	$(".esovcpPointInput").on('input', function(e) { OnEsoCPPointInputChange.call(this, e); });
		
	$(".esovcpSkill").bind('mousewheel DOMMouseScroll', function(e) { 
		if (e.originalEvent.wheelDelta > 0 || e.originalEvent.detail < 0) {
	        OnEsoCPPointInputScrollUp.call(this, e);
	    }
	    else {
	    	OnEsoCPPointInputScrollDown.call(this, e);
	    }
	});
	
	$("#esovcpSearchText").on("keypress", function(e) {
		if ( e.keyCode == 13 ) OnEsoCPSearch(e); 
	});
	$("#esovcpSearchButton").click(OnEsoCPSearch);
	
	$("#esotvcpResetCP").click(OnEsoCPResetAll);
	$(".esotvcpResetDisc").click(OnEsoCPResetDisc);
	
	EsoCpUpdateAll();
}


$( document ).ready(esovcpOnDocReady);