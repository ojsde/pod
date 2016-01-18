{**
 * block.tpl
 *
 * Copyright (c) 2011 Aldi Alimucaj
 * Copyright (c) 2015 Felix Gründer
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * This is the plugin which is rendered at the right side of the page.
 * It contains actions regarding printing, showing the cart and other POD management.
 *}
 
<script type="text/javascript">
	{literal}
	function showPodHelp(url) {
		window.open(url, "pod_help", "location=1,toolbar=1,menubar=1,resizable=1,width=800,height=400");
	}
	{/literal}
</script>

{if $currentJournal}
	<link rel="stylesheet" href="{$baseUrl}/plugins/generic/pod/style/pod.css" type="text/css" />
	<link rel="stylesheet" href="{$baseUrl}/plugins/generic/pod/style/tablednd.css" type="text/css" />

	<div class="block podBlock" id="podBlock">
		<span class="blockTitle">{translate key="plugins.generic.pod.name"}</span>

		<ul id="podLinks">
		
			<!-- select issue journal -->
			{if $currentJournal->getLocalizedTitle() && !$article && !$issue}
				<li>
					<img src="{$baseUrl}/plugins/generic/pod/icons/add_cart.gif"/>
					<a href="{url page="pod" op="current"}" id="currentJournal">{translate key="plugins.generic.pod.showJournal"}</a>
				</li>
			{/if}

			<!-- Issue/Ausgabe print -->
			{if $issue}
				<li>
					<img src="{$baseUrl}/plugins/generic/pod/icons/add_cart.gif"/>
					<a href="{url page="pod" op="view" path=$issue->getBestIssueId($currentJournal)|to_array:"showToc"}" id="issueJournal">{translate key="plugins.generic.pod.showIssue"}</a>
				</li>
			{/if}

			<!-- show cart -->
			<li>
				<img src="{$baseUrl}/plugins/generic/pod/icons/cart.png" class="small" />
				<a href="{url page="pod" op="showPodCart"}" id="pod_show_cart">{translate key="plugins.generic.pod.showCart"}</a> (<span id="cart_counter">0</span>)
			</li>

			<!-- print cart -->
			<li>
				<img src="{$baseUrl}/plugins/generic/pod/icons/print.gif"/>
				<a href="javascript:post_pod('{$baseUrl}/index.php/{$currentJournal->getPath()}/pod/printOnDemand')" id="pod_cart">{translate key="plugins.generic.pod.printBook"}</a>
			</li>


			{if $article && $hasPdfGalleys}
				{assign var=articlePath_ value=$article->getBestArticleId($currentJournal)}
				<li>
					<img src="{$baseUrl}/plugins/generic/pod/icons/doc-option-add.png"/>
					<a href="javascript:addPodArticle('{$articlePath_}/{$article->getPubId('publisher-id')}{*$article->getPublicArticleId()*}')" id="pod_add__article_to_cart">{translate key="plugins.generic.pod.addArticle"}</a>
				</li>
			{/if}
		
		</ul>
		
		<div>
		<!-- search results -->
		{if $new_results && $resultsHaveGalleys}
			{translate key="plugins.generic.pod.currentArticles"}
			<select id="items_in_the_list">
				{iterate from=new_results item=result_ name=result_list}
					{assign var=publishedArticle_ value=$result_.publishedArticle}
					{assign var=article_ value=$result_.article}
					{assign var=journal_ value=$result_.journal}
					{if $publishedArticle_->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN}
						{assign var=hasAccess_ value=1}
					{else}
						{assign var=hasAccess_ value=0}
					{/if}
					{if !$hasAccess_}
					{assign var=articlePath_ value=$publishedArticle_->getBestArticleId($journal_)}
					
					{foreach from=$publishedArticle_->getLocalizedGalleys() item=galley_ name=galley_List}
						{if $galley_->isPdfGalley()}
						<option value="{$articlePath_}/{$galley_->getBestGalleyId($journal_)}">{$article_->getLocalizedTitle()|truncate_title}</option>
						{/if}
					{/foreach}
					
					{/if}
				{/iterate}
			</select>
			<a href="javascript:add_form_select_list()"><img src="{$baseUrl}/plugins/generic/pod/icons/doc-option-add.png"/></a>
			<a href="javascript:remove_form_select_list()"><img src="{$baseUrl}/plugins/generic/pod/icons/doc-option-remove.png"/></a>
		{/if}

		<!-- archives -->
		{if $authorArticles != "true" and $publishedArticles}
			<select id="items_in_the_list">
				{foreach name=sectionsExtra from=$publishedArticles item=sectionExtra key=sectionId}
					{foreach from=$sectionExtra.articles item=articleExtra}
					{assign var=articlePathExtra value=$articleExtra->getBestArticleId($currentJournal)}
						{foreach from=$articleExtra->getLocalizedGalleys() item=galley name=galleyList}
							{if $galley->isPdfGalley()}
							<option value="{$articlePathExtra}/{$galley->getBestGalleyId($currentJournal)}">{$articleExtra->getLocalizedTitle()|strip_unsafe_html|truncate_title}</option>
							{/if}
						{/foreach}
					{/foreach}
				{/foreach}
			</select>
			<a href="javascript:add_form_select_list()"><img src="{$baseUrl}/plugins/generic/pod/icons/doc-option-add.png"/></a>
			<a href="javascript:remove_form_select_list()"><img src="{$baseUrl}/plugins/generic/pod/icons/doc-option-remove.png"/></a>
			
		{/if}
		
		
		<!-- authorListing -->
		{if $authorArticles == "true" and $new_publishedArticles}
			{translate key="plugins.generic.pod.currentArticles"}
			<select id="items_in_the_list">
			{foreach from=$publishedArticles item=article}
				{assign var=issueId value=$article->getIssueId()}
				{assign var=issue value=$issues[$issueId]}
				{assign var=issueUnavailable value=$issuesUnavailable.$issueId}
				{assign var=sectionId value=$article->getSectionId()}
				{assign var=journalId value=$article->getJournalId()}
				{assign var=journal value=$journals[$journalId]}
				{assign var=section value=$sections[$sectionId]}
				{if $issue->getPublished() && $section && $journal}

				{assign var=articlePathExtra value=$article->getBestArticleId($currentJournal)}
				{foreach from=$article->getLocalizedGalleys() item=galley name=galleyList}
					{if $galley->isPdfGalley()}
					<option value="{$articlePathExtra}/{$galley->getBestGalleyId($currentJournal)}">{$article->getLocalizedTitle()|truncate_title}</option>
					{/if}
				{/foreach}
				{/if}
			{/foreach}
			</select>
			<a href="javascript:add_form_select_list()"><img src="{$baseUrl}/plugins/generic/pod/icons/doc-option-add.png"/></a>
			<a href="javascript:remove_form_select_list()"><img src="{$baseUrl}/plugins/generic/pod/icons/doc-option-remove.png"/></a>
		{/if}
		</div>
		{if $baseUrl}
		<script type="text/javascript" src="{$baseUrl}/plugins/generic/pod/scripts/pod_cart.js"></script>
		<script type="text/javascript" src="{$baseUrl}/plugins/generic/pod/scripts/jquery.tablednd.js"></script>
		{/if}

		<!--
		<select name="pod_operator" id="pod_operator">
			<option value="epubli" selected="true">epubli</option>
		</select>
		-->
		
		<div id="load" style="display:none;">{translate key="plugins.generic.pod.loading"}</div>
		
		<ul>
			<li><a class="blockTitle" href="javascript:resetCart()">{translate key="plugins.generic.pod.resetCart"}</a></li>
			<li><a id="linkHelp" href='javascript:showPodHelp("{url page="pod" op="help"}")'>
				{translate key="plugins.generic.pod.help"}
				</a>
			</li>
		</ul>

	</div>
{/if}