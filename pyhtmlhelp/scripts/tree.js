// tree.js
//
// Description:
// 
//   Script for controlling an expandable tree in HTML.
//
// See also:
//
//   http://www.oreillynet.com/pub/a/javascript/2002/02/22/hierarchical_menus.html
//   http://wsabstract.com/script/cut51.shtml
// 
// Author:
//
//   José Fonseca


function toggle(e) {
	parentNode = e.target;
	for(i = 0; i < parentNode.childNodes.length; i++) {
		childNode = parentNode.childNodes.item(i);
		if(childNode.tagName == 'UL') {
			if(childNode.style.display == 'none') {
				childNode.style.display = 'block';
				parentNode.style.listStyleImage = 'url(tree_minus.png)';
			} else {
				childNode.style.display = 'none';
				parentNode.style.listStyleImage = 'url(tree_plus.png)';
			}
			break;
		}
	}
}

function toggle2(e) {
	parentNode = e.target;
	if ( parentNode.className == 'closed') {
		parentNode.className = 'open';
		for(i = 0; i < parentNode.childNodes.length; i++) {
			if ( parentNode.childNodes.item(i).className == 'closed')
				parentNode.childNodes.item(i).className = 'open';
		}
	} else if ( parentNode.className == 'open') {
		parentNode.className = 'closed';
		for(i = 0; i < parentNode.childNodes.length; i++) {
			if ( parentNode.childNodes.item(i).className == 'open')
				parentNode.childNodes.item(i).className = 'closed';
		}
	}
}

document.onclick = toggle2;


