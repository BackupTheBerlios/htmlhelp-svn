/*!
 * \file book.hpp
 * \brief HTML help book abstraction.
 */

#ifndef BOOK_HPP
#define BOOK_HPP


#include <string>


//! HTML help library namespace
namespace htmlhelp {

	
//! Title, TOC entries names, etc.
typedef std::wstring name;


//! Resource locator
typedef std::string link;


typedef unsigned number;


class cursor
{
	public:
		//! Destructor
		virtual ~cursor() {}
} ;


class slist_cursor: public cursor
{
	public:
		//! Advance to the next entry
		virtual bool next(void){ return false; }
} ;

class list_cursor: public slist_cursor
{
	public:
		//! Advance to the previous entry
		virtual bool prev(void){ return false; }
} ;

class tree_cursor: public list_cursor
{
	public:
		//! Advance to the parent entry
		virtual bool parent(void) { return false; }
		
		//! Advance to the first children entry
		virtual bool children(void) { return false; }
} ;

//! Entry in a table of contents
class contents_cursor: public cursor
{
	public:
		//! Copy
		virtual contents_cursor * copy(void) const = 0;
		
		//! Get the entry number
		virtual number get_number(void) const = 0;
		
		//! Get the entry name
		virtual name get_name(void) const = 0;
		
		//! Get the entry link
		virtual link get_link(void) const = 0;
} ;


//! Link in an index entry
class index_link_cursor: list_cursor
{
	public:
		//! Get the link of this entry
		virtual link get_link(void) const = 0;
} ;


//! Entry in an index
class index_cursor: tree_cursor
{
	public:
		//! Copy
		virtual contents_cursor * copy(void) const = 0;
		
		//! Get the entry name
		virtual name get_name(void) const = 0;
		
		//! Get the links of this entry
		virtual index_link_cursor * get_links(void) const = 0;

} ;


typedef std::string type;


typedef std::size_t length;


//! Book page (HTML page, image, etc.)
class page_cursor: list_cursor
{
	public:
		//! Get the link associated with the resource
		virtual link get_link(void) const = 0;

		//! Get the content type
		virtual type get_type(void) const = 0;

		//! Get the content length
		virtual length get_length(void) const = 0;

		//! Read a buffer
		virtual length read(void * buffer, std::size_t length) = 0;
} ;


//! Abstract HTML help book
class book_cursor: list_cursor
{
	public:
		//! Get the book title
		virtual name get_title(void) const = 0;
		
		//! Get the default link
		virtual link get_default_link(void) const = 0;

		//! Get the book table of contents
		virtual contents_cursor * get_contents(void) const = 0;

		//! Get the book index
		virtual index_cursor * get_index(void) const = 0;

		//! Get the archive containing the book resources
		virtual page_cursor * get_page(const link & _link) const = 0;
		
		virtual page_cursor * get_pages(void) const = 0;
} ;


typedef std::string path;


//! A book factory
class factory
{
	public:
		//! Destructor
		virtual ~factory() {}

		//! Attempt to open a book
		virtual book_cursor * operator() (const path &filename) const = 0;
} ;


//! A catalog entry
catalog
{
	public:
		//! Destructor
		virtual ~catalog_entry() {}

		virtual book_cursor * get_books(void) const = 0;
} ;


//! A book catalog
typedef catalog_entry catalog;


}

#endif
