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

use Tobento\Service\Cache\CacheItemPoolsInterface;
use Tobento\Service\Console\AbstractCommand;
use Tobento\Service\Console\InteractorInterface;

class CachePoolClearCommand extends AbstractCommand
{
    /**
     * The signature of the console command.
     */
    public const SIGNATURE = '
        cache:pool:clear | Clears the PSR 6 cache item pool(s)
        {--pool[] : The name of the pool(s)}
    ';
    
    /**
     * Handle the command.
     *
     * @param InteractorInterface $io
     * @param CacheItemPoolsInterface $pools
     * @return int The exit status code: 
     *     0 SUCCESS
     *     1 FAILURE If some error happened during the execution
     *     2 INVALID To indicate incorrect command usage e.g. invalid options
     */
    public function handle(InteractorInterface $io, CacheItemPoolsInterface $pools): int
    {
        $poolNames = $io->option(name: 'pool');
        
        if (empty($poolNames)) {
            $poolNames = $pools->getNames();
        }
        
        foreach($poolNames as $poolName) {
            if (! $pools->has($poolName)) {
                $io->info(sprintf('Cache item pool %s not found to clear', $poolName));
                continue;
            }
            
            if ($pools->get($poolName)->clear()) {
                $io->success(sprintf('Cache item pool %s cleared', $poolName));
            } else {
                $io->error(sprintf('Could not clear cache item pool %s', $poolName));
            }
        }

        return 0;
    }
}