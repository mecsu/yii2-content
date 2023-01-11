<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $module mecsu\content\Module */
/* @var $model mecsu\content\models\Blocks */

if ($block::CONTENT_BLOCK_TYPE_LIST == $block->type)
    $this->title = Yii::t('app/modules/content', 'Edit row: {title}', [
        'title' => $block->title
    ]);
else
    $this->title = Yii::t('app/modules/content', 'Editor: {title}', [
        'title' => $block->title
    ]);

if ($block::CONTENT_BLOCK_TYPE_LIST == $block->type)
    $this->params['breadcrumbs'][] = ['label' => Yii::t('app/modules/content', 'Content lists'), 'url' => ['lists/index']];
else
    $this->params['breadcrumbs'][] = ['label' => Yii::t('app/modules/content', 'Content blocks'), 'url' => ['blocks/index']];

$this->params['breadcrumbs'][] = ['label' => Yii::t('app/modules/content', $block->title), 'url' => ['content/index', 'block_id' => $block->id]];

if ($block::CONTENT_BLOCK_TYPE_LIST == $block->type)
    $this->params['breadcrumbs'][] = Yii::t('app/modules/content', 'Edit row');
else
    $this->params['breadcrumbs'][] = Yii::t('app/modules/content', 'Content editor');




?>
<div class="page-header">
    <h1><?= Html::encode($this->title) ?> <small class="text-muted pull-right">[v.<?= $module->version ?>]</small></h1>
</div>
<div class="content-content-update">
    <?= $this->render('_form', [
        'model' => $model,
        'content' => $content,
        'block' => $block
    ]); ?>
</div>