<?php

namespace PDOSimpleMigration\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use PDO;

class MigrationStatusCommand extends AbstractCommand
{

    protected function configure()
    {
        $this
            ->setName('status')
            ->setDescription('Show current status of your migrations')
        ;
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->loadConfig($input, $output);
        

        $output->writeln("\n <info>==</info> Configuration\n");
        $this->writeStatusInfosLineAligned($output, 'Driver', $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
        $this->writeStatusInfosLineAligned($output, 'Database', $this->pdo->query('select database()')->fetchColumn());
        $this->writeStatusInfosLineAligned($output, 'Migrations table', $this->config['table']);
        $this->writeStatusInfosLineAligned($output, 'Migrations directory', realpath($this->config['dir']));

        $availables = $this->getAvailables();
        $this->writeStatusInfosLineAligned($output, 'Migrations availables', count($availables));

        $executeds = $this->getExecuteds();
        $latest = end($executeds);
        reset($executeds);
        $next = null;
        foreach ($availables as $available) {
            if ((int) $latest < $available) {
                $next = $available;
                break;
            }
        }

        $this->writeStatusInfosLineAligned($output, 'Executed migrations', count($executeds));
        $this->writeStatusInfosLineAligned($output, 'New migrations', count($availables) - count($executeds));
        $this->writeStatusInfosLineAligned($output, 'Latest migration', $latest);
        $this->writeStatusInfosLineAligned($output, 'Next migration', $next);
        $migrationClass = $this->loadMigrationClass($next);
        if ($migrationClass) {
            $this->writeStatusInfosLineAligned($output, 'Next migration description', $migrationClass::$description);
        }
    }

    private function writeStatusInfosLineAligned($output, $title, $value)
    {
        $output->writeln('    <comment>>></comment> ' . $title . ': ' . str_repeat(' ', 50 - strlen($title)) . $value);
    }
}
