function onBookCommand(event)
{
  var menu = event.target;
  var book_id = menu.value;
  
  window.title = menu.label;

  var toc = document.getElementById("toc");
  toc.setAttribute("src", "toc.xul.php?book_id=" + book_id);

  var index = document.getElementById("index");
  index.setAttribute("src", "_index.xul.php?book_id=" + book_id);
  
  var search = document.getElementById("search");
  search.setAttribute("src", "search.xul.php?book_id=" + book_id);
  
  var body = document.getElementById("body");
  body.setAttribute("src", "page.php/" + book_id + "/");
}
