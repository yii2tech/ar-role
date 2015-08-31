<?php

namespace yii2tech\tests\unit\ar\role\data;

use yii\db\ActiveRecord;

/**
 * @property integer $humanId
 * @property integer $studyGroupId
 * @property boolean $hasScholarship
 */
class StudentRole extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Student';
    }

    /**
     * @inheritdoc
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