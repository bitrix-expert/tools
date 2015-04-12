# Bitrix tools
Helpers for developers on Bitrix.

## Infoblocks

Class `\Bex\Tools\Iblock` returns IDs infoblocks and properties from cache by their codes.

Method | Description
------ | -----------
`\Bex\Tools\Iblock::getId()` | Return infoblock ID by code
`\Bex\Tools\Iblock::getPropEnumId()` | Return ID of the list property value by XML_ID
`\Bex\Tools\Iblock::getIblockType()` | Return type of the infoblock
`\Bex\Tools\Iblock::getPropId()` | Return ID of the property

All queries will be cached. Cache will be deleted automatically by events handlers of the iblock module.
