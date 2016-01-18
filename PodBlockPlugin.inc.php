<?php

/**
 * @file Pod.inc.php
 *
 * Copyright (c) 2012 Aldi Alimucaj
 * Copyright (c) 2015 Felix Gründer
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Pod
 * @ingroup plugins_blocks_pod
 *
 * @brief Class for pod block plugin
 */

import('lib.pkp.classes.plugins.BlockPlugin');

class PodBlockPlugin extends BlockPlugin {
	
	/* @var string Name of the parent plugin object */
	var $parentPluginName;
	
	
	/**
	 * Constructor
	 * @param $parentPluginName String
	 */
	function PodBlockPlugin($parentPluginName) {
		$this->parentPluginName = $parentPluginName;
	}

    /**
	 * @copydoc LazyLoadPlugin::getEnabled()
	 */
    function getEnabled() {
        if (!Config::getVar('general', 'installed'))
            return true;
        return parent::getEnabled();
    }

    /**
	 * @copydoc PKPPlugin::getInstallSitePluginSettingsFile()
	 */
    function getInstallSitePluginSettingsFile() {
        return $this->getPluginPath() . '/settings.xml';
    }

    /**
	 * @copydoc PKPPlugin::getContextSpecificPluginSettingsFile()
	 */
    function getContextSpecificPluginSettingsFile() {
        return $this->getPluginPath() . '/settings.xml';
    }

	/**
	 * @copydoc BlockPlugin::getBlockContext()
	 */
    function getBlockContext() {
        if (!Config::getVar('general', 'installed'))
            return BLOCK_CONTEXT_RIGHT_SIDEBAR;
        return parent::getBlockContext();
    }

    /**
	 * @copydoc PKPPlugin::getSeq()
	 */
    function getSeq() {
        if (!Config::getVar('general', 'installed'))
            return 0;
        return parent::getSeq();
    }
	
	/**
	 * @copydoc PKPPlugin::getTemplatePath()
	 */
	function getTemplatePath() {
		return parent::getTemplatePath() . 'templates/';
	}
	
	/**
	 * @copydoc PKPPlugin::getName()
	 */
	function getName() {
		return 'PodBlockPlugin';
	}

	/**
	 * @copydoc PKPPlugin::getDisplayName()
	 */
	function getDisplayName() {
		return __('plugins.generic.pod.displayName');
	}

	/**
	 * @copydoc PKPPlugin::getDescription()
	 */
	function getDescription() {
		return __('plugins.generic.pod.description');
	}
	
	/**
	 * @copydoc PKPPlugin::getPluginPath()
	 */
	function getPluginPath() {
		$plugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);
		return $plugin->getPluginPath();
	}

	/**
	 * @copydoc BlockPlugin::getContents()
	 */
    function getContents(&$templateMgr) {
        $journal = & Request::getJournal();
        $obj = $templateMgr->smartyGetValue('results');
        if ($obj) {
            $arr = array_reverse($obj->toArray(), TRUE);
            $results = new VirtualArrayIterator(array_reverse($arr, TRUE), $obj->getCount(), $obj->getPageCount());
			
			// check if at least one result has galleys
			$resultsHaveGalleys = false;
			foreach($arr as $index => $data) {
				$articleData = $data['publishedArticle'];
				$articleGalleys = $articleData->getData('galleys');

				if (!empty($articleGalleys)) {
					$resultsHaveGalleys = true;
					break;
				}
			}

			$templateMgr->assign('new_results', $results);
            $templateMgr->assign('resultsHaveGalleys', $resultsHaveGalleys);
        }

        $pubArticles = $templateMgr->smartyGetValue('publishedArticles');
        if ($pubArticles && gettype($pubArticles[0]) == 'object') {
            $publishedArticleDao = & DAORegistry::getDAO('PublishedArticleDAO');
            $new_pubArticles = array_reverse($pubArticles, TRUE);
            if (count($new_pubArticles) > 0) {
                $templateMgr->assign('authorArticles', 'true');
                $templateMgr->assign('new_publishedArticles', $new_pubArticles);
            }
        }
		
		// check if an article has at least one pdf galley
		$publishedArticle = $templateMgr->smartyGetValue('article');
		if ($publishedArticle) {
			$hasPdfGalleys = false;
			foreach($publishedArticle->getData('galleys') as $galley) {
				if ($galley->isPdfGalley()) {
					$hasPdfGalleys = true;
					break;
				}
			}
			$templateMgr->assign('hasPdfGalleys', $hasPdfGalleys);
		}

        return parent::getContents($templateMgr, PKPApplication::getRequest());
    }

    /**
     * Returns a specific settings parameters
     * @param string $key
     * @return string
     */
    function getParameter($key) {
        $journal = & Request::getJournal();
		if ($journal) {
			$pluginDao = & DAORegistry::getDAO('PluginSettingsDAO');
			$settings = &$pluginDao->getPluginSettings($journal->getId(), 'PodPlugin');
			return $settings[$key];
		}
		return '';
    }

}
?>