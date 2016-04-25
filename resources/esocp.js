

function OnTestLeft(e)
{
	var element = $("#esovcpRoot");
	var angle = parseInt(element.css('rotate'));
	var newAngle = angle - 360/9;
	
	element.transition({ rotate: newAngle });
}


function OnTestRight(e)
{
	var element = $("#esovcpRoot");
	var angle = parseInt(element.css('rotate'));
	var newAngle = angle + 360/9;
	
	element.transition({ rotate: newAngle });
}


function OnTestZoomIn (e)
{
	var element = $("#esovcpRoot");
	element.transition({ top: '98%', scale: 3.7 });
}


function OnTestZoomOut (e)
{
	var element = $("#esovcpRoot");
	element.transition({ top: '0%', scale: 1 });
}


function LoadHiresBackgroundImage(target, imageUrl)
{
	var $downloadingImage = $("<img>").attr("src", imageUrl).load(function() {
		target.css("background-image", "url('" + imageUrl + "')");
	});
}


function LoadHiresCPImages()
{
	LoadHiresBackgroundImage($("#esovcpCPDisc_Tower"), 'resources/champion_constellations-1-med.png');
	LoadHiresBackgroundImage($("#esovcpCPDisc_Lord"), 'resources/champion_constellations-2-med.png');
	LoadHiresBackgroundImage($("#esovcpCPDisc_Lady"), 'resources/champion_constellations-3-med.png');
	LoadHiresBackgroundImage($("#esovcpCPDisc_Steed"), 'resources/champion_constellations-4-med.png');
	LoadHiresBackgroundImage($("#esovcpCPDisc_Ritual"), 'resources/champion_constellations-5-med.png');
	LoadHiresBackgroundImage($("#esovcpCPDisc_Atronach"), 'resources/champion_constellations-6-med.png');
	LoadHiresBackgroundImage($("#esovcpCPDisc_Apprentice"), 'resources/champion_constellations-7-med.png');
	LoadHiresBackgroundImage($("#esovcpCPDisc_Shadow"), 'resources/champion_constellations-8-med.png');
	LoadHiresBackgroundImage($("#esovcpCPDisc_Lover"), 'resources/champion_constellations-9-med.png');
}


function OnEsoCPHover(e)
{
	$(this).parent().addClass("esovcpDiscplineHighlight")
}


function OnEsoCPLeave(e)
{
	$(this).parent().removeClass("esovcpDiscplineHighlight")
}


function esovcpOnDocReady()
{
	$("#testLeft").click(OnTestLeft);
	$("#testRight").click(OnTestRight);
	$("#testZoomIn").click(OnTestZoomIn);
	$("#testZoomOut").click(OnTestZoomOut);

	LoadHiresCPImages();
	
	$(".esovpDisciplineHoverArea").hover(OnEsoCPHover, OnEsoCPLeave);
}


$( document ).ready(esovcpOnDocReady);