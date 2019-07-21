<?php

namespace src\Decorator;

use DateTime;
use Exception;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use src\Integration\DataProvider;

class DecoratorManager extends DataProvider
{
    private $cache;
    private $logger;
	
    public function __construct($host, $user, $password, CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        parent::__construct($host, $user, $password);
        $this->cache = $cache;
        $this->logger = $logger;
    }
	
    public function getResponse(array &$input)
    {
        try
        {
            $cacheKey = gzcompress(json_encode($input));
            $cacheItem = $this->cache->getItem($cacheKey);
            if ($cacheItem->isHit())
            {
                return $cacheItem->get();
            }
            $result = parent::get($input);
            $cacheItem
                ->set($result)
                ->expiresAt(
                    (new DateTime())->modify('+1 day')
		);
				
	    return $result;
        } 
	catch (Exception $e)
	{
            $this->logger->critical($e->getMessage());
        }
		
        return [];
    }
}
