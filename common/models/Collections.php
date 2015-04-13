<?php
namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class Collections extends Base
{
    public static function tableName()
    {
        return '{{%collections}}';
    }
}
