<?php
	include 'config.inc.php';
	include 'book.inc.php';

	$title = $book->title;

	include 'header.inc.php';
?>
	<frameset rows="80,*">
		<frame src="header.php?book_id=<?php echo $book->id;?>" name="header" frameborder="0" scrolling="no" noresize="noresize"/>
		<frameset rows="*" cols="256,*">
			<frame src="toc.php?book_id=<?php echo $book->id;?>" name="side" frameborder="0"/>
			<frame src="page.php/<?php echo $book->id . '/' . $book->default_path . '#' . $book->default_anchor;?>" name="main" frameborder="0"/>
		</frameset>
	</frameset>
<?php
	include 'footer.inc.php';
?>
