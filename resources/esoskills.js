function EsoSkillShowSkillLine(skillLine)
{
	var id = skillLine.replace(/[ '"]/g, '_');
	
	console.log("'"+skillLine+"'", "'"+id+"'");
	
	$(".esovsSkillContentBlock:visible").hide();
	$("#" + id).show();
}


function OnEsoSkillTypeTitleClick(event)
{
	var currentSkillType = $(".esovsSkillTypeTitle.esovsSkillLineTitleHighlight");
	var currentSkillLine = $(".esovsSkillLineTitle.esovsSkillTypeTitleHighlight");
	
	if ($(this)[0] == currentSkillType[0]) return;
	
	$(".esovsSkillType:visible").slideUp();
	currentSkillType.removeClass("esovsTitleHighlight");
	currentSkillLine.removeClass("esovsTitleHighlight");
	
	$(this).next(".esovsSkillType").slideDown();
	$(this).addClass("esovsSkillLineTitleHighlight");
	
	var firstSkillLine = $(this).next(".esovsSkillType").children(".esovsSkillLineTitle").first();
	firstSkillLine.addClass("esovsSkillTypeTitleHighlight");
	
	var skillType = $(this).text();
	var skillLine = firstSkillLine.text();
	EsoSkillShowSkillLine(skillLine);
}


function OnEsoSkillLineTitleClick(event)
{
	var currentSkillLine = $(".esovsSkillLineTitle.esovsSkillTypeTitleHighlight");
	
	if ($(this)[0] == currentSkillLine[0]) return;
	currentSkillLine.removeClass("esovsSkillTypeTitleHighlight");
	
	$(this).addClass("esovsSkillTypeTitleHighlight");
	
	var skillLine = $(this).text();
	
	EsoSkillShowSkillLine(skillLine);
}

function OnEsoSkillBlockClick(event)
{
	$(this).parent().next('.esovsAbilityBlockList').slideToggle();
}


function esovsOnDocReady()
{
	$('.esovsSkillTypeTitle').click(OnEsoSkillTypeTitleClick);
	$('.esovsSkillLineTitle').click(OnEsoSkillLineTitleClick);
	
	//$('.esovsAbilityBlock').click(OnEsoSkillBlockClick);
	$('.esovsAbilityBlockPlus').click(OnEsoSkillBlockClick);
}


$( document ).ready(esovsOnDocReady);	