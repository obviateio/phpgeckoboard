PHP Geckoboard
=============

A collection of PHP scripts that interact with Geckboard's custom widgets and 
charts.  Most scripts require the use of PHP Composer ( http://getcomposer.org/ ).

### Installing dependenies via Composer

The following is the recommended way to install dependencies through [Composer](http://getcomposer.org).

        curl -s http://getcomposer.org/installer | php && ./composer.phar install

Optionally use PEAR:

        pear -D auto_discover=1 install guzzlephp.org/pear/guzzle

You can find out more on how to install Composer, configure autoloading, and other best-practices for defining dependencies at [getcomposer.org](http://getcomposer.org).

Folder Descriptions
-----------------------

helpspot
- One script that will access the [HelpSpot](http://www.helpspot.com/) [API](http://www.helpspot.com/helpdesk/index.php?pg=kb.book&id=6) generate 4 different charts
- Includes a copy of the HelpSpot [API Implementation](http://www.helpspot.com/helpdesk/index.php?pg=kb.page&id=307) for PHP v1.1
- Known to work against HelpSpot version 3.1.6
- Example [Screenshots](http://imgur.com/a/zMWxo)
