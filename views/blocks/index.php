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

$this->title = Yii::t('app/modules/content', 'Content blocks');
$this->params['breadcrumbs'][] = $this->title;

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
<div class="content-blocks-index">
    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => '{summary}<br\/>{items}<br\/>{summary}<br\/><div class="text-center">{pager}</div>',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'title',
                'format' => 'html',
                'value' => function($data) {
                    if ($data->title & $data->description) {
                        return Html::a('<b>' . $data->title . '</b>', Url::toRoute(['content/index', 'block_id' => $data->id])) . '<br/><span class="text-muted">' . mb_strimwidth(strip_tags($data->description), 0, 64, '…') . '</span>';
                    } elseif ($data->title) {
                        return Html::a('<b>' . $data->title . '</b>', Url::toRoute(['content/index', 'block_id' => $data->id]));
                    } else {
                        return null;
                    }
                }
            ],
            'alias',
            [
                'attribute' => 'fields',
                'format' => 'raw',
                'value' => function($data) {
                    $html = '';
                    if ($fields = $data->getFields($data->fields)) {
                        $list = [];
                        foreach ($fields as $field) {
                            $list[] = '<span class="label label-info">' . $field['label'] . '</span>';
                        }

                        $onMore = false;
                        if (count($list) > 5)
                            $onMore = true;

                        if ($onMore)
                            $html = join(" ", array_slice($list, 0, 5)) . "&nbsp;… ";
                        else
                            $html = join(" ", $list);

                    }

                    if (true || (Yii::$app->authManager && $this->context->module->moduleExist('rbac') && Yii::$app->user->can('updatePosts', [
                        'created_by' => $data->created_by,
                        'updated_by' => $data->updated_by
                    ]))) {
                        if ($data->getFieldsCount()) {
                            $html .= Html::a(
                                Yii::t('app/modules/content', 'Edit'),
                                Url::toRoute(['fields/index', 'block_id' => $data->id]),
                                [
                                    'class' => 'btn btn-link btn-edit btn-sm btn-block',
                                    'title' => Yii::t('app/modules/content', 'Edit fields'),
                                    'data-pjax' => '0'
                                ]
                            );
                        } else {
                            $html .= Html::a(
                                Yii::t('app/modules/content', 'Add field'),
                                Url::toRoute(['fields/create', 'block_id' => $data->id]),
                                [
                                    'class' => 'btn btn-link btn-add btn-sm btn-block',
                                    'title' => Yii::t('app/modules/content', 'Add field'),
                                    'data-pjax' => '0'
                                ]
                            );
                        }
                    }

                    return $html;
                }
            ],
            [
                'attribute' => 'content',
                'format' => 'raw',
                'value' => function($data) {
                    $html = '';

                    if ($data->getContentCount()) {
                        if (true || (Yii::$app->authManager && $this->context->module->moduleExist('rbac') && Yii::$app->user->can('updatePosts', [
                            'created_by' => $data->created_by,
                            'updated_by' => $data->updated_by
                        ]))) {
                            $html .= Html::a(
                                Yii::t('app/modules/content', 'Edit'),
                                Url::toRoute(['content/index', 'block_id' => $data->id]),
                                [
                                    'class' => 'btn btn-link btn-edit btn-sm btn-block',
                                    'title' => Yii::t('app/modules/content', 'Edit content'),
                                    'data-pjax' => '0'
                                ]
                            );
                        }
                        $html .= Html::a(
                            Yii::t('app/modules/content', 'Preview'),
                            Url::toRoute(['blocks/view', 'id' => $data->id]),
                            [
                                'class' => 'btn btn-link btn-view btn-sm btn-block',
                                'title' => Yii::t('app/modules/content', 'Content preview'),
                                'data-toggle' => 'modal',
                                'data-target' => '#contentPreviewModal',
                                'data-id' => $data->id,
                                'data-pjax' => '0'
                            ]
                        );
                    } else {
                        $html .= Html::a(
                            Yii::t('app/modules/content', 'Add content'),
                            ($data->getFieldsCount()) ?
                                Url::toRoute(['content/create', 'block_id' => $data->id]) :
                                '#',
                            [
                                'class' => 'btn btn-link btn-add btn-sm btn-block',
                                'title' => Yii::t('app/modules/content', 'Add content'),
                                'disabled' => ($data->getFieldsCount()) ? false : true,
                                'data-pjax' => '0'
                            ]
                        );
                    }

                    return $html;
                }
            ],
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
                                            'blocks/update', 'id' => $data->id
                                        ], [
                                            'title' => Yii::t('app/modules/content','Edit source version: {language}', [
                                                'language' => $locale['name']
                                            ])
                                        ]
                                    );
                                else  // Other localization versions
                                    $output[] = Html::a($flag,
                                        [
                                            'blocks/update', 'id' => $data->id,
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
                                            'blocks/update', 'id' => $data->id
                                        ], [
                                            'title' => Yii::t('app/modules/content','Edit source version: {language}', [
                                                'language' => $language
                                            ])
                                        ]
                                    );
                                else  // Other localization versions
                                    $output[] = Html::a($language,
                                        [
                                            'blocks/update', 'id' => $data->id,
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
                                return join($separator, array_slice($output, 0, 3)) . "&nbsp;…";
                            else
                                return join($separator, $output);

                        }
                    }

                    return null;
                }
            ],
            [
                'attribute' => 'status',
                'format' => 'html',
                'filter' => SelectInput::widget([
                    'model' => $searchModel,
                    'attribute' => 'status',
                    'items' => $searchModel->getStatusesList(true),
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
                    if ($data->status == $data::CONTENT_BLOCK_STATUS_PUBLISHED) {
                        return '<span class="label label-success">' . Yii::t('app/modules/content', 'Published') . '</span>';
                    } elseif ($data->status == $data::CONTENT_BLOCK_STATUS_DRAFT) {
                        return '<span class="label label-default">' . Yii::t('app/modules/content', 'Draft') . '</span>';
                    } else {
                        return $data->status;
                    }
                }
            ],

            [
                'attribute' => 'created',
                'label' => Yii::t('app/modules/content','Created'),
                'format' => 'html',
                'value' => function($data) {

                    $output = "";
                    if ($user = $data->createdBy) {
                        $output = Html::a($user->username, ['../admin/users/view/?id='.$user->id], [
                            'target' => '_blank',
                            'data-pjax' => 0
                        ]);
                    } else if ($data->created_by) {
                        $output = $data->created_by;
                    }

                    if (!empty($output))
                        $output .= ", ";

                    $output .= Yii::$app->formatter->format($data->updated_at, 'datetime');
                    return $output;
                }
            ],
            [
                'attribute' => 'updated',
                'label' => Yii::t('app/modules/content','Updated'),
                'format' => 'html',
                'value' => function($data) {

                    $output = "";
                    if ($user = $data->updatedBy) {
                        $output = Html::a($user->username, ['../admin/users/view/?id='.$user->id], [
                            'target' => '_blank',
                            'data-pjax' => 0
                        ]);
                    } else if ($data->updated_by) {
                        $output = $data->updated_by;
                    }

                    if (!empty($output))
                        $output .= ", ";

                    $output .= Yii::$app->formatter->format($data->updated_at, 'datetime');
                    return $output;
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
                'buttons'=> [
                    'view' => function($url, $data, $key) {
                        $output = [];
                        $versions = $data->getAllVersions($data->id, true);
                        $locales = ArrayHelper::map($versions, 'id', 'locale');
                        if (isset(Yii::$app->translations)) {
                            foreach ($locales as $item_locale) {
                                $locale = Yii::$app->translations->parseLocale($item_locale, Yii::$app->language);
                                if ($item_locale === $locale['locale']) { // Fixing default locale from PECL intl

                                    if ($data->locale === $locale['locale']) // It`s source version
                                        $output[] = Html::a(Yii::t('app/modules/content','Preview of source version: {language}', [
                                            'language' => $locale['name']
                                        ]), ['blocks/view', 'id' => $data->id], [
                                            'title' => Yii::t('app/modules/content', 'Content preview'),
                                            'data-toggle' => 'modal',
                                            'data-target' => '#contentPreviewModal',
                                            'data-id' => $data->id,
                                            'data-pjax' => '0'
                                        ]);
                                    else  // Other localization versions
                                        $output[] = Html::a(Yii::t('app/modules/content','Preview of language version: {language}', [
                                            'language' => $locale['name']
                                        ]), ['blocks/view', 'id' => $data->id, 'locale' => $locale['locale']], [
                                            'title' => Yii::t('app/modules/content', 'Content preview'),
                                            'data-toggle' => 'modal',
                                            'data-target' => '#contentPreviewModal',
                                            'data-id' => $data->id,
                                            'data-pjax' => '0'
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
                                        $output[] = Html::a(Yii::t('app/modules/content','Preview of version: {language}', [
                                            'language' => $language
                                        ]), ['blocks/view', 'id' => $data->id], [
                                            'title' => Yii::t('app/modules/content', 'Content preview'),
                                            'data-toggle' => 'modal',
                                            'data-target' => '#contentPreviewModal',
                                            'data-id' => $data->id,
                                            'data-pjax' => '0'
                                        ]);
                                    else  // Other localization versions
                                        $output[] = Html::a(Yii::t('app/modules/content','Preview of version: {language}', [
                                            'language' => $language
                                        ]), ['blocks/view', 'id' => $data->id, 'locale' => $locale], [
                                            'title' => Yii::t('app/modules/content', 'Content preview'),
                                            'data-toggle' => 'modal',
                                            'data-target' => '#contentPreviewModal',
                                            'data-id' => $data->id,
                                            'data-pjax' => '0'
                                        ]);

                                }
                            }
                        }

                        if (is_countable($output) && $data->getContentCount() && $data->getFieldsCount()) {
                            if (count($output) > 1) {
                                $html = '';
                                $html .= '<div class="btn-group">';
                                $html .= Html::a(
                                    '<span class="glyphicon glyphicon-eye-open"></span> ' .
                                    Yii::t('app/modules/content', 'Preview') .
                                    ' <span class="caret"></span>',
                                    '#',
                                    [
                                        'class' => "btn btn-block btn-link btn-xs dropdown-toggle",
                                        'disabled' => ($data->getContentCount() && $data->getFieldsCount()) ? false : true,
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

                        if ($data->getContentCount() && $data->getFieldsCount()) {
                            $url = Url::toRoute(['blocks/view', 'id' => $key]);
                            return Html::a('<span class="glyphicon glyphicon-eye-open"></span> ' .
                                Yii::t('app/modules/content', 'Preview'),
                                $url, [
                                    'class' => 'btn btn-link btn-xs',
                                    'title' => Yii::t('app/modules/content', 'Content preview'),
                                    'data-toggle' => 'modal',
                                    'data-target' => '#contentPreviewModal',
                                    'data-id' => $key,
                                    'data-pjax' => '0'
                                ]
                            );
                        }
                    },
                    'update' => function($url, $data, $key) {

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
                                        ]), ['blocks/update', 'id' => $data->id]);
                                    else  // Other localization versions
                                        $output[] = Html::a(Yii::t('app/modules/content','Edit language version: {language}', [
                                            'language' => $locale['name']
                                        ]), ['blocks/update', 'id' => $data->id, 'locale' => $locale['locale']]);

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
                                        ]), ['blocks/update', 'id' => $data->id]);
                                    else  // Other localization versions
                                        $output[] = Html::a(Yii::t('app/modules/content','Edit language version: {language}', [
                                            'language' => $language
                                        ]), ['blocks/update', 'id' => $data->id, 'locale' => $locale]);

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
                                'blocks/update',
                                'id' => $data->id
                            ], [
                                'class' => 'btn btn-link btn-xs'
                            ]
                        );
                    },
                    'delete' => function($url, $data, $key) {

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
                                        ]), ['blocks/delete', 'id' => $data->id], [
                                            'data-method' => 'POST',
                                            'data-confirm' => Yii::t('app/modules/content', 'Are you sure you want to delete this block?')
                                        ]);
                                    else  // Other localization versions
                                        $output[] = Html::a(Yii::t('app/modules/content','Delete language version: {language}', [
                                            'language' => $locale['name']
                                        ]), ['blocks/delete', 'id' => $data->id, 'locale' => $locale['locale']], [
                                            'data-method' => 'POST',
                                            'data-confirm' => Yii::t('app/modules/content', 'Are you sure you want to delete the language version of this block?')
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
                                        ]), ['blocks/delete', 'id' => $data->id], [
                                            'data-method' => 'POST',
                                            'data-confirm' => Yii::t('app/modules/content', 'Are you sure you want to delete this block?')
                                        ]);
                                    else  // Other localization versions
                                        $output[] = Html::a(Yii::t('app/modules/content','Delete language version: {language}', [
                                            'language' => $language
                                        ]), ['blocks/delete', 'id' => $data->id, 'locale' => $locale], [
                                            'data-method' => 'POST',
                                            'data-confirm' => Yii::t('app/modules/content', 'Are you sure you want to delete the language version of this block?')
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
                                'blocks/delete',
                                'id' => $data->id
                            ], [
                                'class' => 'btn btn-link btn-xs',
                                'data-method' => 'POST',
                                'data-confirm' => Yii::t('app/modules/content', 'Are you sure you want to delete this block?')
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
        <?= Html::a(Yii::t('app/modules/content', 'Add new block'), ['blocks/create'], ['class' => 'btn btn-add btn-success pull-right']) ?>
    </div>
    <?php Pjax::end(); ?>
</div>

<?php
$this->registerJs(<<< JS
    $('body').delegate('[data-toggle="modal"][data-target]', 'click', function(event) {
        
        event.preventDefault();
        var target = $(event.target).data('target');
        $.get(
            $(this).attr('href'),
            function (data) {
                
                $(target).find('.modal-body').html($(data).remove('.modal-footer'));
                if ($(data).find('.modal-footer').length > 0) {
                    $(target).find('.modal-footer').remove();
                    $(target).find('.modal-content').append($(data).find('.modal-footer'));
                }
                
                if ($(target).find('button[type="submit"]').length > 0 && $(target).find('form').length > 0) {
                    $(target).find('button[type="submit"]').on('click', function(event) {
                        event.preventDefault();
                        $(target).find('form').submit();
                    });
                }
                
                $(target).modal();
            }  
        );
    });
JS
); ?>

<?php Modal::begin([
    'id' => 'contentPreviewModal',
    'header' => '<h4 class="modal-title">'.Yii::t('app/modules/content', 'Content preview').'</h4>',
    'clientOptions' => [
        'show' => false
    ]
]); ?>
<?php Modal::end(); ?>

<?php echo $this->render('../_debug'); ?>