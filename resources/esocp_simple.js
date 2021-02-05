window.g_EsoCpDisableUpdates = false;
window.g_EsoCpData = {};

window.g_EsoCPSearchText = "";
window.g_EsoCPSearchLastIndex = -1;


window.UpdateEsoCpData = function()
{
	var cpSkills = $(".esovcpSkill");
	var cpDiscs = $(".esovcpDiscipline");
	var cpAttributes = $(".esovcpDiscAttrPoints");
	var cp2TopLevelDisc = $(".esovcp2Discipline:visible").not(".esovcp2DiscCluster");
	var discSlotIndexes = {};
	
	cpSkills.each(function(){
		var $this = $(this);
		
		var skillId = $this.attr("skillid");
		if (skillId == null || skillId == "") return;
		
		var skillName = $this.find(".esovcpSkillName").text();
		var $parent = $this.parent();
		var discIndex = parseInt($parent.attr("disciplineindex"));
		var skillType = $this.attr("skilltype");
		
		if (g_EsoCpData[skillId] == null) 
		{
			g_EsoCpData[skillId] = {};
			g_EsoCpData[skillId].type = "skill";
			g_EsoCpData[skillId].id = skillId;
			g_EsoCpData[skillId].name = skillName;
			g_EsoCpData[skillId].discipline = $parent.attr("disciplineid");
			g_EsoCpData[skillId].unlockLevel = $this.attr("unlockLevel");
			g_EsoCpData[skillId].slotIndex = 0;
			g_EsoCpData[skillName] = g_EsoCpData[skillId];
		}
		
		g_EsoCpData[skillId].points = $this.find(".esovcpPointInput").val();
		g_EsoCpData[skillId].isUnlocked = ($this.attr("unlocked") != 0);
		g_EsoCpData[skillId].description = $this.find(".esovcpSkillDesc").text();
		g_EsoCpData[skillId].slotIndex = 0;
		
		if (skillType > 0) 
		{
			var isEquipped = $this.find(".esovcpEquipCheck").is(":checked");
			
			if (isEquipped)
			{
				if (discSlotIndexes[discIndex] == null) discSlotIndexes[discIndex] = 0;
				var slotIndex = (discIndex - 1) * 4 + 1 + discSlotIndexes[discIndex];
				++discSlotIndexes[discIndex];
				g_EsoCpData[skillId].slotIndex = slotIndex;
			}
		}

	});
		
	cpDiscs.each(function(){
		var $this = $(this);
		
		var discId = $this.attr("id");
		if (discId == null || discId == "") return;
		
		if (g_EsoCpData[discId] == null)
		{
			g_EsoCpData[discId] = {};
			g_EsoCpData[discId].type = "discipline";
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
			g_EsoCpData[attributeId].type = "attribute";
			g_EsoCpData[attributeId].index = attributeIndex;
		}
		
		g_EsoCpData[attributeId].points = parseInt($this.text());
		g_EsoCpData["totalPoints"] += g_EsoCpData[attributeId].points;
	});
	
	cp2TopLevelDisc.each(function() {
		var $this = $(this);
		var discIndex = $this.attr("disciplineindex");
		if (discIndex == null || discIndex == "") return;
		var discId = "attribute" + discIndex;
		
		if (g_EsoCpData[discId] == null)
		{
			g_EsoCpData[discId] = {};
			g_EsoCpData[discId].type = "attribute";
			g_EsoCpData[discId].index = discIndex;
		}
		
		g_EsoCpData[discId].points = parseInt($this.children(".esovcpDiscPoints").text());
		g_EsoCpData["totalPoints"] += g_EsoCpData[discId].points;
	});
}


window.OnEsoCpDisciplineClick = function (e)
{
	var id = $(this).attr('id');
	
	$(".esovcpDiscHighlight").removeClass("esovcpDiscHighlight");
	$(this).addClass("esovcpDiscHighlight");
	
	$(".esovcpDiscSkills").hide();
	$("#skills_" + id).show();
	
	UpdateEsoCPLink();
	UpdateEsoCpData();
}


