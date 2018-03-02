<?php

namespace yii2tech\tests\unit\ar\role\data;

use yii\db\ActiveRecord;
use yii2tech\ar\role\RoleBehavior;

/**
 * @property int $humanId
 * @property int $rankId
 * @property int $salary
 *
 * @property Human $human
 *
 * @property int $id
 * @property string $name
 * @property string $address
 */
class Instructor extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'role' => [
                '__class' => RoleBehavior::class,
                'roleRelation' => 'human',
                'isOwnerSlave' => true,
                'roleAttributes' => [
                    'role' => 'instructor'
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Instructor';
    }

    /**
     * {@inheritdoc}
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
        return $this->hasOne(Human::class, ['id' => 'humanId']);
    }
}