###ItemManager 2###

ItemManager (IM) is a simple flat-file framework for GetSimple-CMS that allows you to develop completely customisable PHP applications bundled with GetSimple-CMS.

ItemManager offers you a XML data management in just as straightforward a manner as if you were working with a real database. ItemManager’s powerful API is very easy and enjoyable.

Design any type of product lists (items), search and filter through the items lists by using flexible API functions. You can create a random set of categories and fields, each category is assigned any number of fields and each item is assigned one category that represent the underlying data structure, which allows a countless design combinations of the data.

**Version 2.3.3**

Slightly API modifications have been made in order to ease the use even for non-programming developers.

More infos: http://get-simple.info/forums/showthread.php?tid=7293

**Version 2.3**

BUGFIX: Small cache bug fixed

NEW: Expire cache method, can be used to automatically hooked to every $item->save() call
NEW: Simple method to count the number of items, can be used to limiting files on the disk

**Version 2.2**

BUGFIX: Category order in admin

NEW: MarkupSectionCache
NEW: Category Joins

**Version 2.1**

BUGFIX: upload file order
BUGFIX: category listing markup

NEW: Field title for uploaded images
NEW: Field money
NEW: Field datepicker

**Install Instructions:**

Installing ItemManager 2.* from the ZIP file:
You can download the current version of the ItemManager here or GitHub. Unzip the downloaded file and copy its contents to your plugins folder. The required data directories and configurations should be created automatically when you first access the plugin. Make sure that PHP-process has enough security permissions to access these files.

**Upgrading ItemManager 2.* from the ZIP file:**
ItemManager upgrades are easy because everything important to your site is contained under the /data/imanager/ and /data/uploads/imanager/ directory. You should delete everything else in /plugins/ directory that belongs to the ItemManager and leave the both directories above as it is.

**Upgrading ItemManager 2.* to 2.3.3:**
Download the latest version of ItemManager here or GitHub
Extract the ZIP file somewhere temporary.
Delete the following file from your /plugins/ directory:

Delete: /plugins/imanager folder
Delete: /plugins/imanager.php  file
Upload the new version imanager folder to /plugins/ directory
Upload the new version imanager.php to /plugins/ directory

> NOTE: Backup any files you replace or delete! 

For more information see: ehret-studio.com/lab/2015/mai/itemmanager-2.0

