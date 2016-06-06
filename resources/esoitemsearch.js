function copyToClipboard(self)
{
	var textToCopy = $(self).text();
	
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

	$('.esois_rawitemlink').click(function() {
	   	copyToClipboard(this);
	});
	 
});


function esoisResetSearchForm()
{
	$('#esois_text').val('');
	$('#esois_trait').val('');
	$('#esois_style').val('');
	$('#esois_quality').val('');
	$('#esois_itemtype').val('');
	$('#esois_equiptype').val('');
	$('#esois_armortype').val('');
	$('#esois_weapontype').val('');
	$('#esois_enchant').val('');
	$('#esois_effect').val('');
	$('#esois_level').val('');
	$('#esois_variablestyle').prop('checked', false);	
}