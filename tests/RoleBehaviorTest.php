<?php

namespace yii2tech\tests\unit\ar\role;

use yii2tech\tests\unit\ar\role\data\Human;
use yii2tech\tests\unit\ar\role\data\Instructor;
use yii2tech\tests\unit\ar\role\data\Student;
use yii2tech\ar\role\RoleBehavior;
use yii2tech\tests\unit\ar\role\data\StudentRole;

class RoleBehaviorTest extends TestCase
{
    public function testGetRoleRelationModel()
    {
        /* @var $model Student|RoleBehavior */
        $model = new Student();
        $roleModel = $model->getRoleRelationModel();
        $this->assertTrue($roleModel instanceof StudentRole);

        $model = Student::findOne(1);
        $roleModel = $model->getRoleRelationModel();
        $this->assertTrue($roleModel instanceof StudentRole);
    }

    /**
     * @depends testGetRoleRelationModel
     */
    public function testFieldAccess()
    {
        $model = new Student();
        $model->studyGroupId = 12;
        $this->assertEquals($model->studyGroupId, $model->studentRole->studyGroupId);
    }

    /**
     * @depends testFieldAccess
     */
    public function testValidate()
    {
        $model = new Student();

        $model->name = 'new name';
        $model->address = 'new address';
        $model->studyGroupId = 'invalid';

        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('studyGroupId'));
    }

    /**
     * @depends testFieldAccess
     */
    public function testInsertRecord()
    {
        $model = new Student();

        $model->name = 'new name';
        $model->studyGroupId = 12;

        $model->save(false);

        $this->assertEquals('student', $model->role);

        $roleModel = StudentRole::findOne(['humanId' => $model->id]);
        $this->assertNotEmpty($roleModel);
        $this->assertEquals($model->studyGroupId, $roleModel->studyGroupId);
    }

    /**
     * @depends testInsertRecord
     */
    public function testUpdateRecord()
    {
        $model = new Student();
        $model->name = 'new name';
        $model->studyGroupId = 12;
        $model->save(false);

        $model = Student::findOne($model->id);
        $model->studyGroupId = 14;
        $model->save(false);

        $roleModels = StudentRole::findAll(['humanId' => $model->id]);
        $this->assertCount(1, $roleModels);
        $this->assertEquals($model->studyGroupId, $roleModels[0]->studyGroupId);
    }

    /**
     * @depends testUpdateRecord
     */
    public function testDelete()
    {
        $model = new Student();
        $model->name = 'new name';
        $model->studyGroupId = 12;
        $model->save(false);

        $model = Student::findOne($model->id);
        $model->delete();

        $this->assertFalse(StudentRole::find()->where(['humanId' => $model->id])->exists());
    }

    /**
     * @depends testFieldAccess
     */
    public function testInsertInverted()
    {
        $model = new Instructor();

        $model->name = 'new name';
        $model->rankId = 15;

        $model->save(false);

        $roleModel = Human::findOne($model->humanId);
        $this->assertNotEmpty($roleModel);
        $this->assertEquals($model->name, $roleModel->name);
        $this->assertEquals('instructor', $model->role);
    }

    /**
     * @depends testInsertInverted
     */
    public function testUpdateInverted()
    {
        $model = new Instructor();
        $model->name = 'new name';
        $model->rankId = 15;
        $model->save(false);

        $model = Instructor::findOne($model->humanId);
        $model->name = 'updated name';
        $model->save(false);

        $roleModel = Human::findOne($model->humanId);
        $this->assertEquals($model->name, $roleModel->name);
    }

    /**
     * @depends testUpdateRecord
     */
    public function testDeleteInverted()
    {
        $model = new Instructor();
        $model->name = 'new name';
        $model->rankId = 15;
        $model->save(false);

        $model = Instructor::findOne($model->humanId);
        $model->delete();

        $this->assertFalse(Human::find()->where(['id' => $model->humanId])->exists());
    }

    /**
     * @depends testFieldAccess
     */
    public function testInvokeRoleRelatedModelMethod()
    {
        $model = new Instructor();
        $this->assertEquals('Hello, John', $model->sayHello('John'));
    }
}