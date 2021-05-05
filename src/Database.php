<?php
declare(strict_types = 1);

namespace Apex\Armor;

use Apex\Armor\Armor;
use Apex\Migrations\Migrations;
use Apex\Migrations\Handlers\{Installer, ClassManager};
use Apex\Container\Di;

/**
 * Database
 */
class Database
{

    /**
     * Constructor
     */
    public function __construct(
        private Armor $armor
    ) { 

    }

    /**
     * Install migrations
     */
    public function installDatabase():void
    {

        // Init migrations
        $migrations = new Migrations(
            container_file: __DIR__ . '/../container.php', 
            yaml_file: __DIR__ . '/../config/migrations.yml'
        );

        // Install
        $installer = Di::make(Installer::class);
        $installer->migrateAll();
    }

}



