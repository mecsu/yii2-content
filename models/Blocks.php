<?php

namespace mecsu\content\models;

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
use mecsu\content\models\Fields;
use mecsu\content\models\Items;
use mecsu\content\models\Content;

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
     * Returns the field (or all available fields) belonging to the
     * current block/list of content including/without language locale.
     *
     * @param null $field_id
     * @param null $locale
     * @param bool $asArray
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getFields($field_id = null, $locale = null, $asArray = true)
    {
        if ($field_id) // One field
            $fields = Fields::find()->where(['id' => $field_id, 'source_id' => null]);
        elseif (!is_null($locale)) // All fields (labels in selected locale)
            $fields = Fields::find()->where(['block_id' => $this->id]);
        else // All fields (labels in default locale)
            $fields = Fields::find()->where(['block_id' => $this->id, 'source_id' => null]);

        if (is_null($locale) && !is_null($this->locale))
            $fields->andWhere(['locale' => $this->locale]);
        elseif (!is_null($locale))
            $fields->andWhere(['locale' => $locale]);

        if ($asArray)
            return $fields->asArray()->all();
        else
            return $fields->all();
    }

    /**
     * Returns items (rows) belonging to a specific content list.
     *
     * @param null $block_id
     * @param bool $asArray
     * @return array|\yii\db\ActiveRecord[]|null
     */
    public function getItems($block_id = null, $asArray = true)
    {
        if ($block_id && $this->type == self::CONTENT_BLOCK_TYPE_LIST)
            $items = Items::find()->where(['block_id' => $block_id]);
        elseif ($this->type == self::CONTENT_BLOCK_TYPE_LIST)
            $items = Items::find()->where(['block_id' => $this->id]);
        else
            return null;

        if ($asArray)
            return $items->asArray()->all();
        else
            return $items->all();
    }

    /**
     * Returns content belonging to a specific block/list.
     *
     * @param null $ext_id
     * @param null $block_id
     * @param bool $asArray
     * @return array|\yii\db\ActiveRecord[]|null
     */
    public function getContent($ext_id = null, $block_id = null, $asArray = true)
    {
        if ($ext_id)
            $content = Content::find()->where(['id' => $ext_id]);
        else
            return null;

        if ($block_id)
            $content->andWhere(['block_id' => $block_id]);
        else
            $content->andWhere(['block_id' => $this->id]);

        if (!is_null($this->locale))
            $content->andWhere(['locale' => $this->locale]);

        if ($asArray)
            return $content->asArray()->all();
        else
            return $content->all();
    }

    /**
     * Returns the contents of a content block with/without a language locale.
     *
     * @param null $id
     * @param null $locale
     * @param bool $asArray
     * @return array|\yii\db\ActiveRecord[]|null
     */
    public static function getBlockContent($id = null, $locale = null, $asArray = false) {

        if (!is_integer($id) && !is_string($id))
            return null;

        $query = Content::find()->alias('content')
            ->select(['fields.sort_order as field_order', 'fields.label', 'fields.name', 'content.content', 'content.locale', 'fields.type', 'fields.params'])
            ->leftJoin(['blocks' => Blocks::tableName()], '`blocks`.`id` = `content`.`block_id`')
            ->leftJoin(['fields' => Fields::tableName()], '`fields`.`id` = `content`.`field_id`');

        if (is_integer($id)) {
            $query->where([
                'blocks.id' => intval($id)
            ]);
        } elseif (is_string($id)) {
            $query->where([
                'blocks.alias' => trim($id)
            ]);
        }

        if (!is_null($locale)) {
            $query->andWhere([
                'fields.locale' => trim($locale),
                'content.locale' => trim($locale)
            ]);
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
     * Returns the contents of a content list with/without a language locale.
     *
     * @param null $id
     * @param null $locale
     * @param bool $asArray
     * @return array|\yii\db\ActiveRecord[]|null
     */
    public static function getListContent($id = null, $locale = null, $asArray = false) {

        if (!is_integer($id) && !is_string($id))
            return null;

        $query = Content::find()->alias('content')
            ->select(['fields.label', 'fields.name', 'content.locale', 'content.content', 'fields.type', 'fields.params', 'items.row_order', 'fields.sort_order as field_order'])
            ->leftJoin(['blocks' => Blocks::tableName()], '`blocks`.`id` = `content`.`block_id`')
            ->leftJoin(['fields' => Fields::tableName()], '`fields`.`id` = `content`.`field_id`')
            ->leftJoin(['items' => Items::tableName()], '`items`.`block_id` = `blocks`.`id` AND `items`.`ext_id` = `content`.`id`');

        if (is_integer($id)) {
            $query->where([
                'blocks.id' => intval($id)
            ]);
        } elseif (is_string($id)) {
            $query->where([
                'blocks.alias' => trim($id)
            ]);
        }

        if (!is_null($locale)) {
            $query->andWhere([
                'fields.locale' => trim($locale),
                'content.locale' => trim($locale)
            ]);
        }

        $query->andWhere([
            'blocks.type' => self::CONTENT_BLOCK_TYPE_LIST,
            'blocks.status' => self::CONTENT_BLOCK_STATUS_PUBLISHED
        ]);

        //$query->andWhere('`items`.`row_order` != 0');

        $query->orderBy(['items.row_order' => 'ASC', 'fields.sort_order' => 'ASC']);

        if ($asArray)
            return $query->asArray()->all();
        else
            return  $query->all();

    }

    /**
     * Returns a counter of fields for this/or selected block/list.
     *
     * @param null $block_id
     * @return int|string
     */
    public function getFieldsCount($block_id = null) {
        if (is_null($block_id))
            $block_id = $this->id;

        if (!is_null($this->locale))
            return Fields::find()->where(['block_id' => $block_id, 'locale' => $this->locale])->count();
        else
            return Fields::find()->where(['block_id' => $block_id])->count();
    }

    /**
     * Returns a counter of content for this/or selected block/list.
     *
     * @param null $block_id
     * @return int|string
     */
    public function getContentCount($block_id = null) {
        if (is_null($block_id))
            $block_id = $this->id;

        return Content::find()->where(['block_id' => $block_id])->count();
    }

    /**
     * Returns an array of block/content list publishing statuses.
     *
     * @param bool $allStatuses
     * @return array
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
     * Finds the model based on its primary key value.
     * If the model is not found, null will be returned.
     *
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