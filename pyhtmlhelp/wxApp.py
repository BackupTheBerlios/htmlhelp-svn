#!/usr/bin/python


from wxPython.wx import *
from wxPython.html import *
import os


wxHF_TOOLBAR = 0x0001
wxHF_CONTENTS = 0x0002
wxHF_INDEX = 0x0004
wxHF_SEARCH = 0x0008
wxHF_BOOKMARKS = 0x0010
wxHF_PRINT = 0x0040
wxHF_FLAT_TOOLBAR = 0x0080
wxHF_MERGE_BOOKS = 0x0100
wxHF_ICONS_BOOK = 0x0200
wxHF_ICONS_BOOK_CHAPTER = 0x0400
wxHF_ICONS_FOLDER = 0x0000 # this is 0 since it is default
wxHF_DEFAULT_STYLE = (wxHF_TOOLBAR | wxHF_CONTENTS | wxHF_INDEX | wxHF_SEARCH | wxHF_BOOKMARKS | wxHF_PRINT)

wxID_HIGHEST = 100
wxID_HTML_PANEL = wxID_HIGHEST + 2
wxID_HTML_BACK = wxID_HIGHEST + 3
wxID_HTML_FORWARD = wxID_HIGHEST + 4
wxID_HTML_UPNODE = wxID_HIGHEST + 5
wxID_HTML_UP = wxID_HIGHEST + 6
wxID_HTML_DOWN = wxID_HIGHEST + 7
wxID_HTML_PRINT = wxID_HIGHEST + 8
wxID_HTML_OPENFILE = wxID_HIGHEST + 9
wxID_HTML_OPTIONS = wxID_HIGHEST + 10
wxID_HTML_BOOKMARKSLIST = wxID_HIGHEST + 11
wxID_HTML_BOOKMARKSADD = wxID_HIGHEST + 12
wxID_HTML_BOOKMARKSREMOVE = wxID_HIGHEST + 13
wxID_HTML_TREECTRL = wxID_HIGHEST + 14
wxID_HTML_INDEXPAGE = wxID_HIGHEST + 15
wxID_HTML_INDEXLIST = wxID_HIGHEST + 16
wxID_HTML_INDEXTEXT = wxID_HIGHEST + 17
wxID_HTML_INDEXBUTTON = wxID_HIGHEST + 18
wxID_HTML_INDEXBUTTONALL = wxID_HIGHEST + 19
wxID_HTML_NOTEBOOK = wxID_HIGHEST + 20
wxID_HTML_SEARCHPAGE = wxID_HIGHEST + 21
wxID_HTML_SEARCHTEXT = wxID_HIGHEST + 22
wxID_HTML_SEARCHLIST = wxID_HIGHEST + 23
wxID_HTML_SEARCHBUTTON = wxID_HIGHEST + 24
wxID_HTML_SEARCHCHOICE = wxID_HIGHEST + 25
wxID_HTML_COUNTINFO = wxID_HIGHEST + 26

class MyHtmlHelpHtmlWindow(wxHtmlWindow):

	def __init__(self, frame, parent):
		wxHtmlWindow.__init__(self, parent)
		self.m_Frame = frame
		
	def OnLinkClicked(self, link):
		self.m_Frame.NotifyPageChanged()

