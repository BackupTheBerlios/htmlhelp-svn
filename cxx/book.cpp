/*!
 * \file book.cpp
 * HTML help book abstraction (implementation).
 */


#include "book.hpp"


namespace htmlhelp {

	
book::book()
{
}

book::~book()
{
}


factory::factory()
{
	_factories.push_back(this);
}

factory::~factory()
{
	// FIXME: fill in here...
}

book_reference factory::create(const std::string &f)
{
	factory_list::const_iterator i;

	for(i = _factories.begin(); i != _factories.end(); ++i)
	{
		if((**i).can_open(f))
			return (**i).open(f);
	}
	return book_reference();	
}


}
