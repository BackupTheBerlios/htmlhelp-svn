function onBookSelect(event)
{
  var list = event.target;
  var book_id = list.value;
  
  window.open('index.xul.php?book_id=' + book_id, 'htmlhelp', 'chrome');
  return false;
}
