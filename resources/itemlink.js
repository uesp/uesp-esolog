function onRenderItem(canvas)
{
	var element = document.getElementById("esoil_outeritembox");
	console.log("received canvas!");
	element.parentNode.insertBefore(canvas, element.nextSibling);
}


function renderItem()
{
	var element = document.getElementById("esoil_outeritembox");
	if (element == null) return false;
	
	var options = {
			background: undefined,
			logging: true,
			onrendered: onRenderItem,
	};
		
	html2canvas(element, options);
	return true;
}