/*!
 * \file book.hpp
 * HTML help book abstraction.
 */

#ifndef BOOK_HPP
#define BOOK_HPP


#include <iostream>
#include <list>
#include <memory>
#include <string>


namespace htmlhelp {

	
//! Title, TOC entries names, etc.
typedef std::wstring name;


//! Resource locator
typedef std::string link;


//! Shared by TOC and index entry 
class entry
{
	protected:
		name _name;
		link _link;

	public:
		entry(void)
		{
		}

		entry(const name n, const link l) : _name(n), _link(l)
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
} ;
		

//! Entry in a table of contents
class contents_entry: public entry
{
	protected:
		typedef std::list<contents_entry> contents_entry_list;
		
		contents_entry_list _children;

	public:
		typedef contents_entry_list::iterator iterator;
		
		typedef contents_entry_list::const_iterator const_iterator;
			
		iterator begin(void)
		{
			return _children.begin();
		}
		
		const_iterator begin(void) const
		{
			return _children.begin();
		}

		iterator end(void)
		{
			return _children.end();
		}
		
		const_iterator end(void) const
		{
			return _children.end();
		}
		
		void push_back(contents_entry c)
		{
			_children.push_back(c);
		}
		
} ;


//! Book table of contents
typedef contents_entry contents;


//! Entry in an index
typedef entry index_entry;


//! Book index
typedef std::list<index_entry> index;


//! A resource (HTML page, image, etc.) in a book
typedef std::istream resource;


//! Abstract HTML help book
class book
{
	protected:
		contents _contents;
		index _index;
		
	public:
		book ();

		virtual ~book();

		//! Get the book's title
		const name & get_title(void) const
		{
			return _contents.get_name();
		}
		
		//! Get the default link
		const link & get_default_link(void) const
		{
			return _contents.get_link();
		}

		//! Get the book table of contents
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


//! Reference counted pointer to a book;
//typedef auto_ptr<const book> book_reference;
typedef const book * book_reference;


//! A book factory
class factory
{
	private:
		typedef std::list<const factory *> factory_list;

		static factory_list _factories;
	
	public:
		factory();
		
		virtual ~factory();

		virtual bool can_open(const std::string &f) const = 0;
		
		virtual book_reference open(const std::string &f) const = 0;
			
		static book_reference create(const std::string &f);
} ;


}

#endif
