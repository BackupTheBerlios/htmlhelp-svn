function goBack(event)
{
	self.content.history.back();
	return true;
}

function goForward(event)
{
	self.content.history.forward();
	return true;
}

function goHome(event, book_id)
{
	self.content.location.href = "page.php/" + book_id + "/";
	return true;
}

function print()
{
	try 
		_content.print();
	catch (e);
}
