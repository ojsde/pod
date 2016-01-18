/**
 * This file is part of a master thesis project at the TU Graz. Licence LGPL v2
 * Copyright Aldi Alimucaj 2012
 * Copyright Felix Gründer 2015
 */

// GLOBALS
var COOKIE_POD_FILES = 'pod_files';
var COOKIE_POD_PRINT_FILES = 'pod_print_files';
var HTML_ELEM_CART = '#cart_counter';


if ($.cookie(COOKIE_POD_PRINT_FILES)) {
	$(HTML_ELEM_CART).text($.cookie(COOKIE_POD_PRINT_FILES));
}

function setCartCounter(counter) {
	$(HTML_ELEM_CART).text(counter);
}

/**
 * increase cart counter
 */
function addPodPrint() {
	if ($.cookie(COOKIE_POD_PRINT_FILES))
		var index = parseInt($.cookie(COOKIE_POD_PRINT_FILES));
	else
		var index = 0;
	$.cookie(COOKIE_POD_PRINT_FILES, index + 1, {
		expires : 2,
		path : '/'
	});
	index = parseInt($.cookie(COOKIE_POD_PRINT_FILES));
	$(HTML_ELEM_CART).text(index);

}

function subPodPrint() {
	if ($.cookie(COOKIE_POD_PRINT_FILES))
		var index = parseInt($.cookie(COOKIE_POD_PRINT_FILES));
	else
		var index = 0;
	$.cookie(COOKIE_POD_PRINT_FILES, index - 1, {
		expires : 2,
		path : '/'
	});
	index = parseInt($.cookie(COOKIE_POD_PRINT_FILES));
	$(HTML_ELEM_CART).text(index);

}

function addPodArticle(ids) {
	var current = $.cookie(COOKIE_POD_FILES);
	if (current) {
		if (current.indexOf(ids) < 0) {
			var ret = addArticleToCookie(ids);
			if (ret) addPodPrint();
		}
	} else {
		var ret = addArticleToCookie(ids);
		if (ret) addPodPrint();
	}
	checkAddRemoveButtons();
}

function removePodArticle(ids, shortCut) {
	if (podArticleExists(ids) == true) {
		var ret = removeArticleFromCookie(ids);
		if (ret) {
			subPodPrint();
		}
		$('tr[id="' + shortCut + '"]').remove();
		resetTableCounter();
	} else {
		//alert("ArticleID: " + ids + ' not found!')
	}
	checkAddRemoveButtons();
}

function podArticleExists(ids) {
	var current = $.cookie(COOKIE_POD_FILES);
	var patt = new RegExp(ids, 'gi');
	return patt.test(current);
}

function getPodArticles() {
	return $.cookie(COOKIE_POD_FILES);
}

function addArticleToCookie(article) {
	try {
		var current = $.cookie(COOKIE_POD_FILES);
		if (current == null) {
			current = article;
		} else {
			current += current == null ? '' : ',';
			current += article;
		}

		$.cookie(COOKIE_POD_FILES, current, {
			expires : 2,
			path : '/'
		});
		return true;
	} catch (ex) {
		return false;
	}
}

function removeArticleFromCookie(article) {
	try {
		var current = $.cookie(COOKIE_POD_FILES);
		current = current.replace(article, '');
		current = current.replace(',,', ',');
		if (current == ",") {
			current = null;
		}
		
		$.cookie(COOKIE_POD_FILES, current, {
			expires : 2,
			path : '/'
		});
		return true;
	} catch (ex) {
		return false;
	}

}

/**
 * put the array of articles into a cookie
 * @param articles
 */
function setCookie(articles) {
	try {
		if (articles == "" || articles == null) {
			return false;
		}
		$.cookie(COOKIE_POD_FILES, articles, {
			expires : 2,
			path : '/'
		});
		return true;
	} catch (ex) {
		return false;
	}
}

function resetCart() {
	var url = document.location.href;
	var ref = url.substr(url.lastIndexOf('/'), url.length);

	$.cookie(COOKIE_POD_FILES, '', {
		expires : 2,
		path : '/'
	});
	$.cookie(COOKIE_POD_PRINT_FILES, 0, {
		expires : 2,
		path : '/'
	});
	setCartCounter(0);
	if (ref == '/showPodCart') {
		$('.podRemove').each(function(index) {
			$(this).closest('table').remove();

		});
		checkAddRemoveButtons();
	}
	
	location.reload();
}

function getPodIndex() {
	if ($.cookie(COOKIE_POD_PRINT_FILES))
		var index = parseInt($.cookie(COOKIE_POD_PRINT_FILES));
	else
		var index = 0;
	return index;
}

/**
 * Builds and posts the form to the address given in path.
 *
 * @param path
 *            represents the action address which expects the post information
 */
