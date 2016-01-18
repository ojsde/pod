{**
 * help.tpl
 *
 * Copyright (c) 2012 Aldi Alimucaj
 * Copyright (c) 2015 Felix Gründer
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Help page
 *
 *}

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{$currentLocale|replace:"_":"-"}" xml:lang="{$currentLocale|replace:"_":"-"}">
	<head>
		<title></title>
		<meta http-equiv="Content-Type" content="text/html; charset={$defaultCharset|escape}" />
		<meta name="description" content="" />
		<meta name="keywords" content="" />

		<link rel="stylesheet" href="{$baseUrl}/lib/pkp/styles/common.css" type="text/css" />
		<link rel="stylesheet" href="{$baseUrl}/styles/common.css" type="text/css" />
		<link rel="stylesheet" href="{$baseUrl}/lib/pkp/styles/rt.css" type="text/css" />
		<link rel="stylesheet" href="{$baseUrl}/plugins/generic/pod/style/pod.css" type="text/css" />
	</head>
	<body>
		<div id="helpContainer">
			<div id="header">
				<h1>{translate key="plugins.generic.pod.helpHeader"}&nbsp;<em>{translate key="plugins.generic.pod.displayName"}</em></h1>
			</div>
			<ul>
				<li><em>{translate key="plugins.generic.pod.showCart"}</em>{translate key="plugins.generic.pod.showCartMouseover"}</li>
				<li><em>{translate key="plugins.generic.pod.showJournal"}</em>{translate key="plugins.generic.pod.showJournalMouseover"}</li>
				<li><em>{translate key="plugins.generic.pod.showIssue"}</em>{translate key="plugins.generic.pod.showIssueMouseover"}</li>
				<li><em>{translate key="plugins.generic.pod.printBook"}</em>{translate key="plugins.generic.pod.printBookMouseover"}</li>
			</ul>
		</div>
		<input type="button" onclick="window.close()" value="{translate key="common.close"}" class="button defaultButton"  style="cursor: pointer"/>
	</body>
</html>