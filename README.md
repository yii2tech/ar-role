ActiveRecord Role Inheritance Extension for Yii2
================================================

This extension provides support for ActiveRecord relation role (table inheritance) composition.

For license information check the [LICENSE](LICENSE.md)-file.

[![Latest Stable Version](https://poser.pugx.org/yii2tech/ar-role/v/stable.png)](https://packagist.org/packages/yii2tech/ar-role)
[![Total Downloads](https://poser.pugx.org/yii2tech/ar-role/downloads.png)](https://packagist.org/packages/yii2tech/ar-role)
[![Build Status](https://travis-ci.org/yii2tech/ar-role.svg?branch=master)](https://travis-ci.org/yii2tech/ar-role)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yii2tech/ar-role
```

or add

```json
"yii2tech/ar-role": "*"
```

to the require section of your composer.json.


Usage
-----

This extension provides support for ActiveRecord relation role composition, which is also known as table inheritance.

For example: assume we have a database for the University. There are students studying in the University and there are
instructors teaching the students. Student has a study group and scholarship information, while instructor has a rank
and salary. However, both student and instructor have name, address, phone number and so on. Thus we can split
their data in the three different tables:
 - 'Human' - stores common data
 - 'Student' - stores student special data and reference to the 'Human' record
 - 'Instructor' - stores instructor special data and reference to the 'Human' record

DDL for such solution may look like following:

```sql
CREATE TABLE `Human`
(
   `id` integer NOT NULL AUTO_INCREMENT,
   `role` varchar(20) NOT NULL,
   `name` varchar(64) NOT NULL,
   `address` varchar(64) NOT NULL,
   `phone` varchar(20) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE InnoDB;

CREATE TABLE `Student`
(
   `humanId` integer NOT NULL,
   `studyGroupId` integer NOT NULL,
   `hasScholarship` integer(1) NOT NULL,
    PRIMARY KEY (`humanId`)
    FOREIGN KEY (`humanId`) REFERENCES `Human` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
) ENGINE InnoDB;

CREATE TABLE `Instructor`
(
   `humanId` integer NOT NULL,
   `rankId` integer NOT NULL,
   `salary` integer NOT NULL,
    PRIMARY KEY (`humanId`)
    FOREIGN KEY (`humanId`) REFERENCES `Human` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
) ENGINE InnoDB;
```

This extension introduces [[\yii2tech\ar\role\RoleBehavior]] ActiveRecord behavior, which allows role relation based
ActiveRecord inheritance.
In oder to make it work, first of all, you should create an ActiveRecord class for the base table, in our example it
will be 'Human':

```php
class Human extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'Human';
    }
}
```

Then you will be able to compose ActiveRecord classes, which implements role-based inheritance using [[\yii2tech\ar\role\RoleBehavior]].
There are 2 different ways for such classes composition:
 - Master role inheritance
 - Slave role inheritance


## Master role inheritance <span id="master-role-inheritance"></span>

This approach assumes role ActiveRecord class be descendant of the base role class:

```php
class Student extends Human // extending `Human` - not `ActiveRecord`!
{
    public function behaviors()
    {
        return [
            'roleBehavior' => [
                'class' => RoleBehavior::className(), // Attach role behavior
                'roleRelation' => 'studentRole', // specify name of the relation to the slave table
                'roleAttributes' => [
                    'roleId' => Human::ROLE_STUDENT // mark 'Human' record as 'student'
                ],
            ],
        ];
    }

    public function getStudentRole()
    {
        // Here `StudentRole` is and ActiveRecord, which uses 'Student' table :
        return $this->hasOne(StudentRole::className(), ['humanId' => 'id']);
    }
}
```

The main benefit of this approach is that role class directly inherits all methods, validation and other logic from
the base one. However, you'll need to declare an extra ActiveRecord class, which corresponds the role table.
Yet another problem is that you'll need to separate 'Student' records from 'Instructor' ones for the search process.
Without following code, it will return all 'Human' records, both 'Student' and 'Instructor':

```php
$students = Student::find()->all();
```

The solution for this could be introduction of special column 'role' in the 'Human' table and usage of the default
scope:

```php
class Student extends Human
{
    // ...

    public static function find()
    {
        return parent::find()->where(['role' => 'student']);
    }
}
```

This approach should be chosen in case most functionality depends on the 'Human' attributes.


## Slave role inheritance <span id="slave-role-inheritance"></span>

This approach assumes role ActiveRecord does not extends the base one, but relates to it:

```php
class Instructor extends \yii\db\ActiveRecord // do not extending `Human`!
{
    public function behaviors()
    {
        return [
            'roleBehavior' => [
                'class' => RoleBehavior::className(), // Attach role behavior
                'roleRelation' => 'human', // specify name of the relation to the master table
                'isOwnerSlave' => true, // indicate that owner is a role slave - not master
                'roleAttributes' => [
                    'roleId' => Human::ROLE_STUDENT // will be applied to the 'Human' record
                ],
            ],
        ];
    }

    public function getHuman()
    {
        return $this->hasOne(Human::className(), ['id' => 'humanId']);
    }
}
```

This approach does not require extra ActiveRecord class for functioning and it does not need default scope specification.
It does not directly inherit logic declared in the base ActiveRecord, however any custom method declared in the related
class will be available via magic method `__call()` mechanism. Thus if class `Human` has method `sayHello()`, you are
able to invoke it through `Instructor` instance.

This approach should be chosen in case most functionality depends on the 'Instructor' attributes.


## Accessing role attributes <span id="accessing-role-attributes"></span>

After being attached [[\yii2tech\ar\role\RoleBehavior]] provides access to the properties of the model bound by relation,
which is specified via [[\yii2tech\ar\role\RoleBehavior::roleRelation]], as they were the main one:

```php
$model = Student::findOne(1);
echo $model->studyGroupId; // equals to $model->studentRole->studyGroupId

$model = Instructor::findOne(2);
echo $model->name; // equals to $model->human->name
```

If the related model does not exist, for example, in case of new record, it will be automatically instantiated:

```php
$model = new Student();
$model->studyGroupId = 12;

$model = new Instructor();
$model->name = 'John Doe';
```


## Accessing role methods <span id="accessing-role-methods"></span>

Any non-static method declared in the model related via [[\yii2tech\ar\role\RoleBehavior::roleRelation]] can be accessed
from the owner model:

```php
class Human extends \yii\db\ActiveRecord
{
    // ...

    public function sayHello($name)
    {
        return 'Hello, ' . $name;
    }
}

class Instructor extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
        return [
            'roleBehavior' => [
                'class' => RoleBehavior::className(), // Attach role behavior
                // ...
            ],
        ];
    }
}

$model = new Instructor();
echo $model->sayHello('John'); // outputs: 'Hello, John'
```

This feature allows to inherit logic from the base role model in case of using 'slave' behavior setup approach.
However, this works both for the 'master' and 'slave' role approaches.


## Validation <span id="validation"></span>

Each time the main model is validated the related role model will be validated as well and its errors will be attached
to the main model:

```php
$model = new Student();
$model->studyGroupId = 'invalid value';
var_dump($model->validate()); // outputs "false"
var_dump($model->hasErrors('studyGroupId')); // outputs "true"
```

You may as well specify validation rules for the related model attributes as they belong to the main model:

```php
class Student extends Human
{
    // ...

    public function rules()
    {
        return [
            // ...
            ['studyGroupId', 'integer'],
            ['hasScholarship', 'boolean'],
        ];
    }
}
```


## Saving role data <span id="saving-role-data"></span>

When main model is saved the related role model will be saved as well:

```php
$model = new Student();
$model->name = 'John Doe';
$model->address = 'Wall Street, 12';
$model->studyGroupId = 14;
$model->save(); // insert one record to the 'Human' table and one record - to the 'Student' table
```

When main model is deleted related role model will be delete as well:

```php
$student = Student::findOne(17);
$student->delete(); // Deletes one record from 'Human' table and one record from 'Student' table
```


## Querying role records <span id="querying-role-records"></span>

[[\yii2tech\ar\role\RoleBehavior]] works through relations. Thus, in order to make role attributes feature work,
it will perform an extra query to retrieve the role slave or master model, which may produce performance impact
in case you are working with several models. In order to reduce number of queries you may use `with()` on the
role relation:

```php
$students = Student::find()->with('studentRole')->all(); // only 2 queries will be performed
foreach ($students as $student) {
    echo $student->studyGroupId . '<br>';
}

$instructors = Instructor::find()->with('human')->all(); // only 2 queries will be performed
foreach ($instructors as $instructor) {
    echo $instructor->name . '<br>';
}
```

You may apply 'with' for the role relation as default scope for the ActiveRecord query:

```php
class Instructor extends ActiveRecord
{
    // ...

    public static function find()
    {
        return parent::find()->with('human');
    }
}
```

> Tip: you may name slave table primary key same as master one: use 'id' instead of 'humanId' for it.
  In this case conditions based on primary key will be always the same. However, this trick may cause extra
  troubles in case you are using joins for role relations at some point.

If you need to specify search condition based on fields from both entities and you are using relational database,
you can use `joinWith()` method:

```php
$students = Student::find()
    ->innerJoinWith('studentRole')
    ->andWhere(['name' => 'John']) // condition for 'Human' table
    ->andWhere(['hasScholarship' => true]) // condition for 'Student' table
    ->all();
```

> Tip: using `joinWith()` will still require an extra SQL query to retrieve relational data.
  You can use [yii2tech/ar-eagerjoin](https://github.com/yii2tech/ar-eagerjoin) extension to remove this extra query.


## Creating role setup web interface <span id="creating-role-setup-web-interface"></span>

Figuratively speaking, [[\yii2tech\ar\role\RoleBehavior]] merges 2 ActiveRecords into a single one.
This means you don't need anything special, while creating web interface for their editing.
You may use standard CRUD controller:

```php
use yii\web\Controller;

class StudentController extends Controller
{
    public function actionCreate()
    {
        $model = new Student();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    // ...
}
```

While creating a web form you may use attributes from related role model as they belong to the main one:

```php
<?php
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $model Student */
?>
<?php $form = ActiveForm::begin(); ?>

<?= $form->field($model, 'name'); ?>
<?= $form->field($model, 'address'); ?>

<?= $form->field($model, 'studyGroupId')->dropDownList(ArrayHelper::map(StudyGroup::find()->all(), 'id', 'name')); ?>
<?= $form->field($model, 'hasScholarship')->checkbox(); ?>

<div class="form-group">
    <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
</div>

<?php ActiveForm::end(); ?>
```

For the best integration you may as well merge labels and hints of the related model:

```php
class Student extends Human
{
    // ...

    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            $this->getRoleRelationModel()->attributeLabels()
        );
    }

    public function attributeHints()
    {
        return array_merge(
            parent::attributeHints(),
            $this->getRoleRelationModel()->attributeHints()
        );
    }
}
```

**Heads up!** In order to work in this simple way you should declare validation rules for the role model attributes
being 'safe' in the main one:

```php
class Student extends Human
{
    // ...

    public function rules()
    {
        return [
            // ...
            [$this->getRoleRelationModel()->attributes(), 'safe'],
        ];
    }
}
```

Otherwise you'll have to load data for the role model separately:

```php
use yii\web\Controller;

class StudentController extends Controller
{
    public function actionCreate()
    {
        $model = new Student();

        $post = Yii::$app->request->post();

        // data loading separated, however only single save required :
        if ($model->load($post) && $model->getRoleRelationModel()->load($post) && $model->save()) {
            return $this->redirect(['view']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    // ...
}
```

You should use the role model for its inputs while creating form as well:

```php
<?php
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $model Student */
?>
<?php $form = ActiveForm::begin(); ?>

<?= $form->field($model, 'name'); ?>
<?= $form->field($model, 'address'); ?>

<?= $form->field($model->getRoleRelationModel(), 'studyGroupId')->dropDownList(ArrayHelper::map(StudyGroup::find()->all(), 'id', 'name')); ?>
<?= $form->field($model->getRoleRelationModel(), 'hasScholarship')->checkbox(); ?>

<div class="form-group">
    <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
</div>

<?php ActiveForm::end(); ?>
```
