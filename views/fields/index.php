<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use wdmg\widgets\SelectInput;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $searchModel mecsu\content\models\BlocksSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

if ($block::CONTENT_BLOCK_TYPE_LIST == $block->type) {
    $this->title = Yii::t('app/modules/content', 'List fields: {title}', [
        'title' => $block->title
    ]);
    $this->params['breadcrumbs'][] = ['label' => Yii::t('app/modules/content', 'Content lists'), 'url' => ['lists/index']];
    $this->params['breadcrumbs'][] = ['label' => $block->title, 'url' => ['lists/update', 'id' => $block->id]];
} else {
    $this->title = Yii::t('app/modules/content', 'Block fields: {title}', [
        'title' => $block->title
    ]);
    $this->params['breadcrumbs'][] = ['label' => Yii::t('app/modules/content', 'Content blocks'), 'url' => ['blocks/index']];
    $this->params['breadcrumbs'][] = ['label' => $block->title, 'url' => ['blocks/update', 'id' => $block->id]];
}

$this->params['breadcrumbs'][] = Yii::t('app/modules/content', 'Fields list');

if (isset(Yii::$app->translations) && class_exists('\wdmg\translations\FlagsAsset')) {
    $bundle = \wdmg\translations\FlagsAsset::register(Yii::$app->view);
} else {
    $bundle = false;
}

?>
<div class="page-header">
    <h1>
        <?= Html::encode($this->title) ?> <small class="text-muted pull-right">[v.<?= $module->version ?>]</small>
    </h1>
