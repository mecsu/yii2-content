<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $module mecsu\content\Module */
/* @var $model mecsu\content\models\Blocks */

if ($source) {
    $this->title = Yii::t('app/modules/content', 'Version of block: {title}', [
        'title' => $source->title,
    ]);
} else {
    $this->title = Yii::t('app/modules/content', 'Create a content block');
}

$this->params['breadcrumbs'][] = ['label' => Yii::t('app/modules/content', 'Content blocks'), 'url' => ['blocks/index']];

if ($source)
    $this->params['breadcrumbs'][] = $source->title;
else
    $this->params['breadcrumbs'][] = $this->title;

?>
<div class="page-header">
    <h1><?= Html::encode($this->title) ?> <small class="text-muted pull-right">[v.<?= $module->version ?>]</small></h1>
</div>
<div class="content-blocks-create">
    <?= $this->render('_form', [
        'model' => $model,
        'source' => $source
    ]); ?>
</div>