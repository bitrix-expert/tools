<?php
/**
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Bex\Tools\HlBlock;

/**
 * Tools for working with users groups.
 *
 * @author Mikhail Zhurov <mmjurov@gmail.com>
 */
class HlBlockTools
{
    /**
     * Gets Finder for hlblock by it's name
     *
     * @param string $name HlBlock name
     * @param bool $silenceMode When you use silence mode instead of an exception \Bex\Tools\ValueNotFoundException
     * (if value was be not found) is returned null.
     *
     * @return HlBlockFinder
     */
    public static function find($name, $silenceMode = false)
    {
        return new HlBlockFinder(
            ['name' => $name],
            $silenceMode
        );
    }

    /**
     * Gets Finder for HlBlock by it's ID.
     *
     * @param int $id HlBlock ID
     * @param bool $silenceMode When you use silence mode instead of an exception \Bex\Tools\ValueNotFoundException
     * (if value was be not found) is returned null.
     *
     * @return HlBlockFinder
     */
    public static function findById($id, $silenceMode = false)
    {
        return new HlBlockFinder(
            ['id' => $id],
            $silenceMode
        );
    }
}
