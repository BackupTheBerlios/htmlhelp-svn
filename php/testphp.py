#!/usr/bin/python
"""Modified CGI HTTP server to handle PHP scripts."""


import os
import sys
import urllib
import select
import CGIHTTPServer
import urlparse


class PhpRequestHandler(CGIHTTPServer.CGIHTTPRequestHandler):

	def _send_head(self):
		"""Version of send_head that support CGI scripts"""
		if self.is_cgi():
			return self.run_cgi()
		else:
			path = self.translate_path(self.path)
			f = None
			if os.path.isdir(path):
				for index in "index.html", "index.htm", "index.php":
					index = os.path.join(path, index)
					if os.path.exists(index):
						path = index
						break
				else:
					return self.list_directory(path)
			ctype = self.guess_type(path)
			if ctype.startswith('text/'):
				mode = 'r'
			else:
				mode = 'rb'
			try:
				f = open(path, mode)
			except IOError:
				self.send_error(404, "File not found")
				return None
			self.send_response(200)
			self.send_header("Content-type", ctype)
			self.send_header("Content-Length", str(os.fstat(f.fileno())[6]))
			self.end_headers()
			return f

	def is_cgi(self):
		scheme, netloc, path, query, fragment = urlparse.urlsplit(self.path)
		if path[-4:] == '.php':
			self.cgi_info = '/usr/lib/cgi-bin', 'php4' + self.path
			return True

		return False

	def run_cgi(self):
		"""Execute a CGI script."""
		dir, rest = self.cgi_info
		i = rest.rfind('?')
		if i >= 0:
			rest, query = rest[:i], rest[i+1:]
		else:
			query = ''
		i = rest.find('/')
		if i >= 0:
			script, rest = rest[:i], rest[i:]
		else:
			script, rest = rest, ''
		scriptname = dir + '/' + script
		scriptfile = scriptname
		if not os.path.exists(scriptfile):
			self.send_error(404, "No such CGI script (%s)" % `scriptname`)
			return
		if not os.path.isfile(scriptfile):
			self.send_error(403, "CGI script is not a plain file (%s)" %
							`scriptname`)
			return
		ispy = self.is_python(scriptname)
		if not ispy:
			if not (self.have_fork or self.have_popen2 or self.have_popen3):
				self.send_error(403, "CGI script is not a Python script (%s)" %
								`scriptname`)
				return
			if not self.is_executable(scriptfile):
				self.send_error(403, "CGI script is not executable (%s)" %
								`scriptname`)
				return

		# Reference: http://hoohoo.ncsa.uiuc.edu/cgi/env.html
		# XXX Much of the following could be prepared ahead of time!
		env = {}
		#env['REDIRECT_REQUEST'] = self.path
		#env['REDIRECT_URL'] = self.path
		env['REDIRECT_STATUS'] = '200'
		env['SERVER_SOFTWARE'] = self.version_string()
		env['SERVER_NAME'] = self.server.server_name
		env['GATEWAY_INTERFACE'] = 'CGI/1.1'
		env['SERVER_PROTOCOL'] = self.protocol_version
		env['SERVER_PORT'] = str(self.server.server_port)
		env['REQUEST_METHOD'] = self.command
		uqrest = urllib.unquote(rest)
		env['PATH_INFO'] = uqrest
		env['PATH_TRANSLATED'] = self.translate_path(uqrest)
		env['SCRIPT_NAME'] = scriptname
		if query:
			env['QUERY_STRING'] = query
		host = self.address_string()
		if host != self.client_address[0]:
			env['REMOTE_HOST'] = host
		env['REMOTE_ADDR'] = self.client_address[0]
		# XXX AUTH_TYPE
		# XXX REMOTE_USER
		# XXX REMOTE_IDENT
		if self.headers.typeheader is None:
			env['CONTENT_TYPE'] = self.headers.type
		else:
			env['CONTENT_TYPE'] = self.headers.typeheader
		length = self.headers.getheader('content-length')
		if length:
			env['CONTENT_LENGTH'] = length
		accept = []
		for line in self.headers.getallmatchingheaders('accept'):
			if line[:1] in "\t\n\r ":
				accept.append(line.strip())
			else:
				accept = accept + line[7:].split(',')
		env['HTTP_ACCEPT'] = ','.join(accept)
		ua = self.headers.getheader('user-agent')
		if ua:
			env['HTTP_USER_AGENT'] = ua
		co = filter(None, self.headers.getheaders('cookie'))
		if co:
			env['HTTP_COOKIE'] = ', '.join(co)
		env['HTTP_HOST'] = self.server.server_name
		# XXX Other HTTP_* headers
		if not self.have_fork:
			# Since we're setting the env in the parent, provide empty
			# values to override previously set values
			for k in ('QUERY_STRING', 'REMOTE_HOST', 'CONTENT_LENGTH',
					  'HTTP_USER_AGENT', 'HTTP_COOKIE'):
				env.setdefault(k, "")
		os.environ.update(env)

		self.send_response(200, "Script output follows")

		decoded_query = query.replace('+', ' ')

		if self.have_fork:
			# Unix -- fork as we should
			args = [script]
			if '=' not in decoded_query:
				args.append(decoded_query)
			nobody = CGIHTTPServer.nobody_uid()
			self.wfile.flush() # Always flush before forking
			pid = os.fork()
			if pid != 0:
				# Parent
				pid, sts = os.waitpid(pid, 0)
				# throw away additional data [see bug #427345]
				while select.select([self.rfile], [], [], 0)[0]:
					if not self.rfile.read(1):
						break
				if sts:
					self.log_error("CGI script exit status %#x", sts)
				return
			# Child
			try:
				try:
					os.setuid(nobody)
				except os.error:
					pass
				os.dup2(self.rfile.fileno(), 0)
				os.dup2(self.wfile.fileno(), 1)
				os.execve(scriptfile, args, os.environ)
			except:
				self.server.handle_error(self.request, self.client_address)
				os._exit(127)

		elif self.have_popen2 or self.have_popen3:
			# Windows -- use popen2 or popen3 to create a subprocess
			import shutil
			if self.have_popen3:
				popenx = os.popen3
			else:
				popenx = os.popen2
			cmdline = scriptfile
			if self.is_python(scriptfile):
				interp = sys.executable
				if interp.lower().endswith("w.exe"):
					# On Windows, use python.exe, not pythonw.exe
					interp = interp[:-5] + interp[-4:]
				cmdline = "%s -u %s" % (interp, cmdline)
			if '=' not in query and '"' not in query:
				cmdline = '%s "%s"' % (cmdline, query)
			self.log_message("command: %s", cmdline)
			try:
				nbytes = int(length)
			except (TypeError, ValueError):
				nbytes = 0
			files = popenx(cmdline, 'b')
			fi = files[0]
			fo = files[1]
			if self.have_popen3:
				fe = files[2]
			if self.command.lower() == "post" and nbytes > 0:
				data = self.rfile.read(nbytes)
				fi.write(data)
			# throw away additional data [see bug #427345]
			while select.select([self.rfile._sock], [], [], 0)[0]:
				if not self.rfile._sock.recv(1):
					break
			fi.close()
			shutil.copyfileobj(fo, self.wfile)
			if self.have_popen3:
				errors = fe.read()
				fe.close()
				if errors:
					self.log_error('%s', errors)
			sts = fo.close()
			if sts:
				self.log_error("CGI script exit status %#x", sts)
			else:
				self.log_message("CGI script exited OK")

		else:
			# Other O.S. -- execute script in this process
			save_argv = sys.argv
			save_stdin = sys.stdin
			save_stdout = sys.stdout
			save_stderr = sys.stderr
			try:
				try:
					sys.argv = [scriptfile]
					if '=' not in decoded_query:
						sys.argv.append(decoded_query)
					sys.stdout = self.wfile
					sys.stdin = self.rfile
					execfile(scriptfile, {"__name__": "__main__"})
				finally:
					sys.argv = save_argv
					sys.stdin = save_stdin
					sys.stdout = save_stdout
					sys.stderr = save_stderr
			except SystemExit, sts:
				self.log_error("CGI script exit status %s", str(sts))
			else:
				self.log_message("CGI script exited OK")


if __name__ == '__main__':
	CGIHTTPServer.test(PhpRequestHandler)
