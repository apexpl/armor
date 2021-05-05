<?php
declare(strict_types = 1);

namespace Apex\Armor\Policy;

use Apex\Armor\Armor;
use Apex\Armor\Policy\ArmorPolicy;
use Apex\Armor\Exceptions\ArmorPolicyNotExistsException;
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;


/**
 * Policy manager
 */
class PolicyManager
{

    /**
     * Constructor
     */
    public function __construct(
        private Armor $armor 
    ) { 
        $this->db = Di::get(DbInterface::class);
    }

    /**
     * Save policy
     */
    public function savePolicy(string $name, ArmorPolicy $policy):void
    {

        // Delete existing from database, if exists
        $this->db->query("DELETE FROM armor_policies WHERE name = %s", $name);

        // Add to database
        $this->db->insert('armor_policies', [
            'name' => $name, 
        'policy' => base64_encode(serialize($policy))
        ]);

    }

    /**
     * Load policy
     */
    public function loadPolicy(string $name):?ArmorPolicy
    {

        // Get from db
        if (!$data = $this->db->getField("SELECT policy FROM armor_policies WHERE name = %s", $name)) { 
            throw new ArmorPolicyNotExistsException("The policy does not exist with name, $name");
        }

        // Return
        return unserialize(base64_decode($data));
    }

    /**
     * List policies
     */
    public function listPolicies():array
    {

        // Go trough rows
        $policies = [];
        $rows = $this->db->getHash("SELECT name,policy FROM armor_policies");
        foreach ($rows as $name => $data) { 
            $policies[$name] = unserialize(base64_decode($data));
        }

        // Return
        return $policies;
    }

    /**
     * Delete policy
     */
    public function deletePolicy(string $name):bool
    {

        // Delete
        $stmt = $this->db->query("DELETE FROM armor_policies WHERE name = %s", $name);
        $num = $this->db->numRows($stmt);

        // Return
        return $num > 0 ? True : false;
    }

    /**
     * Purge policies
     */
    public function purgePolicies():void
    {
        $this->db->query("DELETE FROM armor_policies");
    }

}








