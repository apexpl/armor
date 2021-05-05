<?php
declare(strict_types = 1);

namespace Apex\Armor\Policy;

use Apex\Container\Di;


/**
 * Brute force policy
 */
class BruteForcePolicy
{


    /**
      Constructor
     */
    public function __construct(
        private array $rules = []
    ) { 

    }

    /**
     * Add rule
     */
    public function addRule(int $attempts, int $seconds = 10, int $suspend_seconds = 0):void
    {

        // Add
        $this->rules[] = [
            'attempts' => $attempts, 
            'seconds' => $seconds, 
            'suspend_seconds' => $suspend_seconds
        ];

    }

    /**
     * List rules
     */
    public function getRules():array
    {

        // Get from config, if we don't have any
        if (count($this->rules) == 0) { 
            $this->rules = Di::get('armor.default_brute_force_policy');
        }
        return $this->rules;
    }

    /**
     * Pruge all rules
     */
    public function purgeRules():void
    {
        $this->rules = [];
    }

}



