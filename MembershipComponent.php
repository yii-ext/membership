<?php
/**
 * Created by PhpStorm.
 * User: semenov
 * Date: 09.04.14
 * Time: 13:25
 */

namespace membership;


//use membership\models\BaseMembershipModel;
use membership\models\enums\MembershipStatus;

/**
 * Class MembershipComponent
 * @package membership
 */
class MembershipComponent extends \CApplicationComponent
{

    public $internalClasses;
    /**
     * @var bool
     */
    public $isPaid = false;
    /**
     * @var null
     */
    private $userId = null;

    /**
     * @return bool|null
     */
    protected function getUserId()
    {
        if ($this->userId === null) {
            $user = \Yii::app()->getComponent('user');
            if ($user->isGuest()) {
                $this->userId = false;
            } else {
                $this->userId = $user->getId();
            }
        }
        return $this->userId;
    }

    /**
     * @param $userId used to check other users subscription
     *
     * @return bool
     */
    public function isPaid($userId = null)
    {
        if ($userId === null) {
            if ($this->isPaid === false && $this->getUserId() !== false) {
                $membershipModel = BaseMembershipModel::model()->userScope($this->getUserId())->find();
                if (isset($membershipModel) && $membershipModel->getStatus() == MembershipStatus::ACTIVE) {
                    $this->isPaid = true;
                }
            }
            return $this->isPaid;
        } else {
            $membershipModel = BaseMembershipModel::model()->userScope($userId)->find();
            if (isset($membershipModel) && $membershipModel->getStatus() == MembershipStatus::ACTIVE) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $interval
     *
     * @internal param $membershipId
     * @return mixed
     */
    public function prolongMembership($interval)
    {
        // TODO: Implement prolongMembership() method.
    }

    /**
     *
     * @internal param $membershipId
     *
     * @return mixed
     */
    public function voidMembership()
    {
        // TODO: Implement voidMembership() method.
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        // TODO: Implement getStatus() method.
    }
}