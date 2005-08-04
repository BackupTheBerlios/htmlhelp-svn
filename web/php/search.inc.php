<?php

class Search
{
	function apply($book)
	{
		// TODO: include score
		return array();
	}
}

class TermSearch extends Search
{
	var $term;

	function TermSearch($term)
	{
		$this->term = $term;
	}

	function apply($book)
	{
		return $book->search($this->term);
	}
}

class AndSearch extends Search
{
	var $left;
	var $right;

	function AndSearch($left, $right)
	{
		$this->left = $left;
		$this->right = $right;
	}

	function apply($book)
	{
		$lentries = $this->left->apply($book);
		$rentries = $this->right->apply($book);

		$entries = array();
		foreach($lentries as $lentry)
		{
			foreach($rentries as $rentry)
			{
				if($lentry[0] == $rentry[0])
				{
					$entries[] = $lentry;
					break;
				}
			}
		}
		return $entries;
	}
}


function parse_search($query)
{
	// TODO: implement more complex searches
	$terms = explode(' ', $query);

	$search = new TermSearch($terms[0]);
	unset($terms[0]);
	foreach($terms as $term)
	{
		$search = new AndSearch($search, new TermSearch($term));
	}
	return $search;
}

?>