function post_pod(path) { 
	/*
	if (document.getElementById("pod_operator").value == "") {
		alert("Please chose one POD operator.");
		return;
	}
	*/

	var articles = getPodArticles();
	if (articles == null || articles == "") {
		return;
	} else {
		var method = "post";
		var form = document.createElement("form");
		form.setAttribute("method", method);
		form.setAttribute("action", path);
		
		params = articles.split(',');
		for (param in params) {
			var hiddenField = document.createElement("input");
			hiddenField.setAttribute("type", "hidden");
			hiddenField.setAttribute("name", 'Articles[]');
			hiddenField.setAttribute("value", params[param]);
			form.appendChild(hiddenField);
		}
		var hiddenField = document.createElement("input");
		hiddenField.setAttribute("type", "hidden");
		//hiddenField.setAttribute("name", 'pod_operator');
		//hiddenField.setAttribute("value", document.getElementById("pod_operator").value);
		form.appendChild(hiddenField);
		document.body.appendChild(form);
		form.submit();
	}

}

function add_form_select_list() {
	var selectedItem = $('#items_in_the_list option:selected').val();
	addPodArticle(selectedItem);
}

function remove_form_select_list() {
	var selectedItem = $('#items_in_the_list option:selected').val();
	removePodArticle(selectedItem);
}

function printWithNewOrder(path) {
	var articles = $('#podArticle').tableDnDSerialize();
	setCookie(articles);
	post_pod(path);

}

function articlesToArray() {
	var articles = getPodArticles();
	if (articles == null || articles == "") {
		return;
	} else {
		params = articles.split(',');
		return params;
	}
}

function existsArticle(id) {
	var array = articlesToArray();
	for (article in array) {
		if (array[article] == id) {
			return true;
		}
	}
	return false;
}

function articlesFromArray() {
	var articles = "";
	for (param in params) {
		articles += params[param];
		if (param != params.length - 1) {
			articles += ",";
		}
	}
}

/**
 * Function is used when drag-dropping items to arrange the printed edition.
 */
function resetTableCounter() {
	$('.tocIndex').each(function(index) {
		index++;
		$(this).text(index);
		$(this).attr('name', index);
		$(this).parent().attr('id', index);
		var tmpValue = $(this).parent().find('a[href*="javascript:removePodArticle"]').attr('href');
		tmpValue = tmpValue.substr(0, tmpValue.indexOf(','));
		$(this).parent().find('a[href*="javascript:removePodArticle"]').attr('href', tmpValue + ',' + index + ')');
		$(this).closest("tr[id]").attr('id', index);
	});
}

/**
 * Adds all articles to the pod list
 */
function addAllArticles() {
	$('.podAdd').each(function(index) {
		var id = $(this).attr('id');
		addPodArticle(id);
	});
}

$(document).ready(function() {
	$('#pod_cart, #printPodButton').click(function() {
		var current = $.cookie(COOKIE_POD_FILES);
		if (current) {
			$('#load').show();
		}
	});
	checkAddRemoveButtons();
});

/**
 * Check if the articles are available to be added or removed from the list
 */
function checkAddRemoveButtons() {
	// ADD
	$('.podAdd').each(function() {
		if (existsArticle($(this).attr('id'))) {
			$(this).click(function() {
				return false;
			});
			if ($(this).attr('disabled') == '') {
				$($(this).find('img')[0]).attr('src', getURLtoDisable($($(this).find('img')[0]).attr('src')));
			}
			$(this).attr('disabled', 'true');
		} else if ($(this).attr('disabled') == 'true') {
			$(this).attr('disabled', '');
			$($(this).find('img')[0]).attr('src', getURLtoEnable($($(this).find('img')[0]).attr('src')));
			$(this).attr('onclick', '').unbind('click');
		}

	});

	// REMOVE
	$('.podRemove').each(function() {
		if (!existsArticle($(this).attr('id').substr(4, $(this).attr('id').length))) {
			$(this).click(function() {
				return false;
			});
			if ($(this).attr('disabled') == '') {
				$($(this).find('img')[0]).attr('src', getURLtoDisable($($(this).find('img')[0]).attr('src')));
			}
			$(this).attr('disabled', 'true');
		} else if ($(this).attr('disabled') == 'true') {
			$(this).attr('disabled', '');
			$($(this).find('img')[0]).attr('src', getURLtoEnable($($(this).find('img')[0]).attr('src')));
			$(this).attr('onclick', '').unbind('click');
		}

	});
}

function getURLtoDisable(url) {
	var full = url.substr(0, url.lastIndexOf('.'));
	return full + "-disabled.png";

}

function getURLtoEnable(url) {
	var full = url.substr(0, url.lastIndexOf('-'));
	return full + ".png";

}

function isBusinessAllowed() {
	var inputOptIn = $('input.opt_in');
	
	if($('#businessAllowed').attr('checked')) {
		inputOptIn.attr('disabled', false);
	} else {
		inputOptIn.attr('disabled', true);
	}
}