<?php

use Phpmig\Migration\Migration;

class CreateJobTable extends Migration
{
    public function init()
    {
        $this->container = $this->getContainer();
        $this->database  = $this->container['db'];
    }

    /**
     * Do the migration
     */
    public function up()
    {
        $this->database->query('
            CREATE TABLE job (
                 "id" INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
                 "user_id" INTEGER,
                 "job_token" TEXT(32,0),
                 "slideshow_uuid" TEXT(32,0),
                 "status" TEXT(10),
                 "create_at" INTEGER,
                 "update_at" INTEGER
            );
        ');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->database->query('
            DROP TABLE IF EXISTS job;
        ');
    }
}
