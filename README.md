# Bex\Tools

[![Build Status](https://travis-ci.org/bitrix-expert/tools.svg)](https://travis-ci.org/bitrix-expert/tools)
[![Latest Stable Version](https://poser.pugx.org/bitrix-expert/tools/v/stable)](https://packagist.org/packages/bitrix-expert/tools) 
[![Total Downloads](https://poser.pugx.org/bitrix-expert/tools/downloads)](https://packagist.org/packages/bitrix-expert/tools) 
[![License](https://poser.pugx.org/bitrix-expert/tools/license)](https://packagist.org/packages/bitrix-expert/tools)

Tools for developers on Bitrix CMS:

* IblockTools: finder info blocks and properties by IDs or symbol codes.

```php
<?php
use Bex\Tools\IblockTools;

$iblockFinder = IblockTools::find('iblock_type', 'iblock_code');

$iblockId = $iblockFinder->id();
$propEnumId = $iblockFinder->propEnumId('PROP_CODE', 'VALUE_XML_ID');

// And much more…
```

* GroupTools: finder users groups by IDs or symbol codes.

```php
<?php
use Bex\Tools\GroupTools;

$groupFinder = GroupTools::find('group_code');

$groupId = $groupFinder->id();
$groupCode = GroupTools::findById(3)->code();

// And that's not all ;-)
```

* HlBlockTools: finder for highloadblock IDs by it's names.

```php
<?php
use Bex\Tools\HlBlockTools;

$hlBlockFinder = HlBlockTools::find('ReferenceName');

$hlBlockId = $hlBlockFinder->id();
$hlBlockName = HlBlockTools::findById(2)->name();

```

* Catalog\GroupTools: finder for catalog groups (price types) by it's names? id's or "BASE" flag.

```php
<?php
use Bex\Tools\Catalog\GroupTools;

$catalogGroupFinder = GroupTools::find('RETAIL');
$priceTypeId = $catalogGroupFinder->id();

$priceTypeName = GroupTools::findBase()->name();

```

* Prevents the creation of infoblocks with the same codes.
* Prevents the creation of user groups with the same string id.

# Installation

Add library on your Composer:

```
composer require bitrix-expert/tools
```

# Documentation

See [wiki](https://github.com/bitrix-expert/tools/wiki) and php docs in the classes of library.
