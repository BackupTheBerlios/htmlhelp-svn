function onBookCommand(event)
{
  var menu = event.target;
  var book_id = menu.value;
  
  // TODO: remember the splitter location

  document.location.href = 'index.xul.php?book_id=' + book_id;
}
