window.g_EsoCpDisableUpdates = false;
window.g_EsoCpData = {};

window.g_EsoCPSearchText = "";
window.g_EsoCPSearchLastIndex = -1;


window.EsoCpLog = function ()
{
	if (console == null) return;
	if (console.log == null) return;
	
	console.log.apply(console, arguments);
}


window.g_EsoCpUpdateSkillElements = null;
window.g_EsoCpUpdateDiscElements = null;
window.g_EsoCpUpdatAttributeElements = null;
window.g_EsoCpUpdateTopLevelDiscElements = null;
window.g_EsoCpUpdateEquipSlotElements = null;


window.UpdateEsoCpData = function()
{
	var cpSkills = g_EsoCpUpdateSkillElements;
	var cpDiscs = g_EsoCpUpdateDiscElements;
	var cpAttributes = g_EsoCpUpdatAttributeElements;
	var cp2TopLevelDisc = g_EsoCpUpdateTopLevelDiscElements;
	var discSlotIndexes = {};
	var equipSlotSkillIds = {};
	
	g_EsoCpUpdateEquipSlotElements.each(function() {
		var $this = $(this);
		var slotIndex = parseInt($this.attr("slotindex"));
		var skillId = parseInt($this.attr("skillid"));
		
		if (skillId > 0) equipSlotSkillIds[skillId] = slotIndex;
	});
	
	cpSkills.each(function(){
		var $this = $(this);
		
		var skillId = $this.attr("skillid");
		if (skillId == null || skillId == "") return;
		
		//var skillName = $this.find(".esovcpSkillName").text();
		var skillName = $this.children(".esovcpSkillName").text();
		var $parent = $this.parent();
		var discIndex = parseInt($parent.attr("disciplineindex"));
		var skillType = $this.attr("skilltype");
		var isPurchaseable = !$this.hasClass("esovcpNotPurchaseable");
		
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
		
		//g_EsoCpData[skillId].points = $this.find(".esovcpPointInput").val();
		g_EsoCpData[skillId].points = $this.children(".esovcpSkillControls").children(".esovcpPointInput").val();
		g_EsoCpData[skillId].isUnlocked = ($this.attr("unlocked") != 0);
		g_EsoCpData[skillId].isPurchaseable = isPurchaseable;
		//g_EsoCpData[skillId].description = $this.find(".esovcpSkillDesc").text();
		g_EsoCpData[skillId].description = $this.children(".esovcpSkillDesc").text();
		g_EsoCpData[skillId].slotIndex = 0;
		
		if (window.g_EsoCpIsV2 && !isPurchaseable && g_EsoCpSkillDesc[skillId] != null)
		{
			var newDiv = $("<div />").html(g_EsoCpSkillDesc[skillId][0]);
			g_EsoCpData[skillId].description = newDiv.text();
		}
		
		if (skillType > 0) 
		{
			//var isEquipped = $this.find(".esovcpEquipCheck").is(":checked");
			var isEquipped = $this.children(".esovcpSkillControls").children(".esovcpEquipCheck").is(":checked");
			
			if (equipSlotSkillIds[skillId] > 0)
			{
				g_EsoCpData[skillId].slotIndex = equipSlotSkillIds[skillId];
			}
			else if (isEquipped)
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
	
	$("#esovcpDisciplines, #esovcp2Disciplines").find(".esovcpDiscHighlight").removeClass("esovcpDiscHighlight");
	$(this).addClass("esovcpDiscHighlight");
	
	$("#esovcpSkills").find(".esovcpDiscSkills").hide();
	$("#skills_" + id).show();
	
	esovcpHideTooltip();
	UpdateEsoCPLink();
	UpdateEsoCpData();
}


window.g_esoCpLastPlusTime = 0;


window.OnEsoCPPlusButtonClick = function (e)
{
	//var now = Date.now();
	//var diff = now - g_esoCpLastPlusTime;
	//EsoCpLog("OnEsoCPPlusButtonClick Diff Time = " + diff + "ms");
	//g_esoCpLastPlusTime = now;
	
	var isShift = false;
	if (e.shiftKey) isShift = true;
	
	var skillId = $(this).attr('skillid');
	//var inputControl = $(".esovcpPointInput[skillid='" + skillId + "']");
	var inputControl = $(this).siblings(".esovcpPointInput");
	
	if (inputControl.length == 0) return;
	
	var value = (parseInt(inputControl.val()) || 0);
	var oldValue = value;
	var disciplineId = $(this).closest(".esovcpDiscSkills").attr("disciplineid");
	var maxPoints = parseInt(inputControl.attr("maxpoints"));
	var jumpDelta = parseInt(inputControl.attr("jumpdelta"));
	
	if (isNaN(jumpDelta)) jumpDelta = 1;
	if (isNaN(maxPoints)) maxPoints = 100;
	
	if (isShift)
		value += 10*jumpDelta;
	else
		value += 1*jumpDelta;
	
	if (value > maxPoints) value = maxPoints;
	if (value < 0) value = 0;
	
	if (value == oldValue) return;
	
	inputControl.val(value);
	
	UpdateEsoCP2SkillUnlocks(skillId, oldValue, value);
	UpdateEsoCPSkillDesc(skillId, value);
	UpdateEsoCPDiscPoints(disciplineId);
}


window.OnEsoCPMinusButtonClick = function (e)
{
	var isShift = false;
	if (e.shiftKey) isShift = true;
	
	var skillId = $(this).attr('skillid');
	//var inputControl = $(".esovcpPointInput[skillid='" + skillId + "']");
	var inputControl = $(this).siblings(".esovcpPointInput")
	
	if (inputControl.length == 0) return;
	
	var value = (parseInt(inputControl.val()) || 0);
	var oldValue = value;
	var disciplineId = $(this).closest(".esovcpDiscSkills").attr("disciplineid");
	var maxPoints = parseInt(inputControl.attr("maxpoints"));
	var jumpDelta = parseInt(inputControl.attr("jumpdelta"));
	
	if (isNaN(jumpDelta)) jumpDelta = 1;
	if (isNaN(maxPoints)) maxPoints = 100;
	
	if (isShift)
		value -= 10*jumpDelta;
	else
		value -= 1*jumpDelta;
	
	if (value > maxPoints) value = maxPoints;
	if (value < 0) value = 0;
	
	if (value == oldValue) return;
	
	inputControl.val(value);
	
	UpdateEsoCP2SkillUnlocks(skillId, oldValue, value);
	UpdateEsoCPSkillDesc(skillId, value);
	UpdateEsoCPDiscPoints(disciplineId);
}


window.OnEsoCPPointInputFocus = function (e)
{
	$(this).data('prevvalue', $(this).val());
}


window.OnEsoCPPointInputChange = function (e)
{
	var skillId = $(this).attr('skillid');
	var value = parseInt($(this).val()) || 0;
	var oldValue = $(this).data('prevvalue');
	var disciplineId = $(this).closest(".esovcpDiscSkills").attr("disciplineid");
	var maxPoints =  $(this).attr("maxpoints");
	
	if (maxPoints == null || maxPoints == "")
		maxPoints = 100;
	else
		maxPoints = parseInt(maxPoints);
	
	if (value < 0) value = 0;
	if (value > maxPoints) value = maxPoints;
	
	if (value == oldValue) return;
	
	$(this).data('prevvalue', value);
	
	UpdateEsoCP2SkillUnlocks(skillId, oldValue, value);
	UpdateEsoCPSkillDesc(skillId, value);
	UpdateEsoCPDiscPoints(disciplineId);
}


window.OnEsoCPPointInputScrollUp = function (e)
{
	var inputControl = $(this);
	var skillId = inputControl.parent().parent().attr('skillid');
	
	var oldValue = (parseInt(inputControl.val()) || 0);
	var value = oldValue + 1;
	
	var disciplineId = $(this).closest(".esovcpDiscSkills").attr("disciplineid");
	
	var maxPoints =  inputControl.attr("maxpoints");
	
	if (maxPoints == null || maxPoints == "")
		maxPoints = 100;
	else
		maxPoints = parseInt(maxPoints);
	
	if (value < 0) value = 0;
	if (value > maxPoints) value = maxPoints;
	
	inputControl.val(value);
	
	UpdateEsoCP2SkillUnlocks(skillId, oldValue, value);
	UpdateEsoCPSkillDesc(skillId, value);
	UpdateEsoCPDiscPoints(disciplineId);
	
	e.preventDefault();
}


window.OnEsoCPPointInputScrollDown = function (e)
{
	var inputControl = $(this);
	var skillId = inputControl.parent().parent().attr('skillid');
	
	var oldValue = (parseInt(inputControl.val()) || 0);
	var value = oldValue - 1;
	var disciplineId = $(this).closest(".esovcpDiscSkills").attr("disciplineid");
	
	var maxPoints = inputControl.attr("maxpoints");
	
	if (maxPoints == null || maxPoints == "")
		maxPoints = 100;
	else
		maxPoints = parseInt(maxPoints);
	
	if (value < 0) value = 0;
	if (value > maxPoints) value = maxPoints;
	
	inputControl.val(value);
	
	UpdateEsoCP2SkillUnlocks(skillId, oldValue, value);
	UpdateEsoCPSkillDesc(skillId, value);
	UpdateEsoCPDiscPoints(disciplineId);
	
	e.preventDefault();
}


window.UpdateEsoCP2SkillUnlocks = function (skillId, oldPoints, newPoints)
{
	//console.log("UpdateEsoCP2SkillUnlocks", skillId, oldPoints, newPoints);
	
	if (window.g_EsoCpLinks == null || window.g_EsoCpReverseLinks == null) return;
	
	var childLinks = g_EsoCpLinks[skillId];
	if (childLinks == null) return;
	
	var unlockLevel = $("#skill_" + skillId).attr("unlocklevel");
	if (unlockLevel == null || unlockLevel == "") return;
	
	unlockLevel = parseInt(unlockLevel);
	oldPoints = parseInt(oldPoints);
	newPoints = parseInt(newPoints);
	
	if (isNaN(unlockLevel + oldPoints + newPoints)) return;
	if (oldPoints >= unlockLevel && newPoints >= unlockLevel) return;
	if (oldPoints < unlockLevel && newPoints < unlockLevel) return;
	
		/* Lock parent skills */
	if (oldPoints >= unlockLevel && newPoints < unlockLevel)
	{
		SetCP2SkillChildPurchaseable(skillId, false);
	}
		/* Unlock parent skills */
	else if (oldPoints < unlockLevel && newPoints >= unlockLevel)
	{
		SetCP2SkillChildPurchaseable(skillId, true);
	}
	
}


window.CheckCp2LinksArePurchaseable = function(childLinks)
{
	if (childLinks == null) return true;
	
	childLinks.forEach(function(skillLinkId) {
		var skillElement = $("#skill_" + skillLinkId);
		if (!skillElement.hasClass("esovcpNotPurchaseable")) return true;
	});
	
	return false;
}


var g_EsoCp2_SkillVisited = {};
var g_EsoCp2_SkillPurchaseTemp = {};


window.UpdateCP2SkillPurchaseableChildren = function(abilityId)
{
	if (g_EsoCp2_SkillVisited[abilityId]) return;
	
	g_EsoCp2_SkillVisited[abilityId] = true;
	g_EsoCp2_SkillPurchaseTemp[abilityId] = 1;
	
	var links = g_EsoCpLinks[abilityId];
	if (links == null) return;
	
	var skillData = g_EsoCpSkills[abilityId];
	var jumpPointDelta = parseInt(skillData['jumpPointDelta']);
	//var skillValue = parseInt($(".esovcpPointInput[skillid='" + abilityId + "']").val());
	var skillValue = parseInt($("#cpinput_" + abilityId).val());
	
	if (skillValue < jumpPointDelta) return;
	
	links.forEach(function(linkId)
	{
		UpdateCP2SkillPurchaseableChildren(linkId);
	});
}


window.UpdateCP2SkillPurchaseable = function ()
{
	g_EsoCp2_SkillVisited = {};
	g_EsoCp2_SkillPurchaseTemp = {};
	
	if (!window.g_EsoCpIsV2 || window.g_EsoCpSkills == null) return;
	
	for (var abilityId in g_EsoCpSkills) 
	{
		var cpSkill = g_EsoCpSkills[abilityId];
		
		if (cpSkill['isRoot'] > 0)
		{
			g_EsoCp2_SkillVisited = {};
			UpdateCP2SkillPurchaseableChildren(abilityId);
			continue;
		}
	};

	var skillElements = $("#esovcpContainer").find(".esovcp2Skill, .esovcpSkill");
	$("#esovcpContainer").find(".esovcpNotPurchaseable").removeClass("esovcpNotPurchaseable");
	skillElements.addClass("esovcpNotPurchaseable");
	
	for (var abilityId in g_EsoCp2_SkillPurchaseTemp)
	{
		var skillElement = $("#skill_" + abilityId);
		var clusterElement = $("#skill_" + abilityId + "_cluster");
		skillElement.removeClass("esovcpNotPurchaseable");
		clusterElement.removeClass("esovcpNotPurchaseable");
	}
	
	$("#esovcpSkillEquipBar").find(".esovcpSkillEquipBarSlot").each(function() {
		var $this = $(this);
		var abilityId = $this.attr("skillid");
		
		if (g_EsoCp2_SkillPurchaseTemp[abilityId] == null)
		{
			$this.attr("skillid", "-1");
			$this.find("img").attr("src", "").hide();
		}
	});
	
}


window.SetCP2SkillChildPurchaseable = function (skillId, isPurchaseable, isChild)
{
	UpdateCP2SkillPurchaseable();
}


window.UpdateEsoCPSkillDesc = function(skillId, points)
{
	var descControl = $("#descskill_" + skillId);
	var desc = esovcpGetTooltipDescription(skillId, points)
	
	descControl.html(desc);
	g_EsoCpData[skillId].description = descControl.text();
	
	if (skillId == esovcpTooltipSkillId)
	{
		esovcpUpdateTooltip();
		//$("#esovcp2TooltipDesc").html(desc.replace("\n", "<p/>"));
	}
}


window.UpdateEsoCPDiscSkillDesc = function(discId)
{
	var discElement = $("#skills_" + discId);
	
	discElement.find(".esovcpSkill .esovcp2Skill").each(function(i, element){
		var skillId = $(this).attr("skillid");
		if (skillId == null || skillId == "") return;
		var points = parseInt($(this).find(".esovcpPointInput").val());
		UpdateEsoCPSkillDesc(skillId, points);
	});
}


window.UpdateEsoCPDiscPoints = function(discId)
{
	//var skillInputs = $("#skills_" + discId + " .esovcpPointInput");
	var totalPoints = 0;
	var attributeIndex = $("#" + discId).parent().attr("attributeindex");
	var discIndex = $("#" + discId).parent().attr("disciplineindex");
	var skillInputs = $("#esovcpContainer").find("#skills_" + discId).find(".esovcpPointInput").not(".esovcpPointInputCluster");
	
	skillInputs.each(function() {
		var points = parseInt($(this).val()) || 0;
		var maxPoints = parseInt($(this).attr("maxpoints)")) || 100;
		if (points < 0) points = 0;
		if (points > maxPoints) points = maxPoints;
		totalPoints += points;
	});
	
	$("#esovcpContainer").find(".esovcpSkillCluster[clusterid='" + discId + "']").find(".esovcpPointInput").val(totalPoints);
	
	$("#esovcpContainer").find("#skills_" + discId + " .esovcpDiscTitlePoints").text(totalPoints);
	$("#esovcpContainer").find("#" + discId + " .esovcpDiscPoints").text(totalPoints);
	$("#esovcpContainer").find("#" + discId + "_base .esovcpDiscPoints").text(totalPoints);
	
	UpdateEsoCPUnlockLevels(discId);
	UpdateEsoCPDiscAttrPoints(attributeIndex, discIndex);
}


window.UpdateEsoCPDiscAttrPoints = function(attributeIndex, discIndex)
{
	if (window.g_EsoCpIsV2) 
	{
		var discPoints = $("#esovcpContainer").find(".esovcp2Discipline[clusterindex='" + discIndex + "'] .esovcpDiscPoints")
		var totalPoints = 0;
		
		discPoints.each(function() {
			totalPoints += parseInt($(this).text()) || 0;
		});
		
		$("#esovcpContainer").find(".esovcp2Discipline[disciplineindex='" + discIndex + "'] .esovcpDiscPoints").text(totalPoints);
		UpdateEsoCPTotalCPPoints();
		return;
	}
	
	var discPoints = $("#esovcpContainer").find(".esovcpDiscAttrGroup[attributeindex='" + attributeIndex + "'] .esovcpDiscPoints");
	var totalPoints = 0;
	
	discPoints.each(function() {
		totalPoints += parseInt($(this).text()) || 0;
    });
	
	$("#esovcpContainer").find(".esovcpDiscAttrPoints[attributeindex='" + attributeIndex + "']").text(totalPoints);
	
	UpdateEsoCPTotalCPPoints();
}


window.UpdateEsoCPTotalCPPoints = function()
{
	if (window.g_EsoCpIsV2) 
	{
		var attrPoints = $("#esovcpContainer").find(".esovcp2Discipline").not(".esovcp2DiscCluster").children(".esovcpDiscPoints");
		var totalPoints = 0;
		
		attrPoints.each(function() {
			var $this = $(this);
			var points = parseInt($this.text()) || 0;
			totalPoints += points;
		});
		
		$("#esovcpContainer").find(".esovcpTotalPoints").text(totalPoints + " CP");
		
		UpdateEsoCPLink();
		UpdateEsoCpData();
		
		if (!g_EsoCpDisableUpdates) $( document ).trigger("esocpUpdate");
		
		return;
	}

	var attrPoints = $("#esovcpContainer").find(".esovcpDiscAttrPoints");
	var totalPoints = 0;
	
	attrPoints.each(function() {
		totalPoints += parseInt($(this).text()) || 0;
	});

	$("#esovcpContainer").find(".esovcpTotalPoints").text(totalPoints + " CP");
	
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
	var inputControls = $("#esovcpSkills").find(".esovcpPointInput");
	var cpQueryData = "";
	var selectDiscId = $("#esovcpDisciplines, #esovcp2Disciplines").find(".esovcpDiscHighlight").attr("id");
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
	if (window.g_EsoCpIsV2) return UpdateEsoCP2UnlockLevels(discId);
		
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
	
}


window.g_EsoCpDiscSkills = {};


window.UpdateEsoCP2UnlockLevels = function(discId)
{
	//var skills = $("#skills_" + discId + " .esovcpSkill, #skills_" + discId + " .esovcp2Skill");
	var skills = g_EsoCpDiscSkills[discId];
	
	if (skills == null)
	{
		g_EsoCpDiscSkills[discId] = $("#esovcpContainer").find("#skills_" + discId + " .esovcpSkill, #skills_" + discId + " .esovcp2Skill");
		skills = g_EsoCpDiscSkills[discId];
	}
	
	skills.each(function() {
		var $this = $(this);
		var skillType = parseInt($this.attr("skilltype"));
		var skillId = $this.attr("skillid");
		var unlockLevel = parseInt($this.attr("unlocklevel"));
		var points = parseInt($this.find(".esovcpPointInput").val());
		if (isNaN(points)) points = 0;
		var isActive = true;
		
		if (skillType > 0)
		{
			var isEquipped = IsCp2SkillEquipped(skillId);
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


window.IsCp2SkillEquipped = function(skillId)
{
	var equipBarSlot = $("#esovcpSkillEquipBar").find(".esovcpSkillEquipBarSlot[skillid='" + skillId + "']");
	var equipCheckValue = $("#skill_" + skillId).find(".esovcpEquipCheck").is(":checked");
	
	if (equipBarSlot.length > 0 || equipCheckValue) return true;
	return false;
}


window.OnEsoCPSearch = function (e)
{
	var text = $("#esovcpSearchText").val().trim();
	esovcpHideTooltip();
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
	
	$("#esovcpSkills").find(".esovcpHighlightSkill").removeClass("esovcpHighlightSkill");
	
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


window.SelectEsoCPSkill = function (skillId)
{
	$("#esovcpSkills").find(".esovcpHighlightSkill").removeClass("esovcpHighlightSkill");
	SelectEsoCPSkillElement($("#skill_" + skillId));
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
		$("#esovcpSkills").find(".esovcpDiscSkills:visible").hide();
		parent.show();
		
		$("#esovcpDisciplines, #esovcp2Disciplines").find(".esovcpDiscHighlight").removeClass("esovcpDiscHighlight");
		$("#" + discId).addClass("esovcpDiscHighlight");
	}
	
	var skillOffset = element.offset().top;
	var skillOffsetBottom = skillOffset + element.outerHeight();
	var windowBottom = $(window).scrollTop() + $(window).innerHeight();
	var windowTop = $(window).scrollTop();
	
	if (windowBottom <= skillOffsetBottom || windowTop >= skillOffset)
	{
		$('html, body').animate({scrollTop: skillOffset}, 700);
	}
	
	if ($("#esovcp2StarSvg_craft").length > 0)
	{
		esovcpShowTooltip(skillId, element);
	}
}


window.FindNextEsoCPText = function ()
{
	var searchElements = $("#esovcpSkills").find(".esovcpSkill, .esovcp2Skill");
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
	esovcpHideTooltip();
	
	$("#esovcpSkills").find(".esovcpPointInput").val(0);
	$("#esovcpSkills").find(".esovcpEquipCheck").prop("checked", false);
	
	if (window.g_EsoCpIsV2) 
	{
		$("#esovcpSkills").find(".esovcpNotPurchaseable").removeClass("esovcpNotPurchaseable");
		$("#esovcpSkills").find(".esovcpSkill[isroot='0']").addClass("esovcpNotPurchaseable");
		$("#esovcpSkills").find(".esovcp2Skill[isroot='0']").addClass("esovcpNotPurchaseable");
		$("#esovcpSkillEquipBar").find(".esovcpSkillEquipBarSlot").attr("skillid", "-1").find("img").attr("src", "").hide();
	}
	
	EsoCpUpdateAll();
}


window.OnEsoCPResetDisc = function (e)
{
	var parent = $(this).parent();
	
	esovcpHideTooltip();
	
	var discId = parent.attr("disciplineid");
	if (discId == null || discId == "") return;
	
	if (window.g_EsoCpIsV2) 
	{
		var discIndex = parent.attr("disciplineindex");
		if (discIndex == null) discIndex = "0";
		
		var clusters = $("#esovcp2Disciplines").find(".esovcp2DiscCluster[clusterindex='" + discIndex + "']")
		var equipSlots = $("#esovcpSkillEquipBar").find(".esovcpSkillEquipBarSlot[disciplineindex='" + discIndex + "']");
		
		equipSlots.attr("skillid", "-1").find("img").attr("src", "").hide();
		
		parent.find(".esovcpPointInput").val("0");
		parent.find(".esovcpEquipCheck").prop("checked", false);
		parent.find(".esovcpNotPurchaseable").removeClass("esovcpNotPurchaseable");
		parent.find(".esovcpSkill[isroot='0']").addClass("esovcpNotPurchaseable");
		parent.find(".esovcp2Skill[isroot='0']").addClass("esovcpNotPurchaseable");
		
		if (!parent.hasClass("esovcp2SkillsCluster")) clusters.each(function() {
			var $this = $(this);
			var clusterId = $this.attr("id");
			var cluster = $("#skills_" + clusterId);
			
			cluster.find(".esovcpPointInput").val("0");
			cluster.find(".esovcpEquipCheck").prop("checked", false);
			cluster.find(".esovcpNotPurchaseable").removeClass("esovcpNotPurchaseable");
			cluster.find(".esovcpSkill[isroot='0']").addClass("esovcpNotPurchaseable");
			cluster.find(".esovcp2Skill[isroot='0']").addClass("esovcpNotPurchaseable");
			
			UpdateEsoCPDiscSkillDesc(clusterId);
			UpdateEsoCPDiscPoints(clusterId);
		});
	}
	else 
	{
		parent.find(".esovcpPointInput").val("0");
		parent.find(".esovcpEquipCheck").prop("checked", false);
	}
	
	UpdateEsoCPDiscSkillDesc(discId);
	UpdateEsoCPDiscPoints(discId);
	UpdateCP2SkillPurchaseable();
}


window.EsoCpUpdateAll = function ()
{
	if (window.g_EsoCpIsV2) 
	{
		UpdateCP2SkillPurchaseable();
		UpdateEsoCPDiscSkillDesc('warfare');
		UpdateEsoCPDiscSkillDesc('craft');
		UpdateEsoCPDiscSkillDesc('fitness');
		
		g_EsoCpDisableUpdates = true;
		
		$("#esovcpDisciplines, #esovcp2Disciplines").find(".esovcp2Discipline").not(".esovcp2DiscCluster").each(function() {
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
	
	$("#esovcpDisciplines, #esovcp2Disciplines").find(".esovcpDiscHighlight").removeClass("esovcpDiscHighlight");
	
	$("#esovcpSkills").find(".esovcpDiscSkills:visible").hide();
	//$(".esovcpDiscSkills[disciplineindex='" + disciplineIndex + "']").show();
	$("#" + skillId).show();
	
	esovcpHideTooltip();
	$(this).addClass("esovcpDiscHighlight");
}


window.esovcpTooltipRoot = null;
window.esovcpTooltipSkillId = -1;


window.esovcpShowTooltip = function(skillId, parent)
{
	if (esovcpTooltipRoot == null) esovcpTooltipRoot = $("#esovcp2Tooltip");
	
	esovcpTooltipSkillId = skillId;
	
	if (esovcpUpdateTooltip())
	{
		esovcpTooltipRoot.show();
		esovcpAdjustTooltipPosition(parent);
	}
}


window.esovcpGetTooltipDescription = function(skillId, points)
{
	if (skillId <= 0) return "";
	
	var descText = "";
	var skillData  = g_EsoCpSkills[skillId];
	
	if (g_EsoCpSkillDesc[skillId] != null) descText = g_EsoCpSkillDesc[skillId][points];
	if (descText == null) return "Unknown Skill/Description!";
	
		/* Enlivening Overflow */
	if (skillId == 156008)		// equal to |cffffff.5|r% of your Max Magicka, up to a cap of |cffffff150|r, Current bonus: |cffffff60|r
	{
		var desc = skillData.minDescription;
		var matchResult = desc.match(/equal to \|cffffff([0-9.]+)\|r% of your Max Magicka/);
		var factorValue = 0.005;
		var capValue = 150;
		var magicka = 32000;
		
		if (matchResult && matchResult[1])
		{
			var value = parseFloat(matchResult[1]);
			if (!isNaN(value)) factorValue = value/100;
		}
		
		if (points != skillData.maxPoints) factorValue = 0;
		
		matchResult = desc.match(/up to a cap of \|cffffff([0-9.]+)\|r/);
		
		if (matchResult && matchResult[1])
		{
			var value = parseInt(matchResult[1]);
			if (!isNaN(value)) capValue = value;
		}
		
		if (window.g_EsoBuildLastInputValues && window.g_EsoBuildLastInputValues.Magicka > 0)
		{
			magicka = g_EsoBuildLastInputValues.Magicka;
		}
		
		var tooltipValue = Math.floor(factorValue * magicka);
		if (tooltipValue < 0) tooltipValue = 0;
		if (tooltipValue > capValue) tooltipValue = capValue;
		
		descText = descText.replace(/Current bonus: <.*?>[0-9.]+<.*?>/, "Current bonus: <div class='esovcpDescWhite'>" + tooltipValue + "</div>")
		
	}	/* Hope Infusion */
	else if (skillId == 155992)	//Minor Heroism for |cffffff1|r second for every |cffffff300|r Magicka Recovery you have. Current bonus: |cffffff1.5|r
	{
		var desc = skillData.minDescription;
		var matchResult = desc.match(/for every \|cffffff([0-9.]+)\|r Magicka Recovery/);
		var factorValue = 300;
		var magickaRegen = 1200;
		
		if (matchResult && matchResult[1])
		{
			var value = parseInt(matchResult[1]);
			if (!isNaN(value)) factorValue = value;
		}
		
		if (window.g_EsoBuildLastInputValues && window.g_EsoBuildLastInputValues.MagickaRegen > 0)
		{
			magickaRegen = g_EsoBuildLastInputValues.MagickaRegen;
		}
		
		var tooltipValue = Math.floor(magickaRegen / factorValue * 10) / 10;
		if (tooltipValue < 0) tooltipValue = 0;
		if (points != skillData.maxPoints) tooltipValue = 0;
		
		descText = descText.replace(/Current duration: <.*?>[0-9.]+<.*?>/, "Current duration: <div class='esovcpDescWhite'>" + tooltipValue + "</div>")
	}
		/* Cutting Defense */
	else if (skillId == 142012) // You deal 200 Magic Damage to attackers whenever they damage you with a Direct Damage attack within 7 meters.
	{
		if (window.GetEsoSkillDescription2)
		{
			var newDesc = GetEsoSkillDescription2(142012, null, true);
			descText = newDesc;
		}
	}
	
	descText = descText.replace("\n", "<p/>");
	return descText;
}


window.esovcpUpdateTooltip = function()
{
	var nameElement = $("#esovcp2TooltipName");
	var descElement = $("#esovcp2TooltipDesc");
	var pointsElement = $("#esovcp2TooltipPoints");
	var jumpElement = $("#esovcp2TooltipJump");
	var equipElement = $("#esovcp2TooltipEquip");
	var showCluster = false;
	var jumpText = "";
	var equippableText = "";
	var nameText = "";
	var pointsText = "";
	var descText = "";
	var nameText = "";
	
	if (esovcpTooltipSkillId == null) return false;
	
	if (esovcpTooltipSkillId.toString().indexOf("_cluster") > 0)
	{
		showCluster = true;
		esovcpTooltipSkillId = parseInt(esovcpTooltipSkillId);
	}
	
	var skillData  = g_EsoCpSkills[esovcpTooltipSkillId];
	var pointsInput = $("#cpinput_" + esovcpTooltipSkillId);
	
	if (skillData == null) return false;
	
	if (showCluster)
	{
		var clusterId = skillData['clusterName'].toLowerCase().replace(" ", "_").replace("'", "_");
		var clusterSkills = $("#skills_" + clusterId).find(".esovcp2Skill");
		var totalPoints = 0;
		
		clusterSkills.each(function() {
			var skillId = $(this).attr("skillid");
			var inputElement = $("#cpinput_" + skillId);
			var nameElement = $(this).find(".esovcpSkillName");
			var points = inputElement.val();
			var maxPoints = inputElement.attr("maxpoints");
			
			totalPoints += parseInt(points);
			descText += nameElement.text() + " (<div class='esovcpDescWhite'>" + points + " / " + maxPoints + "</div>)<br/>"
		});
		
		//descText += "<br/>Total Points: <div class='esovcpDescWhite'>" + totalPoints + "</div><br/>"
		nameText = skillData['clusterName'].toUpperCase();
	}
	else
	{
		var points = parseInt(pointsInput.val());
		if (points == null || points < 0) points = 0;
		
		descText = esovcpGetTooltipDescription(esovcpTooltipSkillId, points);
		
		if (esovcpTooltipSkillId <= 0)
		{
			nameElement.text("");
			descElement.text("");
			pointsElement.text("");
			jumpElement.text("");
			return true;
		}
		
		nameText = skillData['name'].toUpperCase();
		
		var jumpPointDelta = parseInt(skillData['jumpPointDelta']);
		var maxPoints = parseInt(skillData['maxPoints']);
		if (maxPoints == null) maxPoints = "?";
		pointsText = "Points: <div class='esovcpDescWhite'>" + points + " / " + maxPoints + "</div>";
		
		if (jumpPointDelta > 1)
		{
			var nextStagePoints = parseInt(points / jumpPointDelta) * jumpPointDelta + jumpPointDelta; 
			jumpText = "Next Stage At: <div class='esovcpDescWhite'>" + nextStagePoints + "</div> pts";
			if (nextStagePoints <= 0 || nextStagePoints > maxPoints) jumpText = "";
		}
		
		if (parseInt(skillData['skillType']) >= 1) 
		{
			var equippedSlot = $("#esovcpSkillEquipBar").find(".esovcpSkillEquipBarSlot[skillid='" + esovcpTooltipSkillId + "']")
			
			if (equippedSlot.length > 0)
				equippableText = "Equipped";
			else
				equippableText = "Add to Champion Bar to Activate";
		}
	}
	
	nameElement.text(nameText);
	descElement.html(descText);
	pointsElement.html(pointsText);
	jumpElement.html(jumpText);
	equipElement.text(equippableText);
	
	if (equippableText == "")
		equipElement.hide();
	else
		equipElement.show();
	
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
	var parent = $(this).parents(".esovcp2Skill");
	var skillId = parent.attr("skillid");
	
	esovcpShowTooltip(skillId, parent)
}


window.OnEsoCP2StarHoverOut = function()
{
	//esovcpHideTooltip();
}


window.OnEsoCP2EquipSlotHoverIn = function()
{
	var skillId = $(this).attr("skillid");
	
	esovcpShowTooltip(skillId, $(this));
}


window.OnEsoCP2EquipSlotHoverOut = function()
{
	//esovcpHideTooltip();
}


window.OnEsoCP2SkillEquipped = function()
{
	var $this = $(this);
	var skillId = $this.attr("skillid");
	var discIndex = $this.attr("disciplineindex");
	var equippedSkills = $("#esovcpSkills").find(".esovcpEquipCheck[disciplineindex='" + discIndex + "']:checked");
	
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


window.OnEsoCP2ChildLinkClick = function()
{
	var skillId = parseInt($(this).attr("skillid")) || -1;
	if (skillId <= 0) return;
	
	SelectEsoCPSkill(skillId);
}


window.OnEsoCP2SkillClusterClick = function()
{
	var $this = $(this);
	var $parent = $this.parent();
	var clusterId = $parent.attr("clusterid");
	
	if (clusterId == null || clusterId == "") return;
	
	$("#esovcpSkills").find(".esovcpDiscSkills:visible").hide();
	$("#skills_" + clusterId).show();
	
	$("#esovcpDisciplines, #esovcp2Disciplines").find(".esovcpDiscHighlight").removeClass("esovcpDiscHighlight");
	$("#" + clusterId).addClass("esovcpDiscHighlight");
}


window.g_EsoCpDragSkillElement = null;
window.g_EsoCpDragEquipElement = null;
window.g_EsoCpDragEquipDropElement = null;


window.OnCp2StarEquipDraggableStop = function(e, ui)
{
	if (g_EsoCpDragEquipElement != null && g_EsoCpDragEquipDropElement == null)
	{
		g_EsoCpDragEquipElement.attr("skillid", "-1");
		g_EsoCpDragEquipElement.find("img").attr("src", "").hide().removeAttr("oldsrc");
		
		g_EsoCpDragEquipElement = null;
		g_EsoCpDragEquipDropElement = null;
		
		OnCp2StarEquipChange();
	}
	
	//console.log("OnCp2StarEquipDraggableStop");
}


window.OnCp2StarEquipDraggableStart = function(e, ui)
{
	var $this = $(this);
	var $parent = $this.parent();
	//var realDraggable = $(".ui-draggable-dragging");
	var realDraggable = ui.helper;
	
	realDraggable.addClass('esovcpSkillDraggableGood');
	g_EsoCpDragEquipElement = $parent;
	g_EsoCpDragEquipDropElement = null;
}


window.OnCp2StarEquipDraggableMove = function(e, ui)
{
	esovcpHideTooltip();
}


window.OnCp2StarDraggableStart = function(e, ui)
{
	var $this = $(this);
	var $parent = $this.parent();
	//var realDraggable = $(".ui-draggable-dragging");
	var realDraggable = ui.helper;
	
	g_EsoCpDragSkillElement = null;
	
	if ($parent.hasClass("esovcpNotPurchaseable")) 
	{
		e.preventDefault();
		return;
	}
	
	realDraggable.addClass('esovcpSkillDraggableBad');
	g_EsoCpDragSkillElement = $parent;

}


window.OnCp2StarDraggableMove = function(e, ui)
{
	esovcpHideTooltip();
}


window.OnCp2StarDroppableAccept = function(e, ui)
{
	var $this = $(this);
	
	if (g_EsoCpDragSkillElement)
	{
		var dragDiscIndex = g_EsoCpDragSkillElement.attr("disciplineindex"); 
		var dropDiscIndex = $this.attr("disciplineindex");
		if (dragDiscIndex != dropDiscIndex) return false;
		return true;
	}
	else if (g_EsoCpDragEquipElement)
	{
		var dragDiscIndex = g_EsoCpDragEquipElement.attr("disciplineindex"); 
		var dropDiscIndex = $this.attr("disciplineindex");
		if (dragDiscIndex != dropDiscIndex) return false;
		return true;
	}
	
	return false;
}


window.OnCp2StarBarDroppableOver = function(e, ui)
{
	var $this = $(this);
	//var realDraggable = $(".ui-draggable-dragging");
	var realDraggable = ui.helper;
	
	if (g_EsoCpDragSkillElement)
	{
		var dragImg = realDraggable.find("img");
		var dragImgSrc = dragImg.attr("src");
		var dropImg = $this.find("img");
		var oldSrc = dropImg.attr("src");
		
		if (oldSrc == null) oldSrc = "";
		
		dropImg.attr("oldsrc", oldSrc);
		dropImg.attr("src", dragImgSrc);
		dropImg.show();
		
		realDraggable.removeClass("esovcpSkillDraggableBad");
		realDraggable.addClass("esovcpSkillDraggableGood");
	}
	else if (g_EsoCpDragEquipElement)
	{
		g_EsoCpDragEquipDropElement = $this;
		
		var dragImgSrc = realDraggable.attr("src");
		
		var dropImg = $this.find("img");
		var oldSrc = dropImg.attr("src");
		
		if (oldSrc == null) oldSrc = "";
		
		dropImg.attr("oldsrc", oldSrc);
		dropImg.attr("src", dragImgSrc);
		dropImg.show();
		
		realDraggable.removeClass("esovcpSkillDraggableBad");
		realDraggable.addClass("esovcpSkillDraggableGood");
	}

}


window.OnCp2StarBarDroppableOut = function(e, ui)
{
	var $this = $(this);
	//var realDraggable = $(".ui-draggable-dragging");
	var realDraggable = ui.helper;
	
	if (g_EsoCpDragSkillElement)
	{
		var dropImg = $this.find("img");
		var oldSrc = dropImg.attr("oldsrc");
	
		if (oldSrc != null)
		{
			dropImg.attr("src", oldSrc);
			dropImg.removeAttr("oldSrc");
			if (oldSrc == "") dropImg.hide();
		}
		
		realDraggable.removeClass("esovcpSkillDraggableGood");
		realDraggable.addClass("esovcpSkillDraggableBad");
	}
	else if (g_EsoCpDragEquipElement)
	{
		g_EsoCpDragEquipDropElement = null;
		
		var dropImg = $this.find("img");
		var oldSrc = dropImg.attr("oldsrc");
	
		if (oldSrc != null)
		{
			dropImg.attr("src", oldSrc);
			dropImg.removeAttr("oldSrc");
			if (oldSrc == "") dropImg.hide();
		}
		
		realDraggable.removeClass("esovcpSkillDraggableGood");
		realDraggable.addClass("esovcpSkillDraggableBad");
	}
	
}


window.OnCp2StarBarDroppableDrop = function(e, ui)
{
	var $this = $(this);
	var skillId = -1;
	
	if (g_EsoCpDragSkillElement)
	{
		skillId = g_EsoCpDragSkillElement.attr("skillid");
		var existingSkillId = $this.attr("skillid");
	
		if (skillId != existingSkillId)
		{
			var existingSlot = $("#esovcpSkillEquipBar").find(".esovcpSkillEquipBarSlot[skillid='" + skillId + "']");
			existingSlot.find("img").attr("src", "").hide();
			existingSlot.attr("skillid", "-1");
		}
	
		$this.attr("skillid", skillId);
		$this.find("img").removeAttr("oldsrc");
		
		OnCp2StarEquipChange();
		
		g_EsoCpDragSkillElement = null;
	}
	else if (g_EsoCpDragEquipElement)
	{
		skillId = g_EsoCpDragEquipElement.attr("skillid");
		var existingSkillId = $this.attr("skillid");
		
		if (skillId != existingSkillId)
		{
			var existingSlot = $("#esovcpSkillEquipBar").find(".esovcpSkillEquipBarSlot[skillid='" + skillId + "']");
			existingSlot.find("img").attr("src", "").hide();
			existingSlot.attr("skillid", "-1");
		}
		
		$this.attr("skillid", skillId);
		$this.find("img").removeAttr("oldsrc");
		
		g_EsoCpDragEquipElement = null;
		
		OnCp2StarEquipChange();
	}
	
	esovcpShowTooltip(skillId, $this);
	//console.log("OnCp2StarBarDroppableDrop");
}


window.OnCp2StarEquipChange = function(discId)
{
	if (discId == null)
	{
		$("#esovcpSkills").find(".esovcpDiscSkills").each(function() {
			var $this = $(this);
			var discId = $this.attr("disciplineid");
			UpdateEsoCPUnlockLevels(discId);
		});
	}
	else
	{
		UpdateEsoCPUnlockLevels(discId);
	}
	
	esovcpUpdateTooltip();
	UpdateEsoCpData();
	
	if (!g_EsoCpDisableUpdates) $( document ).trigger("esocpUpdate");
}


window.g_EsoCp2StarClickTimes = {};
window.g_EsoCp2EquipStarClickTimes = {};


window.OnEsoCP2StarClick = function()
{
	var $this = $(this);
	var $parent = $this.parent();
	var skillId = $parent.attr("skillid");
	var now = Date.now();
	
	if (g_EsoCp2StarClickTimes[skillId] == null)
	{
		//console.log("OnEsoCP2StarClick: Click");
		
		var timeout = setTimeout(function() { 
			delete g_EsoCp2StarClickTimes[skillId];
			esovcpShowTooltip(skillId, $parent);
			//console.log("OnEsoCP2StarClick: Single Click");
		}, 500);
		
		g_EsoCp2StarClickTimes[skillId] = {
				time: now,
				timeout: timeout
		}
	}
	else
	{
		//console.log("OnEsoCP2StarClick: Double Click");
		clearTimeout(g_EsoCp2StarClickTimes[skillId].timeout);
		delete g_EsoCp2StarClickTimes[skillId];
		OnEsoCP2StarDoubleClick.call(this);
	}

}


window.OnEsoCP2EquipStarClick = function()
{
	var $this = $(this);
	var skillId = $this.attr("skillid");
	var now = Date.now();
	
	if (skillId <= 0) return;
	
	if (g_EsoCp2EquipStarClickTimes[skillId] == null)
	{
		//console.log("OnEsoCP2EquipStarClick: Click");
		
		var timeout = setTimeout(function() { 
			delete g_EsoCp2EquipStarClickTimes[skillId];
			esovcpShowTooltip(skillId, $this);
			//console.log("OnEsoCP2EquipStarClick: Single Click");
		}, 500);
		
		g_EsoCp2EquipStarClickTimes[skillId] = {
				time: now,
				timeout: timeout
		}
	}
	else
	{
		//console.log("g_EsoCp2EquipStarClickTimes: Double Click");
		clearTimeout(g_EsoCp2EquipStarClickTimes[skillId].timeout);
		delete g_EsoCp2EquipStarClickTimes[skillId];
		OnEsoCP2EquipStarDoubleClick.call(this);
	}
	
}


window.OnEsoCP2EquipStarDoubleClick = function()
{
	if (!window.g_EsoCpIsEdit) return;
	
	var $this = $(this);
	var skillId = $this.attr("skillid");
	
	if (skillId <= 0) return;
	
	$this.attr("skillid", "-1");
	$this.find("img").attr("src", "").hide();
	
	esovcpHideTooltip();
	
	OnCp2StarEquipChange();
}


window.OnEsoCP2MouseMove = function(e)
{
	var $target = $(e.target);
	
	if ($target.closest(".esovcp2Skill, .esovcpSkillEquipBarDisc, #esovcpSearchBlockFloat").length <= 0) esovcpHideTooltip();
}


window.g_esoCp2NextFreeSlotIndex = 1;


window.OnEsoCP2StarDoubleClick = function()
{
	if (!window.g_EsoCpIsEdit) return;
	
	var $this = $(this);
	var $parent = $this.parent();
	var skillId = parseInt($parent.attr("skillid"));
	var skillData = g_EsoCpSkills[skillId];
	var discIndex = parseInt($parent.attr("disciplineindex"));
	
	if (skillData == null) return;
	if (skillData['skillType'] <= 0) return;
	if ($parent.hasClass("esovcpNotPurchaseable")) return;
	if ($("#esovcpSkillEquipBar").find(".esovcpSkillEquipBarSlot[skillid='" + skillId + "']").length > 0) return;
	
	var firstEmptySlot = $("#esovcpSkillEquipBar").find(".esovcpSkillEquipBarSlot[skillid='-1'][disciplineindex='" + discIndex + "']").first();
	
	if (firstEmptySlot.length <= 0)
	{
		var slotIndex = (discIndex - 1)*4 + g_esoCp2NextFreeSlotIndex;
		firstEmptySlot = $("#esovcpSkillEquipBar").find(".esovcpSkillEquipBarSlot[slotindex='" + slotIndex + "']");
		
		++g_esoCp2NextFreeSlotIndex;
		if (g_esoCp2NextFreeSlotIndex > 4) g_esoCp2NextFreeSlotIndex = 1;
	}
	
	firstEmptySlot.attr("skillid", skillId);
	firstEmptySlot.find("img").attr("src", "//esolog.uesp.net/resources/cpstar_white.png").show();
	
	OnCp2StarEquipChange();
}


window.esovcpOnDocReady = function ()
{
	g_EsoCpUpdateSkillElements = $("#esovcpContainer").find(".esovcpSkill, .esovcp2Skill").not(".esovcpSkillCluster");
	g_EsoCpUpdateDiscElements = $("#esovcpContainer").find(".esovcpDiscipline");
	g_EsoCpUpdatAttributeElements = $("#esovcpContainer").find(".esovcpDiscAttrPoints");
	g_EsoCpUpdateTopLevelDiscElements = $("#esovcpContainer").find(".esovcp2Discipline").not(".esovcp2DiscCluster");
	g_EsoCpUpdateEquipSlotElements = $("#esovcpContainer").find(".esovcpSkillEquipBarSlot");
	
	$(".esovcpDiscipline").click(OnEsoCpDisciplineClick);
	$(".esovcpMinusButton").click(OnEsoCPMinusButtonClick);
	$(".esovcpPlusButton").click(OnEsoCPPlusButtonClick);
	$(".esovcpPointInput").on('focusin', function(e) { OnEsoCPPointInputFocus.call(this, e); });
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
	
	$(".esovcpShowTooltip").hover(OnEsoCP2StarHoverIn, OnEsoCP2StarHoverOut);
	$(".esovcpSkillChildLink").click(OnEsoCP2ChildLinkClick);
	$(".esovcpSkillCluster .esovcp2SkillStar").click(OnEsoCP2SkillClusterClick);
	$(".esovcpSkillEquipBarSlot").hover(OnEsoCP2EquipSlotHoverIn, OnEsoCP2EquipSlotHoverOut);
	$(".esovcp2SkillStar").click(OnEsoCP2StarClick);
	$(".esovcp2SkillStar").on("touchend", OnEsoCP2StarClick);
	$(".esovcpSkillEquipBarSlot").click(OnEsoCP2EquipStarClick);
	$(".esovcp2Discipline").click(OnEsoCP2DisciplineClick);
	
	if (window.g_EsoCpIsV2) $("body").mousemove(OnEsoCP2MouseMove);
	
	//$(".esovcpSkillEquipBarSlot").on("touchend", OnEsoCP2EquipStarClick);
	//$(".esovcp2SkillStar").dblclick(OnEsoCP2StarDoubleClick);
	
	if (window.g_EsoCpIsEdit)
	{
		$(".esovcpEquipCheck").change(OnEsoCP2SkillEquipped);
		
		$(".esovcp2SkillEquippable").draggable({
			containment: false,
			appendTo: $('body'),
			helper: 'clone',
			start: OnCp2StarDraggableStart,
			drag: OnCp2StarDraggableMove,
		});
		
		$(".esovcpSkillEquipBarSlot").droppable({
			drop: OnCp2StarBarDroppableDrop,
			accept: OnCp2StarDroppableAccept,
			out: OnCp2StarBarDroppableOut,
			over: function(event, ui) { setTimeout(OnCp2StarBarDroppableOver.bind(this, event, ui), 0); },
			tolerance: "pointer",
		});
		
		$(".esovcpSkillEquipBarSlot img").draggable({
			containment: false,
			appendTo: $('body'),
			helper: 'clone',
			start: OnCp2StarEquipDraggableStart,
			drag: OnCp2StarEquipDraggableMove,
			stop: OnCp2StarEquipDraggableStop,
			tolerance: "pointer",
		});
	}
	
	EsoCpUpdateAll();
}


$( document ).ready(esovcpOnDocReady);
