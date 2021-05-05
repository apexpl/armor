<?php
declare(strict_types = 1);

namespace Apex\Armor\Adapters;

use Apex\Container\Di;
use Apex\Armor\Interfaces\ArmorUserInterface;
use Apex\Armor\Enums\{EmailMessageType, PhoneMessageType};
use Apex\Armor\Auth\AuthSession;
use Apex\Armor\User\ArmorUser;
use Apex\Db\Interfaces\DbInterface;
use Psr\Http\Message\ServerRequestInterface;
use Apex\Armor\Interfaces\AdapterInterface;
use Apex\Armor\Exceptions\ArmorOutOfBoundsException;
use redis;


/**
 * Apex adapter that utilizes the apex/mercury and apex/syrus packages.
 */
class TestAdapter implements AdapterInterface
{

    /**
     * Get user by uuid
     */
    public function getUuid(DbInterface $db, string $uuid, bool $is_deleted = false):?ArmorUser
    {

        // Get object
        $is_deleted = $is_deleted === true ? 1 : 0;
        if (!$user = $db->getObject(ArmorUser::class, "SELECT * FROM armor_users WHERE uuid = %s AND is_deleted = %i", $uuid, $is_deleted)) { 
            return null;
        }

        // Return
        return $user;
    }

    /**
     * Send e-mail message
     */
    public function sendEmail(ArmorUserInterface $user, string $type, string $armor_code, string $new_email = ''):void
    {

        // Set request
        $request = [
            'uuid' => $user->getUuid(), 
            'type' => $type, 
            'code' => $armor_code
        ];

        // Add to redis
        $redis = Di::get(redis::class);
        $redis->hmset('armor:test:email', $request);
    }

    /**
     * Send SMS
     */
    public function sendSMS(ArmorUserInterface $user, string $type, string $code, string $new_phone = ''):void
    {

        // Set request
        $request = [
            'uuid' => $user->getUuid(), 
            'type' => $type, 
            'code' => $code
        ];

        // Add to redis
        $redis = Di::get(redis::class);
        $redis->hmset('armor:test:sms', $request);
    }

    /**
     * Pending password change added
     */
    public function pendingPasswordChange(ArmorUserInterface $user):void
    {
        $redis = Di::get(redis::class);
        $redis->set('armor:test:change_password', $user->getUuid());
    }

    /**
     * Request initial password
     */
    public function requestInitialPassword(ArmorUserInterface $user):void
    {
            $redis = Di::get(redis::class);
        $redis->set('armor:test:initial_password', $user->getUuid());
    }

    /**
     * Request initial password
     */
    public function requestResetPassword(ArmorUserInterface $user):void
    {
        $redis = Di::get(redis::class);
        $redis->set('armor:test:reset_password', $user->getUuid());
    }


    /**
     * Handle session status
     */
    public function handleSessionStatus(AuthSession $session, string $status):void
    {
        $request = [
            'uuid' => $session->getUuid(), 
            'session_id' => $session->getId(), 
            'status' => $status
        ];
        $redis = Di::get(redis::class);
        $redis->hmset('armor:test:status', $request);
    }

    /**
     * Handle authorized two factor request
     */
    public function handleTwoFactorAuthorized(AuthSession $session, ServerRequestInterface $request, bool $is_login = false):void
    {
        $req = [
            'session_id' => $session->getId(), 
            'uuid' => $session->getUuid(),
            'class' => $request::class
        ];
        $redis = Di::get(redis::class);
        $redis->hmset('armor:test:2fa_auth', $req); 
    }

    /**
     * onUpdate
     */
    public function onUpdate(ArmorUserInterface $user, string $column, string | bool $new_value)
    {

        if (is_bool($new_value)) { 
            $new_value = $new_value === true ? 'true' : 'false';
        }

        $redis = Di::get(redis::class);
        $redis->hmset('armor:test:onupdate', [
            'uuid' => $user->getUuid(), 
            'column' => $column, 
            'new_value' => (string) $new_value
        ]);
    }



}

