/*!
 * \file utf.cpp
 * UTF-8 convertion functions (implementation).
 *
 * \sa The \link http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8 UTF-8 and
 * Unicode FAQ \endlink.
 *
 * \warning These functions do not check for overlong UTF-8 sequences as
 * required by the \link
 * http://www.unicode.org/unicode/uni2errata/UTF-8_Corrigendum.html Unicode
 * Standard \endlink.
 */


#include "utf.hpp"


void utf8_to_wchar(std::wstring &ws, const char *s)
{
	wchar_t c;

	while((c = *s++))
	{
		if(!(c & 0x80))
			ws += c;
		else
		{
			wchar_t t = c & 0x1f;
			
			while(c & 0x40 && *s)
			{
				t = (t << 6) | (*s++ & 0x3f);
				c <<= 1;
			}
			
			ws += t;
		}
	};
}

void utf8_to_wchar(std::wstring &ws, const char *s, size_t n)
{
	wchar_t c;

	while(n--)
	{
		c = *s++;

		if(!(c & 0x80))
			ws += c;
		else
		{
			wchar_t t = c & 0x1f;
			
			while(c & 0x40 && n--)
			{
				t = (t << 6) | (*s++ & 0x3f);
				c <<= 1;
			}
			
			ws += t;
		}
	};
}
