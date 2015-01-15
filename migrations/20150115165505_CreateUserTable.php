<?php

use Phpmig\Migration\Migration;

class CreateUserTable extends Migration
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
            CREATE TABLE user (
                 "id" INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
                 "username" TEXT(50,0),
                 "email" TEXT(80,0),
                 "password" TEXT(64,0),
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
            DROP TABLE IF EXISTS user;
        ');
    }
}
