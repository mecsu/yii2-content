<?php

use wdmg\widgets\LangSwitcher;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use wdmg\widgets\SelectInput;

/* @var $this yii\web\View */
/* @var $model mecsu\content\models\Blocks */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="content-fields-form">
    <?php
        echo LangSwitcher::widget([
            'label' => Yii::t('app/modules/content', 'Language version'),
            'model' => $model,
            'renderWidget' => 'button-group',
            'createRoute' => ['fields/create', 'block_id' => $model->block_id],
            'updateRoute' => ['fields/update', 'block_id' => $model->block_id],
            'supportLocales' => $this->context->module->supportLocales,
            'versions' => (isset($model->source_id)) ? $model->getAllVersions($model->source_id, true) : $model->getAllVersions($model->id, true),
            'options' => [
                'id' => 'locale-switcher',
                'class' => 'pull-right'
            ]
        ]);
    ?>
    <?php $form = ActiveForm::begin([
        'id' => "fieldForm",
        'enableAjaxValidation' => true
    ]); ?>
    <?= $form->field($model, 'label')->textInput(['lang' => ($model->locale ?? Yii::$app->language)]); ?>
    <?= $form->field($model, 'name')->textInput([
            'disabled' => ($model->source_id) ? true : false,
            'maxlength' => true
    ]) ?>
    <?= $form->field($model, 'type')->widget(SelectInput::class, [
        'items' => $model->getTypesList(false),
        'options' => [
            'disabled' => ($model->source_id) ? true : false,
            'class' => 'form-control'
        ]
    ]); ?>
    <hr/>
    <div class="form-group">
        <?= Html::a(Yii::t('app/modules/content', '&larr; Back to list'), ['fields/index', 'block_id' => $block->id], ['class' => 'btn btn-default pull-left']) ?>
        <?php if ((Yii::$app->authManager && $this->context->module->moduleExist('rbac') && Yii::$app->user->can('updatePosts', [
                    'created_by' => $model->created_by,
                    'updated_by' => $model->updated_by
                ])) || !$model->id) : ?>
            <?= Html::submitButton(Yii::t('app/modules/content', 'Save'), ['class' => 'btn btn-save btn-success pull-right']) ?>
        <?php endif; ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<?php $this->registerJs(<<< JS
$(document).ready(function() {
    function afterValidateAttribute(event, attribute, messages)
    {
        if (attribute.name && !attribute.alias && messages.length == 0) {
            var form = $(event.target);
            $.ajax({
                    type: form.attr('method'),
                    url: form.attr('action'),
                    data: form.serializeArray(),
                }
            ).done(function(data) {
                if (data.name && form.find('#fields-name').val().length == 0) {
                    form.find('#fields-name').val(data.name);
                    form.yiiActiveForm('validateAttribute', 'fields-name');
                }
            });
            return false;
        }
    }
    $("#fieldForm").on("afterValidateAttribute", afterValidateAttribute);
});
JS
); ?>