class MyFrame(wxFrame):

	def __init__(self, parent, id, title, style = wxHF_DEFAULT_STYLE):
		wxFrame.__init__(self,parent,-4, title, style=wxDEFAULT_FRAME_STYLE|wxNO_FULL_REPAINT_ON_RESIZE)

		self.SetIcon(wxArtProvider_GetIcon(wxART_HELP, wxART_HELP_BROWSER))

		self.CreateStatusBar()

		toolBar = self.CreateToolBar(wxNO_BORDER | wxTB_HORIZONTAL | wxTB_FLAT)
		self.AddToolBarButtons(toolBar, style)
		toolBar.Realize()
		EVT_TOOL_RANGE(self, wxID_HTML_PANEL, wxID_HTML_OPTIONS, self.OnToolbar)

		if style & (wxHF_CONTENTS | wxHF_INDEX | wxHF_SEARCH):
			# traditional help controller; splitter window with html page on the
			# right and a notebook containing various pages on the left
			m_Splitter = wxSplitterWindow(self, -1)
			self.m_Splitter = m_Splitter

			m_HtmlWin = MyHtmlHelpHtmlWindow(self, m_Splitter)
			m_NavigPan = wxPanel(m_Splitter, -1)
			self.m_NavigPan = m_NavigPan
			m_NavigNotebook = wxNotebook(m_NavigPan, wxID_HTML_NOTEBOOK, wxDefaultPosition, wxDefaultSize)
			nbs = wxNotebookSizer(m_NavigNotebook)
			
			navigSizer = wxBoxSizer(wxVERTICAL)
			navigSizer.Add(nbs, 1, wxEXPAND)

			m_NavigPan.SetAutoLayout(TRUE)
			m_NavigPan.SetSizer(navigSizer)
		else:
			# only html window, no notebook with index,contents etc
			m_HtmlWin = wxHtmlWindow(self)
		self.m_HtmlWin = m_HtmlWin

		self.m_TitleFormat = '%s'
		m_HtmlWin.SetRelatedFrame(self, self.m_TitleFormat)
		m_HtmlWin.SetRelatedStatusBar(0)

		# contents tree panel?
		if style & wxHF_CONTENTS:
			panel = wxPanel(m_NavigNotebook, wxID_HTML_INDEXPAGE)
			topsizer = wxBoxSizer(wxVERTICAL)
			
			panel.SetAutoLayout(TRUE)
			panel.SetSizer(topsizer)

			m_ContentsBox = wxTreeCtrl(panel, wxID_HTML_TREECTRL, wxDefaultPosition, wxDefaultSize, wxSUNKEN_BORDER | wxTR_HAS_BUTTONS | wxTR_HIDE_ROOT | wxTR_LINES_AT_ROOT)
			#m_ContentsBox.AssignImageList(ContentsImageList)
			
			topsizer.Add(m_ContentsBox, 1, wxEXPAND | wxALL)

			m_NavigNotebook.AddPage(panel, "Contents")

		# index list panel?
		if style & wxHF_INDEX:
			panel = wxPanel(m_NavigNotebook, wxID_HTML_INDEXPAGE);	   
			topsizer = wxBoxSizer(wxVERTICAL)

			panel.SetAutoLayout(TRUE)
			panel.SetSizer(topsizer)

			m_IndexText = wxTextCtrl(panel, wxID_HTML_INDEXTEXT, '', wxDefaultPosition, wxDefaultSize, wxTE_PROCESS_ENTER)
			m_IndexList = wxListBox(panel, wxID_HTML_INDEXLIST, wxDefaultPosition, wxDefaultSize, style=wxLB_SINGLE)

			topsizer.Add(m_IndexText, 0, wxEXPAND | wxALL)
			topsizer.Add(m_IndexList, 1, wxEXPAND | wxALL)

			m_NavigNotebook.AddPage(panel, "Index")

		# search list panel?
		if style & wxHF_SEARCH:
			panel = wxPanel(m_NavigNotebook, wxID_HTML_INDEXPAGE);	   
			sizer = wxBoxSizer(wxVERTICAL)

			panel.SetAutoLayout(TRUE)
			panel.SetSizer(sizer)

			m_SearchText = wxTextCtrl(panel, wxID_HTML_SEARCHTEXT, '', wxDefaultPosition, wxDefaultSize, wxTE_PROCESS_ENTER)
			m_SearchChoice = wxChoice(panel, wxID_HTML_SEARCHCHOICE, wxDefaultPosition, wxDefaultSize)
			m_SearchCaseSensitive = wxCheckBox(panel, -1, "Case sensitive")
			m_SearchWholeWords = wxCheckBox(panel, -1, "Whole words only")
			m_SearchList = wxListBox(panel, wxID_HTML_SEARCHLIST, wxDefaultPosition, wxDefaultSize, style=wxLB_SINGLE)
										 
			sizer.Add(m_SearchText, 0, wxEXPAND | wxALL)
			sizer.Add(m_SearchChoice, 0, wxEXPAND | wxLEFT | wxRIGHT | wxBOTTOM)
			sizer.Add(m_SearchCaseSensitive, 0, wxLEFT | wxRIGHT)
			sizer.Add(m_SearchWholeWords, 0, wxLEFT | wxRIGHT)
			sizer.Add(m_SearchList, 1, wxEXPAND | wxALL)

			m_NavigNotebook.AddPage(panel, "Search")

		# bookmar list panel?
		if style & wxHF_BOOKMARKS:
			panel = wxPanel(m_NavigNotebook, wxID_HTML_INDEXPAGE);	   
			topsizer = wxBoxSizer(wxVERTICAL)

			panel.SetAutoLayout(TRUE)
			panel.SetSizer(topsizer)

			m_BookmarksList = wxListBox(panel, wxID_HTML_INDEXLIST, wxDefaultPosition, wxDefaultSize, style=wxLB_SINGLE)

			topsizer.Add(m_BookmarksList, 1, wxEXPAND | wxALL)

			m_NavigNotebook.AddPage(panel, "Bookmarks")

		m_HtmlWin.Show(TRUE)

		#self.RefreshLists()

		if navigSizer:
			navigSizer.SetSizeHints(m_NavigPan)
			m_NavigPan.Layout()

		class Cfg:
			pass
			
		m_Cfg = Cfg()
		m_Cfg.navig_on = 1
		m_Cfg.sashpos = 250

		# showtime
		if m_NavigPan and m_Splitter:
			m_Splitter.SetMinimumPaneSize(20)
			if m_Cfg.navig_on:
				m_Splitter.SplitVertically(m_NavigPan, m_HtmlWin, m_Cfg.sashpos)

			if m_Cfg.navig_on:
				m_NavigPan.Show(TRUE)
				m_Splitter.SplitVertically(m_NavigPan, m_HtmlWin, m_Cfg.sashpos)
			else:
				m_NavigPan.Show(FALSE)
				m_Splitter.Initialize(m_HtmlWin)
	
	def AddToolBarButtons(self, toolBar, style):
		wpanelBitmap = wxArtProvider_GetBitmap(wxART_HELP_SIDE_PANEL, wxART_HELP_BROWSER)
		wbackBitmap = wxArtProvider_GetBitmap(wxART_GO_BACK, wxART_HELP_BROWSER)
		wforwardBitmap = wxArtProvider_GetBitmap(wxART_GO_FORWARD, wxART_HELP_BROWSER)
		wupnodeBitmap = wxArtProvider_GetBitmap(wxART_GO_TO_PARENT, wxART_HELP_BROWSER)
		wupBitmap = wxArtProvider_GetBitmap(wxART_GO_UP, wxART_HELP_BROWSER)
		wdownBitmap = wxArtProvider_GetBitmap(wxART_GO_DOWN, wxART_HELP_BROWSER)
		wopenBitmap = wxArtProvider_GetBitmap(wxART_FILE_OPEN, wxART_HELP_BROWSER)
		wprintBitmap = wxArtProvider_GetBitmap(wxART_PRINT, wxART_HELP_BROWSER)

		assert wpanelBitmap.Ok() and wbackBitmap.Ok() and wforwardBitmap.Ok() and wupnodeBitmap.Ok() and wupBitmap.Ok() and wdownBitmap.Ok() and wopenBitmap.Ok() and wprintBitmap.Ok()

		toolBar.AddSimpleTool(wxID_HTML_PANEL, wpanelBitmap, "Show/hide navigation panel")

		toolBar.AddSeparator()
		toolBar.AddSimpleTool(wxID_HTML_BACK, wbackBitmap, "Go back")
		toolBar.AddSimpleTool(wxID_HTML_FORWARD, wforwardBitmap, "Go forward")
		toolBar.AddSeparator()

		toolBar.AddSimpleTool(wxID_HTML_UPNODE, wupnodeBitmap, "Go one level up in document hierarchy")
		toolBar.AddSimpleTool(wxID_HTML_UP, wupBitmap, "Previous page")
		toolBar.AddSimpleTool(wxID_HTML_DOWN, wdownBitmap, "Next page")

		if style & wxHF_PRINT:
			toolBar.AddSimpleTool(wxID_HTML_PRINT, wprintBitmap, "Print this page")
		
		
	def OnToolbar(self, event):
		
		if event.GetId() == wxID_HTML_PANEL:
			if not (self.m_Splitter and self.m_NavigPan):
				return
		
			if self.m_Splitter.IsSplit():
				self.sashpos = self.m_Splitter.GetSashPosition()
				self.m_Splitter.Unsplit(self.m_NavigPan);
				#m_Cfg.navig_on = FALSE
			else:
				self.m_NavigPan.Show(TRUE)
				self.m_HtmlWin.Show(TRUE)
				self.m_Splitter.SplitVertically(self.m_NavigPan, self.m_HtmlWin, self.sashpos)
				#m_Cfg.navig_on = TRUE
		
	
if __name__ == "__main__":
	app = wxPySimpleApp()
	frame = MyFrame(None, -1, "HTML Help Books")
	frame.Show(TRUE)
	app.MainLoop()
