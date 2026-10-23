<?php
/**
 * @author denis909
 * @license MIT
 */
namespace denis909\yii2\helpers;

use yii\base\Model;
use yii\helpers\Html;

class SaveRelated
{
    public static function inputId(?Model $mainModel, $key, Model $model, string $attribute) : string
    {
        if ($mainModel)
        {
            $inputId = Html::getInputId($mainModel, $key);
        }
        else
        {
            $inputId = $key;
        }

        $inputId .= str_replace('-', '_', '_' . Html::getInputId($model, $attribute));

        return $inputId;
    }
}