window.OnEsoCPPlusButtonClick = function (e)
{
	var isShift = false;
	if (e.shiftKey) isShift = true;
	
	var skillId = $(this).attr('skillid');
	var inputControl = $(".esovcpPointInput[skillid='" + skillId + "']");
	
	if (inputControl.length == 0) return;
	
	var value = (parseInt(inputControl.val()) || 0);
	var disciplineId = $(this).closest(".esovcpDiscSkills").attr("disciplineid");
	var maxPoints = inputControl.attr("maxpoints");
	
	if (maxPoints == null || maxPoints == "")
		maxPoints = 100;
	else
		maxPoints = parseInt(maxPoints);
	
	if (isShift)
		value += 10;
	else
		value += 1;
	
	if (value > maxPoints) value = maxPoints;
	inputControl.val(value);
	
	UpdateEsoCPSkillDesc(skillId, value);
	UpdateEsoCPDiscPoints(disciplineId);
}


window.OnEsoCPMinusButtonClick = function (e)
{
	var isShift = false;
	if (e.shiftKey) isShift = true;
	
	var skillId = $(this).attr('skillid');
	var inputControl = $(".esovcpPointInput[skillid='" + skillId + "']");
	
	if (inputControl.length == 0) return;
	
	var value = (parseInt(inputControl.val()) || 0);
	var disciplineId = $(this).closest(".esovcpDiscSkills").attr("disciplineid");
	
	if (isShift)
		value -= 10;
	else
		value -= 1;
	
	if (value < 0) value = 0;
	inputControl.val(value);
	
	UpdateEsoCPSkillDesc(skillId, value);
	UpdateEsoCPDiscPoints(disciplineId);
}


window.OnEsoCPPointInputChange = function (e)
{
	var skillId = $(this).attr('skillid');
	var value = parseInt($(this).val()) || 0;
	var disciplineId = $(this).closest(".esovcpDiscSkills").attr("disciplineid");
	var maxPoints =  $(this).attr("maxpoints");
	
	if (maxPoints == null || maxPoints == "")
		maxPoints = 100;
	else
		maxPoints = parseInt(maxPoints);
	
	if (value < 0) value = 0;
	if (value > maxPoints) value = maxPoints;
	
	UpdateEsoCPSkillDesc(skillId, value);
	UpdateEsoCPDiscPoints(disciplineId);
}


window.OnEsoCPPointInputScrollUp = function (e)
{
	var inputControl = $(this);
	var skillId = inputControl.parent().parent().attr('skillid');
	
	var value = (parseInt(inputControl.val()) || 0) + 1;
	var disciplineId = $(this).closest(".esovcpDiscSkills").attr("disciplineid");
	
	var maxPoints =  inputControl.attr("maxpoints");
	
	if (maxPoints == null || maxPoints == "")
		maxPoints = 100;
	else
		maxPoints = parseInt(maxPoints);
	
	if (value < 0) value = 0;
	if (value > maxPoints) value = maxPoints;
	
	inputControl.val(value);
	UpdateEsoCPSkillDesc(skillId, value);
	UpdateEsoCPDiscPoints(disciplineId);
	
	e.preventDefault();
}


window.OnEsoCPPointInputScrollDown = function (e)
{
	var inputControl = $(this);
	var skillId = inputControl.parent().parent().attr('skillid');
	
	var value = (parseInt(inputControl.val()) || 0) - 1;
	var disciplineId = $(this).closest(".esovcpDiscSkills").attr("disciplineid");
	
	var maxPoints =  inputControl.attr("maxpoints");
	
	if (maxPoints == null || maxPoints == "")
		maxPoints = 100;
	else
		maxPoints = parseInt(maxPoints);
	
	if (value < 0) value = 0;
	if (value > maxPoints) value = maxPoints;
	
	inputControl.val(value);
	UpdateEsoCPSkillDesc(skillId, value);
	UpdateEsoCPDiscPoints(disciplineId);
	
	e.preventDefault();
}


