<?php

namespace PDOSimpleMigration\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use PDOSimpleMigration\Library\AbstractMigration;
use Exception;

class MigrationExecuteCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('execute')
            ->setDescription('Upgrade to latest migration class')
            ->addArgument(
                'version',
                InputArgument::REQUIRED,
                'Version to execute'
            )
            ->addOption(
                'up',
                null,
                InputOption::VALUE_NONE,
                'Execute migration up'
            )
            ->addOption(
                'down',
                null,
                InputOption::VALUE_NONE,
                'Execute migration down'
            )
            ;
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->loadConfig($input, $output);
        $version = $input->getArgument('version');
        $migrationClass = $this->loadMigrationClass($version);
        if ($migrationClass instanceof AbstractMigration) {
            if ((!$input->getOption('up') && !$input->getOption('down'))
                || ($input->getOption('up') && $input->getOption('down'))
            ) {
                throw new Exception('Need argument --up or --down');
            }
            if ($input->getOption('up')) {
                $this->executeUp($version, $input, $output);
            }
            if ($input->getOption('down')) {
                $this->executeDown($version, $input, $output);
            }
        } else {
            throw new Exception('Version ' . $version . ' not found');
        }

    }

    public function executeUp($version, InputInterface $input, OutputInterface $output)
    {
        $this->loadConfig($input, $output);
        $migrationClass = $this->loadMigrationClass($version);
        if ($migrationClass instanceof AbstractMigration) {
            $migrationClass->up();
            $queries = $migrationClass->getSql();
            $dump = $input->getOption('dump');

            if (!$dump) {
                $output->writeln('Executing <info>' . $version . '</info>');
            }

            foreach ($queries as $query) {
                if ($dump) {
                    $output->writeln($query);
                } else {
                    $this->pdo->exec($query);
                }
            }
        }
    }

    public function executeDown($version, InputInterface $input, OutputInterface $output)
    {
        $this->loadConfig($input, $output);
        $migrationClass = $this->loadMigrationClass($version);
        if ($migrationClass instanceof AbstractMigration) {
            $migrationClass->down();
            $queries = $migrationClass->getSql();
            $dump = $input->getOption('dump');

            if (!$dump) {
                $output->writeln('Executing <info>' . $version . ' --down</info>');
            }

            foreach ($queries as $query) {
                if ($dump) {
                    $output->writeln($query);
                } else {
                    $this->pdo->exec($query);
                }
            }
        }
    }
}
