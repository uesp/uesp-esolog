

function OnTestLeft(e)
{
	var element = $("#esovcpRoot");
	var scale = element.css('scale');
	
	var angle = parseInt(element.css('rotate'));
	var newAngle = angle - 360/9;
	
	console.log(element, angle, newAngle);
	
	/*element.rotate({
	      angle: angle,
	      scale: scale,
	      animateTo: newAngle
	}); //*/
	
	element.transition({ rotate: newAngle });
}


function OnTestRight(e)
{
	var element = $("#esovcpRoot");
	var scale = element.css('scale');
	
	var angle = parseInt(element.css('rotate'));
	var newAngle = angle + 360/9;
	
	console.log(element, angle, newAngle);
	
	element.transition({ rotate: newAngle });
	
	/* element.rotate({
	      angle: angle,
	      scale: scale,
	      animateTo: newAngle
	}); //*/
}


function OnTestZoomIn (e)
{
	var element = $("#esovcpRoot");
	//element.animate({zoom: '300%'}, "slow");
	element.transition({ top: '85%', scale: 3 });
	//element.animate({top: '500px'});
}


function OnTestZoomOut (e)
{
	var element = $("#esovcpRoot");
	//element.animate({zoom: '100%'}, "slow");
	element.transition({ top: '0%', scale: 1 });
	//element.animate({});
}


function esovcpOnDocReady()
{
	$("#testLeft").click(OnTestLeft);
	$("#testRight").click(OnTestRight);
	$("#testZoomIn").click(OnTestZoomIn);
	$("#testZoomOut").click(OnTestZoomOut);
}


$( document ).ready(esovcpOnDocReady);