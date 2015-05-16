
# ItemManager 2.0 #

*This text is still at the drafting stage at this moment*

ItemManager (IM) is a simple flat-file framework for [GetSimple-CMS](http://get-simple.info/) that allows you to develop completely customisable PHP applications bundled with GetSimple-CMS. 
ItemManager is distributed in the hope that it will be useful, to help to promote rapid web application development, which saves you time, and reduces the amount of repetitive coding for developers. Most significant though, is ItemManager's ability to empower you to quickly and easily create and maintain a rich and dynamic web applications.

### Compatibility with previous versions ###
The ItemManager 2.0 was not only completely revised, but also totally restructured, as a result all previous versions of ItemManager are no longer compatible with ItemManager 2.0 version. 


### What applications can be realized with the ItemManager ###
The ItemManager 2.0 is particularly well suited for the development of any web applications of small-sized data volumes and not excessive complexity, depending on the memory and server performance.

XML is an incredibly verbose format, developers who use it to process and handle large & complicated data structures should take into careful consideration its memory usage requirements. ItemManager 2.0 requires the entire Item, Category and Field objects loaded into memory to be available prior to any processing actions on that. Ok for a 40kb data but when dealing with > 100MB files you will see performance degradation, especially if you have a busy server. 


### Requirements ###
The included back-end example of the ItemManager 2.0 was made for modern, standards compliant browsers, older browser are not supported (please feel free to change this according to your wishes). The IM plugin has been developed on the basis of PHP 5.5.10 including some additional modules like GD, mod_rewrite (for friendly URLs/.htaccess) etc. The IM has also been tested with 5.4.26 PHP version on an Apache Server under Linux and Mac. In theory the IM will run on lower versions of PHP like 5.3.* as well but this was not tested.

**Tested on:**  
GetSimple 3.3.4 and 3.3.5 

**Supported Operating Systems:**  
Linux x86, x86-64  
Mac OS X  
Windows (not tested)  

**Supported Web Servers:**  
Apache 1.3.x - 2.2.x (uses htaccess for Friendly URLs by default)


### How this plugin works ###
Unlike previous version of IM, the IM 2.0-API has been heavily optimized, and now enables even more dynamic development. The new API offers a full range of methods required to read and write access, controlling, filtering and searching of the Items, Category and Field data. The new modular concept also allows development of custom Fieldtypes.

Traditionally, IM comes without any predefined front-end templates and leaves Developer free to code his own way. ItemManager 2.0 comes with a back-end area, however, the back-end is completely customizable, based on the available API and shall just act as an example. IM 2.0 allows you to cleanly separate the look of the site with the underlying function.


### Installation ###
You can download the current version of the ItemManager here. Unzip the downloaded file and copy its contents to your plugins folder. The required data directories and configurations should be created automatically when you first access the plugin.


Here the links for more info:
http://ehret-studio.com/lab/2015/mai/itemmanager-2.0-api-reference-list/
get-simple.info
etc …

