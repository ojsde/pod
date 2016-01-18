{**
 * viewPage.tpl
 *
 * Copyright (c) 2012 Aldi Alimucaj
 * Copyright (c) 2015 Felix Gründer
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * View issue: This adds the header and footer code to view.tpl.
 *
 *}

{strip}
{if $issue && !$issue->getPublished()}
	{translate|assign:"previewText" key="editor.issues.preview"}
	{assign var="pageTitleTranslated" value="$issueHeadingTitle $previewText"}
{else}
	{assign var="pageTitleTranslated" value=$issueHeadingTitle}
{/if}
{if $issue && $issue->getShowTitle() && $issue->getLocalizedTitle() && ($issueHeadingTitle != $issue->getLocalizedTitle())}
	{* If the title is specified and should be displayed then show it as a subheading *}
	{assign var="pageSubtitleTranslated" value=$issue->getLocalizedTitle()}
{/if}
{include file="common/header.tpl"}
{/strip}

{include file="$templatePath/view.tpl"}

{include file="common/footer.tpl"}