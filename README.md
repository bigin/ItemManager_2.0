###ItemManager 2###

ItemManager (IM) is a simple flat-file framework for GetSimple-CMS that allows you to develop completely customisable
PHP applications bundled with GetSimple-CMS.

ItemManager offers you a XML data management in just as straightforward a manner as if you were working with a real
database. ItemManager’s powerful API is very easy and enjoyable.

Design any type of product lists (items), search and filter through the items lists by using flexible API functions.
You can create a random set of categories and fields, each category is assigned any number of fields and each item is
assigned one category that represent the underlying data structure, which allows a countless design combinations of the
data.



**What can be achieved with ItemManager?**

The use of ItemManager allows a extrem variable field of application, ItemManager can be used to create tools and
plugins:

- Product -lists, -catalogs
- eCommerce plugins
- Image galleries
- Blog
- User management tools
- Varios data storage
- Basic data structures for applications and plugins
- Settings and data storages for other plugins
- Depiction of complex Informations in Tables
- Masks, Search-Forms
- Substitutions for small database replication
- etc


**Usage**

Here’s a simple ItemManager call, that you can use anywhere in your template files or in your own plugins to get a
current ItemManager class instance:

```php
$imanager = imanager();
```

**Install Instructions:**  

Installing ItemManager 2.* from the ZIP file:  
You can download the current version of the ItemManager on GitHub. Unzip the downloaded file and copy its
contents to your plugins folder. The required data directories and configurations should be created automatically
when you first access the plugin. Make sure that PHP-process has enough security permissions to access these files.

**Upgrading ItemManager 2.* from the ZIP file:**  
ItemManager upgrades are easy because everything important to your site is contained under the `/data/imanager/` and
`/data/uploads/imanager/` directory. You should delete everything else in `/plugins/` directory that belongs to the
ItemManager and leave the both directories above as it is.

**Upgrading ItemManager 2.* to 2.3.3:**  

Download the latest version of ItemManager GitHub  
Extract the ZIP file somewhere temporary.  
Delete the following file from your `/plugins/` directory:  

Delete: `/plugins/imanager` folder  
Delete: `/plugins/imanager.php` file  
Upload the new version imanager folder to `/plugins/` directory  
Upload the new version `imanager.php` to `/plugins/` directory  

> NOTE: Backup any files you replace or delete!

##Changelog

**2.3.4** 

Offers new features, that simplify the usage the plugin in combination with GetSimple native pages

See IM Extra Fields plugin: [http://get-simple.info/extend/plugin/im-extra-fields/1053/](http://get-simple.info/extend/plugin/im-extra-fields/1053/)

**2.3.3**

Slightly API modifications have been made in order to ease the use even for non-programming developers.  
More infos: `http://get-simple.info/forums/showthread.php?tid=7293`

**2.3.0**

BUGFIX: Small cache bug fixed
NEW: Expire cache method, can be used to automatically hooked to every `$item->save()` call  
NEW: Simple method to count the number of items, can be used to limiting files on the disk  

**2.2.0**  

BUGFIX: Category order in admin  
NEW: MarkupSectionCache  
NEW: Category Joins  

**2.1.0**

BUGFIX: upload file order  
BUGFIX: category listing markup  
NEW: Field title for uploaded images  
NEW: Field money  
NEW: Field datepicker  

For more information see: http://ehret-studio.com/lab/2015/mai/itemmanager-2.0


