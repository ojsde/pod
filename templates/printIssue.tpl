{**
 * issue.tpl
 *
 * Copyright (c) 2012 Aldi Alimucaj
 * Copyright (c) 2015 Felix Gründer
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Issue
 *
 * $Id$
 *}

{if $coverImage}
<div class="podCover">
{**
<img width="180" height="240" src="{$coverImage}"/>
*}
<br />
{translate key="plugins.generic.pod.pageNumber"}: {$pageNum}
</div>
<h1>{translate key="plugins.generic.pod.listOfFiles"}</h1>
{/if}

<br />
<br />
<form method="post" name="pod" action="{url op="printOnDemand"}">
<table id="podArticle" class="tocArticle" width="100%">
{assign var=podIndex value=0}
{foreach from=$shopCartArticles item=article key=articleId}
{assign var=podIndex value=$podIndex+1}
{assign var=articlePath value=$article->getBestArticleId($currentJournal)}
<tr valign="top" id="{$podIndex}">
	{if $article->getLocalizedFileName() && $article->getLocalizedShowCoverPage() && !$article->getHideCoverPageToc($locale)}
	<td rowspan="2">
		{if $coverPagePath}
		<div class="tocArticleCoverImage">
			<a href="{url page="article" op="view" path=$articlePath}" class="file">
				<img src="{$coverPagePath|escape}{$article->getFileName($locale)|escape}"{if $article->getCoverPageAltText($locale) != ''} alt="{$article->getCoverPageAltText($locale)|escape}"{else} alt="{translate key="article.coverPage.altText"}"{/if}/>
			</a>
		</div>
		{/if}
	</td>
	{/if}
	{if $article->getLocalizedAbstract() == ""}
		{assign var=hasAbstract value=0}
	{else}
		{assign var=hasAbstract value=1}
	{/if}
	{assign var=articleId value=$article->getId()}
	{if (!$subscriptionRequired || $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN || $subscribedUser || $subscribedDomain || ($subscriptionExpiryPartial && $articleExpiryPartial.$articleId))}
		{assign var=hasAccess value=1}
	{else}
		{assign var=hasAccess value=0}
	{/if}
	<td class="tocIndex" name="{$podIndex}">{$podIndex}</td>
	<td class="tocTitle">{if !$hasAccess || $hasAbstract}{$article->getLocalizedTitle()|strip_unsafe_html}{else}{$article->getLocalizedTitle()|strip_unsafe_html}{/if}</td>
	<td class="tocGalleys">
		{if $hasAccess || ($subscriptionRequired && $showGalleyLinks)}
			{foreach from=$article->getLocalizedGalleys() item=galley name=galleyList}
				{if $galley->isPdfGalley()}
					<input style="display:none" name="Articles[]" type="checkbox" value="{$articlePath}/{$galley->getBestGalleyId($currentJournal)}" checked="checked">
					
					<a class="filePod" title="{translate key="plugins.generic.pod.showArticlePDFMouseover"}" href="{url page="article" op="view" path=$articlePath|to_array:$galley->getBestGalleyId($currentJournal)}"><img src="{$baseUrl}/plugins/generic/pod/icons/pdf-icon.png"/></a>
					<a style="display:none" title="{translate key="plugins.generic.pod.directPrintArticleMouseover"}" class="podDirectPrint" href="http://www.epubli.de/interfaces/partnerInterface.php?repository=url&action=workbench&document_url={url page="article" op="download" path=$articlePath|to_array:$galley->getBestGalleyId($currentJournal)}" class="file"><img src="{$baseUrl}/plugins/generic/pod/icons/print.gif"/></a>
					<a title="{translate key="plugins.generic.pod.removeArticleMouseover"}" class="podRemove" href="javascript:void(0)" onClick="removePodArticle('{$articlePath}/{$galley->getBestGalleyId($currentJournal)}',{$podIndex})"><img src="{$baseUrl}/plugins/generic/pod/icons/doc-option-remove.png"/></a>

					{if $subscriptionRequired && $showGalleyLinks && $restrictOnlyPdf}
						{if $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN || !$galley->isPdfGalley()}
							<img class="accessLogo" src="{$baseUrl}/lib/pkp/templates/images/icons/fulltext_open_medium.gif" alt="{translate key="article.accessLogoOpen.altText"}" />
						{else}
							<img class="accessLogo" src="{$baseUrl}/lib/pkp/templates/images/icons/fulltext_restricted_medium.gif" alt="{translate key="article.accessLogoRestricted.altText"}" />
						{/if}
					{/if}
				{/if}
			{/foreach}
			{if $subscriptionRequired && $showGalleyLinks && !$restrictOnlyPdf}
				{if $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN}
					<img class="accessLogo" src="{$baseUrl}/lib/pkp/templates/images/icons/fulltext_open_medium.gif" alt="{translate key="article.accessLogoOpen.altText"}" />
				{else}
					<img class="accessLogo" src="{$baseUrl}/lib/pkp/templates/images/icons/fulltext_restricted_medium.gif" alt="{translate key="article.accessLogoRestricted.altText"}" />
				{/if}
			{/if}
		{/if}
	</td>
</tr>
<tr>
	<td class="tocPages">{$article->getPages()|escape}</td>
</tr>
{/foreach}
</table>
<br />
{if !$shopCartArticles}0 Items{/if}
{if $shopCartArticles}<input id="printPodButton" type="button" value="{translate key="plugins.generic.pod.printBook"}" onClick="javascript:post_pod('{url op="printOnDemand"}')"/>{/if}
</form>

{literal}
	<script type="text/javascript">
		$(document).ready(function() {
			// Initialise the  table specifying a dragClass and an onDrop function that will display an alert
			$("#podArticle").tableDnD({
			    onDrop: function(table, row) {
			        setCookie($.tableDnD.serialize());
			        resetTableCounter();
			    },
			});
		});

		$("#podArticle tr").hover(function() {
        	$(this.cells[0]).addClass('showDragHandle');
		}, function() {
			$(this.cells[0]).removeClass('showDragHandle');
		});
	</script>
{/literal}