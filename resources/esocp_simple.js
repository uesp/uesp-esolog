function OnDisciplineClick(e)
{
	var id = $(this).attr('id');
	
	$(".esovcpDiscHighlight").removeClass("esovcpDiscHighlight");
	$(this).addClass("esovcpDiscHighlight");
	
	$(".esovcpDiscSkills").hide();
	$("#skills_" + id).show();
}


function OnPlusButtonClick(e)
{
	var skillId = $(this).attr('skillid');
	var inputControl = $(".esovcpPointInput[skillid='" + skillId + "']");
	
	if (inputControl.length == 0) return;
	
	var value = parseInt(inputControl.val()) + 1;
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
	
	var value = parseInt(inputControl.val()) - 1;
	var disciplineId = $(this).closest(".esovcpDiscSkills").attr("disciplineid");
	
	if (value < 0) value = 0;
	inputControl.val(value);
	
	UpdateSkillDesc(skillId, value);
	UpdateDiscPoints(disciplineId);
}


function OnPointInputChange(e)
{
	var skillId = $(this).attr('skillid');
	var value = parseInt($(this).val());
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
	
	var value = parseInt(inputControl.val()) + 1;
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
	
	var value = parseInt(inputControl.val()) - 1;
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
	
	skillInputs.each(function() {
		totalPoints += Number($(this).val());
    });
	
	$("#skills_" + discId + " .esovcpDiscTitlePoints").text(totalPoints);
	$("#" + discId + " .esovcpDiscPoints").text(totalPoints);	
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
}


$( document ).ready(esovcpOnDocReady);