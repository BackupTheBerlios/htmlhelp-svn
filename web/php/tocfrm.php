<?php

$title = 'Table of Contents';
$id = 'toc';
// TODO: Add filtering to TOC
//$search_button = 'Filter';
require 'inc/frmheader.inc.php';

function walk_children($entries, &$linage)
{
	if(count($entries))
	{
		echo '<ul class="tree">';
		foreach($entries as $number => $entry)
		{
			list($name, $link, $children) = $entry;
			walk_toc($number, $name, $link, $children, $linage);
		}
		echo '</ul>';
	}
}

function walk_toc($number, $name, $link, $children, &$linage)
{
	global $book, $alias, $toc_nos;
	
	$has_children = count($children);

	if($has_children)
	{
		if($toc_nos[$number])
			echo '<li class="expanded">';
		else
			echo '<li class="collapsed">';
	}
	else
		echo '<li class="single">';

	array_push($linage, $number);
	
	if($toc_nos[$number] || !$has_children)
		echo '<a href="' . $link . '">';
	else
		echo '<a href="../../tocfrm.php?book=' . htmlspecialchars($alias) . '&amp;toc_nos=' . implode('+', $linage) . '" target="_self">';
	echo htmlspecialchars($name, ENT_NOQUOTES) . '</a>';
	
	if($toc_nos[$number] && $has_children)
		walk_children($children, $linage);
	
	array_pop($linage);
	
	echo '</li>';
}

$toc_nos = array();
foreach(explode(' ', $_GET['toc_nos']) as $toc_no)
	$toc_nos[intval($toc_no)] = TRUE;
$linage = array();
walk_children($book->toc(), $linage);
	
require 'inc/frmfooter.inc.php';
?>
