<?php

namespace yii2tech\tests\unit\ar\role\data;

use yii\db\ActiveRecord;

/**
 * @property int $humanId
 * @property int $studyGroupId
 * @property bool $hasScholarship
 */
class StudentRole extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Student';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['studyGroupId', 'required'],
            ['studyGroupId', 'integer'],
            ['hasScholarship', 'boolean'],
        ];
    }
}