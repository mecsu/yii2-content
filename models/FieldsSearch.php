<?php

namespace mecsu\content\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use mecsu\content\models\Fields;

/**
 * FieldsSearch represents the model behind the search form of `app\vendor\mecsu\content\models\Fields`.
 */
class FieldsSearch extends Fields
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'block_id', 'type'], 'integer'],
            [['label', 'name', 'locale', 'created_at', 'updated_at'], 'safe'],
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
     *
     * @return ActiveDataProvider
     */
    public function search($params, $block_id = null)
    {
        $query = Fields::find();

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
            'block_id' => (!is_null($block_id)) ? $block_id : $this->block_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'label', $this->label]);

        if ($this->type !== "*")
            $query->andFilterWhere(['like', 'type', $this->type]);

        if ($this->locale !== "*")
            $query->andFilterWhere(['like', 'locale', $this->locale]);

        return $dataProvider;
    }
}
