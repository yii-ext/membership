<?php
/**
 * Created by PhpStorm.
 * User: semenov
 * Date: 31.03.14
 * Time: 12:57
 */

namespace yii_ext\membership\models;

use yii_ext\membership\MembershipInterface;
use yii_ext\membership\models\enums\MembershipStatus;
use CModel;

/**
 * Class BaseMembershipModel
 * @package models
 * @property integer $id
 * @property integer $userId
 * @property integer $planId
 * @property string $startDate
 * @property string $endDate
 * @property bool $status
 * @property MembershipPlanModel[] $plan
 * @property UserModel[] $user
 * @method BaseMembershipModel|CActiveRecord find()
 * @method BaseMembershipModel|CActiveRecord findByPK()
 * @method BaseMembershipModel|CActiveRecord findByAttributes()
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
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('userId, planId, startDate', 'required'),
            array('userId, planId, status', 'numerical', 'integerOnly' => true),
            array('endDate', 'safe'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, userId, planId, startDate, endDate, status', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'plan' => array(self::HAS_ONE, 'MembershipPlanModel', array('id' => 'planId')),
            'user' => array(self::HAS_ONE, 'UserModel', array('id' => 'userId')),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'userId' => 'User',
            'planId' => 'Plan',
            'startDate' => 'Start Date',
            'endDate' => 'End Date',
            'status' => 'Status',
        );
    }

    /**
     * Check User Subscription
     * @author Igor Chepurnoy
     *
     * @param type $id
     *
     * @return boolean
     */
    public function checkActiveSubscription($id = null)
    {
        $criteria = new \CDbCriteria();
        if ($id == null && $this->status == self::ACTIVE) {
            $subscription = $this;
        } elseif ($id !== null) {
            $criteria->condition = 'userId =:userId AND status =:status';
            $criteria->params = array(':userId' => $id,':status' => MembershipStatus::ACTIVE);
            $subscription = self::model()->find($criteria);
        }
        if (!empty($subscription)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return array
     */
    public function scopes()
    {
        return array(
            'active' => array(
                'condition' => 'status = ' . MembershipStatus::ACTIVE . ''
            ),
            'upgradeToday' => array(
                'condition' => 'DATE(startDate) = CURDATE() AND status = ' . MembershipStatus::ACTIVE . ''
            ),
            'upgradeThisWeek' => array(
                'condition' => 'YEARWEEK(startDate) = YEARWEEK(CURRENT_DATE)  AND status = ' . MembershipStatus::ACTIVE . ''
            ),
            'upgradeThisMonth' => array(
                'condition' => 'DATE_FORMAT(startDate, "%Y-%m") = DATE_FORMAT(CURDATE(), "%Y-%m")  AND status = ' . MembershipStatus::ACTIVE . ''
            ),
            'expiringToday' => array(
                'condition' => 'DATE(endDate) = CURDATE() AND status = ' . MembershipStatus::ACTIVE . ''
            ),
            'expiringThisWeek' => array(
                'condition' => 'DATE(endDate) <= DATE(DATE_ADD(CURRENT_DATE,INTERVAL 7 DAY))  AND status = ' . MembershipStatus::ACTIVE . ''
            ),
            'expiringThisMonth' => array(
                'condition' => 'DATE(endDate) <= DATE(DATE_ADD(CURRENT_DATE,INTERVAL 1 MONTH))  AND status = ' . MembershipStatus::ACTIVE . ''
            )
        );
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
        $membership->status = MembershipStatus::ACTIVE;
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
        $this->status = MembershipStatus::ACTIVE;
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

    /**
     * @author Igor Chepurnoy
     * @return type
     */
    public static function getCountUpgradeUserToday()
    {
        return self::model()->upgradeToday()->cache(3600, self::getMemberShipDependies())->count();
    }

    /**
     * @author Igor Chepurnoy
     * @return type
     */
    public static function getCountUpgradeUserThisWeek()
    {
        return self::model()->upgradeThisWeek()->cache(3600, self::getMemberShipDependies())->count();
    }

    /**
     * @author Igor Chepurnoy
     * @return type
     */
    public static function getCountUpgradeUserThisMonth()
    {
        return self::model()->upgradeThisMonth()->cache(3600, self::getMemberShipDependies())->count();
    }
    /**
     * @author Igor Chepurnoy
     * @return type
     */
    public static function getCountExpiringThisWeek()
    {
        return self::model()->expiringThisWeek()->cache(3600, self::getMemberShipDependies())->count();
    }

    /**
     * @author Igor Chepurnoy
     * @return \CDbCacheDependency
     */
    public static function getMemberShipDependies()
    {
        return new \CDbCacheDependency('SELECT MAX(id) FROM ' . self::tableName() . '');

    }


} 