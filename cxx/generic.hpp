/*!
 * \file generic.hpp
 * Generic HTML help book.
 */

#ifndef GENERIC_HPP
#define GENERIC_HPP


#include <iostream>
#include <list>
#include <memory>
#include <string>

#include <book.hpp>


namespace generic {

class contents_entry;

typedef std::list< contents_entry > contents_entry_list;

class contents_entry {
	public:
		contents_entry * parent;

		htmlhelp::name name;
		htmlhelp::link link;

		contents_entry_list children;

		contents_entry() {}

		contents_entry(const htmlhelp::name & _name, const htmlhelp::link & _link) : name(_name), link(_link);
} ;

class contents_cursor: public htmlhelp::contents_cursor
{
	private:
		const contents_entry_list & _contents_entry_list;
		
	public:
		contents_cursor(const contents_entry &entry, ), _entry(entry) { }
}


class book {
	public:
		htmlhelp::name title;
		htmlhelp::link link;

		contents_entry_list contents;
		index_entry_list index;
} ;

	
//! Tree node
/*!
 * \todo Consider the use STL containers instead of hand-crafted tree nodes to
 * allow more flexibility (such as in-memory modification of a book).
 */
class tree_node
{

	protected:
		tree_node * _parent;
		tree_node * _prev_sibling;
		tree_node * _next_sibling;
		tree_node * _children_head;
		tree_node * _children_tail;

	public:
		tree_node()
		{
			_parent = NULL;
			_prev_sibling = NULL;
			_next_sibling = NULL;
			_children_head = NULL;
			_children_tail = NULL;
		}

		virtual ~tree_node()
		{
			if(_parent)
			{
				if(_parent->_children_head == this)
					_parent->_children_head = _next_sibling;

				if(_parent->_children_tail == this)
					_parent->_children_tail = _prev_sibling;
			}

			if(_prev_sibling)
				_prev_sibling->_next_sibling = _next_sibling;
			
			if(_next_sibling)
				_next_sibling->_prev_sibling = _prev_sibling;

			tree_node * child = _children_head;
			while(child)
			{
				tree_node * tmp = child->_next_sibling;
				delete child;
				child = tmp;
			}
		}
		
		//! Set the parent entry
		void set_parent(tree_node *parent)
		{
			_parent = parent;
		}
		
		//! Get the parent entry
		tree_node * get_parent(void)
		{
			return _parent;
		}

		//! Get the parent entry
		const tree_node * get_parent(void) const
		{
			return _parent;
		}
		
		//! Append a child entry
		void append(tree_node * child)
		{
			if(!_children_tail)
			{
				_children_head = _children_tail = child;
			}
			else
			{
				_children_tail->_next_sibling = child;
				child->_prev_sibling = _children_tail;
				_children_tail = child;
			}

			child->_parent = this;
		}
		
		//! Get the previous sibling entry
		tree_node * get_prev(void)
		{
			return _prev_sibling;
		}

		//! Get the previous sibling entry
		const tree_node * get_prev(void) const
		{
			return _prev_sibling;
		}
		
		//! Get the next sibling entry
		tree_node * get_next(void)
		{
			return _next_sibling;
		}

		//! Get the next sibling entry
		const tree_node * get_next(void) const
		{
			return _next_sibling;
		}
		
		//! Get the children entries
		tree_node * get_children(void)
		{
			return _children_head;
		}

		//! Get the children entries
		const tree_node * get_children(void) const
		{
			return _children_head;
		}

} ;

//! Entry in a table of contents
class generic_contents_entry: public contents_entry, private tree_node
{
	private:
		name _name;
		link _link;

	public:
		generic_contents_entry()
		{
		}

		generic_contents_entry(const name &n, const link &l) : _name(n), _link(l)
		{
		}

		virtual ~generic_contents_entry()
		{
		}
		
		//! Set the name of this entry
		void set_name(const name & n)
		{
			_name = n;
		}
		
		//! Get the name of this entry
		const name & get_name(void) const
		{
			return _name;
		}
		
		//! Set the link of this entry
		void set_link(const link & l)
		{
			_link = l;
		}
		
		//! Get the link of this entry
		const link & get_link(void) const
		{
			return _link;
		}

		//! Set the parent entry
		void set_parent(contents_entry *parent)
		{
			tree_node::set_parent(static_cast<tree_node *>(_parent));
		}
		
		//! Get the parent entry
		contents_entry * get_parent(void)
		{
			return static_cast<generic_contents_entry *>(tree_node::get_parent());
		}

		//! Get the parent entry
		const contents_entry * get_parent(void) const
		{
			return static_cast<const generic_contents_entry *>(tree_node::get_parent());
		}
		
		//! Get the previous sibling entry
		contents_entry * get_prev(void)
		{
			return static_cast<generic_contents_entry *>(tree_node::get_prev());
		}

		//! Get the previous sibling entry
		const contents_entry * get_prev(void) const
		{
			return static_cast<const generic_contents_entry *>(tree_node::get_prev());
		}
		
		//! Get the next sibling entry
		contents_entry * get_next(void)
		{
			return static_cast<generic_contents_entry *>(tree_node::get_next());
		}

