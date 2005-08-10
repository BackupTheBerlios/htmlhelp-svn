<?php

class Searchable
{
	// Should be overriden by derived classes
	function search_lexeme($lexeme)
	{
		return array();
	}

	function search($query)
	{
		$search = Search::parse($query);
		return $search->apply($this);
	}
}

class Search
{
	// Class method to parse query
	function parse($query)
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

	// Should be overriden by derived classes
	function apply($searchable)
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
		return $book->search_lexeme($this->term);
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

?>