window.UpdateEsoCPSkillDesc = function(skillId, points)
{
	var descControl = $("#descskill_" + skillId);
	
	if (g_EsoCpSkillDesc[skillId] == null) return;
	var desc = g_EsoCpSkillDesc[skillId][points];
	
	descControl.html(desc);
	
	if (skillId == esovcpTooltipSkillId) {
		esovcpUpdateTooltip();
		//$("#esovcp2TooltipDesc").html(desc.replace("\n", "<p/>"));
	}
}


window.UpdateEsoCPDiscSkillDesc = function(discId)
{
	var discElement = $("#skills_" + discId);
	
	discElement.find(".esovcpSkill").each(function(i, element){
		var skillId = $(this).attr("skillid");
		if (skillId == null || skillId == "") return;
		var points = $(this).find(".esovcpPointInput").val();
		UpdateEsoCPSkillDesc(skillId, points);
	});
}


window.UpdateEsoCPDiscPoints = function(discId)
{
	var skillInputs = $("#skills_" + discId + " .esovcpPointInput");
	var totalPoints = 0;
	var attributeIndex = $("#" + discId).parent().attr("attributeindex");
	var discIndex = $("#" + discId).parent().attr("disciplineindex");
	
	skillInputs.each(function() {
		totalPoints += parseInt($(this).val()) || 0;
    });
	
	$("#skills_" + discId + " .esovcpDiscTitlePoints").text(totalPoints);
	$("#" + discId + " .esovcpDiscPoints").text(totalPoints);
	$("#" + discId + "_base .esovcpDiscPoints").text(totalPoints);
	
	UpdateEsoCPUnlockLevels(discId);
	UpdateEsoCPDiscAttrPoints(attributeIndex, discIndex);
}


window.UpdateEsoCPDiscAttrPoints = function(attributeIndex, discIndex)
{
	if (window.g_EsoCpIsV2) 
	{
		var discPoints = $(".esovcp2Discipline[clusterindex='" + discIndex + "'] .esovcpDiscPoints")
		var totalPoints = 0;
		
		discPoints.each(function() {
			totalPoints += parseInt($(this).text()) || 0;
		});
		
		$(".esovcp2Discipline[disciplineindex='" + discIndex + "'] .esovcpDiscPoints").text(totalPoints);
		UpdateEsoCPTotalCPPoints();
		return;
	}
	
	var discPoints = $(".esovcpDiscAttrGroup[attributeindex='" + attributeIndex + "'] .esovcpDiscPoints");
	var totalPoints = 0;
	
	discPoints.each(function() {
		totalPoints += parseInt($(this).text()) || 0;
    });
	
	$(".esovcpDiscAttrPoints[attributeindex='" + attributeIndex + "']").text(totalPoints);
	
	UpdateEsoCPTotalCPPoints();
}


window.UpdateEsoCPTotalCPPoints = function()
{
	if (window.g_EsoCpIsV2) 
	{
		var attrPoints = $(".esovcp2Discipline").not(".esovcp2DiscCluster").children(".esovcpDiscPoints");
		var totalPoints = 0;
		
		attrPoints.each(function() {
			var $this = $(this);
			var points = parseInt($this.text()) || 0;
			totalPoints += points;
		});
		
		$(".esovcpTotalPoints").text(totalPoints + " CP");
		
		UpdateEsoCPLink();
		UpdateEsoCpData();
		
		if (!g_EsoCpDisableUpdates) $( document ).trigger("esocpUpdate");
		
		return;
	}

	var attrPoints = $(".esovcpDiscAttrPoints");
	var totalPoints = 0;
	
	attrPoints.each(function() {
		totalPoints += parseInt($(this).text()) || 0;
	});

	$(".esovcpTotalPoints").text(totalPoints + " CP");
	
	UpdateEsoCPLink();
	UpdateEsoCpData();
	
	if (!g_EsoCpDisableUpdates) $( document ).trigger("esocpUpdate");
}


