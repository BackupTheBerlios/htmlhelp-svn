function onTocSelect(event, book_id)
{
  var tree = event.target;
  var link = tree.view.getCellValue(tree.currentIndex, "name");

  var iframe = parent.frames['body'];
  iframe.location.href = "page.php/" + book_id + "/" + link;
}
