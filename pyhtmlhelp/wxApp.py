#!/usr/bin/python

from wxPython.wx import *
from wxPython.html import *
import os

wxHF_TOOLBAR = 0x0001
wxHF_CONTENTS = 0x0002
wxHF_INDEX = 0x0004
wxHF_SEARCH = 0x0008
wxHF_BOOKMARKS = 0x0010
wxHF_OPEN_FILES = 0x0020
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

		toolBar = self.CreateToolBar(wxNO_BORDER | wxTB_HORIZONTAL | wxTB_DOCKABLE | wxTB_FLAT)
		toolBar.SetMargins((2, 2))
		self.AddToolBarButtons(toolBar, style)
		toolBar.Realize()

		if style & (wxHF_CONTENTS | wxHF_INDEX | wxHF_SEARCH):
			# traditional help controller; splitter window with html page on the
			# right and a notebook containing various pages on the left
			m_Splitter = wxSplitterWindow(self, -1)

			m_HtmlWin = MyHtmlHelpHtmlWindow(self, m_Splitter)
			m_NavigPan = wxPanel(m_Splitter, -1)
			m_NavigNotebook = wxNotebook(m_NavigPan, wxID_HTML_NOTEBOOK, wxDefaultPosition, wxDefaultSize)
			nbs = wxNotebookSizer(m_NavigNotebook)
			
			navigSizer = wxBoxSizer(wxVERTICAL)
			navigSizer.Add(nbs, 1, wxEXPAND)

			m_NavigPan.SetAutoLayout(TRUE)
			m_NavigPan.SetSizer(navigSizer)
		else:
			# only html window, no notebook with index,contents etc
			m_HtmlWin = wxHtmlWindow(self)

		self.m_TitleFormat = ''
		m_HtmlWin.SetRelatedFrame(self, self.m_TitleFormat)
		m_HtmlWin.SetRelatedStatusBar(0)

		notebook_page = 0

		# contents tree panel?
		if style & wxHF_CONTENTS:
			dummy = wxPanel(m_NavigNotebook, wxID_HTML_INDEXPAGE)
			topsizer = wxBoxSizer(wxVERTICAL)
			
			topsizer.Add(0, 10)
			
			dummy.SetAutoLayout(TRUE)
			dummy.SetSizer(topsizer)

			if style & wxHF_BOOKMARKS and 0:
				m_Bookmarks = wxComboBox(dummy, wxID_HTML_BOOKMARKSLIST, '', wxDefaultPosition, wxDefaultSize, 0, NULL, wxCB_READONLY | wxCB_SORT)
				m_Bookmarks.Append("(bookmarks)")
				for i in range(m_BookmarksNames.GetCount()):
					m_Bookmarks.Append(m_BookmarksNames[i])
				m_Bookmarks.SetSelection(0)

				bmpbt1 = wxBitmapButton(dummy, wxID_HTML_BOOKMARKSADD, wxArtProvider.GetBitmap(wxART_ADD_BOOKMARK, wxART_HELP_BROWSER))
				bmpbt2 = wxBitmapButton(dummy, wxID_HTML_BOOKMARKSREMOVE, wxArtProvider.GetBitmap(wxART_DEL_BOOKMARK, wxART_HELP_BROWSER))
				bmpbt1.SetToolTipString("Add current page to bookmarks")
				bmpbt2.SetToolTipString("Remove current page from bookmarks")

				sizer = wxBoxSizer(wxHORIZONTAL)
				
				sizer.Add(m_Bookmarks, 1, wxALIGN_CENTRE_VERTICAL | wxRIGHT, 5)
				sizer.Add(bmpbt1, 0, wxALIGN_CENTRE_VERTICAL | wxRIGHT, 2)
				sizer.Add(bmpbt2, 0, wxALIGN_CENTRE_VERTICAL, 0)
				
				topsizer.Add(sizer, 0, wxEXPAND | wxLEFT | wxBOTTOM | wxRIGHT, 10)

			m_ContentsBox = wxTreeCtrl(dummy, wxID_HTML_TREECTRL, wxDefaultPosition, wxDefaultSize, wxSUNKEN_BORDER | wxTR_HAS_BUTTONS | wxTR_HIDE_ROOT | wxTR_LINES_AT_ROOT)

			#m_ContentsBox.AssignImageList(ContentsImageList)
			
			topsizer.Add(m_ContentsBox, 1, wxEXPAND | wxLEFT | wxBOTTOM | wxRIGHT, 2)

			m_NavigNotebook.AddPage(dummy, "Contents")
			m_ContentsPage = notebook_page
			notebook_page += 1

		# index listbox panel?
		if style & wxHF_INDEX:
			dummy = wxPanel(m_NavigNotebook, wxID_HTML_INDEXPAGE);	   
			topsizer = wxBoxSizer(wxVERTICAL)

			dummy.SetAutoLayout(TRUE)
			dummy.SetSizer(topsizer)

			m_IndexText = wxTextCtrl(dummy, wxID_HTML_INDEXTEXT, '', wxDefaultPosition, wxDefaultSize, wxTE_PROCESS_ENTER)
			m_IndexButton = wxButton(dummy, wxID_HTML_INDEXBUTTON, "Find")
			m_IndexButtonAll = wxButton(dummy, wxID_HTML_INDEXBUTTONALL, "Show all")
			m_IndexCountInfo = wxStaticText(dummy, wxID_HTML_COUNTINFO, '', wxDefaultPosition, wxDefaultSize, wxALIGN_RIGHT | wxST_NO_AUTORESIZE)
			m_IndexList = wxListBox(dummy, wxID_HTML_INDEXLIST, wxDefaultPosition, wxDefaultSize, style=wxLB_SINGLE)

			m_IndexButton.SetToolTipString("Display all index items that contain given substring. Search is case insensitive.")
			m_IndexButtonAll.SetToolTipString("Show all items in index")

			topsizer.Add(m_IndexText, 0, wxEXPAND | wxALL, 10)
			btsizer = wxBoxSizer(wxHORIZONTAL)
			btsizer.Add(m_IndexButton, 0, wxRIGHT, 2)
			btsizer.Add(m_IndexButtonAll)
			topsizer.Add(btsizer, 0, wxALIGN_RIGHT | wxLEFT | wxRIGHT | wxBOTTOM, 10)
			topsizer.Add(m_IndexCountInfo, 0, wxEXPAND | wxLEFT | wxRIGHT, 2)
			topsizer.Add(m_IndexList, 1, wxEXPAND | wxALL, 2)

			m_NavigNotebook.AddPage(dummy, "Index")
			m_IndexPage = notebook_page
			notebook_page += 1

		# search list panel?
		if style & wxHF_SEARCH:
			dummy = wxPanel(m_NavigNotebook, wxID_HTML_INDEXPAGE);	   
			sizer = wxBoxSizer(wxVERTICAL)

			dummy.SetAutoLayout(TRUE)
			dummy.SetSizer(sizer)

			m_SearchText = wxTextCtrl(dummy, wxID_HTML_SEARCHTEXT, '', wxDefaultPosition, wxDefaultSize, wxTE_PROCESS_ENTER)
			m_SearchChoice = wxChoice(dummy, wxID_HTML_SEARCHCHOICE, wxDefaultPosition, wxDefaultSize)
			m_SearchCaseSensitive = wxCheckBox(dummy, -1, "Case sensitive")
			m_SearchWholeWords = wxCheckBox(dummy, -1, "Whole words only")
			m_SearchButton = wxButton(dummy, wxID_HTML_SEARCHBUTTON, "Search")
			m_SearchButton.SetToolTipString("Search contents of help book(s) for all occurences of the text you typed above")
			m_SearchList = wxListBox(dummy, wxID_HTML_SEARCHLIST, wxDefaultPosition, wxDefaultSize, style=wxLB_SINGLE)
										 
			sizer.Add(m_SearchText, 0, wxEXPAND | wxALL, 10)
			sizer.Add(m_SearchChoice, 0, wxEXPAND | wxLEFT | wxRIGHT | wxBOTTOM, 10)
			sizer.Add(m_SearchCaseSensitive, 0, wxLEFT | wxRIGHT, 10)
			sizer.Add(m_SearchWholeWords, 0, wxLEFT | wxRIGHT, 10)
			sizer.Add(m_SearchButton, 0, wxALL | wxALIGN_RIGHT, 8)
			sizer.Add(m_SearchList, 1, wxALL | wxEXPAND, 2)

			m_NavigNotebook.AddPage(dummy, "Search")
			m_SearchPage = notebook_page
			notebook_page += 1

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
		woptionsBitmap = wxArtProvider_GetBitmap(wxART_HELP_SETTINGS, wxART_HELP_BROWSER)

		assert wpanelBitmap.Ok() and wbackBitmap.Ok() and wforwardBitmap.Ok() and wupnodeBitmap.Ok() and wupBitmap.Ok() and wdownBitmap.Ok() and wopenBitmap.Ok() and wprintBitmap.Ok() and woptionsBitmap.Ok()

		toolBar.AddSimpleTool(wxID_HTML_PANEL, wpanelBitmap, "Show/hide navigation panel")

		toolBar.AddSeparator()
		toolBar.AddSimpleTool(wxID_HTML_BACK, wbackBitmap, "Go back")
		toolBar.AddSimpleTool(wxID_HTML_FORWARD, wforwardBitmap, "Go forward")
		toolBar.AddSeparator()

		toolBar.AddSimpleTool(wxID_HTML_UPNODE, wupnodeBitmap, "Go one level up in document hierarchy")
		toolBar.AddSimpleTool(wxID_HTML_UP, wupBitmap, "Previous page")
		toolBar.AddSimpleTool(wxID_HTML_DOWN, wdownBitmap, "Next page")

		if style & wxHF_PRINT or style & wxHF_OPEN_FILES:
			toolBar.AddSeparator()

		if style & wxHF_OPEN_FILES:
			toolBar.AddSimpleTool(wxID_HTML_OPENFILE, wopenBitmap, "Open HTML document")

		if style & wxHF_PRINT:
			toolBar.AddSimpleTool(wxID_HTML_PRINT, wprintBitmap, "Print this page")

		toolBar.AddSeparator()
		toolBar.AddSimpleTool(wxID_HTML_OPTIONS, woptionsBitmap, "Display options dialog")
		
	
