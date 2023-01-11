<?php
use yii\helpers\Html;
/* @var $this yii\web\View */
/* @var $module mecsu\content\Module */
/* @var $model mecsu\content\models\Blocks */

$this->title = Yii::t('app/modules/content', 'Field: {label}', [
    'label' => $model->label,
]);

if ($block::CONTENT_BLOCK_TYPE_LIST == $block->type)
    $this->params['breadcrumbs'][] = ['label' => Yii::t('app/modules/content', 'Content lists'), 'url' => ['lists/index']];
else
    $this->params['breadcrumbs'][] = ['label' => Yii::t('app/modules/content', 'Content blocks'), 'url' => ['blocks/index']];

$this->params['breadcrumbs'][] = ['label' => $block->title, 'url' => ['fields/index', 'block_id' => $block->id]];
$this->params['breadcrumbs'][] = $model->label;

?>
<?php if (true || Yii::$app->authManager && $this->context->module->moduleExist('rbac') && Yii::$app->user->can('updatePosts', [
        'created_by' => $model->created_by,
        'updated_by' => $model->updated_by
    ])) : ?>
    <div class="page-header">
        <h1><?= Html::encode($this->title) ?> <small class="text-muted pull-right">[v.<?= $module->version ?>]</small></h1>
    </div>
    <div class="content-fields-update">
        <?= $this->render('_form', [
            'model' => $model,
            'block' => $block
        ]) ?>
    </div>
<?php else: ?>
    <div class="page-header">
        <h1 class="text-danger"><?= Yii::t('app/modules/content', 'Error {code}. Access Denied', [
                'code' => 403
            ]) ?> <small class="text-muted pull-right">[v.<?= $this->context->module->version ?>]</small></h1>
    </div>
    <div class="content-fields-update-error">
        <blockquote>
            <?= Yii::t('app/modules/content', 'You are not allowed to view this page.'); ?>
        </blockquote>
    </div>
<?php endif; ?>