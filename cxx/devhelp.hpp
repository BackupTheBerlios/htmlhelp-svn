/*!
 * \file devhelp.hpp
 * DevHelp books.
 */

#ifndef DEVHELP_HPP
#define DEVHELP_HPP


#include <iostream>

#include "book.hpp"


namespace htmlhelp {

	
//! DevHelp book
class devhelp_book: public book
{
	public:
		virtual ~devhelp_book();

		//! Get a resource
		resource get_resource(const link & link) const;
} ;


//! DevHelp book factory
class devhelp_factory: public factory
{
	public:
		bool can_open(const std::string &f) const;
		
		book_reference open(const std::string &f) const;
} ;


}

#endif
