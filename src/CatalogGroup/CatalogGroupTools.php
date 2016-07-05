<?php
/**
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Bex\Tools\CatalogGroup;

/**
 * Tools for working with users groups.
 *
 * @author Mikhail Zhurov <mmjurov@gmail.com>
 */
class CatalogGroupTools
{
    /**
     * Gets Finder for catalog groups by group name.
     *
     * @param string $code Group name
     * @param bool $silenceMode When you use silence mode instead of an exception \Bex\Tools\ValueNotFoundException
     * (if value was be not found) is returned null.
     *
     * @return CatalogGroupFinder
     */
    public static function find($name, $silenceMode = false)
    {
        return new CatalogGroupFinder(
            ['name' => $name],
            $silenceMode
        );
    }

    /**
     * Gets Finder for catalog groups by group ID.
     *
     * @param int $id Group ID
     * @param bool $silenceMode When you use silence mode instead of an exception \Bex\Tools\ValueNotFoundException
     * (if value was be not found) is returned null.

     * @return CatalogGroupFinder
     */
    public static function findById($id, $silenceMode = false)
    {
        return new CatalogGroupFinder(
            ['id' => $id],
            $silenceMode
        );
    }

    /**
     * Gets Finder for catalog groups by base flag.
     *
     * @param bool $silenceMode When you use silence mode instead of an exception \Bex\Tools\ValueNotFoundException
     * (if value was be not found) is returned null.
     *
     * @return CatalogGroupFinder
     */
    public static function findBase($silenceMode = false)
    {
        return new CatalogGroupFinder(
            ['base' => true],
            $silenceMode
        );
    }
}