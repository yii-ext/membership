<?php
/**
 * Created by PhpStorm.
 * User: semenov
 * Date: 01.04.14
 * Time: 13:12
 */

namespace membership\models;

use membership\MembershipPlanInterface;
use CModel;

class BaseMembershipPlanModel extends CModel implements MembershipPlanInterface
{
    /**
     * Returns the static model of the specified AR class.
     *
     * @param string $className active record class name.
     *
     * @return BaseMembershipPlanModel the static model class
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
        return 'MembershipPlan';
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
}