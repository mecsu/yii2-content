<?php

namespace mecsu\content\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mecsu\content\models\Blocks;

/**
 * BlocksSearch represents the model behind the search form of `mecsu\content\models\Blocks`.
 */
class BlocksSearch extends Blocks
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param array $cond
     *
     * @return ActiveDataProvider
     */
    public function search($params, $cond)
    {
        if (!is_null($cond))
            $query = Blocks::find()->where($cond);
        else
            $query = Blocks::find();

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        } else {
            // query all without languages version
            $query->andWhere([
                'source_id' => null,
            ]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'title' => $this->title,
            'alias' => $this->alias,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by
        ]);

        return $dataProvider;
    }
}
