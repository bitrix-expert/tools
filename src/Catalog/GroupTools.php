<?php
/**
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Bex\Tools\Catalog;

/**
 * Tools for working with catalog groups.
 *
 * @author Mikhail Zhurov <mmjurov@gmail.com>
 */
class GroupTools
{
    /**
     * Gets Finder for catalog groups by group name.
     *
     * @param string $code Group name
     * @param bool $silenceMode When you use silence mode instead of an exception \Bex\Tools\ValueNotFoundException
     * (if value was be not found) is returned null.
     *
     * @return GroupFinder
     */
    public static function find($name, $silenceMode = false)
    {
        return new GroupFinder(
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
 * @return GroupFinder
     */
    public static function findById($id, $silenceMode = false)
    {
        return new GroupFinder(['id' => $id],
            $silenceMode
        );
    }

    /**
     * Gets Finder for catalog groups by base flag.
     *
     * @param bool $silenceMode When you use silence mode instead of an exception \Bex\Tools\ValueNotFoundException
     * (if value was be not found) is returned null.
     *
     * @return GroupFinder
     */
    public static function findBase($silenceMode = false)
    {
        return new GroupFinder(['base' => true],
            $silenceMode
        );
    }
}