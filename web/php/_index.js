function onIndexSelect(event, book_id)
{
  var list = event.target;
  var link = list.value;

  var iframe = parent.frames['body'];
  iframe.location.href = "page.php/" + book_id + "/" + link;
}
