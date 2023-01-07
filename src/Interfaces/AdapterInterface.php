<?php
declare(strict_types = 1);

namespace Apex\Armor\Interfaces;

use Apex\Armor\Auth\AuthSession;
use Apex\Armor\Interfaces\{ArmorUserInterface};
use Psr\Http\Message\ServerRequestInterface;
use Apex\Db\Interfaces\DbInterface;


/**
 * Adapter interface
 */
interface AdapterInterface
{

    /**
     * Get user by uuid
     *
     * This allows an instance of your own user class to be returned every time Armor loads 
     * a user, instead of only an instance of ArmorUser being returned.  Your user class must extend the 
     * Apex\Armor\User\ArmorUser class.
     *
     * Please see the MercuryAdapter.php adapter for an example, or if you don't wish / need to load your own 
     * user class, simply copy this method directly from the MercuryAdapter.pph class.
     *
     * @param DbInterface $db The database connection object to SQL database. 
     * @param string $uuid The UUID of the user to load.
     * @param bool $is_deleted Whether or not to look for users who have been previously deleted or not.
     *
     * @return ArmorUserInterface | null
     */
    public function getUuid(DbInterface $db, string $uuid, bool $is_deleted = false):?ArmorUserInterface;


    /**
     * Send e-mail message.
     *
     * @param ArmorUser $user The user to send e-mail to.  You may get the e-mail address via $user->getEmail() method.
     * @param string $type The e-mail message that is being sent.  Will be one of the constants within the Apex\Armor\Enums\EmailMessageType class.
     * @param string $armor_code Either the 48 character hash or 6 digit verification code required to authenticate the request.  This must be included in the contents of the e-mail message.
     * @param string $new_email Will contain an e-mail address during an update, if verification is required before the database is updated.  If this string is not empty, send to this e-mail instead of $user->getEmail(). 
     */
    public function sendEmail(ArmorUserInterface $user, string $type, string $armor_code, string $new_email = ''):void;


    /**
     * Send SMS message
     *
     * @param ArmorUser $user The user to send SMS message to.  You may get the phone number via the $user->getPhone() method.  All numbers are valid ISO numbers.
     * @param string $type The type of SMS message being sent.  Will be one of the constants within the Apex\Armor\Enums\PhoneMessageType class.
     * @param string $armor_code The six digit verification code required to authenticate the request.  This must be included within the SMS message.
     * @param string $new_phone Will contain an phone number during an update, if verification is required before the database is updated.  If this string is not empty, send to this phone instead of $user->getPhone(). 
     */
    public function sendSMS(ArmorUserInterface $user, string $type, string $code, string $new_phone = ''):void;


    /**
     * Handle a session status.
     *
     * If the session status is not AUTH_OK, this method will be called.  Used if for example, e-mail / phone verification is 
     * required, a phone number still must be defined, et al.
     *
     * Generally, this will display the appropriate template / view to the user informing them of the status, and provide 
     * any necessary instructions.
     *
     * @param AuthSession $session The authenticated session
     * @param string $status The status of the session.  Will be one of the constants within the Apex\Armor\Enums\SessionStatus class.
     */
    public function handleSessionStatus(AuthSession $session, string $status):void;


    /**
     * Handle a request authorized via two factor.
     *
     * Upon a user successfully authenticating a two factor request, either by clicking on a link 
     * within the e-mail message or by entering the six digit confirmation code, this method will be called.  This will pass a PSR7 compliant ServerRequest object of the exact request 
     * that was being made before it was interrupted requesting two factor authorization.
     *
     * This should treat the ServerRequest object as a new HTTP request, and process it normally.  The session status will have already 
     * been updated to SessionStatus::TWO_FACTOR_AUTHORIZED, so the next time $session->requireTwoFactor() is called it will not interrupt the 
     * request, and instead will continue processing.
     *   *
     * @param AuthSession $session The authenticated user session.
     * @param ServerRequest $request A PSR7 compliant server request of the request that was interrupted to request two factor authentication.
     * @param bool $is_login Whether or not this request is during initial login, or a standard request triggered via $session->requestTwoFactor() call.
     */
    public function handleTwoFactorAuthorized(AuthSession $session, ServerRequestInterface $request, bool $is_login = false):void;


    /**
     * Define initial password request
     *
     * Only applicable if the "require_password" setting within the ArmorPolicy is set to RequirePassword::AFTER_REGISTER.  No password 
     * is required upon registration, and instead the user will receive an e-mail with verification link.  Upon clicking said link, 
     * this method will be called which should display a template allowing the 
     * user to define their password.
     *
     * See the /docs/initial_password_request.md file for details.
     *
     * @param ArmorUser $user The user who is making the request.
     */  
    public function requestInitialPassword(ArmorUserInterface $user):void;

    /**
     * Obtain new password during password reset
     *
     * Upon a user requesting a password reset, and clicking on the link within the e-mail 
     * verification, this method will be called.  This should should display a template asking 
     * the user to define their new password.
     *
     * See the /docs/reset_password.md file for details.
     *
     * @param ArmorUser $user The user who is making the request.
     */  
    public function requestResetPassword(ArmorUserInterface $user):void;


    /**
     * Pending password change.
     *
     * Called when a password change is called with no old / current password specified, the user has 
     * encrypted data on their account, and a master encryption password was defined during 
     * installation.  This signifies a password change is pending, which can only be performed 
     * with use of the master encryption password, and should notify admin.
     *
     * See the /docs/pending_password_updates.md file for details.
     *
     * @param ArmorUser $user The user who requested a password change.
     */
    public function pendingPasswordChange(ArmorUserInterface $user):void;

    /**
     * onUpdate
     *
     * Called any time a user's profile information is updated, such as their e-mail address, 
     * phone number, active status, et al.  Used in cases where you're storing profiles in an external 
     * data source such as redis.
     *
     * @param ArmorUserInterface $user The user who was updated.
     * @param string $column The column / property name that was updated.
     * @param string | bool $new_value The new value of the column.
     */
    public function onUpdate(ArmorUserInterface $user, string $column, string | bool $new_value);

}



