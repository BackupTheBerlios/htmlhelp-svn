<?php
	include 'config.inc.php';
	include 'mysql.inc.php';

	if($book_id = intval($_GET['book_id']))
	{
		$books = mysql_query('SELECT * FROM `books` WHERE `id`=' . $book_id);
		$book = mysql_fetch_object($books);
		$title = htmlentities($book->title, ENT_NOQUOTES, 'UTF-8');
	}
	else
		$title = 'HTML Help Books';

	include 'header.inc.php';
?>
	<body>
		<div class="search">
			<form action="search.php" target="navigation">
				<input type="hidden" name="book_id" value="<?php echo $book_id; ?>" />
				<input type="text" name="query" value=""/>
				<input type="submit" value="Search">
			</form>
		</div>
		<div class="header"><?php echo $title;?></div>
		<div class="menubar">
<?php
	if($book_id)
	{
?>
			<span class="left">
				<a href="toc.php?book_id=<?php echo $book_id;?>" target="navigation">Contents</a> |
				<a href="_index.php?book_id=<?php echo $book_id;?>" target="navigation">Index</a>
			</span>
<?php
	}
?>
			<span class="right">
				<a href="books.php" target="navigation">Catalog</a>
			</span>
		</div>
	</body>
<?php
	include 'footer.inc.php';
?>
