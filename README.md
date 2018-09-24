# ItemManager 2

ItemManager (IM) is a simple flat-file framework for GetSimple-CMS that allows you to develop completely customizable
PHP applications bundled with GetSimple-CMS.

ItemManager offers you a XML data management in just as straightforward a manner as if you were working with a real
database. ItemManager’s powerful API is very easy and enjoyable.

Design any type of product lists (items), search and filter through the items lists by using flexible API functions.
You can create a random set of categories and fields, each category is assigned any number of fields and each item is
assigned to one category, that represent the underlying data structure of ItemManager, which allows a countless design
combinations.

If you are going to create a new GetSimple plugin, no matter how complex: a blog, shopping cart, user management or just 
a simple script, be sure that ItemManager will help you realise your goals.
Stop wasting your effort re-inventing the wheel, ItemManager takes care of the
basic data management, getter, setter methods and any XML data storage processes for your plugin properties, while you
may continue to focus on more important things, such as plugin architecture. 

**What can be achieved with ItemManager?**

The use of ItemManager allows a very variable field of application, ItemManager can be used to create tools and
plugins:

- Product -lists, -catalogs
- eCommerce plugins
- Image galleries
- Blogs
- User management tools
- Varios data storage
- Basic data structures for applications and plugins
- Settings and data storages for other plugins
- Depiction of complex Informations in Tables
- Masks, search functions
- Substitutions for small databases
- etc

## About   
ItemManager was born as a fork of Items Manager on May, 2013. IM was written out of the necessity to create a simple 
product list with images, and has been constantly developed ever since. As of version 2 ItemManager offers a powerful 
API, which allows you to perform all kinds of tasks very comfortably.   

## Plugins Based On ItemManager   
ItemManager has already been used several times to create helpful plugins:   

- [ImForms](http://get-simple.info/extend/plugin/imforms/1121/) ([More info](https://ehret-studio.com/articles/imforms-a-form-generator/))   
- [SimpleCatalog](http://get-simple.info/extend/plugin/simplecatalog/1091/) ([More info](https://ehret-studio.com/articles/simplecatalog-itemmanager-based-plugin/))   
- [User Management](https://ehret-studio.com/articles/itemmanager/creating-user-management/)   
- [IM Extra Fields Plugin](http://get-simple.info/extend/plugin/im-extra-fields/1057/) or [IM Extra Fields Plugin (GitHub repository)](https://github.com/bigin/ImExtraFields)    
- [IM Photogallery](http://get-simple.info/extend/theme/itemmanagers-photogallery/1043/) ([Gallery example](http://ehret-studio.com/gallery/))   
- [IM Simple Forum Theme](http://get-simple.info/extend/theme/simpleforum/1016/) ([Example](http://im.ehret-studio.com/forum/))   
- [Frontainer User Management plugin](http://get-simple.info/extend/plugin/frontainer/1015/) ([Sign up / Login / Logout / Password recovery](http://im.ehret-studio.com/login/))   

## Usage:   
   
> First, create an instance of the ItemManager core (It should be called at least once in your code): 
   
```php
$imanager = imanager();
```
   
Here's an example, should you want to save your data in order to be able
to use it again later on, just do this:
   
```php
$item = new Item(1);
$item->name = 'My item name';
$item->setFieldValue('data', 'Hello world');
$item->save();
```
   
In order to bring the data into display again do this:
   
```php
$item = imanager()->getItem(1, 'name=My item name');
echo $item->fields->data->value; // Outputs: Hello world
```
   
## Documentation Website    
More infos about ItemManager: [https://ehret-studio.com/articles/itemmanager/](https://ehret-studio.com/articles/itemmanager/)   
   
## Other Tips & Examples    
- [Resizing images on the fly](https://bigin.github.io/ghpages/resizeimage/)   
- [How to make content of the GS components a little more page-based](https://bigin.github.io/ghpages/pagelayout/)   
- [ItemManager Simplify Field Value Access](https://bigin.github.io/ghpages/simplification/)   
- [How to add additional text under the GetSimple's page edit menu](http://get-simple.info/forums/showthread.php?tid=8664)   
- [MarkupSectionCache for ItemManager](http://get-simple.info/forums/showthread.php?tid=8016)   
- [CategoryJoins for ItemManager](http://get-simple.info/forums/showthread.php?tid=8017)   
   
   
## Installation Instructions:   
   
**Installing ItemManager 2.+ from the ZIP file:**   
You can download the current version of the ItemManager on GitHub. Unzip the downloaded file and copy its
contents to your plugins folder. The required data directories and configurations should be created automatically
when you first access the plugin. Make sure that PHP-process has enough security permissions to access these files.
   
**Upgrading ItemManager 2.+ from the ZIP file:**   
ItemManager upgrades are easy because everything important to your site is contained under the `/data/imanager/` and
`/data/uploads/imanager/` directory. You should delete everything else in `/plugins/` directory that belongs to the
ItemManager and leave the both directories above as it is.
   
**Upgrading ItemManager 2.+ to 2.3.+:**   
Download the latest version of ItemManager GitHub     
Extract the ZIP file somewhere temporary.   
Delete the following file from your `/plugins/` directory:     
   
Delete: `/plugins/imanager` folder   
Delete: `/plugins/imanager.php` file   
Upload the new version imanager folder to `/plugins/` directory   
Upload the new version `imanager.php` to `/plugins/` directory     
   
> NOTE: Backup any files you replace or delete!   

## Latest Changes V2.4.5:
**:::New features**   
- This version adds new methods to format the output of the Money field [#10](https://github.com/bigin/ItemManager_2.0/issues/10).
- A custom config file can now be used: The IM can run with different configuration options without having to exchange 
the default `config.php`. To accomplish this, just copy an existing `config.php` file from `/plugins/imanager/lib/inc/` 
to your `/data/imanager/settings/` directory and modify the relevant variables in it.    
- Minimal CSS adjustment for the rendering of fields in the admin.    

**:::Bug fixes**   
- Money field error `Invalid value format for the Fieldtype NAME` has been fixed [#10](https://github.com/bigin/ItemManager_2.0/issues/10).   
- Type assignment for the config variables `thumbwidth` and `thumbheight` has been corrected.   
- Redundant Allocator code block `` has been removed

## Complete changelog    
[https://gist.github.com/bigin/7ec315c670b7009b55be901c1b448c94](https://gist.github.com/bigin/7ec315c670b7009b55be901c1b448c94)   


