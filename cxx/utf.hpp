/*!
 * \file utf.hpp
 * UTF-8 convertion functions.
 */

#ifndef UTF_HPP
#define UTF_HPP


#include <string>


//! Convert a zero terminated string into a STL wide string
void utf8_to_wchar(std::wstring &ws, const char *s);

//! Convert a fixed length string into a STL wide string
void utf8_to_wchar(std::wstring &ws, const char *s, size_t n);


#endif
