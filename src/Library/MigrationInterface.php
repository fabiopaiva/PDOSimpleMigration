<?php

namespace PDOSimpleMigration\Library;

interface MigrationInterface
{
    /**
     * Execute migration
     */
    public function up();
    /**
     * Rollback migration
     */
    public function down();
}