window.EncodeEsoCPSkillData64 = function (skillData)
{
	var rawData = new Uint8Array(skillData);
	var result = btoa(String.fromCharCode.apply(null, rawData));
	
	return encodeURIComponent(result);
}


window.DecodeEsoCPSkillData64 = function (rawData)
{
	var result = new Uint8Array(atob(decodeURIComponent(rawData)).split("").map(function(c) {
		return c.charCodeAt(0); 
	}));
	
	return result;
}


window.UpdateEsoCPLink = function()
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
	if (window.g_EsoCpVersion && g_EsoCpVersion != "") cpQueryData += "&version=" + g_EsoCpVersion;
	
	link.attr("href", "?cp=" + cpQueryData);
}


window.UpdateEsoCPUnlockLevels = function(discId)
{
	var points = parseInt($("#skills_" + discId + " .esovcpDiscTitlePoints").text()) || 0;
	var passives = $("#skills_" + discId + " .esovcpSkillLevel");
	
	passives.each(function() {
		var parent = $(this).parent(); 
		var unlockLevel = parent.attr("unlocklevel");
		
		if (unlockLevel <= points)
		{
			parent.addClass("esovcpPassiveUnlocked");
			parent.removeClass("esovcpPassiveLocked");
			parent.attr("unlocked", "1");
			$(this).addClass("esovcpPassiveUnlocked");
		}
		else
		{
			parent.addClass("esovcpPassiveLocked");
			parent.removeClass("esovcpPassiveUnlocked");
			parent.attr("unlocked", "0");
			$(this).removeClass("esovcpPassiveUnlocked");
		}
	});
	
	UpdateEsoCP2UnlockLevels(discId);
}


window.UpdateEsoCP2UnlockLevels = function(discId)
{
	var skills = $("#skills_" + discId + " .esovcpSkill");
	
	skills.each(function() {
		var $this = $(this);
		var skillType = parseInt($this.attr("skilltype"));
		var unlockLevel = parseInt($this.attr("unlocklevel"));
		var points = parseInt($this.find(".esovcpPointInput").val());
		if (isNaN(points)) points = 0;
		var isActive = true;
		
		if (skillType > 0)
		{
			var isEquipped = $this.find(".esovcpEquipCheck").is(":checked");
			if (!isEquipped) isActive = false;
		}
		
		if (!isActive || points < unlockLevel)
		{
			$this.attr("unlocked", "0");
		}
		else
		{
			$this.attr("unlocked", "1");
		}
	});
}


window.OnEsoCPSearch = function (e)
{
	var text = $("#esovcpSearchText").val().trim();
	DoEsoCPSearch(text);
}


window.DoEsoCPSearch = function (text)
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


window.SelectEsoCPSkillElement = function (element)
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


window.FindNextEsoCPText = function ()
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


window.OnEsoCPResetAll = function (e)
{
	$(".esovcpPointInput").val(0);
	$(".esovcpEquipCheck").prop("checked", false);
	
	EsoCpUpdateAll();
}


window.OnEsoCPResetDisc = function (e)
{
	var parent = $(this).parent();
	var discId = parent.attr("disciplineid");
	if (discId == null || discId == "") return;
	
	parent.find(".esovcpPointInput").val("0");
	parent.find(".esovcpEquipCheck").prop("checked", false);
	
	UpdateEsoCPDiscSkillDesc(discId);
	UpdateEsoCPDiscPoints(discId);
}


