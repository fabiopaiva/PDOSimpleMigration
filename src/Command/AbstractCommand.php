<?php

namespace PDOSimpleMigration\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use PDO;
use Exception;

abstract class AbstractCommand extends Command
{
    protected $config;
    protected $tableName = 'migrations';
    protected $dir = 'migrations';
    protected $pdo;

    protected function configure()
    {
        $this
            ->addOption(
                'dump',
                null,
                InputOption::VALUE_NONE,
                'If set, only show query output'
            )
            ->addOption(
                'dsn',
                null,
                InputOption::VALUE_REQUIRED,
                'PDO DSN string'
            )
            ->addOption(
                'username',
                null,
                InputOption::VALUE_REQUIRED,
                'PDO database username'
            )
            ->addOption(
                'dir',
                null,
                InputOption::VALUE_REQUIRED,
                'Directory where migrations classes are saved'
            )
            ->addOption(
                'table',
                null,
                InputOption::VALUE_REQUIRED,
                'Database table where migrations versioning history are stored'
            )
        ;
    }

    protected function getPDO(InputInterface $input, OutputInterface $output)
    {
        if ($this->pdo === null) {
            $config = $this->getConfig($input, $output);
            $pdo = new PDO(
                $config['db']['dsn'],
                $config['db']['username'],
                $config['db']['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]
            );
            $this->pdo = $pdo;

            $this->checkTableExists();
        }
        return $this->pdo;
    }

    private function checkTableExists()
    {
        try {
            $sql = 'SELECT 1 FROM ' . $this->tableName . ' LIMIT 1';
            $result = $this->pdo->query($sql);
            $exists = true;
        } catch (Exception $e) {
            $exists = false;
        }

        if (!$exists) {
            $sql = 'CREATE TABLE ' . $this->tableName
                . '( '
                . 'version VARCHAR(255) NOT NULL PRIMARY KEY, '
                . 'description VARCHAR(255)'
                . ') ENGINE=INNODB'
            ;
            $this->pdo->query($sql);
        }
    }

    protected function getConfig(InputInterface $input, OutputInterface $output)
    {
        if ($this->config === null) {
            $config = null;
            if (is_file(getcwd() . '/config.php')) {
                $config = include getcwd() . '/config.php';
                if (!is_array($config) || !array_key_exists('db', $config)) {
                    unset($config);
                }
            }
            if (!$config) {
                if (!$input->getOption('dsn')) {
                    throw new Exception('Unable to find config.php. Please send --dsn parameter');
                } elseif (!$input->getOption('username')) {
                    throw new Exception('Unable to find config.php. Please send --username parameter');
                }
                $helper = $this->getHelper('question');
                $question = new Question('What is the database password?' . PHP_EOL);
                $question->setHidden(true);
                $question->setHiddenFallback(false);
                $password = $helper->ask($input, $output, $question);
                $config = [
                    'db' => [
                        'dsn' => $input->getOption('dsn'),
                        'username' => $input->getOption('username'),
                        'password' => $password
                    ],
                    'table' => $input->getOption('table'),
                    'dir' => $input->getOption('dir')
                ];
            }
            if (!array_key_exists('table', $config) || empty($config['table'])) {
                $config['table'] = 'migrations';
            }
            if (!array_key_exists('dir', $config) || empty($config['dir'])) {
                $config['dir'] = 'migrations';
            }
            $this->tableName = $config['table'];
            $this->dir = $config['dir'];
            $this->config = $config;
        }
        $this->checkDir();
        return $this->config;
    }

    private function checkDir()
    {
        if (!is_dir($this->dir)) {
            if (!mkdir($this->dir)) {
                throw new Exception('Directory ' . $this->dir . ' don\'t exists');
            }
        }
        if (!is_writable($this->dir)) {
            throw new Exception('Directory ' . $this->dir . ' is not writeable');
        }
    }

    protected function getAvailables()
    {
        $path = $this->config['dir'];
        $dir = opendir($path);
        $availables = [];
        while ($file = readdir($dir)) {
            if (is_file($path . DIRECTORY_SEPARATOR . $file) && preg_match('/^(Migration+\d+\.php)$/', $file)) {
                $availables[] = str_replace('Migration', '', basename($file, '.php'));
            }
        }
        sort($availables);
        return $availables;
    }

    protected function getExecuteds()
    {
        $sql = 'SELECT version FROM ' . $this->config['table'] . ' ORDER BY version';
        $result = $this->pdo->query($sql);
        $executeds = [];
        foreach ($result as $item) {
            $executeds[] = $item['version'];
        }
        sort($executeds);
        return $executeds;
    }

    protected function loadConfig(InputInterface $input, OutputInterface $output)
    {
        $this->getConfig($input, $output);
        $this->getPDO($input, $output);
    }

    protected function loadMigrationClass($version)
    {
        if (file_exists($this->config['dir'] . DIRECTORY_SEPARATOR . 'Migration' . $version . '.php')) {
            include_once $this->config['dir'] . DIRECTORY_SEPARATOR . 'Migration' . $version . '.php';
            $clasName = 'PDOSimpleMigration\\Migrations\\Migration' . $version;
            if (class_exists($clasName)) {
                return new $clasName();
            }
        }
    }
}
