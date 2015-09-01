<?php

namespace yii2tech\tests\unit\ar\role\data;

use yii\db\ActiveRecord;
use yii2tech\ar\role\RoleBehavior;

/**
 * @property integer $humanId
 * @property integer $rankId
 * @property integer $salary
 *
 * @property Human $human
 *
 * @property integer $id
 * @property string $name
 * @property string $address
 */
class Instructor extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'roleBehavior' => [
                'class' => RoleBehavior::className(),
                'roleRelation' => 'human',
                'isOwnerSlave' => true,
                'roleAttributes' => [
                    'role' => 'instructor'
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Instructor';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['rankId', 'required'],
            ['salary', 'required'],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHuman()
    {
        return $this->hasOne(Human::className(), ['id' => 'humanId']);
    }
}