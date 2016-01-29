<?php

namespace PDOSimpleMigration\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationMigrateCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('migrate')
            ->setDescription('Upgrade to latest migration class')
            ;
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->loadConfig($input, $output);

        $availables = $this->getAvailables();
        $executeds = $this->getExecuteds();

        $diff = array_diff($availables, $executeds);

        foreach ($diff as $version) {
            $executor = new MigrationExecuteCommand();
            $executor->executeUp($version, $input, $output);
            $stmt = $this->pdo->prepare(
                'INSERT INTO '
                . $this->tableName
                . ' SET version = :version, description = :description'
            );
            $migrationClass = $this->loadMigrationClass($version);
            $stmt->bindParam('version', $version);
            $stmt->bindParam('description', $migrationClass::$description);
            $stmt->execute();
        }

        $output->writeln('<info>Everything updated</info>');
    }
}
