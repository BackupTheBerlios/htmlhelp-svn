function doSelect(event)
{
  // the tree is the target of the event
  var tree=event.target;

  // get the label element
  var label=document.getElementById("body");

  // get the text of the description column in the selected row.
  // First, call the getCellText function of the tree's view.
  // We need to supply two parameters, the selected row index
  // held in the tree's currentIndex property and the id of the
  // column.
  var txt=tree.view.getCellValue(tree.currentIndex,"name")

  // assign the text to the label.
  label.setAttribute("src",txt);
}

