<?php
/**
 * Created by PhpStorm.
 * User: semenov
 * Date: 31.03.14
 * Time: 13:04
 */

namespace yii_ext\membership\models\enums;


/**
 * Class UserMembershipStatus
 * @package models\enums
 */
class MembershipStatus extends \CEnumerable
{
    /**
     * @var int db representation for Active status
     */
    const ACTIVE = 1;

    /**
     * @var int db representation for Inactive status
     */
    const INACTIVE = 2;
    /**
     * @var int db representation for Cancelled status
     */
    const CANCELLED = 3;

    /**
     * @return array of key => values
     */
    public static function listData()
    {
        return array(
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::CANCELLED => 'Cancelled',
        );
    }

    /**
     * @var key
     * @return string label
     */
    public static function getLabel($key)
    {
        $list = self::listData();
        if (isset($list[$key])) {
            return $list[$key];
        }
        return false;
    }


} 