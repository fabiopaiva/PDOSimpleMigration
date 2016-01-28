<?php

namespace PDOSimpleMigration\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Generator\FileGenerator;

class MigrationGenerateCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('generate')
            ->setDescription('Generate a new migration class')
            ;
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->getConfig($input, $output);

        $version = date('YmdHis');
        $code = $this->generate($version);
        $path = realpath($config['dir']) . '/Migration' . $version . '.php';
        file_put_contents($path, $code);
        $output->writeln('Generated class <info>'.$path.'</info>');
    }

    private function generate($version)
    {
        $generator = new ClassGenerator();
        $docblock = DocBlockGenerator::fromArray(array(
            'shortDescription' => 'PDO Simple Migration Class',
            'longDescription'  => 'Add your queries below',
        ));
        $generator
            ->setName('PDOSimpleMigration\\Migrations\\Migration' . $version)
            ->setExtendedClass('AbstractMigration')
            ->addUse('PDOSimpleMigration\Library\AbstractMigration')
            ->setDocblock($docblock)
            ->addProperties(array(
                array('description', 'Migration description', PropertyGenerator::FLAG_STATIC),
            ))
            ->addMethods(array(
                MethodGenerator::fromArray(array(
                    'name'       => 'up',
                    'parameters' => array(),
                    'body'       => '//$this->addSql(/*Sql instruction*/);',
                    'docblock'   => DocBlockGenerator::fromArray(array(
                        'shortDescription' => 'Migrate up',
                        'longDescription'  => null
                    )),
                )),
                MethodGenerator::fromArray(array(
                    'name'       => 'down',
                    'parameters' => array(),
                    'body'       => '//$this->addSql(/*Sql instruction*/);',
                    'docblock'   => DocBlockGenerator::fromArray(array(
                        'shortDescription' => 'Migrate down',
                        'longDescription'  => null
                    )),
                ))
            ));

        $file = FileGenerator::fromArray(array(
            'classes'  => array($generator)
        ));

        return $file->generate();
    }
}
