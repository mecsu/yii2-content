<?php

namespace mecsu\content\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\data\ArrayDataProvider;
use mecsu\content\models\Blocks;
use mecsu\content\models\BlocksSearch;

/**
 * ListsController implements the CRUD actions.
 */
class ListsController extends Controller
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

    /**
     * Lists of all Content lists.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BlocksSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, ['type' => Blocks::CONTENT_BLOCK_TYPE_LIST]);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'module' => $this->module
        ]);
    }

    public function actionView($id, $locale = null)
    {
        $this->_locale = null;
        $model = self::findModel($id);

        if (is_null($locale))
            $locale = $model->locale;

        $rows = $model->getListContent($model->id, $locale, true);
        $data = ArrayHelper::map($rows, 'label', 'content', 'row_order');
        $data = array_values($data);
        $dataProvider = new ArrayDataProvider([
            'allModels' => $data,
            'sort' => [
                'attributes' => array_keys($data)
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->renderAjax('_view', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'module' => $this->module,
            'locale' => $locale
        ]);
    }

    public function actionCreate()
    {
        $model = new Blocks();
        $model->type = $model::CONTENT_BLOCK_TYPE_LIST;

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

        $source = null;
        if (!is_null($this->_source_id)) {
            $model->source_id = $this->_source_id;
            if ($source = $model::findOne(['id' => $this->_source_id])) {
                if ($source->id) {
                    $model->source_id = $source->id;
                    $model->alias = $source->alias;
                }
            }
        }

        if (Yii::$app->request->isAjax) {
            if ($model->load(Yii::$app->request->post())) {
                if ($model->validate())
                    $success = true;
                else
                    $success = false;

                return $this->asJson(['success' => $success, 'alias' => $model->alias, 'errors' => $model->errors]);
            }
        } else {
            if ($model->load(Yii::$app->request->post())) {

                if ($model->save()) {
                    // Log activity
                    $this->module->logActivity(
                        'New content list `' . $model->title . '` with ID `' . $model->id . '` has been successfully added.',
                        $this->uniqueId . ":" . $this->action->id,
                        'success',
                        1
                    );

                    Yii::$app->getSession()->setFlash(
                        'success',
                        Yii::t('app/modules/content', 'Content list has been successfully added!')
                    );
                    return $this->redirect(['lists/index']);
                } else {
                    // Log activity
                    $this->module->logActivity(
                        'An error occurred while add the content list: ' . $model->title,
                        $this->uniqueId . ":" . $this->action->id,
                        'danger',
                        1
                    );

                    Yii::$app->getSession()->setFlash(
                        'danger',
                        Yii::t('app/modules/content', 'An error occurred while add the content list.')
                    );
                }
            }
        }

        return $this->render('create', [
            'module' => $this->module,
            'source' => $source,
            'model' => $model
        ]);

    }

    public function actionUpdate($id)
    {
        $model = self::findModel($id);
        if (!is_null($model->source_id) && is_null($model->alias)) {
            if ($source = $model::findOne(['id' => $model->source_id])) {
                $model->alias = $source->alias;
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

                return $this->asJson(['success' => $success, 'alias' => $model->alias, 'errors' => $model->errors]);
            }
        } else {
            if ($model->load(Yii::$app->request->post())) {
                if ($model->save()) {
                    // Log activity
                    $this->module->logActivity(
                        'Content list `' . $model->title . '` with ID `' . $model->id . '` has been successfully updated.',
                        $this->uniqueId . ":" . $this->action->id,
                        'success',
                        1
                    );

                    Yii::$app->getSession()->setFlash(
                        'success',
                        Yii::t(
                            'app/modules/content',
                            'OK! Content list `{title}` successfully updated.',
                            [
                                'title' => $model->title
                            ]
                        )
                    );
                    return $this->redirect(['lists/index']);
                } else {
                    // Log activity
                    $this->module->logActivity(
                        'An error occurred while update the content list `' . $model->title . '` with ID `' . $model->id . '`.',
                        $this->uniqueId . ":" . $this->action->id,
                        'danger',
                        1
                    );

                    Yii::$app->getSession()->setFlash(
                        'danger',
                        Yii::t(
                            'app/modules/content',
                            'An error occurred while update a content list `{title}`.',
                            [
                                'title' => $model->title
                            ]
                        )
                    );
                }
            }
        }

        return $this->render('update', [
            'module' => $this->module,
            'model' => $model
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->delete()) {
            // Log activity
            $this->module->logActivity(
                'Content list `' . $model->title . '` with ID `' . $model->id . '` has been successfully deleted.',
                $this->uniqueId . ":" . $this->action->id,
                'success',
                1
            );

            Yii::$app->getSession()->setFlash(
                'success',
                Yii::t(
                    'app/modules/content',
                    'OK! Content list `{title}` successfully deleted.',
                    [
                        'title' => $model->title
                    ]
                )
            );
        } else {
            // Log activity
            $this->module->logActivity(
                'An error occurred while deleting the content list `' . $model->title . '` with ID `' . $model->id . '`.',
                $this->uniqueId . ":" . $this->action->id,
                'danger',
                1
            );

            Yii::$app->getSession()->setFlash(
                'danger',
                Yii::t(
                    'app/modules/content',
                    'An error occurred while deleting a content list `{title}`.',
                    [
                        'title' => $model->title
                    ]
                )
            );
        }
        return $this->redirect(['lists/index']);
    }

    /**
     * Finds the model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ActiveRecord model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {

        if (is_null($this->_locale) && ($model = Blocks::findOne(['id' => $id, 'type' => Blocks::CONTENT_BLOCK_TYPE_LIST])) !== null) {
            return $model;
        } else {
            if (($model = Blocks::findOne(['source_id' => $id, 'locale' => $this->_locale, 'type' => Blocks::CONTENT_BLOCK_TYPE_LIST])) !== null)
                return $model;
        }

        throw new NotFoundHttpException(Yii::t('app/modules/content', 'The requested list does not exist.'));
    }
}