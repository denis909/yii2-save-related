<?php
/**
 * @author denis909
 * @license MIT
 */
namespace denis909\yii2\behaviors;

use yii\base\Event;
use yii\base\ModelEvent;
use yii\base\Behavior;
use yii\base\Model;
use yii\helpers\Html;
use yii\db\ActiveRecord;
use yii\db\AfterSaveEvent;
use denis909\yii2\exceptions\ModelException;
use denis909\yii2\events\LoadEvent;
use denis909\yii2\helpers\SaveRelated;
use Closure;

class SaveRelatedBehavior extends Behavior
{
    public $models;

    public $beforeSaveModel;

    public function events() : array
    {
        return [
            LoadEvent::class => 'onLoad',
            ActiveRecord::EVENT_AFTER_VALIDATE => 'onAfterValidate',
            ActiveRecord::EVENT_AFTER_INSERT => 'onAfterSave',
            ActiveRecord::EVENT_AFTER_UPDATE => 'onAfterSave'
        ];
    }

    protected function getModels() : array
    {
        if ($this->models instanceof Closure)
        {
            $models = $this->models;

            $this->models = $models($this->owner);
        }

        return $this->models;
    }

    public function onLoad(LoadEvent $event)
    {
        $isValid = Model::loadMultiple($this->getModels(), $event->values);

        if ($event->isValid)
        {
            $event->isValid = $isValid;
        }
    }

    public function onAfterValidate(Event $event)
    {
        $models = $this->getModels();

        $isValidModels = Model::validateMultiple($models);

        if (!$isValidModels)
        {
            foreach($models as $key => $model)
            {
                $errors = $model->getErrors();

                if ($errors)
                {
                    foreach($errors as $attribute => $attributeErrors)
                    {
                        $event->sender->addErrors([
                            SaveRelated::inputId(null, $key, $model, $attribute) => $attributeErrors
                        ]);
                    }
                }
            }
        }
    }

    public function onAfterSave(AfterSaveEvent $event)
    {
        foreach($this->getModels() as $model)
        {
            $beforeSaveModel = $this->beforeSaveModel;

            $beforeSaveModel($model);

            if (!$model->save(false))
            {
                throw new ModelException($model);
            }
        }
    }
}