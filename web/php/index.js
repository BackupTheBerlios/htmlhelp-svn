var xul = 0;


if(navigator.userAgent.indexOf("Gecko") >= 0)
	xul = 1;


function openBook(book_id)
{
	var options = 'resizable=yes,scrollbars=yes,toolbar=no,menubar=no,location=no,status=yes,directories=no';
				
	if(xul)
		// XXX: if 'chrome' option is set here, the history does not work
		window.open('book.xul.php?book_id=' + book_id, '', options);
	else
		window.open('book.php?book_id=' + book_id, '', options);
	return false;
}
