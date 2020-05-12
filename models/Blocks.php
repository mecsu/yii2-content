<?php

namespace wdmg\content\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\Expression;
//use yii\db\ActiveRecord;
use wdmg\base\models\ActiveRecordML;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\SluggableBehavior;
use wdmg\content\models\Fields;
use wdmg\content\models\Items;
use wdmg\content\models\Content;

/**
 * This is the model class for table "{{%content_blocks}}".
 *
 * @property int $id
 * @property int $source_id
 * @property string $title
 * @property string $description
 * @property string $alias
 * @property string $fields
 * @property int $type
 * @property int $status
 * @property int $locale
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 */
class Blocks extends ActiveRecordML
{
    const CONTENT_BLOCK_TYPE_ONCE = 1;
    const CONTENT_BLOCK_TYPE_LIST = 2;

    const CONTENT_BLOCK_STATUS_DRAFT = 0;
    const CONTENT_BLOCK_STATUS_PUBLISHED = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%content_blocks}}';
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
            'sluggable' =>  [
                'class' => SluggableBehavior::class,
                'attribute' => ['title'],
                'slugAttribute' => 'alias',
                'skipOnEmpty' => true,
                'immutable' => true
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
            [['title', 'alias', 'type'], 'required'],
            [['title', 'alias'], 'string', 'min' => 3, 'max' => 64],
            ['description', 'string', 'max' => 255],
            [['type', 'status'], 'integer'],
            //['alias', 'unique', 'skipOnEmpty' => true, 'message' => Yii::t('app/modules/content', 'Alias attribute must be unique.')],
            ['alias', 'match', 'skipOnEmpty' => true, 'pattern' => '/^[A-Za-z0-9\-\_]+$/', 'message' => Yii::t('app/modules/content','It allowed only Latin alphabet, numbers and the «-», «_» characters.')],
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
            'source_id' => Yii::t('app/modules/blog', 'Source ID'),
            'title' => Yii::t('app/modules/content', 'Title'),
            'description' => Yii::t('app/modules/content', 'Description'),
            'alias' => Yii::t('app/modules/content', 'Alias'),
            'content' => Yii::t('app/modules/content', 'Content'),
            'fields' => Yii::t('app/modules/content', 'Fields'),
            'type' => Yii::t('app/modules/content', 'Type'),
            'status' => Yii::t('app/modules/content', 'Status'),
            'locale' => Yii::t('app/modules/content', 'Locale'),
            'created_at' => Yii::t('app/modules/content', 'Created at'),
            'created_by' => Yii::t('app/modules/content', 'Created by'),
            'updated_at' => Yii::t('app/modules/content', 'Updated at'),
            'updated_by' => Yii::t('app/modules/content', 'Updated by'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {

        if (is_string($this->status))
            $this->status = intval($this->status);

        if (is_null($this->fields))
            $this->fields = Json::encode([]);
        elseif (is_array($this->fields))
            $this->fields = Json::encode($this->fields);

        return parent::beforeSave($insert);
    }

    /**
     * {@inheritdoc}
     */
    public function afterFind()
    {

        if (!($this->fields = Json::decode($this->fields)))
            $this->fields = [];

        parent::afterFind();
    }

    /**
     * @return array or null
     */
    public function getFields($field_id = null, $asArray = true)
    {

        if ($field_id) {
            if ($asArray)
                return Fields::find()->where(['id' => $field_id, 'source_id' => null])->asArray()->all();
            else
                return Fields::find()->where(['id' => $field_id, 'source_id' => null])->all();
        } else {
            if ($asArray)
                return Fields::find()->where(['block_id' => $this->id, 'source_id' => null])->asArray()->all();
            else
                return Fields::find()->where(['block_id' => $this->id, 'source_id' => null])->all();
        }
    }

    /**
     * @return array or null
     */
    public function getItems($block_id = null, $asArray = true)
    {

        if ($block_id && $this->type == self::CONTENT_BLOCK_TYPE_LIST) {
            if ($asArray)
                return Items::find()->where(['block_id' => $block_id])->asArray()->all();
            else
                return Items::find()->where(['block_id' => $block_id])->all();
        } elseif ($this->type == self::CONTENT_BLOCK_TYPE_LIST) {
            if ($asArray)
                return Items::find()->where(['block_id' => $this->id])->asArray()->all();
            else
                return Items::find()->where(['block_id' => $this->id])->all();
        } else {
            return null;
        }
    }

    /**
     * @return array or null
     */
    public function getContent($ext_id = null, $block_id = null, $asArray = true)
    {

        if ($ext_id) {
            if ($block_id) {
                if ($asArray)
                    return Content::find()->where(['id' => $ext_id, 'block_id' => $block_id])->asArray()->all();
                else
                    return Content::find()->where(['id' => $ext_id, 'block_id' => $block_id])->all();
            } else {
                if ($asArray)
                    return Content::find()->where(['id' => $ext_id, 'block_id' => $this->id])->asArray()->all();
                else
                    return Content::find()->where(['id' => $ext_id, 'block_id' => $this->id])->all();
            }
        }
        return null;
    }

    /**
     * @param null $id
     * @param bool $asArray
     * @return array|null|ActiveRecord
     */
    public static function getBlockContent($id = null, $locale = null, $asArray = false) {

        if (!is_integer($id) && !is_string($id))
            return null;

        $query = Content::find()->alias('content')
            ->select(['fields.sort_order as field_order', 'fields.name', 'content.content', 'fields.type', 'fields.params'])
            ->leftJoin(['blocks' => Blocks::tableName()], '`blocks`.`id` = `content`.`block_id` AND `blocks`.`locale` = `content`.`locale`')
            ->leftJoin(['fields' => Fields::tableName()], '`fields`.`id` = `content`.`field_id` AND `fields`.`locale` = `content`.`locale`');

        if (is_integer($id)) {
            if (!is_null($locale)) {
                $query->where([
                    'blocks.source_id' => intval($id),
                    'blocks.locale' => trim($locale)
                ]);
            } else {
                $query->where([
                    'blocks.id' => intval($id)
                ]);
            }
        } elseif (is_string($id)) {
            if (!is_null($locale)) {
                $query->where([
                    'blocks.alias' => trim($id),
                    'blocks.locale' => trim($locale)
                ]);
            } else {
                $query->where([
                    'blocks.alias' => trim($id)
                ]);
            }
        }

        $query->andWhere([
            'blocks.type' => self::CONTENT_BLOCK_TYPE_ONCE,
            'blocks.status' => self::CONTENT_BLOCK_STATUS_PUBLISHED
        ]);

        $query->orderBy(['fields.sort_order' => 'ASC']);

        if ($asArray)
            return $query->asArray()->all();
        else
            return $query->all();
    }

    /**
     * @param null $id
     * @param bool $asArray
     * @return array|null|ActiveRecord
     */
    public static function getListContent($id = null, $locale = null, $asArray = false) {

        if (!is_integer($id) && !is_string($id))
            return null;

        $query = Content::find()->alias('content')
            ->select(['items.row_order', 'fields.sort_order as field_order', 'fields.name', 'content.content', 'fields.type', 'fields.params'])
            ->leftJoin(['blocks' => Blocks::tableName()], '`blocks`.`id` = `content`.`block_id` AND `blocks`.`locale` = `content`.`locale`')
            ->leftJoin(['fields' => Fields::tableName()], '`fields`.`id` = `content`.`field_id` AND `fields`.`locale` = `content`.`locale`')
            ->leftJoin(['items' => Items::tableName()], '`items`.`block_id` = `blocks`.`id` AND `items`.`ext_id` = `content`.`id`');

        if (is_integer($id)) {
            if (!is_null($locale)) {
                $query->where([
                    'blocks.source_id' => intval($id),
                    'blocks.locale' => trim($locale)
                ]);
            } else {
                $query->where([
                    'blocks.id' => intval($id)
                ]);
            }
        } elseif (is_string($id)) {
            if (!is_null($locale)) {
                $query->where([
                    'blocks.alias' => trim($id),
                    'blocks.locale' => trim($locale)
                ]);
            } else {
                $query->where([
                    'blocks.alias' => trim($id)
                ]);
            }
        }

        $query->andWhere([
            'blocks.type' => self::CONTENT_BLOCK_TYPE_LIST,
            'blocks.status' => self::CONTENT_BLOCK_STATUS_PUBLISHED
        ]);

        $query->andWhere('`items`.`row_order` != 0');
        $query->orderBy(['items.row_order' => 'ASC', 'fields.sort_order' => 'ASC']);

        if ($asArray)
            return $query->asArray()->all();
        else
            return  $query->all();

    }

    public function getFieldsCount($block_id = null) {
        if (is_null($block_id))
            $block_id = $this->id;

        return Fields::find()->where(['block_id' => $block_id])->count();
    }

    public function getContentCount($block_id = null) {
        if (is_null($block_id))
            $block_id = $this->id;

        return Content::find()->where(['block_id' => $block_id])->count();
    }

    /**
     * @return object of \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        if (class_exists('\wdmg\users\models\Users'))
            return $this->hasOne(\wdmg\users\models\Users::class, ['id' => 'created_by']);
        else
            return $this->created_by;
    }

    /**
     * @return object of \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        if (class_exists('\wdmg\users\models\Users'))
            return $this->hasOne(\wdmg\users\models\Users::class, ['id' => 'updated_by']);
        else
            return $this->updated_by;
    }

    /**
     * @return array of list
     */
    public function getStatusesList($allStatuses = false)
    {
        if ($allStatuses)
            $list[] = [
                '*' => Yii::t('app/modules/content', 'All statuses')
            ];

        $list[] = [
            self::CONTENT_BLOCK_STATUS_DRAFT => Yii::t('app/modules/content', 'Draft'),
            self::CONTENT_BLOCK_STATUS_PUBLISHED => Yii::t('app/modules/content', 'Published')
        ];

        return $list;
    }

    /**
     * Finds the Newsletters model based on its primary key value.
     * If the model is not found, null will be returned.
     * @param integer/string $id_or_alias
     * @return ActiveRecord model or null
     */
    public static function findModel($id)
    {
        if (is_integer($id)) {
            if (($model = self::findOne(['id' => intval($id)])) !== null)
                return $model;
        } else if (is_string($id)) {
            if (($model = self::findOne(['alias' => trim($id)])) !== null)
                return $model;
        }

        return null;
    }
}