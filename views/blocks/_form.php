<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use wdmg\widgets\SelectInput;

/* @var $this yii\web\View */
/* @var $model wdmg\content\models\Blocks */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="content-blocks-form">
    <?php $form = ActiveForm::begin([
        'id' => "addBlockForm",
        'enableAjaxValidation' => true
    ]); ?>
    <?= $form->field($model, 'title'); ?>
    <?= $form->field($model, 'alias')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'description')->textarea(['rows' => 2]) ?>
    <?= $form->field($model, 'status')->widget(SelectInput::class, [
        'items' => $model->getStatusesList(false),
        'options' => [
            'class' => 'form-control'
        ]
    ]); ?>
    <hr/>
    <div class="form-group">
        <?= Html::a(Yii::t('app/modules/content', '&larr; Back to list'), ['blocks/index'], ['class' => 'btn btn-default pull-left']) ?>&nbsp;
        <?= Html::submitButton(Yii::t('app/modules/content', 'Save'), ['class' => 'btn btn-success pull-right']) ?>
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
    $("#addBlockForm").on("afterValidateAttribute", afterValidateAttribute);
});
JS
); ?>