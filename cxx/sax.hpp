/*!
 * \file sax.hpp
 * Thin wrappers around libxml2 XML and HTML parsers.
 */

#ifndef SAX_HPP
#define SAX_HPP


#include <iostream>
#include <string>
#include <map>


namespace sax {
	

//! Parser abstract base class 
class parser
{
	public:
		virtual ~parser(void);
		
		//! Document start callback
		virtual void start_document(void);

		//! Document end callback
		virtual void end_document(void);

		//! Comment callback
		virtual void comment(const std::wstring &contents);

		//! Text callback
		virtual void text(const std::wstring &contents);

		//! Parse from string
		virtual void parse(const std::string &s) = 0;
		
		//! Parse from a stream
		virtual void parse(std::istream &is) = 0;
} ;

//! Base class for the libxml based parsers
class libxml_parser : public parser
{
	private:
		//! Pointer to the xmlSAXHandler structure
		void * _handler;

	protected:
		virtual void parse_chunk(const char *chunk, int size, int terminate) = 0;

	public:
		libxml_parser(void);
		
		virtual ~libxml_parser(void);

		//! Element start callback
		virtual void start_element(const std::wstring &tag, const std::map<std::wstring, std::wstring> &attrs);

		//! Element end callback
		virtual void end_element(const std::wstring &tag);

		//! CDATA callback
		virtual void cdata(const std::wstring &contents);

		void parse(std::string &s);
		
		void parse(std::istream &is);
} ;


//! A XML parser
class xml_parser: public libxml_parser
{
	protected:
		void parse_chunk(const char *chunk, int size, int terminate);
} ;


//! A HTML parser
class html_parser: public libxml_parser
{
	protected:
		void parse_chunk(const char *chunk, int size, int terminate);
} ;


//! A Windows INI file parser
class ini_parser: public parser
{
	protected:
		void parse_line(std::wstring line);
		
	public:
		ini_parser();

		virtual ~ini_parser();

		//! Section start callback (implying the end of the previous section).
		virtual void start_section(const std::wstring &name);

		//! Option (i.e., a <i>name = value</i> pair) callback
		virtual void option(const std::wstring &name, const std::wstring &value);

		void parse(std::string &s);
		
		void parse(std::istream &is);
} ;


}

#endif
