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

use Tobento\Service\Cache\CanDeleteExpiredItems;
use Tobento\Service\Cache\CacheItemPoolsInterface;
use Tobento\Service\Console\AbstractCommand;
use Tobento\Service\Console\InteractorInterface;

class CachePoolPruneCommand extends AbstractCommand
{
    /**
     * The signature of the console command.
     */
    public const SIGNATURE = '
        cache:pool:prune | Pruning expired PSR 6 cache pool(s) items
        {--pool[] : The name of the pool(s) to prune}
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
                $io->info(sprintf('Cache item pool %s not found to prune', $poolName));
                continue;
            }
            
            $pool = $pools->get($poolName);

            if ($pool instanceof CanDeleteExpiredItems) {
                if ($pool->deleteExpiredItems()) {
                    $io->success(sprintf('Cache item pool %s pruned', $poolName));
                } else {
                    $io->error(sprintf('Could not prune cache item pool %s', $poolName));
                }
            } else {
                $io->info(sprintf('Cache item pool %s does not support pruning', $poolName));
            }
        }

        return 0;
    }
}