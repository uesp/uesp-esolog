window.EsoAchTree_LastOpenTree = null;
window.EsoAchTree_LastOpenTreeName = null;
window.EsoAchTree_LastContentName = null;
window.EsoAchTree_LastContent = null;


window.lastAchSearchText = "";
window.lastAchSearchPos = -1;
window.lastAchSearchElement = null;



function OnEsoAchievementTreeName1Click(e)
{
	EsoAchTree_LastOpenTreeName = $('.ecdAchTreeName1.ecdAchTreeNameHighlight').first();
	EsoAchTree_LastOpenTree = EsoAchTree_LastOpenTreeName.next(".ecdAchTreeContent1");
	
	if (EsoAchTree_LastOpenTree.is($(this).next(".ecdAchTreeContent1"))) return;
	
	EsoAchTree_LastOpenTree.slideUp();
	EsoAchTree_LastOpenTreeName.removeClass('ecdAchTreeNameHighlight');
		
	EsoAchTree_LastContentName = $('.ecdAchTreeNameHighlight2').first();
	//EsoAchTree_LastContentName.removeClass('ecdAchTreeNameHighlight2');
	$(".ecdAchTreeNameHighlight2").removeClass("ecdAchTreeNameHighlight2");
	EsoAchTree_LastContent = $('.ecdAchData:visible').first();
	EsoAchTree_LastContent.hide();
		
	EsoAchTree_LastOpenTreeName = $(this);
	EsoAchTree_LastOpenTree = EsoAchTree_LastOpenTreeName.next(".ecdAchTreeContent1");
	
	EsoAchTree_LastOpenTreeName.addClass('ecdAchTreeNameHighlight');
	EsoAchTree_LastOpenTree.slideDown();

	SelectEsoAchievementTreeContents(EsoAchTree_LastOpenTree.children(".ecdAchTreeName2").first());
}


function OnEsoAchievementTreeName2Click(e)
{
	SelectEsoAchievementTreeContents($(this));
}


