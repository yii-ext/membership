<?php
/**
 * Created by PhpStorm.
 * User: semenov
 * Date: 01.04.14
 * Time: 13:12
 */

namespace yii_ext\membership\models;

    /**
     * Class BaseMembershipPlanModel
     * @package membership\models
     */
/**
 * Class BaseMembershipPlanModel
 * @package yii_ext\membership\models
 */
class BaseMembershipPlanModel extends \CActiveRecord
{

    /**
     * @var
     */
    public $priceDiscounted;

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
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('title, description, price, intervalLength, intervalUnit', 'required'),
            array('intervalLength, intervalUnit', 'numerical', 'integerOnly' => true),
            array('price', 'numerical'),
            array('title', 'length', 'max' => 100),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, title, description, price, intervalLength, intervalUnit', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array();
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'title' => 'Title',
            'description' => 'Description',
            'price' => 'Price',
            'intervalLength' => 'Interval Length',
            'intervalUnit' => 'Interval Unit',
        );
    }


    /**
     * @static
     *
     * @param $id
     *
     * @return mixed
     */
    public static function getPrice($id)
    {
        $model = self::model()->findByPk($id);
        return $model->price;
    }

    /**
     * @static
     *
     * @param $id
     *
     * @return mixed
     */
    public static function getPlanIntervalLength($id)
    {
        $model = self::model()->findByPk($id);
        return $model->intervalLength;
    }

    /**
     * @static
     *
     * @param $id
     *
     * @return mixed
     */
    public static function getPlanIntervalUnit($id)
    {
        $model = self::model()->findByPk($id);
        return $model->intervalUnit;
    }
}