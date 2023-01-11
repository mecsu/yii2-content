<?php

namespace mecsu\content\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\data\ActiveDataProvider;
use mecsu\content\models\Fields;
use mecsu\content\models\Blocks;
use mecsu\content\models\FieldsSearch;

/**
 * FieldsController implements the CRUD actions.
 */
class FieldsController extends Controller
{

    /**
     * @var string|null Selected language (locale)
     */
    private $_locale;

    /**
     * @var string|null Selected id of source
     */
    private $_source_id;

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'roles' => ['admin'],
                        'allow' => true
                    ],
                ],
            ],
        ];

        // If auth manager not configured use default access control
        if (!Yii::$app->authManager) {
            $behaviors['access'] = [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'roles' => ['@'],
                        'allow' => true
                    ],
                ]
            ];
        } else if ($this->module->moduleExist('admin/rbac')) { // Ok, then we check access according to the rules
            $behaviors['access'] = [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['update', 'create', 'delete'],
                        'roles' => ['updatePosts'],
                        'allow' => true
                    ], [
                        'roles' => ['viewDashboard'],
                        'allow' => true
                    ],
                ],
            ];
        }

        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        $this->_locale = Yii::$app->request->get('locale', null);
        $this->_source_id = Yii::$app->request->get('source_id', null);
        return parent::beforeAction($action);
    }

    public function actionIndex($block_id)
    {
        $block = Blocks::findModel(intval($block_id));
        $searchModel = new FieldsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $block_id);

        return $this->render('index', [
            'model' => $searchModel,
            'block' => $block,
            'dataProvider' => $dataProvider,
            'module' => $this->module
        ]);
    }

    public function actionCreate($block_id = null)
    {
        $model = new Fields();

        $source = null;
        if (!is_null($this->_source_id) && $source = Fields::findOne(['id' => $this->_source_id])) {
            $model->source_id = $source->id;
            $model->name = $source->name;
            $model->type = $source->type;

            if (is_null($block_id)) {
                $block_id = $source->block_id;
            }
        }

        $block = Blocks::findModel(intval($block_id));
        $model->block_id = $block_id;

        // No language is set for this model, we will use the current user language
        if (is_null($model->locale)) {
            if (is_null($this->_locale)) {

                $model->locale = Yii::$app->sourceLanguage;
                if (!Yii::$app->request->isPost) {

                    $languages = $model->getLanguagesList(false);
                    Yii::$app->getSession()->setFlash(
                        'danger',
                        Yii::t(
                            'app/modules/content',
                            'No display language has been set. Source language will be selected: {language}',
                            [
                                'language' => (isset($languages[Yii::$app->sourceLanguage])) ? $languages[Yii::$app->sourceLanguage] : Yii::$app->sourceLanguage
                            ]
                        )
                    );
                }
            } else {
                $model->locale = $this->_locale;
            }
        }

        if (Yii::$app->request->isAjax) {
            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate())
                    $success = true;
                else
                    $success = false;

                return $this->asJson(['success' => $success, 'name' => $model->name, 'errors' => $model->errors]);
            }
        } else {

            if ($model->load(Yii::$app->request->post()) && $model->validate()) {

                $sort_order = Fields::find()->where(['block_id' => $block->id])->max('sort_order');
                $model->sort_order = intval($sort_order) + 10;

                if ($model->save()) {
                    // Log activity
                    $this->module->logActivity(
                        'New content field `' . $model->label . '` with ID `' . $model->id . '` has been successfully added.',
                        $this->uniqueId . ":" . $this->action->id,
                        'success',
                        1
                    );

                    Yii::$app->getSession()->setFlash(
                        'success',
                        Yii::t('app/modules/content', 'Field has been successfully added!')
                    );
                    return $this->redirect(['fields/index', 'block_id' => $block->id]);
                } else {
                    // Log activity
                    $this->module->logActivity(
                        'An error occurred while add the content field: ' . $model->label,
                        $this->uniqueId . ":" . $this->action->id,
                        'danger',
                        1
                    );

                    Yii::$app->getSession()->setFlash(
                        'danger',
                        Yii::t('app/modules/content', 'An error occurred while add the field.')
                    );
                }
            }
        }

        return $this->render('create', [
            'module' => $this->module,
            'model' => $model,
            'source' => $source,
            'block' => $block
        ]);
    }

    public function actionUpdate($id, $block_id = null)
    {
        $block = Blocks::findModel(intval($block_id));

        if (is_null($block_id) && !is_null($this->_source_id))
            $block_id = $this->_source_id;

        $model = self::findModel(intval($id), intval($block_id));
        if (!is_null($model->source_id) && is_null($model->name)) {
            if ($source = $model::findOne(['id' => $model->source_id])) {
                $model->name = $source->name;
            }
        }

        // No language is set for this model, we will use the current user language
        if (is_null($model->locale)) {

            $model->locale = Yii::$app->sourceLanguage;
            if (!Yii::$app->request->isPost) {

                $languages = $model->getLanguagesList(false);
                Yii::$app->getSession()->setFlash(
                    'danger',
                    Yii::t(
                        'app/modules/content',
                        'No display language has been set. Source language will be selected: {language}',
                        [
                            'language' => (isset($languages[Yii::$app->sourceLanguage])) ? $languages[Yii::$app->sourceLanguage] : Yii::$app->sourceLanguage
                        ]
                    )
                );
            }
        }

        if (Yii::$app->request->isAjax) {
            if ($model->load(Yii::$app->request->post())) {
                if ($model->validate())
                    $success = true;
                else
                    $success = false;

                return $this->asJson(['success' => $success, 'name' => $model->name, 'errors' => $model->errors]);
            }
        } else {
            if ($model->load(Yii::$app->request->post())) {
                if ($model->save()) {
                    // Log activity
                    $this->module->logActivity(
                        'Field of content `' . $model->label . '` with ID `' . $model->id . '` has been successfully updated.',
                        $this->uniqueId . ":" . $this->action->id,
                        'success',
                        1
                    );

                    Yii::$app->getSession()->setFlash(
                        'success',
                        Yii::t(
                            'app/modules/content',
                            'OK! Field `{label}` successfully updated.',
                            [
                                'label' => $model->label
                            ]
                        )
                    );
                    return $this->redirect(['fields/index', 'block_id' => $block_id]);
                } else {
                    // Log activity
                    $this->module->logActivity(
                        'An error occurred while update the content field `' . $model->label . '` with ID `' . $model->id . '`.',
                        $this->uniqueId . ":" . $this->action->id,
                        'danger',
                        1
                    );

                    Yii::$app->getSession()->setFlash(
                        'danger',
                        Yii::t(
                            'app/modules/content',
                            'An error occurred while update a field `{label}`.',
                            [
                                'label' => $model->label
                            ]
                        )
                    );
                }
            }
        }

        return $this->render('update', [
            'module' => $this->module,
            'model' => $model,
            'block' => $block
        ]);
    }

    public function actionDelete($id, $block_id)
    {
        $model = $this->findModel(intval($id), intval($block_id));
        if ($model->delete()) {
            // Log activity
            $this->module->logActivity(
                'Field of content `' . $model->label . '` with ID `' . $model->id . '` has been successfully deleted.',
                $this->uniqueId . ":" . $this->action->id,
                'success',
                1
            );

            Yii::$app->getSession()->setFlash(
                'success',
                Yii::t(
                    'app/modules/content',
                    'OK! Field `{label}` successfully deleted.',
                    [
                        'label' => $model->label
                    ]
                )
            );
        } else {
            // Log activity
            $this->module->logActivity(
                'An error occurred while deleting the content field `' . $model->title . '` with ID `' . $model->id . '`.',
                $this->uniqueId . ":" . $this->action->id,
                'danger',
                1
            );

            Yii::$app->getSession()->setFlash(
                'danger',
                Yii::t(
                    'app/modules/content',
                    'An error occurred while deleting a field `{label}`.',
                    [
                        'label' => $model->label
                    ]
                )
            );
        }
        return $this->redirect(['fields/index', 'block_id' => intval($block_id)]);
    }

    /**
     * Finds the model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @param integer $block_id
     * @return ActiveRecord model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id, $block_id = null)
    {

        if (!is_null($block_id)) {
            if (is_null($this->_locale) && ($model = Fields::findOne(['id' => $id, 'block_id' => $block_id])) !== null) {
                return $model;
            } else {
                if (($model = Fields::findOne(['source_id' => $id, 'block_id' => $block_id, 'locale' => $this->_locale])) !== null)
                    return $model;
            }
        } else {
            if (is_null($this->_locale) && ($model = Fields::findOne(['id' => $id])) !== null) {
                return $model;
            } else {
                if (($model = Fields::findOne(['source_id' => $id, 'locale' => $this->_locale])) !== null)
                    return $model;
            }
        }

        throw new NotFoundHttpException(Yii::t('app/modules/content', 'The requested field does not exist.'));
    }
}