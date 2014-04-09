<?php
/**
 * Created by PhpStorm.
 * User: semenov
 * Date: 01.04.14
 * Time: 12:59
 */

namespace membership;


/**
 * Interface UserMembershipInterface
 * @package payment
 */
/**
 * Interface MembershipInterface
 * @package membership
 */
interface MembershipInterface
{
    /**
     * @static
     *
     * @param $userId
     * @param $membershipPlanId
     *
     * @return mixed
     */
    public static function createSubscription($userId, $membershipPlanId);

    /**
     * @param $interval
     *
     * @internal param $membershipId
     * @return mixed
     */
    public function prolongMembership($interval);

    /**
     *
     * @internal param $membershipId
     *
     * @return mixed
     */
    public function voidMembership();

    /**
     * @return mixed
     */
    public function getStatus();
}

/**
 * Interface MembershipPlanInterface
 * @package membership
 */
interface MembershipPlanInterface
{

}