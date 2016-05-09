<?php

/**
 * @file PODHandler.inc.php
 *
 * Copyright (c) 2012 Aldi Alimucaj
 * Copyright (c) 2015 Felix Gründer
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PODHandler
 * @ingroup pages_pod
 *
 * @brief Handle requests for pod functions.
 */

import('classes.issue.IssueAction');
import('classes.handler.Handler');
import('classes.file.ArticleFileManager');

define('POD_PROVIDER_INTERFACE', 'http://www.epubli.de/interfaces/partnerInterface.php?');

class PODHandler extends Handler {
	
	/* @var object */
	var $podPlugin;

    /**
     * Constructor
     */
    function PODHandler() {
        parent::Handler();
		
		$this->podPlugin = PluginRegistry::getPlugin('generic', POD_PLUGIN_NAME);
        $this->addCheck(new HandlerValidatorJournal($this));
        $this->addCheck(new HandlerValidatorCustom($this, false, null, null, create_function('$journal', 'return $journal->getSetting(\'publishingMode\') != PUBLISHING_MODE_NONE;'), array(Request::getJournal())));
    }

    /**
     * Display about index page.
	 * @param Array $args
     */
    function index($args) {
        $this->current();
    }

    /**
     * Random pdf file name
     * @return string
     */
    function getRandomPDFFileName() {
        $length = 10;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $string = '';

        for ($p = 0; $p < $length; $p++) {
            $string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        return $string;
    }

    /**
     * Download the PDF file
     * @param string $args
     */
    function downloadPOD($args = null) {
        $arg = isset($args[0]) ? $args[0] : '';
        $arg.='.pdf';
        $cacheManager = & CacheManager::getManager();
        $path = $cacheManager->getFileCachePath();
        import('lib.pkp.classes.file.FileManager');
        $fileManager = new FileManager();
        $filePath = $path . '/' . $arg;

        if (!$fileManager->fileExists($filePath)) {
            print("Error file not found!");
            return;
        }
        // We'll be outputting a PDF
        header('Content-type: application/octet-stream');

        // It will be called downloaded.pdf
        header('Content-Disposition: attachment; filename="downloaded.pdf"');
        header("Content-Length: " . filesize($filePath));
        header("Pragma: public"); // required
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false); // required for certain browsers
        header("Content-Transfer-Encoding: binary");
        flush();

        // The PDF source is in original .pdf
        readfile($filePath);

        // Delete the file after successful download
        //$fileManager->deleteFile($filePath);TODO
    }

    /**
     * Builds the pdf file
	 * @param string $randomFileName
	 * @param Array $publishedArticles
	 * @return string
     */
    function preparePDFFile($randomFileName, $publishedArticles = null) {
        include 'PDFMerger/PDFMerger.php';
        //-------------

        $pdf = new PDFMerger;
        $cacheManager = & CacheManager::getManager();
        $path = $cacheManager->getFileCachePath();
        $fullPath = $path . '/' . $randomFileName . '.pdf';

        $articles = Request::getUserVar('Articles');
        if ($publishedArticles == null) {
            $galleyArray = $this->getGalleysFromRequest($articles);
        } else {
            $galleyArray = $this->getGalleysFromRequest($publishedArticles);
        }

        //Adding the customized cover
        import('lib.pkp.classes.file.FileManager');
        $fileManager = new FileManager();
        $journal = Request::getJournal();

		$currentPath = Config::getVar('files', 'files_dir') . '/journals/' . $journal->getId() . '/';

		if($this->getParameter('cover')) {
			$preCover = $currentPath . $this->getParameter('cover');
			if ($fileManager->fileExists($preCover)) {
				$pdf->addPDF($preCover);
			}
		}

        // This is the second cover or the titles and journal description
        $coverPath = $this->printCover($galleyArray, $path);
        $pdf->addPDF($coverPath);

        foreach ($galleyArray as $key => $galley) {
            if ($galley->isPdfGalley()) {
				$pdf->addPDF($galley->getFilePath());
			}
        }
        try {
            $pdf->merge('file', $fullPath);
        } catch (Exception $e) {
            
        }

        // set the footer
        $pageNum = $this->getNumPagesPdf($fullPath);
		$footer = $this->printFooter($path, ((4 - ($pageNum + 1) % 4)) + 1);
		
        $pdf->addPDF($footer);
        try {
            $pdf->merge('file', $fullPath);
        } catch (Exception $e) {}

        try {
            $fileManager->deleteFile($coverPath);
            $fileManager->deleteFile($footer);
        } catch (Exception $e) {
            print $e->getMessage();
        }

        return $fullPath;
    }

