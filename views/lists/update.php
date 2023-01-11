<?php
use yii\helpers\Html;
/* @var $this yii\web\View */
/* @var $module mecsu\content\Module */
/* @var $model mecsu\content\models\Blocks */

$this->title = Yii::t('app/modules/content', 'Updating list: {title}', [
    'title' => $model->title,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/modules/content', 'Content lists'), 'url' => ['lists/index']];
$this->params['breadcrumbs'][] = Yii::t('app/modules/content', 'Edit list');

?>
<?php if (Yii::$app->authManager && $this->context->module->moduleExist('rbac') && Yii::$app->user->can('updatePosts', [
        'created_by' => $model->created_by,
        'updated_by' => $model->updated_by
    ])) : ?>
    <div class="page-header">
        <h1><?= Html::encode($this->title) ?> <small class="text-muted pull-right">[v.<?= $module->version ?>]</small></h1>
    </div>
    <div class="content-lists-update">
        <?= $this->render('_form', [
            'model' => $model
        ]) ?>
    </div>
<?php else: ?>
    <div class="page-header">
        <h1 class="text-danger"><?= Yii::t('app/modules/content', 'Error {code}. Access Denied', [
                'code' => 403
            ]) ?> <small class="text-muted pull-right">[v.<?= $this->context->module->version ?>]</small></h1>
    </div>
    <div class="content-lists-update-error">
        <blockquote>
            <?= Yii::t('app/modules/content', 'You are not allowed to view this page.'); ?>
        </blockquote>
    </div>
<?php endif; ?>