function SelectEsoAchievementTreeContents(object)
{
	EsoAchTree_LastContentName = $('.ecdAchTreeNameHighlight2').first();
	EsoAchTree_LastContent = $('.ecdAchData:visible').first();
	
	//EsoAchTree_LastContentName.removeClass('ecdAchTreeNameHighlight2');
	$(".ecdAchTreeNameHighlight2").removeClass("ecdAchTreeNameHighlight2");
	EsoAchTree_LastContent.hide();
	
	EsoAchTree_LastContentName = object;
	object.addClass('ecdAchTreeNameHighlight2');
	
	var catName = EsoAchTree_LastOpenTreeName.attr("achcategory");
	var subCatName = EsoAchTree_LastContentName.attr("achsubcategory");
	
	var idName = "ecdAch_" + catName + "_" + subCatName;
	if (subCatName == null || subCatName == "") idName = "ecdAch_" + catName;
	
	idName = idName.replace(/ /g, "").replace(/'/g, "");
	
	EsoAchTree_LastContent = $('#'+idName); 
	EsoAchTree_LastContent.show(); 
}



function OnSlideAchievementComplete()
{
	//SlideAchievementIntoView($(this).parent());
}


function SlideAchievementIntoView(element, instant)
{
	var offset = $(element).offset(); 

	offset.left -= 20;
	offset.top -= 20;
		
	$('html, body').animate({
			scrollTop: offset.top,
			scrollLeft: offset.left
	});
	
	return;
	
	var offsetTop = element.position().top;
	var parent = element.parent(".ecdAchData");
	var nextAch = element.next(".ecdAchievement1");
	var nextTop = offsetTop + element.height() + 25;
	var bottomScroll = parent.scrollTop() + parent.height();
	var delay = 400;
	
	if (instant) delay = 0;
	
	if (offsetTop < 0)
	{
		parent.animate({ 
	        scrollTop: offsetTop + parent.scrollTop(),
	    }, delay);
	}
	else if (nextTop > parent.height())
	{
		parent.animate({ 
	        scrollTop: nextTop - parent.height() + parent.scrollTop(),
	    }, delay);
	}
}


function OnAchievementClick()
{
	var dataBlock = $(this).children(".ecdAchDataBlock");
	dataBlock.slideToggle(400, OnSlideAchievementComplete);
	
	//if (dataBlock.length == 0) SlideAchievementIntoView($(this));
}


function OnFindEsoCharAchievement(element)
{
	var text = $("#ecdFindAchInput").val().trim().toLowerCase();
	if (text == "") return;
	
	if (text != lastAchSearchText)
	{
		lastAchSearchText = text;
		lastAchSearchPos = -1;
		lastAchSearchElement = null;
	}
	
	FindEsoCharNextAchievement();
}


function FindEsoCharNextAchievement()
{
	var isFound = false;
	
	$(".ecdAchSearchHighlight").removeClass("ecdAchSearchHighlight");
		
	$(".ecdAchName, .ecdAchDesc, .ecdAchReward, .ecdAchCriteria, .ecdAchListItem img").each(function(index) {
		if (index <= lastAchSearchPos) return true;
		var $this = $(this);
		var text = $this.text().toLowerCase();
		var title = $this.attr("title");
		if (title == null) title = "";
		title = title.toLowerCase();
		
		lastAchSearchPos = index;
		
		if (text.indexOf(lastAchSearchText) >= 0 || title.indexOf(lastAchSearchText) >= 0) 
		{
			var achievement = $this.closest(".ecdAchievement1");
			if (lastAchSearchElement != null && achievement.is(lastAchSearchElement)) return true;
			
			lastAchSearchElement = achievement;
			SelectFoundAchievement($this);
			isFound = true;
			return false
		}
	});
	
	if (!isFound)
	{
		lastAchSearchText = "";
		lastAchSearchPos = -1;
		lastAchSearchElement = null;
		$(".ecdFindAchTextBox button").text("Done!");
	}
	else
	{
		$(".ecdFindAchTextBox button").text("Find Next");
	}
}


function SelectFoundAchievement(element)
{
	var achievement = $(element).closest(".ecdAchievement1");
	var parent = $(element).closest(".ecdAchData");
	var parentId = parent.attr("id");
	var catData = parentId.split("_");
	var catName = catData[1];
	var subCatName = catData[2];
	var currentCat = $(".ecdAchTreeNameHighlight").attr("achcategory");
	var currentSubCat = $(".ecdAchTreeNameHighlight2").attr("achsubcategory");
	
	if (currentCat != catName)
	{
		$(".ecdAchTreeContent1:visible").slideUp();
		$(".ecdAchTreeNameHighlight").removeClass("ecdAchTreeNameHighlight");
		
		EsoAchTree_LastOpenTreeName = $(".ecdAchTreeName1[achcategory='" + catName + "']");
		EsoAchTree_LastOpenTreeName.addClass("ecdAchTreeNameHighlight");
		
		EsoAchTree_LastOpenTree = EsoAchTree_LastOpenTreeName.next(".ecdAchTreeContent1");
		EsoAchTree_LastOpenTree.slideDown();

		currentSubCat = "";
	}
	
	if (currentSubCat != subCatName)
	{
		$(".ecdAchTreeNameHighlight2").removeClass("ecdAchTreeNameHighlight2");
		
		EsoAchTree_LastOpenTreeName = $(".ecdAchTreeNameHighlight");
		EsoAchTree_LastOpenTree = EsoAchTree_LastOpenTreeName.next(".ecdAchTreeContent1");
		
		EsoAchTree_LastContentName = $(".ecdAchTreeName2[achsubcategory='" + subCatName + "']");
		EsoAchTree_LastContentName.addClass('ecdAchTreeNameHighlight2');
	}
	
	$(".ecdAchData:visible").hide();
	EsoAchTree_LastContent = parent;
	parent.show();
	
	achievement.addClass("ecdAchSearchHighlight");
	var dataBlock = achievement.children(".ecdAchDataBlock");
	if (dataBlock.length > 0) dataBlock.slideDown(0, OnSlideAchievementComplete);
	
	SlideAchievementIntoView(achievement, true);
}


function OnEsoAchievementDocReady()
{
	$('.ecdAchTreeName1').click(OnEsoAchievementTreeName1Click);
	$('.ecdAchTreeName2').click(OnEsoAchievementTreeName2Click);
	
	EsoAchTree_LastOpenTreeName = $('.ecdAchTreeName1:visible').first();
	EsoAchTree_LastOpenTree = EsoAchTree_LastOpenTreeName.next(".ecdAchTreeContent1");
	
	EsoAchTree_LastContentName = $('.ecdAchTreeNameHighlight2').first();
	EsoAchTree_LastContent = $('.ecdAchData:visible').first();
	
	$(".ecdSelectAchievement1").click(OnAchievementClick);
	
	$("#ecdFindAchInput").keyup(function(e) {
		if (e.keyCode == 13) 
			OnFindEsoCharAchievement();
		else
			$(".ecdFindAchTextBox button").text("Find...");
	});
	
}


$( document ).ready(OnEsoAchievementDocReady);