    /**
     * Display current pod page.
	 * @param Array $args
     */
    function printOnDemand($args = null) {
        $this->validate();
        $this->setupTemplate();

        $templateMgr = & TemplateManager::getManager();
        $locale = AppLocale::getLocale();
        $templateMgr->assign('locale', $locale);

        $randomFileName = $this->getRandomPDFFileName();
        $fullPath = $this->preparePDFFile($randomFileName);

        $pageNum = $this->getNumPagesPdf($fullPath);
		
        if (POD_PROVIDER_INTERFACE != null) {
            $randomDownloadAddess = substr(Request::getCompleteUrl(), 0, strripos(Request::getCompleteUrl(), '/')) . '/downloadPOD/' . $randomFileName;

            // this method inits the array with all necessary parameters and HMAC if charging is active
            $settingsArray = $this->getAllParametersWithHMAC($randomDownloadAddess, $pageNum);
			
            // serialize the parameters in order to get out of an array the URL
            $appendParameters = $this->serializeArrayForGET($settingsArray);
			
			
			echo '<pre>' . print_r($settingsArray, true) . '</pre>';
			echo POD_PROVIDER_INTERFACE . $appendParameters;
			die();
			
			
            // redirect the user with the complete parameter set to the provider
            $this->redirect(POD_PROVIDER_INTERFACE . $appendParameters);
        }
    }

    /**
     * Redirect the user to pod provider
     * @param unknown_type $Str_Location
     * @param unknown_type $Bln_Replace
     * @param unknown_type $Int_HRC
     */
    function redirect($Str_Location, $Bln_Replace = 1, $Int_HRC = NULL) {
        if (!headers_sent()) {
            header('location: ' . $Str_Location, $Bln_Replace, $Int_HRC);
            exit;
        }

        exit('<meta http-equiv="refresh" content="0; url=' . $Str_Location . '"/>'); # | exit('<script>document.location.href=' . urldecode($Str_Location) . ';</script>');
        return;
    }

	/**
     * Get page number
     * @param string $filepath
	 * @return int
     */
    function getNumPagesPdf($filepath) {
        $fp = @fopen(preg_replace("/\[(.*?)\]/i", "", $filepath), "r");
        $max = 0;
        if (!$fp) {
            return "Could not open file: $filepath";
        } else {
            while (!@feof($fp)) {
                $line = @fgets($fp, 255);
                if (preg_match('/\/Count [0-9]+/', $line, $matches)) {
                    preg_match('/[0-9]+/', $matches[0], $matches2);
                    if ($max < $matches2[0]) {
                        $max = trim($matches2[0]);
                        break;
                    }
                }
            }
            @fclose($fp);
        }

        return $max;
    }

	/**
     * Print the bottom part of the PDF file
     * @param string $path
	 * @param int $pages
	 * @return string
     */
    function printFooter($path, $pages = 1) {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		
        // ---------------------------------------------------------
        // Add a page
        while ($pages-- > 0) {
            $pdf->AddPage();
        }

        $fileName = $path . '/' . $this->getRandomPDFFileName();

        $pdf->Output($fileName, 'F');

        return $fileName;
    }

    /**
     * Create a cover for the concatenated pdf files.
     * @param array $galleyArray galleys
     * @param string $path path where to save cover
     * @return string full path of the new cover
     */
    function printCover(Array $galleyArray, $path) {

        include 'tcpdf/config/lang/eng.php';
        include 'tcpdf/tcpdf.php';

        $journal = & Request::getJournal();

        // create new PDF document
        $pHeight = $this->getParameter('pageHeight');
        $pWidth = $this->getParameter('pageWidth');
        if ($pHeight <= 0 || $pWidth <= 0) {
            $pagelayout = PDF_PAGE_FORMAT;
        } else {
            $pagelayout = array($pHeight, $pWidth);
        }
       
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, $pagelayout, true, 'UTF-8', false);
		$pdf->SetPrintFooter(false); // disable page numeration for the table of contents page
		
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Mixed Authors');
        $pdf->SetTitle('POD Articles');
        $pdf->SetSubject('OJS POD');
        //$pdf->SetKeywords('TCPDF, PDF, example, test, guide');
        // set default header data
        $pdf->SetHeaderData($journal->getLocalizedPageHeaderLogo(), PDF_HEADER_LOGO_WIDTH, $journal->getJournalTitle(), '');

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        //set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        //set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        //set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        //set some language-dependent strings
		/*
		$l = array('w_page' => __('plugins.generic.pod.page'));
        $pdf->setLanguageArray($l);
		*/

