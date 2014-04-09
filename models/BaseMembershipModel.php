<?php
/**
 * Created by PhpStorm.
 * User: semenov
 * Date: 31.03.14
 * Time: 12:57
 */

namespace membership\models;

use membership\MembershipInterface;
use CModel;
use membership\models\enums\MembershipStatus;

/**
 * Class BaseMembershipModel
 * @package models
 */
class BaseMembershipModel extends \CActiveRecord implements MembershipInterface
{

    /**
     * Returns the static model of the specified AR class.
     *
     * @param string $className active record class name.
     *
     * @return BaseMembershipModel the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'Membership';
    }

    /**
     * Returns the list of attribute names of the model.
     * @return array list of attribute names.
     */
    public function attributeNames()
    {
        // TODO: Implement attributeNames() method.
    }


    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array();
    }

    /**
     * @scope
     *
     * @param mixed $user user model instance, array representation or plain id.
     *
     * @return static self reference.
     */
    public function userScope($user)
    {
        $criteria = $this->getDbCriteria();

        if (is_object($user)) {
            $userId = $user->id;
        } elseif (is_array($user)) {
            $userId = $user['id'];
        } else {
            $userId = $user;
        }
        $criteria->addColumnCondition(array('userId' => $userId));
        return $this;
    }

    /**
     * @scope
     *
     * @param $status
     *
     * @return $this
     */
    public function statusScope($status)
    {
        $criteria = $this->getDbCriteria();
        $criteria->addColumnCondition(array('status' => $status));
        return $this;
    }

    /**
     * @static
     *
     * @param $userId
     * @param $membershipPlanId
     *
     * @return mixed|void
     */
    public static function createSubscription($userId, $membershipPlanId)
    {
        $membership = new self();
        $membership->userId = $userId;
        $membership->membershipPlanId = $membershipPlanId;
        $membership->status = UserMembershipStatus::STATUS_ACTIVE;
        $membership->save();
        return $membership;
    }

    /**
     * @param $interval
     *
     * @internal param $membershipId
     * @return mixed|void
     */
    public function prolongMembership($interval)
    {
        $this->status = MembershipStatus::STATUS_ACTIVE;
        $this->endDate = new CDbExpression('DATE_ADD(NOW(), INTERVAL ' . $interval . ' DAY)');
        return $this->save();
    }

    /**
     * @return mixed|void
     */
    public function voidMembership()
    {
        $this->status = MembershipStatus::CANCELLED;
        return $this->save();
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        if ($this->status == MembershipStatus::ACTIVE && new \DateTime() > new \DateTime($this->endDate)) {
            $this->voidMembership();
        }
        return $this->status;
    }


} 