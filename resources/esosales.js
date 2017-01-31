function EsoSalesResetSearchForm()
{
	$('#esovsd_text').val('');
	$('#esovsd_level').val('');
	$('#esovsd_trait').val('');
	$('#esovsd_quality').val('');
	$('#esovsd_itemtype').val('');
	$('#esovsd_armortype').val('');
	$('#esovsd_weapontype').val('');
	$('#esovsd_equiptype').val('');
	$('#esovsd_saletype').val('');
}


function EsoSalesCopyToClipboard(self)
{
	var textToCopy = $(self).attr("copydata");
	
	$("body")
		.append($('<input type="text" name="fname" class="textToCopyInput" style="opacity: 0; position: absolute;" />' )
				.val(textToCopy))
		.find(".textToCopyInput")
		.select();
	
	try 
	{
		var successful = document.execCommand('copy');
		var msg = successful ? 'successful' : 'unsuccessful';
		//alert('Text copied to clipboard!');
    }
	catch (err) 
    {
    	window.prompt("To copy the text to clipboard: Ctrl+C, Enter", textToCopy);
    }
	
	$(".textToCopyInput").remove();
}



$( document ).ready(function() 
{

	$('.esovsd_copyprice').click(function() {
		EsoSalesCopyToClipboard(this);
	});
	 
});