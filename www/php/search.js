function onQueryKeypress(event, book_id)
{
  if(event.keyCode == 13)
  {
    var text = event.target;
    var query = text.value;

    document.location.href = "search.xul.php?book_id=" + book_id + "&query=" + query;
  }
}

function onSearchSelect(event, book_id)
{
  var list = event.target;
  var link = list.value;

  var iframe = parent.frames['body'];
  iframe.location.href = "page.php/" + book_id + "/" + link;
}
