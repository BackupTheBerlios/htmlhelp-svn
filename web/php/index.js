var xul = 0;


if(navigator.userAgent.indexOf("Gecko") >= 0)
	xul = 1;


function openBook(book_id)
{
	if(xul)
		// XXX: 'chrome' option is not applicable here, as it prevents
		// the history buttons to work
		window.open('book.xul.php?book_id=' + book_id, '', 'resizable=yes,scrollbars=yes,toolbar=no,menubar=no,location=no,status=yes,directories=no');
	else
		window.open('book.php?book_id=' + book_id, '', 'resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,status=yes,directories=no,width=640,height=420');
	return false;
}
