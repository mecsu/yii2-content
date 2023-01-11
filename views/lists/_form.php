<?php

use wdmg\widgets\LangSwitcher;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use wdmg\widgets\SelectInput;

/* @var $this yii\web\View */
/* @var $model mecsu\content\models\Blocks */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="content-lists-form">
    <?php
        echo LangSwitcher::widget([
            'label' => Yii::t('app/modules/content', 'Language version'),
            'model' => $model,
            'renderWidget' => 'button-group',
            'createRoute' => 'lists/create',
            'updateRoute' => 'lists/update',
            'supportLocales' => $this->context->module->supportLocales,
            'versions' => (isset($model->source_id)) ? $model->getAllVersions($model->source_id, true) : $model->getAllVersions($model->id, true),
            'options' => [
                'id' => 'locale-switcher',
                'class' => 'pull-right'
            ]
        ]);
    ?>
    <?php $form = ActiveForm::begin([
        'id' => "addListForm",
        'enableAjaxValidation' => true
    ]); ?>
    <?= $form->field($model, 'title')->textInput(['lang' => ($model->locale ?? Yii::$app->language)]); ?>
    <?= $form->field($model, 'alias')->textInput([
        'disabled' => ($model->source_id) ? true : false,
        'maxlength' => true
    ]) ?>
    <?= $form->field($model, 'description')->textarea(['rows' => 2, 'lang' => ($model->locale ?? Yii::$app->language)]) ?>
    <?= $form->field($model, 'status')->widget(SelectInput::class, [
        'items' => $model->getStatusesList(false),
        'options' => [
            'class' => 'form-control'
        ]
    ]); ?>
    <hr/>
    <div class="form-group">
        <?= Html::a(Yii::t('app/modules/content', '&larr; Back to list'), ['lists/index'], ['class' => 'btn btn-default pull-left']) ?>&nbsp;
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
                if (data.alias && form.find('#blocks-alias').val().length == 0) {
                    form.find('#blocks-alias').val(data.alias);
                    form.yiiActiveForm('validateAttribute', 'blocks-alias');
                }
            });
            return false;
        }
    }
    $("#addListForm").on("afterValidateAttribute", afterValidateAttribute);
});
JS
); ?>