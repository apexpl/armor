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



