function onBookSelect(event)
{
  var list = event.target;
  var book_id = list.value;
  
  // TODO: remember the splitter location

  parent.location.href = 'index.xul.php?book_id=' + book_id;
}
