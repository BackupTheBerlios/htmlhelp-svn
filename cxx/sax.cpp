/*!
 * \file sax.cpp
 * SAX  (implementation).
 */


#include <cstdlib>
#include <new>

#include <libxml/parser.h>

#include "sax.hpp"
#include "utf.hpp"


namespace sax {


parser::~parser(void)
{
}
	

/*!
 * \name xmlSAXHandler callbacks
 */
/*@{*/

static void _startDocument(void *ctx)
{
	static_cast<libxml_parser *>(ctx)->start_document();
}

static void _endDocument(void *ctx)
{
	static_cast<libxml_parser *>(ctx)->end_document();
}

static void _startElement(void *ctx, const xmlChar *name, const xmlChar **atts)
{
	std::wstring tag;
	utf8_to_wchar(tag, reinterpret_cast<const char*>(name));
	
	std::map<std::wstring, std::wstring> attrs;
	while(*atts)
	{
		std::wstring key, value;
		utf8_to_wchar(key, reinterpret_cast<const char*>(*atts++));
		utf8_to_wchar(value, reinterpret_cast<const char*>(*atts++));
		attrs[key] = value;
	}
	
	static_cast<libxml_parser *>(ctx)->start_element(tag, attrs);
}

static void _endElement(void *ctx, const xmlChar *name)
{
	std::wstring tag;
	utf8_to_wchar(tag, reinterpret_cast<const char*>(name));
	
	static_cast<libxml_parser *>(ctx)->end_element(tag);
}

static void _characters(void *ctx, const xmlChar *ch, int len)
{
	std::wstring contents;
	utf8_to_wchar(contents, reinterpret_cast<const char*>(ch), len);
	
	static_cast<libxml_parser *>(ctx)->text(contents);
}

static void _comment(void *ctx, const xmlChar *value)
{
	std::wstring contents;
	utf8_to_wchar(contents, reinterpret_cast<const char*>(value));
	
	static_cast<libxml_parser *>(ctx)->comment(contents);
}

static void _cdataBlock( void *ctx, const xmlChar *value, int len)
{
	std::wstring contents;
	utf8_to_wchar(contents, reinterpret_cast<const char*>(value), len);
	
	static_cast<libxml_parser *>(ctx)->cdata(contents);
}

#if 0
/*!
 * warning:
 * @ctx:  an XML libxml_parser context
 * @msg:  the message to display/transmit
 * @...:  extra parameters for the message display
 * 
 * Display and format a warning messages, callback.
 */
static void _warning(void *ctx, const char *msg, ...);

/*!
 * error:
 * @ctx:  an XML libxml_parser context
 * @msg:  the message to display/transmit
 * @...:  extra parameters for the message display
 * 
 * Display and format an error messages, callback.
 */
static void _error(void *ctx, const char *msg, ...);

/*!
 * fatalError:
 * @ctx:  an XML libxml_parser context
 * @msg:  the message to display/transmit
 * @...:  extra parameters for the message display
 * 
 * Display and format fatal error messages, callback.
 * Note: so far fatalError() SAX callbacks are not used, error()
 *       get all the callbacks for errors.
 */
static void _fatalError(void *ctx, const char *msg, ...);
#endif
 
/*@}*/

libxml_parser::libxml_parser(void)
{
	xmlSAXHandlerPtr handler;
	
	if(!(handler = (xmlSAXHandlerPtr)std::malloc(sizeof(xmlSAXHandler))))
		throw std::bad_alloc();

	std::memset(handler, 0, sizeof(xmlSAXHandler));

	handler->startDocument = _startDocument;
	handler->endDocument = _endDocument;
	handler->startElement = _startElement;
	handler->endElement = _endElement;
	handler->characters = _characters;
	//handler->processingInstruction = _processingInstruction;
	handler->comment = _comment;
	handler->cdataBlock = _cdataBlock;
	//handler->warning = _warning;
	//handler->error = _error;
	//handler->fatalError = _fatalError;

	_handler = handler;
}

libxml_parser::~libxml_parser(void)
{
	std::free(_handler);
}

}
