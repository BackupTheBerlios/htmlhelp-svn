<?php

class Search_Result
{
	// TODO: write another version of this class which compiles SQL statements; 
	// however it will be difficult not to resort to subselects...

	// FIXME: use sets
	var $entries;

	function Search_Result(&$entries) 
	{
		$this->entries = &$entries;
	}

	function union(&$other) 
	{
		// FIXME: implement this
	}

	function intersection(&$other)
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
		return new Search_Result($entries);
	}

	function subtraction(&$other)
	{
		// FIXME: implement this
	}

	function enumerate()
	{
		return $this->entries;
	}
}

class Searchable
{
	// Should be overriden by derived classes
	function search_lexeme($lexeme)
	{
		return new Search_Result(array());
	}
	
	function search($query)
	{
		$search = & Search_Parser::parse($query);
		return $search->apply($this)->enumerate();
	}
}

class Search_Node
{
	// Should be overriden by derived classes
	function apply(&$searchable)
	{
		// TODO: include score
		return array();
	}
}

class Search_TermNode extends Search_Node
{
	var $term;

	function Search_TermNode($term)
	{
		$this->term = $term;
	}

	function apply(&$searchable)
	{
		return $searchable->search_lexeme($this->term);
	}
}

class Search_AndNode extends Search_Node
{
	var $left_node;
	var $right_node;

	function Search_AndNode(&$left_node, &$right_node)
	{
		$this->left_node = &$left_node;
		$this->right_node = &$right_node;
	}

	function apply(&$searchable)
	{
		$lresult = & $this->left_node->apply($searchable);
		$rresult = & $this->right_node->apply($searchable);
		return $lresult->intersection($rresult);
	}
}

class Search_Parser
{
	// Class method to parse query
	function parse($query)
	{
		// FIXME: tokenize $terms
		// TODO: implement more complex searches
		$terms = explode(' ', $query);

		$search = & new Search_TermNode($terms[0]);
		unset($terms[0]);
		foreach($terms as $term)
		{
			$search = & new Search_AndNode($search, new Search_TermNode($term));
		}
		return $search;
	}
}

?>
