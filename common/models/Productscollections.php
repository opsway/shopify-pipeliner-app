<?php
namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class Productscollections extends Base
{

    public static function tableName()
    {
        return '{{%products_categories}}';
    }

}
