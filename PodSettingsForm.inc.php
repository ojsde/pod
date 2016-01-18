<?php

/**
 * @file PodSettingsForm.inc.php
 *
 * Copyright (c) 2012 Aldi Alimucaj
 * Copyright (c) 2015 Felix Gründer
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PodSettingsForm
 * @ingroup plugins_blocks_pod
 *
 * @brief Form for journal managers to modify pod plugin settings
 */


import('lib.pkp.classes.form.Form');

class PodSettingsForm extends Form {

	/** @var int */
	var $journalId;

	/** @var object */
	var $plugin;

	/**
	 * Constructor
	 * @param $plugin object
	 * @param $journalId int
	 */
	function PodSettingsForm(&$plugin, $journalId) {
		$this->journalId = $journalId;
		$this->plugin =& $plugin;

		parent::Form($plugin->getTemplatePath() . 'settingsForm.tpl');
	}

	/**
	 * @copydoc Form::display()
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();
		$additionalHeadData = $templateMgr->get_template_vars('additionalHeadData');
		$stylesheetFileLocation = $this->plugin->getPluginPath() . '/' . $this->plugin->getStylesheetFilename();
		$templateMgr->assign('canSave', is_writable($stylesheetFileLocation));
		$templateMgr->assign('stylesheetFileLocation', $stylesheetFileLocation);

		return parent::display();
	}

	/**
	 * Initialize form data.
	 */
	function initData() {
		$journalId = $this->journalId;
		$plugin =& $this->plugin;

		$this->_data = array(
			'activated' => $plugin->getSetting($journalId, 'activated'),
			'partner' => $plugin->getSetting($journalId, 'partner'),
			'podSecretKey' => $plugin->getSetting($journalId, 'podSecretKey'),
			'mandant' => $plugin->getSetting($journalId, 'mandant'),
			'action' => $plugin->getSetting($journalId, 'action'),
			'repository' => $plugin->getSetting($journalId, 'repository'),
			'authors' => $plugin->getSetting($journalId, 'authors'),
			'format' => $plugin->getSetting($journalId, 'format'),
			'content_bw' => $plugin->getSetting($journalId, 'content_bw'),
			'price' => $plugin->getSetting($journalId, 'price'),
			'currency' => $plugin->getSetting($journalId, 'currency'),
			'preface' => $plugin->getSetting($journalId, 'preface'),
			'pageHeight' => $plugin->getSetting($journalId, 'pageHeight'),
			'pageWidth' => $plugin->getSetting($journalId, 'pageWidth'),
			'cover' => $plugin->getSetting($journalId, 'cover'),
			//'failure_url' => $plugin->getSetting($journalId, 'failure_url'),
			'user_id' => $plugin->getSetting($journalId, 'user_id'),
			'firstname' => $plugin->getSetting($journalId, 'firstname'),
			'lastname' => $plugin->getSetting($journalId, 'lastname'),
			'street' => $plugin->getSetting($journalId, 'street'),
			'number' => $plugin->getSetting($journalId, 'number'),
			'zip' => $plugin->getSetting($journalId, 'zip'),
			'town' => $plugin->getSetting($journalId, 'town'),
			'country' => $plugin->getSetting($journalId, 'country'),
			'email' => $plugin->getSetting($journalId, 'email'),
		);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array(
			'activated',
			'partner', //* user id or partner id given by the provider
			'podSecretKey', //* the secret key to build HMAC-s given by the provider
			'mandant', //* the mandant given by the provider
			'action', //* workbench | price | voucher | order
			'repository',
			'authors',
			'format', // format for the combination of Format, Binding, Paper
			'content_bw', // 0 = colored, 1 = b/w
			'price', // float ###need to be cleared###
			'currency', // 1 (1 = EUR, 2 = USD, 3 = CHF)
			'preface',
			'pageHeight',
			'pageWidth',
			'cover', 'currentCover',
			//'failure_url',
			'user_id',
			'firstname',
			'lastname',
			'street',
			'number',
			'zip',
			'town',
			'country',
			'email',
		));
	}

	/**
	 * Save settings.
	 */
	function execute() {
		$plugin =& $this->plugin;
		$journalId = $this->journalId;
		
		$activated= $this->getData('activated');
		$plugin->updateSetting($journalId, 'activated', $activated, 'string');
		
		$partner = $this->getData('partner');
		$plugin->updateSetting($journalId, 'partner', $partner, 'string');

		$podSecretKey = $this->getData('podSecretKey');
		$plugin->updateSetting($journalId, 'podSecretKey', $podSecretKey, 'string');

		$mandant = $this->getData('mandant');
		$plugin->updateSetting($journalId, 'mandant', $mandant, 'string');

		$action = $this->getData('action');
		$plugin->updateSetting($journalId, 'action', $action, 'string');
		
		$repository = $this->getData('repository');
		$plugin->updateSetting($journalId, 'repository', $repository, 'string');
		
		$authors = $this->getData('authors');
		$plugin->updateSetting($journalId, 'authors', $authors, 'string');
		
		$format = $this->getData('format');
		$plugin->updateSetting($journalId, 'format', $format, 'string');
		
		$content_bw = $this->getData('content_bw');
		$plugin->updateSetting($journalId, 'content_bw', $content_bw, 'string');

		$price = $this->getData('price');
		$plugin->updateSetting($journalId, 'price', $price, 'string');

		$currency = $this->getData('currency');
		$plugin->updateSetting($journalId, 'currency', $currency, 'string');
		
		$preface = trim($this->getData('preface'));
		$plugin->updateSetting($journalId, 'preface', $preface, 'string');

		$pageHeight = $this->getData('pageHeight');
		$plugin->updateSetting($journalId, 'pageHeight', $pageHeight, 'string');

		$pageWidth = $this->getData('pageWidth');
		$plugin->updateSetting($journalId, 'pageWidth', $pageWidth, 'string');
		
		$cover = $this->getData('cover') ? $this->getData('cover') : $this->getData('currentCover');
		$plugin->updateSetting($journalId, 'cover', $cover, 'string');
		
		/*
		$failure_url = $this->getData('failure_url');
		$plugin->updateSetting($journalId, 'failure_url', $failure_url, 'string');
		*/
		$user_id = $this->getData('user_id');
		$plugin->updateSetting($journalId, 'user_id', $user_id, 'string');

		$firstname = $this->getData('firstname');
		$plugin->updateSetting($journalId, 'firstname', $firstname, 'string');

		$lastname = $this->getData('lastname');
		$plugin->updateSetting($journalId, 'lastname', $lastname, 'string');

		$street = $this->getData('street');
		$plugin->updateSetting($journalId, 'street', $street, 'string');

		$number = $this->getData('number');
		$plugin->updateSetting($journalId, 'number', $number, 'string');

		$zip = $this->getData('zip');
		$plugin->updateSetting($journalId, 'zip', $zip, 'string');

		$town = $this->getData('town');
		$plugin->updateSetting($journalId, 'town', $town, 'string');

		$country = $this->getData('country');
		$plugin->updateSetting($journalId, 'country', $country, 'string');

		$email = $this->getData('email');
		$plugin->updateSetting($journalId, 'email', $email, 'string');
	}
}

?>