var noSelect = 0;

function onTocSelect(event, book_id)
{
	var tree = event.target;
	var link = tree.view.getCellValue(tree.currentIndex, "name");

	if(!noSelect)
		parent.content.location.href = "page.php/" + book_id + "/" + link;
}

function parentTreeItem(element)
{
	do
		element = element.parentNode;
	while(element.nodeName != 'treeitem')

	return element;
}

function showTreeItem(tree, item)
{
	if(tree.contentView.getIndexOfItem(item) == -1)
	{
		var parentItem = parentTreeItem(item);
		showTreeItem(tree, parentItem);
		parentItem.setAttribute('open', 'true');
	}
}

function selectTreeItem(tree, item)
{
	noSelect = 1;
	showTreeItem(tree, item);
	var row = tree.contentView.getIndexOfItem(item);
	tree.view.selection.select(row);
	tree.currentIndex = row;
	noSelect = 0;
	tree.treeBoxObject.ensureRowIsVisible(row);
}

function onButtonCommand(event)
{
	var tree = document.getElementById("tree");

	var href = parent.content.location.href;
	var base = document.location.href.replace(/\/toc\.xul\.php\?book_id=(.*)$/,'/page.php/$1/')

	if(href.substr(0, base.length) == base)
	{
		var link = href.substr(base.length);
		var elements = tree.contentView.root.getElementsByTagName('treecell');

		for(var i = 0; i < elements.length; ++i)
		{
			if(elements[i].getAttribute('value') == link)
			{
				var item = parentTreeItem(elements[i]);
				selectTreeItem(tree, item);
			}
		}
	}
}

// FIXME: eventually make the sync automatic
//setTimeout("onTimeout()", 1000);

