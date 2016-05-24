Key data
============

- name of the plugin: Print-on-Demand Plugin
- author: Aldi Alimucaj, Felix Gründer
- current version: 1.0.0.0
- tested on OJS version: 2.4.6
- github link: https://github.com/ojsde/pod.git
- community plugin: no
- date: 24.5.2016

Description
============

This plugin adds a print-on-demand functionality to OJS.
The plugin is displayed in the frontend as a block in the sidebar in the form of a shopping cart - that means that articles can be added/removed arbitrarily.
By clicking "Print book" the selected articles are merged to a single pdf file and transferred to the print-on-demand provider *(epubli)*.

It has originally been developed by the Technical University Graz (Austria) and has been modified by the Center for Digital Systems (FU Berlin).

Installation
============

- rename the unzipped folder to *pod* and copy it to *plugins/generic*
- execute the following shell command: 
  *$ php tools/upgrade.php upgrade* (see https://pkp.sfu.ca/ojs/UPGRADE)
- in *Setup / Step 5* move the print-on-demand block to the appropriate position in the sidebar

 
Implementation
================

Database access, server access
-----------------------------
- reading access to OJS tables: 1

		plugin_settings

- writing access to OMP tables: 1

		plugin_settings

- new tables: 0
- recurring server access: yes

		writing/getting pdf files to/from cache folder
 
Classes, plugins, external software
-----------------------
- OJS classes used (php): 7
	
		lib.pkp.classes.plugins.BlockPlugin
		lib.pkp.classes.plugins.GenericPlugin
		lib.pkp.classes.file.FileManager
		lib.pkp.classes.form.Form
		classes.issue.IssueAction
		classes.handler.Handler
		classes.file.ArticleFileManager
		classes.file.PublicFileManager

- necessary plugins: 1

		customBlockManager
		
- optional plugins: 0
- use of external software: yes

		PDFMerger
		tcpdf
	
- file upload: no
 
Metrics
--------
- number of files: 412 (including external libraries)

Settings
--------
- settings: 4

		Partner ID: [i.e. fuberlin] created by the provider
		Secret Key: [sha-256 key] created by the provider
		Mandator: [i.e. epubli]
		Action: [workbench/order/price/voucher]
		Repository: [i.e. fuberlin] created by the provider
		Authors (optional)
		Color (optional)
		Preface (optional)
		Cover (optional)
		User ID 
		First name
		Last name
		Street
		Number
		ZIP 
		Town
		Country
		E-Mail 

Plugin category
----------
- plugin category: generic

Other
=============
- does using the plugin require special (background)-knowledge?: no
- access restrictions: no
- technical contact epubli: Daniel Poschmann <d.poschmann@epubli.com>


