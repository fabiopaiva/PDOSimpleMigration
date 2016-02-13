# PDO Simple Migration

Simple migration is a minimalist tool to manage your database versioning.

## Installation

    composer require fabiopaiva/pdo-simple-migration

![Installation](https://github.com/fabiopaiva/PDOSimpleMigration/blob/master/docs/install.png)

## Configuration

This library tries to find a file called config.php in working directory with PDO setup,
if file doesn't exist, you can send PDO parameters in command line.

### config.php (optional)

``` php
<?php
    return [
        'db' => [
            'dsn' => 'mysql:host=localhost;dbname=myDb;port=3306;charset=UTF8',
            'username' => 'root',
            'password' => 'pass',
        ],
        'table' => 'migrations',
        'dir' => 'migrations'
    ];

```

`table` is the name of database table to control your versioning, migrations is default.

`dir` is the path where you want to store you migrations file.

## Usage

### Status

List current status of your migrations

    vendor/bin/migration status

![Status](https://github.com/fabiopaiva/PDOSimpleMigration/blob/master/docs/status.png)

### Generate

Generate a empty migration

    vendor/bin/migration generate

![Generate](https://github.com/fabiopaiva/PDOSimpleMigration/blob/master/docs/generate.png)

#### Generated code example

``` php
<?php

namespace PDOSimpleMigration\Migrations;

use PDOSimpleMigration\Library\AbstractMigration;

class Version20160128130854 extends AbstractMigration
{
    public static $description = "Migration description";

    public function up()
    {
        //$this->addSql(/*Sql instruction*/);
    }

    public function down()
    {
        //$this->addSql(/*Sql instruction*/);
    }
}
```

### Migrate

Migrate to latest version

    vendor/bin/migration migrate

![Migrate](https://github.com/fabiopaiva/PDOSimpleMigration/blob/master/docs/migrate.png)

### Execute

Execute specific migration version (up or down)

    vendor/bin/migration execute version --up --down

![Execute](https://github.com/fabiopaiva/PDOSimpleMigration/blob/master/docs/execute.png)

## Options

### --dump

If `--dump` parameter is present, migration will only dump query in screen

### --dsn and --username

If you don't want to create config.php file, you can send `--dsn` setup parameter.
If you send `--dsn` parameter, you need to send `--username` parameter too.
The prompt will ask your database password.

### --dir

Directory where to save migrations classes, default `migrations`

### --table

Table where to store migrations versioning history, default `migrations`

## Issues

Please report issues to [Github Issue Tracker](https://github.com/fabiopaiva/PDOSimpleMigration/issues)
