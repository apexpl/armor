<?php
use Apex\Armor\{Armor, Database};
use Apex\Armor\AES\KeyManager;
use Apex\Container\Di;

/**
 * Armor Installation Script
 *
 * To install, open the ~/config/container.php file and modify the DbInterface::class item with 
 * your database credentials.  Then simply run this script in terminal to create the Armor database tables.
 */

/**
 * Master Encryption Password
 *
 * Enter your desired master encryption password below, or leave blank to not have a master encryption
 * password.  Please note, if you do not define a master password, and a user loses their password, any 
 * encrypted information associated with that user account will be permanently lost.
 */
$master_password = '';


// Load composer
require_once(__DIR__ . '/vendor/autoload.php');

// Perform install
performInstall($master_password);

/**
 * Perform install
 */
function performInstall($master_password):void
{

    // Init
    $armor = new Armor();
    $database = Di::make(Database::class);

    // Install database
    $database->installDatabase();

    // Generate master RSA key-pair, if needed
    if ($master_password != '') { 
        $manager = Di::make(KeyManager::class);
        $manager->generateMaster($master_password);
    }

    // Echo
    echo "Success!  Armor database has been installed, and is ready for use.\n\n";

}