</div>
<div class="content-fields-index">
    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $model,
        'layout' => '{summary}<br\/>{items}<br\/>{summary}<br\/><div class="text-center">{pager}</div>',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'label',
            'name',
            [
                'attribute' => 'type',
                'format' => 'html',
                'filter' => SelectInput::widget([
                    'model' => $model,
                    'attribute' => 'type',
                    'items' => $model->getTypesList(true),
                    'options' => [
                        'class' => 'form-control'
                    ]
                ]),
                'headerOptions' => [
                    'class' => 'text-center'
                ],
                'contentOptions' => [
                    'class' => 'text-center'
                ],
                'value' => function($data) {
                    if ($type = $data->getType())
                        return $type;
                    else
                        return $data->type;
                }
            ],
            'sort_order',
            [
                'attribute' => 'locale',
                'label' => Yii::t('app/modules/content','Language versions'),
                'format' => 'raw',
                'filter' => false,
                'headerOptions' => [
                    'class' => 'text-center',
                    'style' => 'min-width:96px;'
                ],
                'contentOptions' => [
                    'class' => 'text-center'
                ],
                'value' => function($data) use ($bundle) {

                    $output = [];
                    $separator = ", ";
                    $versions = $data->getAllVersions($data->id, true);
                    $locales = ArrayHelper::map($versions, 'id', 'locale');

                    if (isset(Yii::$app->translations)) {
                        foreach ($locales as $item_locale) {

                            $locale = Yii::$app->translations->parseLocale($item_locale, Yii::$app->language);

                            if ($item_locale === $locale['locale']) { // Fixing default locale from PECL intl

                                if (!($country = $locale['domain']))
                                    $country = '_unknown';

                                $flag = \yii\helpers\Html::img($bundle->baseUrl . '/flags-iso/flat/24/' . $country . '.png', [
                                    'alt' => $locale['name']
                                ]);

                                if ($data->locale === $locale['locale']) // It`s source version
                                    $output[] = Html::a($flag,
                                        [
                                            'fields/update', 'id' => $data->id,
                                            'block_id' => $data->block_id
                                        ], [
                                            'title' => Yii::t('app/modules/content','Edit source version: {language}', [
                                                'language' => $locale['name']
                                            ])
                                        ]
                                    );
                                else  // Other localization versions
                                    $output[] = Html::a($flag,
                                        [
                                            'fields/update', 'id' => $data->id,
                                            'block_id' => $data->block_id,
                                            'locale' => $locale['locale']
                                        ], [
                                            'title' => Yii::t('app/modules/content','Edit language version: {language}', [
                                                'language' => $locale['name']
                                            ])
                                        ]
                                    );

                            }

                        }
                        $separator = "";
                    } else {
                        foreach ($locales as $locale) {
                            if (!empty($locale)) {

                                if (extension_loaded('intl'))
                                    $language = mb_convert_case(trim(\Locale::getDisplayLanguage($locale, Yii::$app->language)), MB_CASE_TITLE, "UTF-8");
                                else
                                    $language = $locale;

                                if ($data->locale === $locale) // It`s source version
                                    $output[] = Html::a($language,
                                        [
                                            'fields/update', 'id' => $data->id,
                                            'block_id' => $data->block_id
                                        ], [
                                            'title' => Yii::t('app/modules/content','Edit source version: {language}', [
                                                'language' => $language
                                            ])
                                        ]
                                    );
                                else  // Other localization versions
                                    $output[] = Html::a($language,
                                        [
                                            'fields/update', 'id' => $data->id,
                                            'block_id' => $data->block_id,
                                            'locale' => $locale
                                        ], [
                                            'title' => Yii::t('app/modules/content','Edit language version: {language}', [
                                                'language' => $language
                                            ])
                                        ]
                                    );
                            }
                        }
                    }


                    if (is_countable($output)) {
                        if (count($output) > 0) {
                            $onMore = false;
                            if (count($output) > 3)
                                $onMore = true;

                            if ($onMore)
                                return join(array_slice($output, 0, 3), $separator) . "&nbsp;…";
                            else
                                return join($separator, $output);

                        }
                    }

                    return null;
                }
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => Yii::t('app/modules/content','Actions'),
                'headerOptions' => [
                    'class' => 'text-center'
                ],
                'contentOptions' => [
                    'class' => 'text-center'
                ],
                'visibleButtons' => [
                    'view' => false
                ],
                'buttons'=> [
                    'update' => function($url, $data, $key) use ($block) {

                        if (Yii::$app->authManager && $this->context->module->moduleExist('rbac') && !Yii::$app->user->can('updatePosts', [
                                'created_by' => $data->created_by,
                                'updated_by' => $data->updated_by
                            ])) {
                            return false;
                        }

                        $output = [];
                        $versions = $data->getAllVersions($data->id, true);
                        $locales = ArrayHelper::map($versions, 'id', 'locale');
                        if (isset(Yii::$app->translations)) {
                            foreach ($locales as $item_locale) {
                                $locale = Yii::$app->translations->parseLocale($item_locale, Yii::$app->language);
                                if ($item_locale === $locale['locale']) { // Fixing default locale from PECL intl

                                    if ($data->locale === $locale['locale']) // It`s source version
                                        $output[] = Html::a(Yii::t('app/modules/content','Edit source version: {language}', [
                                            'language' => $locale['name']
                                        ]), ['fields/update', 'id' => $data->id, 'block_id' => $block->id]);
                                    else  // Other localization versions
                                        $output[] = Html::a(Yii::t('app/modules/content','Edit language version: {language}', [
                                            'language' => $locale['name']
                                        ]), ['fields/update', 'id' => $data->id, 'block_id' => $block->id, 'locale' => $locale['locale']]);

                                }
                            }
                        } else {
                            foreach ($locales as $locale) {
                                if (!empty($locale)) {

                                    if (extension_loaded('intl'))
                                        $language = mb_convert_case(trim(\Locale::getDisplayLanguage($locale, Yii::$app->language)), MB_CASE_TITLE, "UTF-8");
                                    else
                                        $language = $locale;

                                    if ($data->locale === $locale) // It`s source version
                                        $output[] = Html::a(Yii::t('app/modules/content','Edit source version: {language}', [
                                            'language' => $language
                                        ]), ['fields/update', 'id' => $data->id, 'block_id' => $block->id]);
                                    else  // Other localization versions
                                        $output[] = Html::a(Yii::t('app/modules/content','Edit language version: {language}', [
                                            'language' => $language
                                        ]), ['fields/update', 'id' => $data->id, 'block_id' => $block->id, 'locale' => $locale]);

                                }
                            }
                        }

                        if (is_countable($output)) {
                            if (count($output) > 1) {
                                $html = '';
                                $html .= '<div class="btn-group">';
                                $html .= Html::a(
                                    '<span class="glyphicon glyphicon-pencil"></span> ' .
                                    Yii::t('app/modules/content', 'Edit') .
                                    ' <span class="caret"></span>',
                                    '#',
                                    [
                                        'class' => "btn btn-block btn-link btn-xs dropdown-toggle",
                                        'data-toggle' => "dropdown",
                                        'aria-haspopup' => "true",
                                        'aria-expanded' => "false"
                                    ]);
                                $html .= '<ul class="dropdown-menu dropdown-menu-right">';
                                $html .= '<li>' . implode("</li><li>", $output) . '</li>';
                                $html .= '</ul>';
                                $html .= '</div>';
                                return $html;
                            }
                        }
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span> ' .
                            Yii::t('app/modules/content', 'Edit'),
                            [
                                'fields/update',
                                'id' => $data->id,
                                'block_id' => $block->id
                            ], [
                                'class' => 'btn btn-link btn-xs'
                            ]
                        );
                    },
                    'delete' => function($url, $data, $key) use ($block) {

                        if (Yii::$app->authManager && $this->context->module->moduleExist('rbac') && !Yii::$app->user->can('updatePosts', [
                                'created_by' => $data->created_by,
                                'updated_by' => $data->updated_by
                            ])) {
                            return false;
                        }

                        $output = [];
                        $versions = $data->getAllVersions($data->id, true);
                        $locales = ArrayHelper::map($versions, 'id', 'locale');
                        if (isset(Yii::$app->translations)) {
                            foreach ($locales as $item_locale) {
                                $locale = Yii::$app->translations->parseLocale($item_locale, Yii::$app->language);
                                if ($item_locale === $locale['locale']) { // Fixing default locale from PECL intl

                                    if ($data->locale === $locale['locale']) // It`s source version
                                        $output[] = Html::a(Yii::t('app/modules/content','Delete source version: {language}', [
                                            'language' => $locale['name']
                                        ]), ['fields/delete', 'id' => $data->id, 'block_id' => $block->id], [
                                            'data-method' => 'POST',
                                            'data-confirm' => Yii::t('app/modules/content', 'Are you sure you want to delete this field?')
                                        ]);
                                    else  // Other localization versions
                                        $output[] = Html::a(Yii::t('app/modules/content','Delete language version: {language}', [
                                            'language' => $locale['name']
                                        ]), ['fields/delete', 'id' => $data->id, 'block_id' => $block->id, 'locale' => $locale['locale']], [
                                            'data-method' => 'POST',
                                            'data-confirm' => Yii::t('app/modules/content', 'Are you sure you want to delete the language version of this field?')
                                        ]);

                                }
                            }
                        } else {
                            foreach ($locales as $locale) {
                                if (!empty($locale)) {

                                    if (extension_loaded('intl'))
                                        $language = mb_convert_case(trim(\Locale::getDisplayLanguage($locale, Yii::$app->language)), MB_CASE_TITLE, "UTF-8");
                                    else
                                        $language = $locale;

                                    if ($data->locale === $locale) // It`s source version
                                        $output[] = Html::a(Yii::t('app/modules/content','Delete source version: {language}', [
                                            'language' => $language
                                        ]), ['fields/delete', 'id' => $data->id, 'block_id' => $block->id], [
                                            'data-method' => 'POST',
                                            'data-confirm' => Yii::t('app/modules/content', 'Are you sure you want to delete this field?')
                                        ]);
                                    else  // Other localization versions
                                        $output[] = Html::a(Yii::t('app/modules/content','Delete language version: {language}', [
                                            'language' => $language
                                        ]), ['fields/delete', 'id' => $data->id, 'block_id' => $block->id, 'locale' => $locale], [
                                            'data-method' => 'POST',
                                            'data-confirm' => Yii::t('app/modules/content', 'Are you sure you want to delete the language version of this field?')
                                        ]);

                                }
                            }
                        }

                        if (is_countable($output)) {
                            if (count($output) > 1) {
                                $html = '';
                                $html .= '<div class="btn-group">';
                                $html .= Html::a(
                                    '<span class="glyphicon glyphicon-trash"></span> ' .
                                    Yii::t('app/modules/content', 'Delete') .
                                    ' <span class="caret"></span>',
                                    '#',
                                    [
                                        'class' => "btn btn-block btn-link btn-xs dropdown-toggle",
                                        'data-toggle' => "dropdown",
                                        'aria-haspopup' => "true",
                                        'aria-expanded' => "false"
                                    ]);
                                $html .= '<ul class="dropdown-menu dropdown-menu-right">';
                                $html .= '<li>' . implode("</li><li>", $output) . '</li>';
                                $html .= '</ul>';
                                $html .= '</div>';
                                return $html;
                            }
                        }
                        return Html::a('<span class="glyphicon glyphicon-trash"></span> ' .
                            Yii::t('app/modules/content', 'Delete'),
                            [
                                'fields/delete',
                                'id' => $data->id,
                                'block_id' => $block->id
                            ], [
                                'class' => 'btn btn-link btn-xs',
                                'data-method' => 'POST',
                                'data-confirm' => Yii::t('app/modules/content', 'Are you sure you want to delete this field?')
                            ]
                        );
                    }
                ],
            ]
        ],
        'pager' => [
            'options' => [
                'class' => 'pagination',
            ],
            'maxButtonCount' => 5,
            'activePageCssClass' => 'active',
            'prevPageCssClass' => 'prev',
            'nextPageCssClass' => 'next',
            'firstPageCssClass' => 'first',
            'lastPageCssClass' => 'last',
            'firstPageLabel' => Yii::t('app/modules/content', 'First page'),
            'lastPageLabel'  => Yii::t('app/modules/content', 'Last page'),
            'prevPageLabel'  => Yii::t('app/modules/content', '&larr; Prev page'),
            'nextPageLabel'  => Yii::t('app/modules/content', 'Next page &rarr;')
        ],
    ]); ?>
    <hr/>
    <div>
        <?php
            if ($block::CONTENT_BLOCK_TYPE_LIST == $block->type)
                echo Html::a(Yii::t('app/modules/content', '&larr; Back to list'), ['fields/index'], ['class' => 'btn btn-default pull-left']);
            else
                echo Html::a(Yii::t('app/modules/content', '&larr; Back to list'), ['fields/index'], ['class' => 'btn btn-default pull-left']);
        ?>
        <?php if (true || Yii::$app->authManager && $this->context->module->moduleExist('rbac') && Yii::$app->user->can('updatePosts', [
            'created_by' => $block->created_by,
            'updated_by' => $block->updated_by
        ])) : ?>
            <?= Html::a(Yii::t('app/modules/content', 'Add new field'), ['fields/create', 'block_id' => $block->id], ['class' => 'btn btn-add btn-success pull-right']) ?>
        <?php endif; ?>
    </div>
    <?php Pjax::end(); ?>
</div>

<?php echo $this->render('../_debug'); ?>