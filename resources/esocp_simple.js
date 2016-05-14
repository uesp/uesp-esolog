function OnDisciplineClick(e)
{
	var id = $(this).attr('id');
	
	$(".esovcpDiscSkills").hide();
	$("#skills_" + id).show();
}


function OnPlusButtonClick(e)
{
	var skillId = $(this).attr('skillid');
	var inputControl = $(".esovcpPointInput[skillid='" + skillId + "']");
	var value = parseInt(inputControl.val()) + 1;
	
	if (value > 100) value = 100;
	inputControl.val(value);
	
	UpdateSkillDesc(skillId, value);
}


function OnMinusButtonClick(e)
{
	var skillId = $(this).attr('skillid');
	var inputControl = $(".esovcpPointInput[skillid='" + skillId + "']");
	var value = parseInt(inputControl.val()) - 1;
	
	if (value < 0) value = 0;
	inputControl.val(value);
	
	UpdateSkillDesc(skillId, value);
}


function OnPointInputChange(e)
{
	var skillId = $(this).attr('skillid');
	var value = parseInt($(this).val());
	
	if (value < 0) value = 0;
	if (value > 100) value = 100;
	
	UpdateSkillDesc(skillId, value);
}


function OnPointInputScrollUp(e)
{
	var skillId = $(this).attr('skillid');
	var inputControl = $(this).find("input");
	var value = parseInt(inputControl.val()) + 1;
	
	if (value < 0) value = 0;
	if (value > 100) value = 100;
	
	inputControl.val(value);
	UpdateSkillDesc(skillId, value);
}


function OnPointInputScrollDown(e)
{
	var skillId = $(this).attr('skillid');
	var inputControl = $(this).find("input");
	var value = parseInt(inputControl.val()) - 1;
	
	if (value < 0) value = 0;
	if (value > 100) value = 100;
	
	inputControl.val(value);
	UpdateSkillDesc(skillId, value);
}


function UpdateSkillDesc(skillId, points)
{
	var descControl = $("#descskill_" + skillId);
	var desc = g_SkillDesc[skillId][points];
	
	descControl.html(desc);
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