var iframe = parent.frames['body'];
  
function onTocSelect(event, book_id)
{
  var tree = event.target;
  var link = tree.view.getCellValue(tree.currentIndex, "name");

  iframe.location.href = "page.php/" + book_id + "/" + link;
}

function onButtonCommand(event)
{
  var tree = document.getElementById("tree");

  var href = parent.body.location.href;
  var base = document.location.href.replace(/\/toc\.xul\.php\?book_id=(.*)$/,'/page.php/$1/')

  if(href.substr(0, base.length) == base)
  {
    var link = href.substr(base.length);
    
    // FIXME: this only searches the visible rows
    for(row = 0; row < tree.view.rowCount; row++)
    {
    	if(tree.view.getCellValue(row, "name") == link)
	{
	  tree.view.selection.select(row);
	  return;
	}
    }
    alert("Could not find topic for current page.");
  }
}

// FIXME: eventually make the sync automatic
//setTimeout("onTimeout()", 1000);

