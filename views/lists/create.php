<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $module mecsu\content\Module */
/* @var $model mecsu\content\models\Blocks */

if ($source) {
    $this->title = Yii::t('app/modules/content', 'Version of list: {title}', [
        'title' => $source->title,
    ]);
} else {
    $this->title = Yii::t('app/modules/content', 'Create a content list');
}


$this->params['breadcrumbs'][] = ['label' => Yii::t('app/modules/content', 'Content lists'), 'url' => ['lists/index']];

if ($source)
    $this->params['breadcrumbs'][] = $source->title;
else
    $this->params['breadcrumbs'][] = $this->title;

?>
<div class="page-header">
    <h1><?= Html::encode($this->title) ?> <small class="text-muted pull-right">[v.<?= $module->version ?>]</small></h1>
</div>
<div class="content-lists-create">
    <?= $this->render('_form', [
        'model' => $model
    ]); ?>
</div>