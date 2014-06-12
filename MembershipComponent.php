<?php
/**
 * Created by PhpStorm.
 * User: semenov
 * Date: 09.04.14
 * Time: 13:25
 */

namespace yii_ext\membership;

/**
 * Class MembershipComponent
 * @package membership
 */
use yii_ext\membership\models\enums\MembershipIntervalUnit;
use yii_ext\membership\models\enums\MembershipStatus;

/**
 * Class MembershipComponent
 * @package membership
 */
class MembershipComponent extends \CApplicationComponent
{
    /**
     * @var
     */
    public $internalClasses;

    /**
     * @var bool
     */
    public $isPaid = false;
    /**
     * @var null
     */
    public $freeCondition = null;
    public $paidArray = array();
    /**
     * @var null
     */
    private $userId = null;

    /**
     * Initialization of classes from configuration to be used
     * @return void
     */
    public function init()
    {
        foreach ($this->internalClasses as $className => $classPath) {
            $this->$className = new $classPath;
        }
    }

    /**
     * Setting dynamic attributes (classes init)
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function __set($key, $value)
    {
        $this->$key = $value;
    }

    /**
     * @param $userId used to check other users subscription
     *
     * @return bool
     */
    public function isPaid($userId = null)
    {
        if ($userId == null) {
            $userId = $this->getUserId();
        }
        if (in_array($userId, array_keys($this->paidArray))) {
            return (bool)$this->paidArray[$userId];
        }

        if (is_callable($this->freeCondition)) {
            if (call_user_func($this->freeCondition, $userId) == true) {
                $this->paidArray[$userId] = true;
                return true;
            }
        }
        $membershipStatus = $this->membershipStatus;
        $membershipModel = $this->membershipModel;
        if ($userId === null) {
            if ($this->isPaid === false && $this->getUserId() !== false) {
                $membershipModel = self::getMembershipModel();
                if (isset($membershipModel) && $membershipModel->getStatus() == $membershipStatus::ACTIVE) {
                    $this->paidArray[$userId] = true;
                    $this->isPaid = true;
                }
            }
            $this->paidArray[$userId] = (bool)$this->isPaid();
            return $this->isPaid;
        } else {
            $membershipModel = $membershipModel::model()->userScope($userId)->find();
            if (isset($membershipModel) && $membershipModel->getStatus() == $membershipStatus::ACTIVE) {
                $this->paidArray[$userId] = true;
                return true;
            }
        }
        $this->paidArray[$userId] = false;
        return false;
    }

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

    public function getMembershipModel()
    {
        $model = $this->membershipModel;
        $model = $model->findByAttributes(array('userId' => $this->getUserId()));
        return $model;
    }

    public function isDemo()
    {
        $model = self::getMembershipModel();
        if (isset($model) && $model->status = MembershipStatus::ACTIVE) {
            return false;
        }
        return true;
    }

    /**
     * @param $planId
     * @param $intervalLength
     * @param $intervalUnit
     * @param $userId
     *
     * @return mixed
     */
    public function prolong($planId, $intervalLength, $intervalUnit, $userId = null)
    {
        if ($userId == null) {
            $userId = \Yii::app()->user->getId();
        }
        $model = $this->membershipModel;
        $model = $model->findByAttributes(array('userId' => $userId));
        if (!$model) {
            $model = $this->membershipModel;
            $model->userId = \Yii::app()->user->getId();
            $model->startDate = date('Y-m-d H:i:s');
            $model->endDate = date('Y-m-d H:i:s', strtotime("today + $intervalLength " . MembershipIntervalUnit::getLabel($intervalUnit)));
        } else {
            if (empty($model->endDate)) {
                $model->endDate = date('Y-m-d H:i:s');
            }
            $model->endDate = date('Y-m-d H:i:s', strtotime($model->endDate . " + $intervalLength " . MembershipIntervalUnit::getLabel($intervalUnit)));
        }
        $model->planId = $planId;
        $model->status = MembershipStatus::ACTIVE;
        return $model->save();
    }

    /**
     *
     * @param $membershipId
     *
     * @return mixed
     */
    public function voidMembership($membershipId)
    {
        $model = $this->membershipModel;
        $model->findByAttributes(array('id' => $membershipId));
        $model->status = MembershipStatus::CANCELLED;
        $model->save();
    }

    /**
     * @param $userId
     *
     * @return mixed
     */
    public function getSubscriptionTitle($userId = null)
    {
        if ($userId == null) {
            $userId = \Yii::app()->user->getId();
        }
        $model = $this->membershipModel;
        $model = $model->findByAttributes(array('userId' => $userId));
        if (!$model) {
            return 'Free';
        } else {
            return $model->plan->title;
        }
    }
}