var g_SkillsData = {};	// Loaded asynchronously at load time
var g_SkillSearchIds = [];
var g_SkillHighlightId = 0;
var g_SkillHighlightType = "";
var g_SkillHighlightLine = "";
var g_SkillShowAll = false;
var g_SkillUseUpdate10Cost = true;

g_SkillDisplayType = "summary";

var g_EsoSkillPointsUsed = 0;
var g_EsoSkillPassiveData = [];
var g_EsoSkillActiveData = [];
var g_EsoSkillBarData = [[[],[],[],[],[],[]],[[],[],[],[],[],[]],[[],[],[],[],[],[]],[[],[],[],[],[],[]]];


function OnReceiveEsoSkillClientData(skillData)
{
	g_SkillsData = skillData;
}


function OnEsoSkillClientHover(e)
{
	var skillid = $(this).attr("skillid");
	
	if (skillid == null || skillid == "") return;
	
	var skillData = g_SkillsData[parseInt(skillid)];
	
	console.log("OnEsoSkillClientHover", skillid, skillData);
	
	EsoShowPopupSkillTooltip(skillData, $(this)[0]);
}


function OnEsoSkillClientLeave(e)
{
	var popupElement = $("#esovsPopupSkillTooltip");
	popupElement.hide();
}


function EsoSkillClientOnReady()
{
	
	$.ajax({
        url: '//esolog.uesp.net/getSkillData.php',
        type: 'get',
        dataType: 'json',
        cache: false,
        success: OnReceiveEsoSkillClientData,
        async:true,
        });
	
	
	if (g_EsoSkillIsMobile)
	{
		$(".eso_skill_tooltip").click(function(e) {
			setTimeout(function() {	OnEsoSkillClientHover.bind(this, e); }, 250); 
			e.preventDefault(); 
			e.stopPropagation(); 
			return false; });
	}
	else
	{
		$(".eso_skill_tooltip").hover(OnEsoSkillClientHover, OnEsoSkillClientLeave);
	}
	
	
}



$( document ).ready(EsoSkillClientOnReady);