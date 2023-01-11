<?php

namespace mecsu\content;

/**
 * Yii2 Content manager
 *
 * @category        Module
 * @version         1.1.2
 * @author          Alexsander Vyshnyvetskyy <alex.vyshnyvetskyy@gmail.com>
 * @link            https://github.com/mecsu/yii2-content
 * @copyright       Copyright (c) 2019 - 2021 W.D.M.Group, Ukraine
 * @license         https://opensource.org/licenses/MIT Massachusetts Institute of Technology (MIT) License
 *
 */

use Yii;
use wdmg\base\BaseModule;

/**
 * Content module definition class
 */
class Module extends BaseModule
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'mecsu\content\controllers';

    /**
     * {@inheritdoc}
     */
    public $defaultRoute = "content/index";

    /**
     * @var string, the name of module
     */
    public $name = "Content";

    /**
     * @var string, the description of module
     */
    public $description = "Content manager";

    /**
     * @var array, the list of support locales for multi-language versions of content.
     * @note This variable will be override if you use the `wdmg\yii2-translations` module.
     */
    public $supportLocales = ['ru-RU', 'uk-UA', 'en-US'];

    /**
     * @var string the module version
     */
    private $version = "1.1.2";

    /**
     * @var integer, priority of initialization
     */
    private $priority = 4;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // Set version of current module
        $this->setVersion($this->version);

        // Set priority of current module
        $this->setPriority($this->priority);

    }

    /**
     * {@inheritdoc}
     */
    public function dashboardNavItems($options = false)
    {
        $items = [
            'label' => $this->name,
            'url' => [$this->routePrefix . '/'. $this->id],
            'icon' => 'fa fa-fw fa-list-alt',
            'active' => in_array(\Yii::$app->controller->module->id, [$this->id]),
            'items' => [
                [
                    'label' => Yii::t('app/modules/content', 'Content blocks'),
                    'url' => [$this->routePrefix . '/content/blocks/'],
                    'active' => (in_array(\Yii::$app->controller->module->id, ['content']) &&  Yii::$app->controller->id == 'blocks'),
                ],
                [
                    'label' => Yii::t('app/modules/content', 'Content lists'),
                    'url' => [$this->routePrefix . '/content/lists/'],
                    'active' => (in_array(\Yii::$app->controller->module->id, ['content']) &&  Yii::$app->controller->id == 'lists'),
                ]
            ]
        ];
        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function bootstrap($app)
    {
        parent::bootstrap($app);

        // Configure content component
        $app->setComponents([
            'content' => [
                'class' => 'mecsu\content\components\Content'
            ]
        ]);
    }
}