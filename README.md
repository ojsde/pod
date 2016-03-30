# Print-on-demand for OJS

This plugin adds a print-on-demand functionality to OJS.
It has originally been developed by the Technical University Graz (Austria) and has been modified by the Center for Digital Systems (FU Berlin).

##Installation:
- rename the unzipped folder to *pod* and copy it to *plugins/generic*
- execute the following shell command: 
  *$ php tools/upgrade.php upgrade* (see https://pkp.sfu.ca/ojs/UPGRADE)
- in *Setup / Step 5* move the print-on-demand block to the appropriate position in the sidebar

##Configuration:
As this plugin has been developed in cooperation with the print-on-demand provider *epubli* (http://www.epubli.de), these parameters in the "settings" section of the plugin have to be inquired by *epubli*:
- partner ID
- secret key
- mandator
- action
- repository 

The other parameters can be set individually, moreover it is possible to upload a file for the cover.

##Usage:
The plugin is displayed in the frontend as a block in the sidebar in the form of a shopping cart - that means that articles can be added/removed arbitrarily.
By clicking "Print book" the selected articles are merged to a single pdf file and transferred to the print-on-demand provider *(epubli)*.
