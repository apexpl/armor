<?php
declare(strict_types = 1);

namespace Apex\Armor\Migrations\install_1619632663;

use Apex\Migrations\Handlers\Migration;
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;

/**
 * Migration - install_1619632663
 */
class migrate extends Migration
{

    // Properties
    public string $author_username = 'mdizak';
    public string $author_name = 'Matt Dizak';
    public string $author_email = 'mdizak@apexpl.io';
    public string $branch = '';


    /**
     * Install
     */
    public function install(DbInterface $db):void
    {

        // Execute install.sql file
        $db->executeSqlFile(__DIR__ .'/install.sql');
    }

    /**
     * Rollback
     */
    public function rollback(DbInterface $db):void
    {

        // Execute SQL file
        $db->executeSqlFile(__DIR__ . '/rollback.sql');
    }

}


