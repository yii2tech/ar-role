<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\ar\role;

use yii\base\Behavior;
use yii\base\Model;
use yii\base\UnknownPropertyException;
use yii\db\BaseActiveRecord;

/**
 * RoleBehavior
 *
 * @property BaseActiveRecord $owner
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class RoleBehavior extends Behavior
{
    /**
     * @var string name of relation, which corresponds to role entity.
     */
    public $roleRelation;
    /**
     * @var boolean
     */
    public $isOwnerSlave = false;


    /**
     * @return BaseActiveRecord
     */
    public function getRoleRelationModel()
    {
        $model = $this->owner->{$this->roleRelation};
        if (is_object($model)) {
            return $model;
        }

        $relation = $this->owner->getRelation($this->roleRelation);
        $class = $relation->modelClass;
        $model = new $class();
        $this->owner->populateRelation($this->roleRelation, $model);

        return $model;
    }

    // Property Access Extension:

    /**
     * PHP getter magic method.
     * This method is overridden so that variation attributes can be accessed like properties.
     *
     * @param string $name property name
     * @throws UnknownPropertyException if the property is not defined
     * @return mixed property value
     */
    public function __get($name)
    {
        try {
            return parent::__get($name);
        } catch (UnknownPropertyException $exception) {
            $model = $this->getRoleRelationModel();
            if ($model->hasAttribute($name) || $model->canGetProperty($name)) {
                return $model->$name;
            }
            throw $exception;
        }
    }

    /**
     * PHP setter magic method.
     * This method is overridden so that role model attributes can be accessed like properties.
     * @param string $name property name
     * @param mixed $value property value
     * @throws UnknownPropertyException if the property is not defined
     */
    public function __set($name, $value)
    {
        try {
            parent::__set($name, $value);
        } catch (UnknownPropertyException $exception) {
            $model = $this->getRoleRelationModel();
            if ($model->hasAttribute($name) || $model->canSetProperty($name)) {
                $model->$name = $value;
            } else {
                throw $exception;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function canGetProperty($name, $checkVars = true)
    {
        if (parent::canGetProperty($name, $checkVars)) {
            return true;
        }
        $model = $this->getRoleRelationModel();
        return $model->hasAttribute($name) || $model->canGetProperty($name, $checkVars);
    }

    /**
     * @inheritdoc
     */
    public function canSetProperty($name, $checkVars = true)
    {
        if (parent::canSetProperty($name, $checkVars)) {
            return true;
        }
        $model = $this->getRoleRelationModel();
        return $model->hasAttribute($name) || $model->canSetProperty($name, $checkVars);
    }

    // Events :

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            Model::EVENT_AFTER_VALIDATE => 'afterValidate',
            BaseActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            BaseActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
            BaseActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            BaseActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            BaseActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
            BaseActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }

    /**
     * Handles owner 'afterValidate' event, ensuring role model is validated as well
     * in case it have been fetched.
     * @param \yii\base\Event $event event instance.
     */
    public function afterValidate($event)
    {
        if (!$this->owner->isRelationPopulated($this->roleRelation)) {
            return;
        }
        $model = $this->getRoleRelationModel();
        if (!$model->validate()) {
            $this->owner->addErrors($model->getErrors());
        }
    }

    /**
     * Handles owner 'beforeInsert' and 'beforeUpdate' events, ensuring role model is saved.
     * @param \yii\base\Event $event event instance.
     */
    public function beforeSave($event)
    {
        if (!$this->isOwnerSlave) {
            return;
        }

        $model = $this->getRoleRelationModel();

        $relation = $this->owner->getRelation($this->roleRelation);
        list($roleReferenceAttribute) = array_values($relation->link);

        $model->save(false);
        $this->owner->{$roleReferenceAttribute} = $model->getPrimaryKey();
    }

    /**
     * Handles owner 'afterInsert' and 'afterUpdate' events, ensuring role model is saved
     * in case it has been fetched before.
     * @param \yii\base\Event $event event instance.
     */
    public function afterSave($event)
    {
        if ($this->isOwnerSlave) {
            return;
        }

        if (!$this->owner->isRelationPopulated($this->roleRelation)) {
            return;
        }

        $model = $this->getRoleRelationModel();

        $relation = $this->owner->getRelation($this->roleRelation);
        list($ownerReferenceAttribute) = array_keys($relation->link);

        $model->{$ownerReferenceAttribute} = $this->owner->getPrimaryKey();
        $model->save(false);
    }

    /**
     * Handles owner 'beforeDelete' events, ensuring role model is deleted as well.
     * @param \yii\base\Event $event event instance.
     */
    public function beforeDelete($event)
    {
        if ($this->isOwnerSlave) {
            return;
        }
        $this->deleteRoleRelationModel();
    }

    /**
     * Handles owner 'beforeDelete' events, ensuring role model is deleted as well.
     * @param \yii\base\Event $event event instance.
     */
    public function afterDelete($event)
    {
        if (!$this->isOwnerSlave) {
            return;
        }
        $this->deleteRoleRelationModel();
    }

    /**
     * Deletes related role model.
     */
    protected function deleteRoleRelationModel()
    {
        $model = $this->owner->{$this->roleRelation};
        if (is_object($model)) {
            $model->delete();
        }
    }
}