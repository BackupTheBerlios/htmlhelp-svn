/*!
 * \file devhelp.hpp
 * DevHelp books.
 */

#ifndef DEVHELP_HPP
#define DEVHELP_HPP


#include <iostream>

#include "generic.hpp"


namespace htmlhelp {

	
//! DevHelp book
class devhelp_book: public generic_book
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
		virtual ~devhelp_factory();
		
		virtual book * operator() (const path &filename) = 0;
} ;


}

#endif
