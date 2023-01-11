<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $module mecsu\content\Module */
/* @var $model mecsu\content\models\Blocks */

if ($source)
    $this->title = Yii::t('app/modules/content', 'Version of field: {label}', [
        'label' => $source->label,
    ]);
else
    $this->title = Yii::t('app/modules/content', 'Create a field');

if ($block::CONTENT_BLOCK_TYPE_LIST == $block->type)
    $this->params['breadcrumbs'][] = ['label' => Yii::t('app/modules/content', 'Content lists'), 'url' => ['lists/index']];
else
    $this->params['breadcrumbs'][] = ['label' => Yii::t('app/modules/content', 'Content blocks'), 'url' => ['blocks/index']];

$this->params['breadcrumbs'][] = ['label' => $block->title, 'url' => ['fields/index', 'block_id' => $block->id]];

if ($source)
    $this->params['breadcrumbs'][] = $source->label;
else
    $this->params['breadcrumbs'][] = $this->title;

?>
<div class="page-header">
    <h1><?= Html::encode($this->title) ?> <small class="text-muted pull-right">[v.<?= $module->version ?>]</small></h1>
</div>
<div class="content-fields-create">
    <?= $this->render('_form', [
        'model' => $model,
        'source' => $source,
        'block' => $block
    ]); ?>
</div>