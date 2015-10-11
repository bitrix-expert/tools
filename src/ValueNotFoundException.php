<?php
/**
 * @link https://github.com/bitrix-expert/tools
 * @copyright Copyright © 2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Tools;

/**
 * Value in the Finder was not found.
 * 
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class ValueNotFoundException extends \Exception
{
    protected $destination;
    protected $by;

    /**
     * An exception is thrown if the value in the Finder was not found.
     * 
     * @param string $destination The value to search for.
     * @param string $by Filter parameters for search.
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($destination, $by, $code = 0, \Exception $previous = null)
    {
        $this->destination = $destination;
        $this->by = $by;
        
        parent::__construct($destination . ' by ' . $by . ' not found', $code, $previous);
    }

    /**
     * Gets destination.
     * 
     * @return string
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * Gets by (filter parameters for search).
     * 
     * @return string
     */
    public function getBy()
    {
        return $this->by;
    }
}