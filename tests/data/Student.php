<?php

namespace yii2tech\tests\unit\ar\role\data;

use yii2tech\ar\role\RoleBehavior;

/**
 * @property StudentRole $studentRole
 *
 * @property int $studyGroupId
 * @property bool $hasScholarship
 */
class Student extends Human
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'role' => [
                '__class' => RoleBehavior::class,
                'roleRelation' => 'studentRole',
                'roleAttributes' => [
                    'role' => 'student'
                ],
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStudentRole()
    {
        return $this->hasOne(StudentRole::class, ['humanId' => 'id']);
    }
}