ID_ABOUT=101
ID_OPEN=102
ID_BUTTON1=110
ID_EXIT=200

class MainWindow(wxFrame):
    def __init__(self,parent,id,title):
        self.dirname=''
        wxFrame.__init__(self,parent,-4, title, style=wxDEFAULT_FRAME_STYLE|
                                        wxNO_FULL_REPAINT_ON_RESIZE)
        self.control = wxTextCtrl(self, 1, style=wxTE_MULTILINE)
        self.CreateStatusBar() # A Statusbar in the bottom of the window 
        # Setting up the menu. 
        filemenu= wxMenu()
        filemenu.Append(ID_OPEN, "&Open"," Open a file to edit")
        filemenu.AppendSeparator()
        filemenu.Append(ID_ABOUT, "&About"," Information about this program")
        filemenu.AppendSeparator()
        filemenu.Append(ID_EXIT,"E&xit"," Terminate the program")
        # Creating the menubar. 
        menuBar = wxMenuBar()
        menuBar.Append(filemenu,"&File") # Adding the "filemenu" to the MenuBar 
        self.SetMenuBar(menuBar)  # Adding the MenuBar to the Frame content. 
        EVT_MENU(self, ID_ABOUT, self.OnAbout)
        EVT_MENU(self, ID_EXIT, self.OnExit)
        EVT_MENU(self, ID_OPEN, self.OnOpen)

        self.sizer2 = wxBoxSizer(wxHORIZONTAL)
        self.buttons=[]
        for i in range(0,6):
            self.buttons.append(wxButton(self, ID_BUTTON1+i, "Button &"+`i`))
            self.sizer2.Add(self.buttons[i],1,wxEXPAND)

        # Use some sizers to see layout options
        self.sizer=wxBoxSizer(wxVERTICAL)
        self.sizer.Add(self.control,1,wxEXPAND)
        self.sizer.Add(self.sizer2,0,wxEXPAND)

        #Layout sizers
        self.SetSizer(self.sizer)
        self.SetAutoLayout(1)
        self.sizer.Fit(self)

        self.Show(1)

    def OnAbout(self,e):
        d= wxMessageDialog( self, " A sample editor \n"
                            " in wxPython","About Sample Editor", wxOK)
                            # Create a message dialog box 
        d.ShowModal() # Shows it 
        d.Destroy() # finally destroy it when finished. 

    def OnExit(self,e):
        self.Close(true)  # Close the frame. 

    def OnOpen(self,e):
        """ Open a file"""
        dlg = wxFileDialog(self, "Choose a file", self.dirname, "", "*.*", wxOPEN)
        if dlg.ShowModal() == wxID_OK:
            self.filename=dlg.GetFilename()
            self.dirname=dlg.GetDirectory()
            f=open(os.path.join(self.dirname, self.filename),'r')
            self.control.SetValue(f.read())
            f.close()
        dlg.Destroy()


app = wxPySimpleApp()
frame = MyFrame(None, -1, "Sample editor")
frame.Show(1)
app.MainLoop()
