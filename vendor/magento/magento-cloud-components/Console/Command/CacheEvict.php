<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CloudComponents\Console\Command;

use Magento\CloudComponents\Model\Cache\Evictor;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Performs force key eviction with a "scan" command.
 */
class CacheEvict extends Command
{
    /**
     * @var Evictor
     */
    private $evictor;

    /**
     * @param Evictor $evictor
     */
    public function __construct(Evictor $evictor)
    {
        $this->evictor = $evictor;

        parent::__construct('cache:evict');
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setDescription('Evicts unused keys by performing scan command');
    }

    /**
     * @inheritdoc
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Begin scanning of cache keys');

        $count = $this->evictor->evict();

        $output->writeln(sprintf(
            'Total scanned keys: %s',
            $count
        ));

        return Cli::RETURN_SUCCESS;
    }
}
