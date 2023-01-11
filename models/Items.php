<?php

namespace mecsu\content\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%content_items}}".
 *
 * @property int $id
 * @property int $block_id
 * @property int $ext_id
 * @property int $row_order
 */
class Items extends ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%content_items}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = [];
        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = [
            [['block_id', 'ext_id'], 'required'],
            [['block_id', 'ext_id', 'row_order'], 'integer']
        ];
        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/modules/content', 'ID'),
            'block_id' => Yii::t('app/modules/content', 'Block ID'),
            'ext_id' => Yii::t('app/modules/content', 'Extended ID'),
            'row_order' => Yii::t('app/modules/content', 'Row order'),
        ];
    }
}