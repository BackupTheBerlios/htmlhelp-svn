/*!
 * \file book.cpp
 * \brief HTML help book abstraction (implementation).
 */


#include "book.hpp"


namespace htmlhelp {

	
contents_entry::contents_entry()
{
}

contents_entry::~contents_entry()
{
}


index_link::index_link()
{
}

index_link::~index_link()
{
}


index_entry::index_entry()
{
}

index_entry::~index_entry()
{
}


book::book()
{
}

book::~book()
{
}

const name & book::get_title(void) const
{
	return get_contents().get_name();
}

const link & book::get_default_link(void) const
{
	return get_contents().get_link();
}


factory::factory()
{
}

factory::~factory()
{
}


catalog_entry::catalog_entry()
{
}

catalog_entry::~catalog_entry()
{
}


}
