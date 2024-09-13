function OnEsoLogViewSearchRowClick()
{
	var link = $(this).find("a").first();
	
	if (link.length == 0) return;
	
	var url = link.attr("href");
	window.location = url;	
}


window.esologViewerOnDocReady = function ()
{
	$(".esologSearchRow").click(OnEsoLogViewSearchRowClick)
	
	$("#esologtable").tablesorter({
		theme : 'default'
	});
}


$( document ).ready(esologViewerOnDocReady);