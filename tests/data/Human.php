<?php

namespace yii2tech\tests\unit\ar\role\data;

use yii\db\ActiveRecord;

/**
 * @property integer $id
 * @property string $role
 * @property string $name
 * @property string $address
 */
class Human extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Human';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['name', 'required'],
            ['address', 'required'],
        ];
    }
}