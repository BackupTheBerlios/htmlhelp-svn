<?php
	include 'config.inc.php';
	include 'book.inc.php';

	$title = $book->title;

	include 'header.inc.php';
?>
	<body>
		<div class="header"><?php echo htmlentities($book->title, ENT_NOQUOTES, 'UTF-8');?></div>
		<div class="menubar">
			<div class="left">
				<a href="toc.php?book_id=<?php echo $book->id;?>" target="side">Contents</a> |
				<a href="index_.php?book_id=<?php echo $book->id;?>" target="side">Index</a>
			</div>
			<div class="right">
				<a href="catalog.php" target="_parent">Catalog</a>
			</div>
			<form id="search" action="search.php" target="side">
				<input type="hidden" name="book_id" value="<?php echo $book->id; ?>" />
				<input type="text" name="query" value="<?php echo $query; ?>"/>
				<input type="submit" value="Search">
			</form>
		</div>
	</body>
<?php
	include 'footer.inc.php';
?>
