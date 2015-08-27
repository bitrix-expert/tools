# Bitrix tools
Helpers for developers on Bitrix.

## Infoblocks

Class `\Bex\Tools\Iblocks` returns IDs infoblocks and properties from cache by their codes.

Method | Description
------ | -----------
`\Bex\Tools\Iblocks::getId($iblockType, $iblockCode, $withoutException = false)` | Return infoblock ID by code
`\Bex\Tools\Iblocks::getPropEnumId($iblockType, $iblockCode, $propCode, $valueXmlId)` | Return ID of the list property value by XML_ID
`\Bex\Tools\Iblocks::getIblockType($iblockId)` | Return type of the infoblock
`\Bex\Tools\Iblocks::getPropId($iblockType, $iblockCode, $propCode)` | Return ID of the property

All queries will be cached. Cache will be deleted automatically by events handlers of the iblock module.
