<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        https://www.tobento.ch
 */

declare(strict_types=1);

namespace Tobento\App\Cache\Console;

use Tobento\Service\Cache\Simple\CachesInterface;
use Tobento\Service\Console\AbstractCommand;
use Tobento\Service\Console\InteractorInterface;

class CacheClearCommand extends AbstractCommand
{
    /**
     * The signature of the console command.
     */
    public const SIGNATURE = '
        cache:clear | Clears the PSR 16 cache(s)
        {--cache[] : The name of the cache(s)}
    ';
    
    /**
     * Handle the command.
     *
     * @param InteractorInterface $io
     * @param CachesInterface $caches
     * @return int The exit status code: 
     *     0 SUCCESS
     *     1 FAILURE If some error happened during the execution
     *     2 INVALID To indicate incorrect command usage e.g. invalid options
     */
    public function handle(InteractorInterface $io, CachesInterface $caches): int
    {
        $cacheNames = $io->option(name: 'cache');
        
        if (empty($cacheNames)) {
            $cacheNames = $caches->getNames();
        }
        
        foreach($cacheNames as $cacheName) {
            if (! $caches->has($cacheName)) {
                $io->info(sprintf('Cache %s not found to clear', $cacheName));
                continue;
            }
            
            if ($caches->get($cacheName)->clear()) {
                $io->success(sprintf('Cache %s cleared', $cacheName));
            } else {
                $io->error(sprintf('Could not clear cache %s', $cacheName));
            }
        }

        return 0;
    }
}