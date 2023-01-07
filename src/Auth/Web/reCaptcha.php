<?php
declare(strict_types = 1);

namespace Apex\Armor\Auth\Web;

use Apex\Armor\Armor;
use Apex\Armor\Auth\Operations\IpAddress;

/**
 * reCaptcha
 */
class reCaptcha
{

    /**
     * Constructor
     */
    public function __construct(
        private Armor $armor
    ) { 

    }

    /**
     * Verify
     */
    public function verify(string $secret_key):bool
    {

        // Set request
        $request = [
            'secret' => $secret_key, 
            'response' => $_POST['g-recaptcha-response'] ?? '', 
            'remoteip' => IpAddress::get()
        ];

        // Send message via curl
        $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request));

        // Send http request
        $response = curl_exec($ch);
        curl_close($ch);

        // Decode json
        if (!$vars = json_decode($response, true)) { 
            return false;
        }
        $ok = $vars['success'] ?? false;

        // Return
        return (bool) $ok;
    }

}