window.EsoCpUpdateAll = function ()
{
	if (window.g_EsoCpIsV2) {
		UpdateEsoCPDiscSkillDesc('warfare');
		UpdateEsoCPDiscSkillDesc('craft');
		UpdateEsoCPDiscSkillDesc('fitness');
		
		g_EsoCpDisableUpdates = true;
		
		$(".esovcp2Discipline:visible").each(function() {
			var id = $(this).attr("id");
			UpdateEsoCPDiscPoints(id);
		});
		
		g_EsoCpDisableUpdates = false;
		UpdateEsoCPDiscPoints('fitness');
		
		return;
	}
	
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


window.OnEsoCP2DisciplineClick = function()
{
	var id = $(this).attr("id");
	var skillId = "skills_" + id;
	
	//var disciplineIndex = $(this).attr("disciplineindex");
	//if (disciplineIndex == null || disciplineIndex == "") return;
	
	$(".esovcpDiscHighlight").removeClass("esovcpDiscHighlight");
	
	$(".esovcpDiscSkills:visible").hide();
	//$(".esovcpDiscSkills[disciplineindex='" + disciplineIndex + "']").show();
	$("#" + skillId).show();
	
	$(this).addClass("esovcpDiscHighlight");
}


window.esovcpTooltipRoot = null;
window.esovcpTooltipSkillId = -1;


window.esovcpShowTooltip = function(skillId, parent)
{
	if (esovcpTooltipRoot == null) esovcpTooltipRoot = $("#esovcp2Tooltip");
	
	esovcpTooltipSkillId = skillId;
	
	if (esovcpUpdateTooltip()) {
		esovcpTooltipRoot.show();
		esovcpAdjustTooltipPosition(parent);
	}
}


window.esovcpUpdateTooltip = function()
{
	var nameElement = $("#esovcp2TooltipName");
	var descElement = $("#esovcp2TooltipDesc");
	var pointsElement = $("#esovcp2TooltipPoints");
	var jumpElement = $("#esovcp2TooltipJump");
	var equipElement = $("#esovcp2TooltipEquip");
	var skillData  = g_EsoCpSkills[esovcpTooltipSkillId];
	var pointsInput = $(".esovcpPointInput[skillid='" + esovcpTooltipSkillId + "']");
	
	if (skillData == null) return false;
	if (skillData['isClusterRoot'] > 0) return false;	//TODO
	
	var points = parseInt(pointsInput.val());
	if (points == null || points < 0) points = 0;
	
	var descText = "";
	if (g_EsoCpSkillDesc[esovcpTooltipSkillId] != null) descText = g_EsoCpSkillDesc[esovcpTooltipSkillId][points];
	if (descText == null) descText = "Unknown Skill/Description!";
	descText = descText.replace("\n", "<p/>");
	
	if (esovcpTooltipSkillId <= 0) 
	{
		nameElement.text("");
		descElement.text("");
		pointsElement.text("");
		jumpElement.text("");
		return true;
	}
	
	var jumpPointDelta = parseInt(skillData['jumpPointDelta']);
	var maxPoints = skillData['maxPoints'];
	if (maxPoints == null) maxPoints = "?";
	
	nameElement.text(skillData['name'].toUpperCase());
	descElement.html(descText);
	pointsElement.html("Points: <div class='esovcpDescWhite'>" + points + " / " + maxPoints + "</div>");
	
	if (jumpPointDelta <= 1) {
		jumpElement.html("");
	}
	else {
		var nextStagePoints = parseInt(points / jumpPointDelta) * jumpPointDelta + jumpPointDelta; 
		var jumpText = "Next Stage At: <div class='esovcpDescWhite'>" + nextStagePoints + "</div> pts";
		
		if (nextStagePoints <= 0 || nextStagePoints > maxPoints) jumpText = "";
		jumpElement.html(jumpText);
	}
	
	if (parseInt(skillData['skillType']) >= 1)
		equipElement.text("Equippable").show();
	else
		equipElement.text("").hide();
	
	return true;
}


window.esovcpAdjustTooltipPosition = function(parent)
{
	var windowWidth = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
	var windowHeight = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
    var toolTipWidth = esovcpTooltipRoot.width();
    var toolTipHeight = esovcpTooltipRoot.height();
    var elementHeight = parent.height();
    var elementWidth = parent.width();
    var NARROW_WINDOW_WIDTH = 800;
     
    var top = parent.offset().top - 50;
    var left = parent.offset().left + parent.outerWidth() + 3;
    
    if (windowWidth < NARROW_WINDOW_WIDTH)
    {
    	top = parent.offset().top - 25 - toolTipHeight;
    	left = parent.offset().left - toolTipWidth/2 + elementWidth/2;
    }
     
    esovcpTooltipRoot.offset({ top: top, left: left });
     
    var viewportTooltip = esovcpTooltipRoot[0].getBoundingClientRect();
     
    if (viewportTooltip.bottom > windowHeight) 
    {
    	var deltaHeight = viewportTooltip.bottom - windowHeight + 10;
        top = top - deltaHeight;
    }
    else if (viewportTooltip.top < 0)
    {
    	var deltaHeight = viewportTooltip.top - 10;
    	
    	if (windowWidth < NARROW_WINDOW_WIDTH) deltaHeight = -toolTipHeight - elementHeight - 30;
    	
        top = top - deltaHeight;
    }
         
    if (viewportTooltip.right > windowWidth) 
    {
    	var deltaLeft = -toolTipWidth - parent.width() - 28;

    	if (windowWidth < NARROW_WINDOW_WIDTH)
    	{
    		deltaLeft = windowWidth - viewportTooltip.right - 10;
    	}
    		
    	left = left + deltaLeft;
    }
    
    if (viewportTooltip.left < 0)
    {
    	if (windowWidth < NARROW_WINDOW_WIDTH)
    		left = left - viewportTooltip.left + 10;
    	else
    		left = left;
    }
     
    esovcpTooltipRoot.offset({ top: top, left: left });
    viewportTooltip = esovcpTooltipRoot[0].getBoundingClientRect();
     
	if (viewportTooltip.left < 0 )
	{
		left = left - viewportTooltip.left + 10;
		esovcpTooltipRoot.offset({ top: top, left: left });
	}

}


window.esovcpHideTooltip = function()
{
	if (esovcpTooltipRoot) esovcpTooltipRoot.hide();
}


window.OnEsoCP2StarHoverIn = function()
{
	var parent = $(this).parent();
	var skillId = parent.attr("skillid");
	
	esovcpShowTooltip(skillId, parent)
}


window.OnEsoCP2StarHoverOut = function()
{
	esovcpHideTooltip();
}


window.OnEsoCP2SkillEquipped = function()
{
	var $this = $(this);
	var skillId = $this.attr("skillid");
	var discIndex = $this.attr("disciplineindex");
	var equippedSkills = $(".esovcpEquipCheck[disciplineindex='" + discIndex + "']:checked");
	
	if (equippedSkills.length > 4) 
	{
		//equippedSkills.not("[skillid='" + skillId + "']").first().prop('checked', false);
		$this.prop('checked', false);
		return false;
	}
	
	var discId = $this.parents(".esovcpDiscSkills").attr("disciplineid");
	
	UpdateEsoCPUnlockLevels(discId);
	UpdateEsoCPTotalCPPoints();
}


window.esovcpOnDocReady = function ()
{
	$(".esovcpDiscipline").click(OnEsoCpDisciplineClick);
	$(".esovcpMinusButton").click(OnEsoCPMinusButtonClick);
	$(".esovcpPlusButton").click(OnEsoCPPlusButtonClick);
	$(".esovcpPointInput").on('input', function(e) { OnEsoCPPointInputChange.call(this, e); });
		
	$(".esovcpPointInput").bind('mousewheel DOMMouseScroll', function(e) { 
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
	
	$(".esovcp2Discipline").click(OnEsoCP2DisciplineClick);
	$(".esovcpShowTooltip").hover(OnEsoCP2StarHoverIn, OnEsoCP2StarHoverOut);
	$(".esovcpEquipCheck").change(OnEsoCP2SkillEquipped);
	
	EsoCpUpdateAll();
}


$( document ).ready(esovcpOnDocReady);
