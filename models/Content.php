<?php

namespace mecsu\content\models;

use Yii;
use yii\db\Expression;
//use yii\db\ActiveRecord;
use wdmg\base\models\ActiveRecordML;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%content}}".
 *
 * @property int $id
 * @property int $source_id
 * @property int $field_id
 * @property int $block_id
 * @property string $content
 * @property string $locale
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 */
class Content extends ActiveRecordML
{

    public $block;
    public $source_id;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%content}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    self::EVENT_BEFORE_INSERT => 'created_at',
                    self::EVENT_BEFORE_UPDATE => 'updated_at',
                ],
                'value' => new Expression('NOW()'),
            ],
            'blameable' =>  [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ]
        ];

        return ArrayHelper::merge(parent::behaviors(), $behaviors);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = [
            [['field_id', 'block_id', 'content'], 'required'],
            [['field_id', 'block_id'], 'integer'],
            ['content', 'string'],
            [['created_at', 'updated_at'], 'safe'],
        ];

        if (class_exists('\wdmg\users\models\Users') && (Yii::$app->hasModule('admin/users') || Yii::$app->hasModule('users'))) {
            $rules[] = [['created_by', 'updated_by'], 'safe'];
        }

        return ArrayHelper::merge(parent::rules(), $rules);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/modules/content', 'ID'),
            'source_id' => Yii::t('app/modules/content', 'Source ID'),
            'field' => Yii::t('app/modules/content', 'Field'),
            'field_id' => Yii::t('app/modules/content', 'Field ID'),
            'block' => Yii::t('app/modules/content', 'Block'),
            'block_id' => Yii::t('app/modules/content', 'Block ID'),
            'content' => Yii::t('app/modules/content', 'Content'),
            'locale' => Yii::t('app/modules/content', 'Locale'),
            'created_at' => Yii::t('app/modules/content', 'Created at'),
            'created_by' => Yii::t('app/modules/content', 'Created by'),
            'updated_at' => Yii::t('app/modules/content', 'Updated at'),
            'updated_by' => Yii::t('app/modules/content', 'Updated by'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getField()
    {
        return $this->hasOne(Fields::class, ['id' => 'field_id']);
    }


    /**
     * Return the query relation for author who create model or value.
     *
     * @return int|\yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        if (class_exists('\app\modules\users\models\Users'))
            return $this->hasOne(\app\modules\users\models\Users::class, ['user_id' => 'created_by']);
        return parent::getCreatedBy();
    }

    /**
     * Return the query relation for author who update model or value.
     *
     * @return int|\yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        if (class_exists('\app\modules\users\models\Users'))
            return $this->hasOne(\app\modules\users\models\Users::class, ['user_id' => 'updated_by']);
        return parent::getUpdatedBy();
    }
}