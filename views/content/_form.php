<?php

use wdmg\widgets\LangSwitcher;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model mecsu\content\models\Blocks */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="content-content-form">
    <?php
        echo LangSwitcher::widget([
            'label' => Yii::t('app/modules/content', 'Language version'),
            'model' => $content,
            'renderWidget' => 'button-group',
            'createRoute' => ['content/update', 'block_id' => $content->block_id],
            'updateRoute' => ['content/update', 'block_id' => $content->block_id],
            'supportLocales' => $this->context->module->supportLocales,
            'versions' => $content->getAllVersions(),
            'options' => [
                'id' => 'locale-switcher',
                'class' => 'pull-right'
            ]
        ]);
    ?>
    <?php $form = ActiveForm::begin([
        'id' => "addContentForm"
    ]); ?>
    <?php
        foreach ($model->getAttributes() as $attribute => $value) {
            $label = $model->getAttributeLabel($attribute);
            echo $form->field($model, $attribute, [
                'inputOptions' => [
                    'class' => 'form-control',
                    'lang' => ($content->locale ?? Yii::$app->language)
                ]
            ])->label($label . ' <span class="text-muted">[' .$attribute. ']</span>');
        }
    ?>
    <hr/>
    <div>
        <?= Html::a(Yii::t('app/modules/content', '&larr; Back to list'), ['content/index', 'block_id' => $block->id], ['class' => 'btn btn-default pull-left']) ?>
        <?php if true || (Yii::$app->authManager && $this->context->module->moduleExist('rbac') && Yii::$app->user->can('updatePosts', [
                    'created_by' => $block->created_by,
                    'updated_by' => $block->updated_by
                ])) || !$block->id) : ?>
            <?= Html::submitButton(Yii::t('app/modules/content', 'Save'), ['class' => 'btn btn-save btn-success pull-right']) ?>
        <?php endif; ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>