<?php
/**
 * PHPIDS
 *
 * Requirements: PHP5, SimpleXML
 *
 * Copyright (c) 2008 PHPIDS group (https://phpids.org)
 *
 * PHPIDS is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, version 3 of the License, or
 * (at your option) any later version.
 *
 * PHPIDS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with PHPIDS. If not, see <http://www.gnu.org/licenses/>.
 *
 * PHP version 5.1.6+
 *
 * @category Security
 * @package  PHPIDS
 * @author   Mario Heiderich <mario.heiderich@gmail.com>
 * @author   Christian Matthies <ch0012@gmail.com>
 * @author   Lars Strojny <lars@strojny.net>
 * @license  http://www.gnu.org/licenses/lgpl.html LGPL
 * @link     http://php-ids.org/
 */
namespace IDS\Caching;

/**
 * File caching wrapper
 *
 * This class inhabits functionality to get and set cache via memcached.
 *
 * @category  Security
 * @package   PHPIDS
 * @author    Christian Matthies <ch0012@gmail.com>
 * @author    Mario Heiderich <mario.heiderich@gmail.com>
 * @author    Lars Strojny <lars@strojny.net>
 * @copyright 2007-2009 The PHPIDS Groupoup
 * @license   http://www.gnu.org/licenses/lgpl.html LGPL
 * @link      http://php-ids.org/
 * @since     Version 0.4
 */
class MemcachedCache implements CacheInterface
{
    /**
     * Caching type
     *
     * @var string
     */
    private $type = null;

    /**
     * Cache configuration
     *
     * @var array
     */
    private $config = null;

    /**
     * Flag if the filter storage has been found in memcached
     *
     * @var boolean
     */
    private $isCached = false;

    /**
     * Memcache object
     *
     * @var object
     */
    private $memcache = null;

    /**
     * Holds an instance of this class
     *
     * @var object
     */
    private static $cachingInstance = null;

    /**
     * Constructor
     *
     * @param string $type caching type
     * @param array  $init the IDS_Init object
     *
     * @return void
     */
    public function __construct($type, $init)
    {

        $this->type   = $type;
        $this->config = $init->config['Caching'];

        $this->connect();
    }

    /**
     * Returns an instance of this class
     *
     * @param string $type caching type
     * @param object $init the IDS_Init object
     *
     * @return object $this
     */
    public static function getInstance($type, $init)
    {
        if (!self::$cachingInstance) {
            self::$cachingInstance = new MemcachedCache($type, $init);
        }

        return self::$cachingInstance;
    }

    /**
     * Writes cache data
     *
     * @param array $data the caching data
     *
     * @return object $this
     */
    public function setCache(array $data)
    {
        if (!$this->isCached) {
            if (get_class($this->memcache) === 'Memcached') {
                $this->memcache->set(
                    $this->config['key_prefix'] . '.storage',
                    $data,
                    $this->config['expiration_time']
                );
            } else {    // 'Memcache'
                $this->memcache->set(
                    $this->config['key_prefix'] . '.storage',
                    $data,
                    false,
                    $this->config['expiration_time']
                );
            }
            
            
            
        }

        return $this;
    }

    /**
     * Returns the cached data
     *
     * Note that this method returns false if either type or file cache is
     * not set
     *
     * @return mixed cache data or false
     */
    public function getCache()
    {
        $data = $this->memcache->get(
            $this->config['key_prefix'] .
            '.storage'
        );
        $this->isCached = !empty($data);

        return $data;
    }

    /**
     * Connect to the memcached server
     *
     * @throws Exception if connection parameters are insufficient
     * @return void
     */
    private function connect()
    {

        if ($this->config['host'] && $this->config['port']) {
            // establish the memcache connection
            if (class_exists('Memcached', FALSE))
    		{
    			$this->memcache = new \Memcached;
    			$this->memcache->addServer(
    			    $this->config['host'],
    			    $this->config['port'], 1
    			);
    		} else {
    			$this->memcache = new \Memcache;
                $this->memcache->pconnect(
                    $this->config['host'],
                    $this->config['port']
                );
    		}
            

        } else {
            throw new \Exception('Insufficient connection parameters');
        }
    }
}
