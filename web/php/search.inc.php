<?php

class SearchResult
{
	// TODO: write another version of this class which compiles SQL statements

	var $entries;

	function SearchResult($entries) 
	{
		// FIXME: use references instead of copies where applicable
		$this->entries = $entries;
	}

	function union($other) 
	{
		// FIXME: implement this
	}

	function intersection($other)
	{
		$entries = array();
		foreach($this->entries as $lentry)
		{
			foreach($other->entries as $rentry)
			{
				if($lentry[0] == $rentry[0])
				{
					$entries[] = $lentry;
					break;
				}
			}
		}
		return new SearchResult($entries);
	}

	function subtraction($other)
	{
		// FIXME: implement this
	}

	function list_()
	{
		return $this->entries;
	}
}

class Searchable
{
	// Should be overriden by derived classes
	function search_lexeme($lexeme)
	{
		return new SearchResult(array());
	}

	function search($query)
	{
		$search = Search::parse($query);
		return $search->apply($this)->list_();
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

	function apply($searchable)
	{
		return $searchable->search_lexeme($this->term);
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

	function apply($searchable)
	{
		return $this->left->apply($searchable)->intersection($this->right->apply($searchable));
	}
}

?>