        // ---------------------------------------------------------
        // set default font subsetting mode
        $pdf->setFontSubsetting(true);

        // Set font
        // dejavusans is a UTF-8 Unicode font, if you only need to
        // print standard ASCII chars, you can use core fonts like
        // helvetica or times to reduce file size.
        $pdf->SetFont('dejavusans', '', 14, '', true);

        // Add a page
        // This method has several options, check the source code documentation for more information.
        $pdf->AddPage();

        // Set some content to print
        $html = nl2br($this->getParameter('preface')) . '<br />';

        $articleIndex = 1;
        foreach ($galleyArray as $key => $galley) {
            if (is_null($galley))
                continue;
            $articleDao = & DAORegistry::getDAO('ArticleDAO');
            $article = &$articleDao->getArticle($galley->getArticleId());
            $html .= '<p>' . $articleIndex++ . ') ' . $article->getArticleTitle() . '</p>';
        }

        // Print text using writeHTMLCell()
        $pdf->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);

        // ---------------------------------------------------------

        ob_end_clean();
        // Close and output PDF document
        // This method has several options, check the source code documentation for more information.

        $fileName = $path . '/' . $this->getRandomPDFFileName();

        $pdf->Output($fileName, 'F');

        return $fileName;
    }

    /**
     * Serializes an Array of parameters to a URL form.
     * like: a=1&b=2&c=three
     *
     * @param array $array parameters
     * @return string URL
     */
    function serializeArrayForGET(Array $array) {
        $ret = '';
        $size = count($array);
        $i = 1;

        $cleanArray = $this->cleanUpSettings($array);
        foreach ($cleanArray as $key => $value) {
            if ($key == 'signature') {
                $ret .= sprintf("%s=%s", $key, urlencode($value));
            } else if ($key == 'author') {
                $ret .= sprintf("%s=%s", $key, urlencode($value));
            } else if ($key == 'podPartnerID') {
                //TODO CHANGE THIS IN THE SETTINGS
                $ret .= sprintf("%s=%s", 'partner', $value);
            } else {
                $ret .= sprintf("%s=%s", $key, $value);
            }

            if ($i != $size)
                $ret .= '&';
            $i++;
        }
        return $ret;
    }

    /**
     * Cleans up an array with nonset parameter values.
     * Based on blacklisting and empty parameters.
     * @param array $array
     * @return multitype
     */
    function cleanUpSettings(Array $array) {
        unset($array['context']); //default plugin setting
        unset($array['enabled']); //default plugin setting
        unset($array['activated']); //default plugin setting
        unset($array['cover']); //default plugin setting
        unset($array['authors']); //default plugin setting
        unset($array['pageHeight']); //default plugin setting
        unset($array['pageWidth']); //default plugin setting
        $array['failure_url'] = substr(Request::getCompleteUrl(), 0, strripos(Request::getCompleteUrl(), '/')) . '/error';
        foreach ($array as $key => $value) {
            if (is_null($value) || $value == '') {
                unset($array[$key]);
            }
        }
        return $array;
    }

    /**
     * Returns a specific settings parameter
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

	/**
     * Returns the price
     * @param string $priceAndStep
	 * @param int $pageNum
     * @return int|float
     */
    function getPrice($priceAndStep, $pageNum) {
        $arrayPrice = explode(':', $priceAndStep);
        if (sizeof($arrayPrice) == 1) {
            return $priceAndStep;
        } else if (sizeof($arrayPrice) == 2) {
            $price = $arrayPrice[0];
            $step = $arrayPrice[1];
            $multiplicator = round($pageNum / $step, 2);
            return $price * $multiplicator;
        }
        return 0;
    }

    /**
     * Generates the HMAC for the POST request.
     * The HMAC is necessary to determine whether it comes from a trusted source.
     *
     * @param string $downloadURL the generated url to download the file
	 * @param int $pageNum
	 * @return array
     */
    function getAllParametersWithHMAC($downloadURL, $pageNum) {
        $podSecretKey = 'podSecretKey';
        //--------
		include('PodSecurity.inc.php');
        $sec = new PodSecurity();

        $journal = & Request::getJournal();
        $pluginDao = & DAORegistry::getDAO('PluginSettingsDAO');
        $settings = &$pluginDao->getPluginSettings($journal->getId(), 'PodPlugin'); //TODO: fix this

        // check if charging for the pod service is activated
        $activated = $settings['activated'];

        // if not activated then just send it to the pod provider
        if ($activated != '1') {
            $newSettings = Array();
            $newSettings['repository'] = 'url';
            $newSettings['action'] = 'workbench';
            $newSettings['document_url'] = $downloadURL;
            return $newSettings;
        }
		
        // set the generated download URL
        $settings['pdfURI'] = $downloadURL;
        $settings['author'] = $settings['authors'];
        $settings['title'] = ' ';
        $settings['price'] = '' . $this->getPrice($settings['price'], $pageNum);
		
        $settings = $this->cleanUpSettings($settings);
		
        // set the key: MAKE SURE TO REMOVE IT AS SOON AS POSSIBLE
        $secretKey = $settings[$podSecretKey];

        // remove key from array
        unset($settings[$podSecretKey]);

        // returns the array together with the timestamp and the HMAC
        return $sec->sign($settings, $secretKey);
    }

    /**
     * Generates a list of the required articles.
     * If an article was not found due to different journals or access conflics
     * it is omitted from the list.
     * @param array $articles
     * @return array
     */
    function getGalleysFromRequest(Array $articles) {
        $galleyDao = & DAORegistry::getDAO('ArticleGalleyDAO');
        $galleyArray = Array();
        foreach ($articles as $article => $value) {
            $article_galley = explode('/', $value);
            //if(!$article_galley || $value == '') continue;
            if ($article_galley[1] == '' || $article_galley[1] == 'pdf')
                $article_galley[1] = $article_galley[0];

            $galleys = & $galleyDao->getGalleysByArticle($article_galley[0]);
            array_push($galleyArray, $galleys[0]);
        }

        foreach ($galleyArray as $key => $galley) {
            if (is_null($galley))
                unset($galleyArray[$key]);
        }

        return $galleyArray;
    }

    /**
     * This function show the cart before the data is compiled and sent to the pod provider.
     * It allows the user to change some settings like the order or qantity.
     * @param unknown_type $args
     */
    function showPodCart($args = null) {
        //import('pages.manager.FilesHandler');
        $this->validate();
        $this->setupTemplate();

        $journal = & Request::getJournal();

        $issueDao = & DAORegistry::getDAO('IssueDAO');
        $issue = & $issueDao->getCurrentIssue($journal->getId(), true);

        $templateMgr = & TemplateManager::getManager();

        $pod_files = Request::getCookieVar('pod_files');

        if ($issue != null) {
            import('classes.file.PublicFileManager');
            $publicFileManager = new PublicFileManager();
			
            if ($styleFileName = $issue->getStyleFileName()) {
                $templateMgr->addStyleSheet(
                    Request::getBaseUrl() . '/' . $publicFileManager->getJournalFilesPath($journal->getId()) . '/' . $styleFileName
                );
            }
			
            $issueHeadingTitle = $issue->getIssueIdentification(false, true);
            $issueCrumbTitle = $issue->getIssueIdentification(false, true);

            $arg = isset($args[0]) ? $args[0] : '';
            $showToc = true;

            $locale = AppLocale::getLocale();
            $templateMgr->assign('locale', $locale);

            if ($pod_files) {
                $articlesFromUser = explode(',', $pod_files);
                $galleyDao = & DAORegistry::getDAO('ArticleGalleyDAO');
                $publishedArticleDao = & DAORegistry::getDAO('PublishedArticleDAO');
                $publishedArticles = array();
                foreach ($articlesFromUser as $articleFromUser => $value) {
                    $articles = explode('/', $value);
                    if (!$articles || $value == '')
                        continue;
                    $pubArticle = & $publishedArticleDao->getPublishedArticleByArticleId($articles[0]);
                    if (!is_null($pubArticle)) {
                        array_push($publishedArticles, $pubArticle);
                    }
                }
                $templateMgr->assign_by_ref('shopCartArticles', $publishedArticles);
                $pageNum = 0;
                $pageNum = $this->getTotalPageNumber($articlesFromUser);
                $templateMgr->assign('pageNum', $pageNum);
            }

            $showToc = true;

            $templateMgr->assign_by_ref('issue', $issue);
            $templateMgr->assign('showToc', $showToc);

            // Subscription Access
            import('classes.issue.IssueAction');
            $subscriptionRequired = IssueAction::subscriptionRequired($issue);
            $subscribedUser = IssueAction::subscribedUser($journal);
            $subscribedDomain = IssueAction::subscribedDomain($journal);
            $subscriptionExpiryPartial = $journal->getSetting('subscriptionExpiryPartial');

            if ($showToc && $subscriptionRequired && !$subscribedUser && !$subscribedDomain && $subscriptionExpiryPartial) {
                $templateMgr->assign('subscriptionExpiryPartial', true);
                $publishedArticleDao = & DAORegistry::getDAO('PublishedArticleDAO');
                $publishedArticlesTemp = & $publishedArticleDao->getPublishedArticles($issue->getId());

                $articleExpiryPartial = array();
                foreach ($publishedArticlesTemp as $publishedArticle) {
                    $partial = IssueAction::subscribedUser($journal, $issue->getId(), $publishedArticle->getId());
                    if (!$partial)
                        IssueAction::subscribedDomain($journal, $issue->getId(), $publishedArticle->getId());
                    $articleExpiryPartial[$publishedArticle->getId()] = $partial;
                }
                $templateMgr->assign_by_ref('articleExpiryPartial', $articleExpiryPartial);
            }

            $templateMgr->assign('subscriptionRequired', $subscriptionRequired);
            $templateMgr->assign('subscribedUser', $subscribedUser);
            $templateMgr->assign('subscribedDomain', $subscribedDomain);
            $templateMgr->assign('showGalleyLinks', $journal->getSetting('showGalleyLinks'));

            //import('classes.payment.ojs.OJSPaymentManager');
            //$paymentManager = & OJSPaymentManager::getManager();
            import('classes.payment.ojs.OJSPaymentManager');
            $paymentManager = & new OJSPaymentManager(PKPApplication::getRequest());
            
            if ($paymentManager->onlyPdfEnabled()) {
                $templateMgr->assign('restrictOnlyPdf', true);
            }
            if ($paymentManager->purchaseArticleEnabled()) {
                $templateMgr->assign('purchaseArticleEnabled', true);
            }
        } else {
            $issueCrumbTitle = AppLocale::translate('current.noCurrentIssue');
            $issueHeadingTitle = AppLocale::translate('current.noCurrentIssue');
        }

        // Display creative commons logo/licence if enabled
        $templateMgr->assign('displayCreativeCommons', $journal->getSetting('includeCreativeCommons'));
        $templateMgr->assign('issueCrumbTitle', $issueCrumbTitle);
        $templateMgr->assign('issueHeadingTitle', $issueHeadingTitle);
        $templateMgr->assign('pageHierarchy', array(array(Request::url(null, 'issue', 'current'), 'current.current')));
        $templateMgr->assign('helpTopicId', 'user.currentAndArchives');

		$templateMgr->assign('templatePath', $this->podPlugin->getTemplatePath());
		$templateMgr->display($this->podPlugin->getTemplatePath() . '/viewPrintPage.tpl');
    }

	/**
     * Get the page number (incl. blank pages at the end)
	 * @param array $articlesFromUser
     * @return int
     */
    function getTotalPageNumber($articlesFromUser) {
        $pageNum = 0;
        $randomFileName = $this->getRandomPDFFileName();
        $fullPath = $this->preparePDFFile($randomFileName, $articlesFromUser);
        $pageNum = $this->getNumPagesPdf($fullPath);
        $publicFileManager = new PublicFileManager();
        $publicFileManager->deleteFile($fullPath);
        return $pageNum;
    }

    /**
     * Display current issue page
	 * @param array $args
     */
    function current($args = null) {
        $this->validate();
        $this->setupTemplate();

        $journal = & Request::getJournal();

        $issueDao = & DAORegistry::getDAO('IssueDAO');
        $issue = & $issueDao->getCurrentIssue($journal->getId(), true);

        $templateMgr = & TemplateManager::getManager();

        if ($issue != null) {
            import('classes.file.PublicFileManager');
            $publicFileManager = new PublicFileManager();

            if ($styleFileName = $issue->getStyleFileName()) {
                $templateMgr->addStyleSheet(
                    Request::getBaseUrl() . '/' . $publicFileManager->getJournalFilesPath($journal->getId()) . '/' . $styleFileName
                );
            }

            $issueHeadingTitle = $issue->getIssueIdentification(false, true);
            $issueCrumbTitle = $issue->getIssueIdentification(false, true);

            $arg = isset($args[0]) ? $args[0] : '';
            $showToc = true;

            $locale = AppLocale::getLocale();
            $templateMgr->assign('locale', $locale);
			
            if (!$showToc && $issue->getFileName($locale) && $issue->getShowCoverPage($locale) && !$issue->getHideCoverPageCover($locale)) {
                $templateMgr->assign('fileName', $issue->getFileName($locale));
                $templateMgr->assign('width', $issue->getWidth($locale));
                $templateMgr->assign('height', $issue->getHeight($locale));
                $templateMgr->assign('coverPageAltText', $issue->getCoverPageAltText($locale));
                $templateMgr->assign('originalFileName', $issue->getOriginalFileName($locale));

                $showToc = false;
            } else {
                $publishedArticleDao = & DAORegistry::getDAO('PublishedArticleDAO');
                $publishedArticles = & $publishedArticleDao->getPublishedArticlesInSections($issue->getId(), true);
                $templateMgr->assign_by_ref('publishedArticlesFiles', $publishedArticlesFiles);

                $templateMgr->assign_by_ref('publishedArticles', $publishedArticles);
                $showToc = true;
            }

            $templateMgr->assign_by_ref('issue', $issue);
            $templateMgr->assign('showToc', $showToc);



            // Subscription Access
            import('classes.issue.IssueAction');
            $subscriptionRequired = IssueAction::subscriptionRequired($issue);
            $subscribedUser = IssueAction::subscribedUser($journal);
            $subscribedDomain = IssueAction::subscribedDomain($journal);
            $subscriptionExpiryPartial = $journal->getSetting('subscriptionExpiryPartial');

            if ($showToc && $subscriptionRequired && !$subscribedUser && !$subscribedDomain && $subscriptionExpiryPartial) {
                $templateMgr->assign('subscriptionExpiryPartial', true);
                $publishedArticleDao = & DAORegistry::getDAO('PublishedArticleDAO');
                $publishedArticlesTemp = & $publishedArticleDao->getPublishedArticles($issue->getId());

                $articleExpiryPartial = array();
                foreach ($publishedArticlesTemp as $publishedArticle) {
                    $partial = IssueAction::subscribedUser($journal, $issue->getId(), $publishedArticle->getId());
                    if (!$partial)
                        IssueAction::subscribedDomain($journal, $issue->getId(), $publishedArticle->getId());
                    $articleExpiryPartial[$publishedArticle->getId()] = $partial;
                }
                $templateMgr->assign_by_ref('articleExpiryPartial', $articleExpiryPartial);
            }

            $templateMgr->assign('subscriptionRequired', $subscriptionRequired);
            $templateMgr->assign('subscribedUser', $subscribedUser);
            $templateMgr->assign('subscribedDomain', $subscribedDomain);
            $templateMgr->assign('showGalleyLinks', $journal->getSetting('showGalleyLinks'));

//            import('classes.payment.ojs.OJSPaymentManager');
//            $paymentManager =& OJSPaymentManager::getManager();
            
            import('classes.payment.ojs.OJSPaymentManager');
            $paymentManager = & new OJSPaymentManager(PKPApplication::getRequest());
            
            if ($paymentManager->onlyPdfEnabled()) {
                $templateMgr->assign('restrictOnlyPdf', true);
            }
            if ($paymentManager->purchaseArticleEnabled()) {
                $templateMgr->assign('purchaseArticleEnabled', true);
            }
        } else {
            $issueCrumbTitle = AppLocale::translate('current.noCurrentIssue');
            $issueHeadingTitle = AppLocale::translate('current.noCurrentIssue');
        }

        // Display creative commons logo/licence if enabled
        $templateMgr->assign('displayCreativeCommons', $journal->getSetting('includeCreativeCommons'));
        $templateMgr->assign('issueCrumbTitle', $issueCrumbTitle);
        $templateMgr->assign('issueHeadingTitle', $issueHeadingTitle);
        $templateMgr->assign('pageHierarchy', array(array(Request::url(null, 'pod', 'current'), 'current.current')));
        $templateMgr->assign('helpTopicId', 'user.currentAndArchives');
		$templateMgr->assign('templatePath', $this->podPlugin->getTemplatePath());
		$templateMgr->display($this->podPlugin->getTemplatePath() . '/viewPage.tpl');
    }

    /**
     * Display issue view page
	 * @param array $args
     */
    function view($args) {
        $this->validate();
        $this->setupTemplate();

        $issueId = isset($args[0]) ? $args[0] : 0;
        $showToc = isset($args[1]) ? $args[1] : '';

        $journal = & Request::getJournal();

        $issueDao = & DAORegistry::getDAO('IssueDAO');

        if ($journal->getSetting('enablePublicIssueId')) {
            $issue = & $issueDao->getIssueByBestIssueId($issueId, $journal->getId());
        } else {
            $issue = & $issueDao->getIssueById((int) $issueId, null, true);
        }

        if (!$issue)
            Request::redirect(null, null, 'current');

        $templateMgr = & TemplateManager::getManager();
        $this->setupIssueTemplate($issue, ($showToc == 'showToc') ? true : false);
		
        // Display creative commons logo/licence if enabled
        $templateMgr->assign('displayCreativeCommons', $journal->getSetting('includeCreativeCommons'));
        $templateMgr->assign('pageHierarchy', array(array(Request::url(null, 'pod', 'archive'), 'archive.archives')));
        $templateMgr->assign('helpTopicId', 'user.currentAndArchives');
        $templateMgr->assign('templatePath', $this->podPlugin->getTemplatePath());
		$templateMgr->display($this->podPlugin->getTemplatePath() . '/viewPage.tpl');
    }

    /**
     * Display issue view page
	 * @param array $args
     */
    function help($args) {
        $this->validate();
        $this->setupTemplate();
        $journal = & Request::getJournal();
        $templateMgr = & TemplateManager::getManager();

        // Display creative commons logo/licence if enabled
        $templateMgr->assign('displayCreativeCommons', $journal->getSetting('includeCreativeCommons'));
        $templateMgr->assign('pageHierarchy', array(array(Request::url(null, 'pod', 'archive'), 'archive.archives')));
        $templateMgr->assign('helpTopicId', 'user.currentAndArchives');
        $templateMgr->display($this->podPlugin->getTemplatePath() . '/help.tpl');
    }

    /**
     * Display issue view page
	 * @param array $args
     */
    function error($args) {
        $this->validate();
        $this->setupTemplate();

        $journal = & Request::getJournal();

        $templateMgr = & TemplateManager::getManager();

        // Display creative commons logo/licence if enabled
        $templateMgr->assign('displayCreativeCommons', $journal->getSetting('includeCreativeCommons'));
        $templateMgr->assign('pageHierarchy', array(array(Request::url(null, 'pod', 'archive'), 'archive.archives')));
        $templateMgr->assign('helpTopicId', 'user.currentAndArchives');
		$templateMgr->display($this->podPlugin->getTemplatePath() . '/error.tpl');
    }

    /**
     * Given an issue, set up the template with all the required variables for view.tpl to function properly.
     * @param $issue object The issue to display
     * @param $showToc boolean iff false and a custom cover page exists,
     * 	the cover page will be displayed. Otherwise table of contents will be displayed.
     */
    function setupIssueTemplate(&$issue, $showToc = false) {
        $journal = & Request::getJournal();
        $journalId = $journal->getId();
        $templateMgr = & TemplateManager::getManager();
        if (isset($issue) && ($issue->getPublished() || Validation::isEditor($journalId) || Validation::isLayoutEditor($journalId) || Validation::isProofreader($journalId)) && $issue->getJournalId() == $journalId) {

            $issueHeadingTitle = $issue->getIssueIdentification(false, true);
            $issueCrumbTitle = $issue->getIssueIdentification(false, true);

            $locale = AppLocale::getLocale();

            import('classes.file.PublicFileManager');
			
			$templateMgr->assign('locale', $locale);


            if (!$showToc && $issue->getFileName($locale) && $issue->getShowCoverPage($locale) && !$issue->getHideCoverPageCover($locale)) {
                $templateMgr->assign('fileName', $issue->getFileName($locale));
                $templateMgr->assign('width', $issue->getWidth($locale));
                $templateMgr->assign('height', $issue->getHeight($locale));
                $templateMgr->assign('coverPageAltText', $issue->getCoverPageAltText($locale));
                $templateMgr->assign('originalFileName', $issue->getOriginalFileName($locale));

                $showToc = false;
            } else {
                $publishedArticleDao = & DAORegistry::getDAO('PublishedArticleDAO');
                $publishedArticles = & $publishedArticleDao->getPublishedArticlesInSections($issue->getId(), true);

                $publicFileManager = new PublicFileManager();
                $templateMgr->assign('publishedArticles', $publishedArticles);
                $showToc = true;
            }
            $templateMgr->assign('showToc', $showToc);
            $templateMgr->assign('issueId', $issue->getBestIssueId());
            $templateMgr->assign('issue', $issue);

            // Subscription Access
            import('classes.issue.IssueAction');
            $subscriptionRequired = IssueAction::subscriptionRequired($issue);
            $subscribedUser = IssueAction::subscribedUser($journal);
            $subscribedDomain = IssueAction::subscribedDomain($journal);
            $subscriptionExpiryPartial = $journal->getSetting('subscriptionExpiryPartial');

            if ($showToc && $subscriptionRequired && !$subscribedUser && !$subscribedDomain && $subscriptionExpiryPartial) {
                $templateMgr->assign('subscriptionExpiryPartial', true);
                $publishedArticleDao = & DAORegistry::getDAO('PublishedArticleDAO');
                $publishedArticlesTemp = & $publishedArticleDao->getPublishedArticles($issue->getId());

                $articleExpiryPartial = array();
                foreach ($publishedArticlesTemp as $publishedArticle) {
                    $partial = IssueAction::subscribedUser($journal, $issue->getId(), $publishedArticle->getId());
                    if (!$partial)
                        IssueAction::subscribedDomain($journal, $issue->getId(), $publishedArticle->getId());
                    $articleExpiryPartial[$publishedArticle->getId()] = $partial;
                }
                $templateMgr->assign_by_ref('articleExpiryPartial', $articleExpiryPartial);
            }

            $templateMgr->assign('subscriptionRequired', $subscriptionRequired);
            $templateMgr->assign('subscribedUser', $subscribedUser);
            $templateMgr->assign('subscribedDomain', $subscribedDomain);
            $templateMgr->assign('showGalleyLinks', $journal->getSetting('showGalleyLinks'));

            import('classes.payment.ojs.OJSPaymentManager');
            $paymentManager = & new OJSPaymentManager(PKPApplication::getRequest());

            if ($paymentManager->onlyPdfEnabled()) {
                $templateMgr->assign('restrictOnlyPdf', true);
            }
            if ($paymentManager->purchaseArticleEnabled()) {
                $templateMgr->assign('purchaseArticleEnabled', true);
            }
        } else {
            $issueCrumbTitle = AppLocale::translate('archive.issueUnavailable');
            $issueHeadingTitle = AppLocale::translate('archive.issueUnavailable');
        }

        if ($styleFileName = $issue->getStyleFileName()) {
            import('classes.file.PublicFileManager');
            $publicFileManager = new PublicFileManager();
            $templateMgr->addStyleSheet(
                Request::getBaseUrl() . '/' . $publicFileManager->getJournalFilesPath($journalId) . '/' . $styleFileName
            );
        }

        $templateMgr->assign('pageCrumbTitleTranslated', $issueCrumbTitle);
        $templateMgr->assign('issueHeadingTitle', $issueHeadingTitle);
    }

    /**
     * Display the issue archive listings
     */
    function archive() {
        $this->validate();
        $this->setupTemplate();

        $journal = & Request::getJournal();
        $issueDao = & DAORegistry::getDAO('IssueDAO');
        $rangeInfo = Handler::getRangeInfo('issues');

        $publishedIssuesIterator = $issueDao->getPublishedIssues($journal->getId(), $rangeInfo);
		$templateMgr = & TemplateManager::getManager();

        $templateMgr->assign('locale', AppLocale::getLocale());
        $templateMgr->assign_by_ref('issues', $publishedIssuesIterator);
        $templateMgr->assign('helpTopicId', 'user.currentAndArchives');
		$templateMgr->display($this->podPlugin->getTemplatePath() . '/archive.tpl');
    }

	/**
     * Setup the template
     */
    function setupTemplate() {
        parent::setupTemplate();
        AppLocale::requireComponents(array(LOCALE_COMPONENT_PKP_READER, LOCALE_COMPONENT_OJS_EDITOR));
    }
}
?>