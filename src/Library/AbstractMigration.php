<?php

namespace PDOSimpleMigration\Library;

abstract class AbstractMigration implements MigrationInterface
{
    private $sql = [];
     /**
     * Add query
     *
     * @param string $sql
     */
    protected function addSql($sql)
    {
        $this->sql[] = $sql;
    }

    public function getSql()
    {
        return $this->sql;
    }
}