		//! Get the next sibling entry
		const contents_entry * get_next(void) const
		{
			return static_cast<const generic_contents_entry *>(tree_node::get_next());
		}
		
		//! Append a child entry
		void append_child(contents_entry * child)
		{
			tree_node::append(static_cast<tree_node *>(dynamic_cast<generic_contents_entry *>(child)));
		}
		
		//! Get the children entries
		contents_entry * get_children(void)
		{
			return static_cast<generic_contents_entry *>(tree_node::get_children());
		}

		//! Get the children entries
		const contents_entry * get_children(void) const
		{
			return static_cast<const generic_contents_entry *>(tree_node::get_children());
		}
} ;


//! Link in an index entry
class generic_link_item: public link_item
{
	private:
		friend class generic_index_entry;

		link _link;
		
		generic_link_item * _prev;
		generic_link_item * _next;

	public:
		generic_link_item()
		{
			_prev = NULL;
			_next = NULL;
		}

		generic_link_item(const link &l) : _link(l)
		{
			_prev = NULL;
			_next = NULL;
		}

		virtual ~generic_link_item()
		{
			if(_prev)
				_prev->_next = _next;
			
			if(_next)
				_next->_prev = _prev;
		}
		
		//! Set the link of this entry
		void set_link(const link & l)
		{
			_link = l;
		}
		
		//! Get the link of this entry
		const link & get_link(void) const
		{
			return _link;
		}

		//! Get the previous entry
		link_item * get_prev(void)
		{
			return _prev;
		}

		//! Get the previous entry
		const link_item * get_prev(void) const
		{
			return _prev;
		}
		
		//! Get the next entry
		link_item * get_next(void)
		{
			return _next;
		}

		//! Get the next entry
		const link_item * get_next(void) const
		{
			return _next;
		}
} ;


//! Entry in an index
class generic_index_entry : public index_entry, private tree_node
{
	private:
		name _name;
		link_item * _links_head;
		link_item * _links_tail;

	public:
		generic_index_entry()
		{
			_links_head = NULL;
			_links_tail = NULL;
		}

		generic_index_entry(const name &n) : _name(n)
		{
			_links_head = NULL;
			_links_tail = NULL;
		}

		virtual ~index_entry()
		{
			link_item * child = _links_head;
			while(child)
			{
				link_item * tmp = child->_next;
				delete child;
				child = tmp;
			}
		}
		
		//! Set the name of this entry
		void set_name(const name & n)
		{
			_name = n;
		}
		
		//! Get the name of this entry
		const name & get_name(void) const
		{
			return _name;
		}
		
		//! Append a link of this entry
		void append_link(const link & l)
		{
			link_item * _link = new generic_link_item(l);
				
			if(!_links_tail)
			{
				_links_head = _links_tail = _link;
			}
			else
			{
				_links_tail->_next = _link;
				_link->_prev = _links_tail;
				_links_tail = _link;
			}
		}
		
		//! Get the links of this entry
		const link_item * get_links(void) const
		{
			return _links_head;
		}

		//! Set the parent entry
		void set_parent(index_entry *parent)
		{
			tree_node::set_parent(static_cast<tree_node *>(parent));
		}
		
		//! Get the parent entry
		index_entry * get_parent(void)
		{
			return static_cast<generic_index_entry *>(tree_node::get_parent());
		}

		//! Get the parent entry
		const index_entry * get_parent(void) const
		{
			return static_cast<const generic_index_entry *>(tree_node::get_parent());
		}
		
		//! Get the previous sibling entry
		index_entry * get_prev(void)
		{
			return static_cast<generic_index_entry *>(tree_node::get_prev());
		}

		//! Get the previous sibling entry
		const index_entry * get_prev(void) const
		{
			return static_cast<const generic_index_entry *>(tree_node::get_prev());
		}
		
		//! Get the next sibling entry
		index_entry * get_next(void)
		{
			return static_cast<generic_index_entry *>(tree_node::get_next());
		}

		//! Get the next sibling entry
		const index_entry * get_next(void) const
		{
			return static_cast<const generic_index_entry *>(tree_node::get_next());
		}
		
		//! Append a child entry
		void append_child(index_entry * child)
		{
			tree_node::append(static_cast<tree_node *>(dynamic_cast<generic_index_entry *>(child)));
		}
		
		//! Get the children entries
		index_entry * get_children(void)
		{
			return static_cast<generic_index_entry *>(tree_node::get_children());
		}

		//! Get the children entries
		const index_entry * get_children(void) const
		{
			return static_cast<const generic_index_entry *>(tree_node::get_children());
		}
} ;


//! FIXME:
typedef int archive;

//! Abstract HTML help book
class generic_book: public book
{
	protected:
		generic_contents _contents;
		generic_index _index;
		archive * _archive;
		
	public:
		generic_book();

		virtual ~generic_book();

		const contents & get_contents(void) const
		{
			return _contents;
		}

		//! Get the book index
		const index & get_index(void) const
		{
			return _index;
		}

		//! Get a resource
		virtual resource get_resource(const link & link) const = 0;
} ;



}

#endif
