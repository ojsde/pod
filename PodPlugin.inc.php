<?php

/**
 * @file plugins/generic/pod/PodPlugin.inc.php
 *
 * Copyright (c) 2012 Aldi Alimucaj
 * Copyright (c) 2015 Felix Gründer
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PodPlugin
 * @ingroup plugins_generic_podPlugin
 *
 * @brief Pod plugin class
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class PodPlugin extends GenericPlugin {
	
	/* Constructor */
	function PodPlugin() {
		parent::GenericPlugin();
	}
	
	/**
	 * @copydoc PKPPlugin::register()
	 */
	function register($category, $path) {
		if (parent::register($category, $path)) { 
			if ($this->getEnabled()) {
				HookRegistry::register('PluginRegistry::loadCategory', array($this, 'callbackLoadCategory')); 
				HookRegistry::register('LoadHandler', array($this, 'callbackHandleContent'));
			}
			
			$request = PKPApplication::getRequest();
			$templateMgr = TemplateManager::getManager($request);
			$templateMgr->register_modifier('truncate_title', array($this, 'truncateTitle'));
			
			return true;
		}
		return false;
	}
	
	/**
	 * @copydoc PKPPlugin::manage()
	 */
	function manage($verb, $args, $message, $messageParams) {
		if (!parent::manage($verb, $args, $message, $messageParams)) return false;
		
        if ($verb != 'settings')
            return false;
		
        $journal = Request::getJournal();
        $templateMgr = TemplateManager::getManager();

        $templateMgr->register_function('plugin_url', array($this, 'smartyPluginUrl'));
        $templateMgr->setCacheability(CACHEABILITY_MUST_REVALIDATE);

        $this->import('PodSettingsForm');
        $form = new PodSettingsForm($this, $journal->getId());
		
		// validation rules:
		$form->addCheck(new FormValidator($form, 'partner', 'required', 'plugins.generic.pod.partnerRequired'));
		$form->addCheck(new FormValidator($form, 'podSecretKey', 'required', 'plugins.generic.pod.podSecretKeyRequired'));
		$form->addCheck(new FormValidator($form, 'mandant', 'required', 'plugins.generic.pod.mandantRequired'));
		$form->addCheck(new FormValidator($form, 'action', 'required', 'plugins.generic.pod.actionRequired'));
		$form->addCheck(new FormValidator($form, 'repository', 'required', 'plugins.generic.pod.repositoryRequired'));
		
        if (Request::getUserVar('save')) {
            $form->readInputData();
            if ($form->validate()) {
				
				import('lib.pkp.classes.file.FileManager');
				$fileManager = new FileManager();
				$filePath = Config::getVar('files', 'files_dir') . '/journals/' . $journal->getId() . '/';
				
				// upload cover
				if ($fileManager->uploadedFileExists('cover')) {
					$coverFile = $fileManager->getUploadedFileName('cover');
					$coverPath =  $filePath . $coverFile;
					
					$coverFileTemp = explode('.', $coverFile);
					$extension = array_pop($coverFileTemp);

					if($extension == 'pdf') {
						if ($fileManager->uploadFile('cover', $coverPath)) {
							$form->setData('cover', $coverFile);
						}
					}
				}
				
				// delete cover
				if(Request::getUserVar('checkDeleteCover')) {
					$coverFile = Request::getUserVar('currentCover');
					$coverPath =  $filePath . $coverFile;
					
					if ($fileManager->deleteFile($coverPath)) {
						$form->setData('cover', '');
						$form->setData('currentCover', '');
					}
				}

                $form->execute();
                Request::redirect(null, 'manager', 'plugin', array('generic', 'PodPlugin', 'settings'));
            } else {
                $form->display();
            }
        } else {
            $form->initData();
            $form->display();
        }

        return true;
    }
	
	/**
	 * @copydoc PKPPlugin::getManagementVerbs()
	 */
    function getManagementVerbs() {
        $verbs = array();
        if ($this->getEnabled()) {
			$verbs[] = array('settings', __('plugins.generic.pod.settings'));
        }
        return parent::getManagementVerbs($verbs);
    }
	
	/**
     * Defines the stylesheet for the plugin
     * @return string
     */
	function getStylesheetFilename() {
        return './style/pod.css';
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
		return 'PodPlugin';
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
	 * Handle the request and generate the export file
	 * @param $hookName String
	 * @param $args Array
	 * @return Boolean
	 */
	function callbackHandleContent($hookName, $args) {
		$templateMgr = TemplateManager::getManager();

		$page = $args[0];
		$op = $args[1];

		if ($page == 'pod') {
			define('POD_PLUGIN_NAME', $this->getName());
			define('HANDLER_CLASS', 'PodHandler'); 
			$this->import('PodHandler'); 
			return true;
		}
		return false;
	}
	
	/**
	 * Register as a block plugin, even though this is a generic plugin.
	 * This will allow the plugin to behave as a block plugin
	 * @param $hookName String
	 * @param $args Array
	 * @return Boolean
	 */
	function callbackLoadCategory($hookName, $args) {
		$category = $args[0];
		$plugins =& $args[1]; // & operator necessary in this case

		switch ($category) {
			case 'blocks':
				$this->import('PodBlockPlugin');
				$blockPlugin = new PodBlockPlugin($this->getName());
				$plugins[$blockPlugin->getSeq()][$blockPlugin->getPluginPath()] = $blockPlugin;
				break;
		}
		return false;
	}
	
	/**
	 * Truncate long article titles
	 * @param $arg String
	 * @return String
	 */
	function truncateTitle($arg) {
		$limit = 35;
		if (strlen($arg) > $limit) {
			return substr($arg, 0, $limit - 3) . '...';
		}
		return $arg;
	}
}
?>