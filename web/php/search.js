function onQueryKeypress(event, book_id)
{
  if(event.keyCode == KeyEvent.DOM_VK_ENTER || event.keyCode == KeyEvent.DOM_VK_RETURN)
  {
    var text = event.target;
    var query = text.value;

    document.location.href = "search.xul.php?book_id=" + book_id + "&query=" + encodeURIComponent(query);
  }
}

function onSearchSelect(event, book_id)
{
  var list = event.target;
  var link = list.value;

  parent.content.location.href = "page.php/" + book_id + "/" + link;
}
