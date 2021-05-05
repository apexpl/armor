<?php
declare(strict_types = 1);

namespace Apex\Armor\Auth\Operations;


/**
 * Phone
 */
class Phone
{

    /**
     * Get phone number from form.
     */
    public static function get(array $post = []):string
    {

        // Get post data, if needed
        if (count($post) == 0) { 
            $post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING) ?? [];
        }

        // Check for phone
        $phone = $post['phone'] ?? '';
        if ($phone == '') { 
            return '';
        }

        // Check for country code
        if (isset($post['phone_country']) && $post['phone_country'] != '') { 
            $phone = $post['phone_country'] . $phone;
        }

        // Format and return
        $phone = preg_replace("/[\W\D\s]/", "", $phone);
        return $phone;
    }

}

