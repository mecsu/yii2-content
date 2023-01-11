<?php

namespace mecsu\content\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

class InitController extends Controller
{
    /**
     * @inheritdoc
     */
    public $choice = null;

    /**
     * @inheritdoc
     */
    public $defaultAction = 'index';

    public function options($actionID)
    {
        return ['choice', 'color', 'interactive', 'help'];
    }

    public function actionIndex($params = null)
    {
        $module = Yii::$app->controller->module;
        $version = $module->version;
        $welcome =
            '╔════════════════════════════════════════════════╗'. "\n" .
            '║                                                ║'. "\n" .
            '║            CONTENT MODULE, v.'.$version.'             ║'. "\n" .
            '║          by Alexsander Vyshnyvetskyy           ║'. "\n" .
            '║       (c) 2019-2021 W.D.M.Group, Ukraine       ║'. "\n" .
            '║                                                ║'. "\n" .
            '╚════════════════════════════════════════════════╝';
        echo $name = $this->ansiFormat($welcome . "\n\n", Console::FG_GREEN);
        echo "Select the operation you want to perform:\n";
        echo "  1) Apply all module migrations\n";
        echo "  2) Revert all module migrations\n";
        echo "  3) Add demo data\n\n";
        echo "Your choice: ";

        if(!is_null($this->choice))
            $selected = $this->choice;
        else
            $selected = trim(fgets(STDIN));

        if ($selected == "1") {
            Yii::$app->runAction('migrate/up', ['migrationPath' => '@vendor/mecsu/yii2-content/migrations', 'interactive' => true]);
        } else if ($selected == "2") {
            Yii::$app->runAction('migrate/down', ['migrationPath' => '@vendor/mecsu/yii2-content/migrations', 'interactive' => true]);
        } else if($selected == "3") {

            echo $this->ansiFormat("\n");

            $datetime = date("Y-m-d H:i:sP");
            $blocksTable = \mecsu\content\models\Blocks::tableName();
            Yii::$app->db->createCommand()->batchInsert($blocksTable, [
                'id', 'source_id', 'title', 'description', 'alias', 'fields', 'type', 'status', 'locale', 'created_at', 'created_by', 'updated_at', 'updated_by'
            ], [
                [1, null, 'Contacts Info', 'Description of contacts info block', 'contacts-info', '', 1, 1, 'en-US', $datetime, null, $datetime, null],
                [2, 1, 'Контактная информация', 'Описание блока контактной информации', 'contacts-info', '', 1, 1, 'ru-RU', $datetime, null, $datetime, null],
                [3, 1, 'Контактна інформація', 'Опис блоку контактної інформації', 'contacts-info', '', 1, 1, 'uk-UA', $datetime, null, $datetime, null],
                [4, null, 'Our Team', 'Description of our team list', 'our-team', '[]', 2, 1, 'en-US', $datetime, null, $datetime, null],
                [5, 4, 'Наша команда', 'Описание списка нашей команды', 'our-team', '[]', 2, 1, 'ru-RU', $datetime, null, $datetime, null],
                [6, 4, 'Наша команда', 'Опис списку нашої команди', 'our-team', '[]', 2, 1, 'uk-UA', $datetime, null, $datetime, null]
            ])->execute();

            $datetime = date("Y-m-d H:i:sP");
            $fieldsTable = \mecsu\content\models\Fields::tableName();
            Yii::$app->db->createCommand()->batchInsert($fieldsTable, [
                'id', 'source_id', 'block_id', 'label', 'name', 'type', 'sort_order', 'params', 'locale', 'created_at', 'created_by', 'updated_at', 'updated_by'
            ], [
                [1, null, 1, 'Adress', 'adress', 2, 10, null, 'en-US', $datetime, null, $datetime, null],
                [2, 1, 1, 'Адрес', 'adress', 2, 20, null, 'ru-RU', $datetime, null, $datetime, null],
                [3, 1, 1, 'Адреса', 'adress', 2, 30, null, 'uk-UA', $datetime, null, $datetime, null],
                [4, null, 1, 'Phone', 'phone', 1, 40, null, 'en-US', $datetime, null, $datetime, null],
                [5, 4, 1, 'Контактный телефон', 'phone', 1, 50, null, 'ru-RU', $datetime, null, $datetime, null],
                [6, 4, 1, 'Контактний телефон', 'phone', 1, 60, null, 'uk-UA', $datetime, null, $datetime, null],
                [7, null, 1, 'E-mail', 'email', 1, 70, null, 'en-US', $datetime, null, $datetime, null],
                [8, 7, 1, 'Эл. почта', 'email', 1, 80, null, 'ru-RU', $datetime, null, $datetime, null],
                [9, 7, 1, 'Ел. пошта', 'email', 1, 90, null, 'uk-UA', $datetime, null, $datetime, null],
                [10, null, 4, 'First Name', 'first_name', 1, 10, null, 'en-US', $datetime, null, $datetime, null],
                [11, null, 4, 'Last Name', 'last_name', 1, 20, null, 'en-US', $datetime, null, $datetime, null],
                [12, null, 4, 'Age', 'age', 1, 30, null, 'en-US', $datetime, null, $datetime, null],
                [13, null, 4, 'About', 'about', 3, 40, null, 'en-US', $datetime, null, $datetime, null],
                [14, 10, 4, 'Фамилия', 'first_name', 1, 50, null, 'ru-RU', $datetime, null, $datetime, null],
                [15, 10, 4, 'Прізвище', 'first_name', 1, 60, null, 'uk-UA', $datetime, null, $datetime, null],
                [16, 11, 4, 'Имя', 'last_name', 1, 70, null, 'ru-RU', $datetime, null, $datetime, null],
                [17, 11, 4, 'Ім`я', 'last_name', 1, 80, null, 'uk-UA', $datetime, null, $datetime, null],
                [18, 12, 4, 'Возраст', 'age', 1, 90, null, 'ru-RU', $datetime, null, $datetime, null],
                [19, 12, 4, 'Вік', 'age', 1, null, null, 'uk-UA', $datetime, null, $datetime, null],
                [20, 13, 4, 'Обо мне', 'about', 3, 110, null, 'ru-RU', $datetime, null, $datetime, null],
                [21, 13, 4, 'Про мене', 'about', 3, 120, null, 'uk-UA', $datetime, null, $datetime, null]
            ])->execute();

            $datetime = date("Y-m-d H:i:sP");
            $contentTable = \mecsu\content\models\Content::tableName();
            Yii::$app->db->createCommand()->batchInsert($contentTable, [
                'id', 'field_id', 'block_id', 'content', 'locale', 'created_at', 'created_by', 'updated_at', 'updated_by'
            ], [
                [1, 1, 1, '01001, Ukraine, Kiev, 117 Instytutska Str.', 'en-US', $datetime, null, $datetime, null],
                [2, 4, 1, '+380 (44) 123-45-67', 'en-US', $datetime, null, $datetime, null],
                [3, 7, 1, 'office@example.com', 'en-US', $datetime, null, $datetime, null],
                [4, 2, 1, '01001, Украина, Киев, ул. Институтская, 117', 'ru-RU', $datetime, null, $datetime, null],
                [5, 5, 1, '+380 (44) 123-45-67', 'ru-RU', $datetime, null, $datetime, null],
                [6, 8, 1, 'office@example.com', 'ru-RU', $datetime, null, $datetime, null],
                [10, 3, 1, '01001, Україна, Київ, вул. Інститутська, 117', 'uk-UA', $datetime, null, $datetime, null],
                [11, 6, 1, '+380 (44) 123-45-67', 'uk-UA', $datetime, null, $datetime, null],
                [12, 9, 1, 'office@example.com', 'uk-UA', $datetime, null, $datetime, null],
                [13, 10, 4, 'Doe', 'en-US', $datetime, null, $datetime, null],
                [14, 11, 4, 'John', 'en-US', $datetime, null, $datetime, null],
                [15, 12, 4, '32 year old', 'en-US', $datetime, null, $datetime, null],
                [16, 13, 4, 'Programmer', 'en-US', $datetime, null, $datetime, null],
                [17, 14, 4, 'Доу', 'ru-RU', $datetime, null, $datetime, null],
                [18, 16, 4, 'Джон', 'ru-RU', $datetime, null, $datetime, null],
                [19, 18, 4, '32 года', 'ru-RU', $datetime, null, $datetime, null],
                [20, 20, 4, 'Программист', 'ru-RU', $datetime, null, $datetime, null],
                [21, 15, 4, 'Доу', 'uk-UA', $datetime, null, $datetime, null],
                [22, 17, 4, 'Джон', 'uk-UA', $datetime, null, $datetime, null],
                [23, 19, 4, '32 роки', 'uk-UA', $datetime, null, $datetime, null],
                [24, 21, 4, 'Програміст', 'uk-UA', $datetime, null, $datetime, null]
            ])->execute();

            $itemsTable = \mecsu\content\models\Items::tableName();
            Yii::$app->db->createCommand()->batchInsert($itemsTable, [
                'id', 'block_id', 'ext_id', 'row_order'
            ], [
                [1, 4, 17, 10],
                [2, 4, 18, 10],
                [3, 4, 19, 10],
                [4, 4, 20, 10],
                [5, 4, 21, 20],
                [6, 4, 22, 20],
                [7, 4, 23, 20],
                [8, 4, 24, 20]
            ])->execute();

            echo $this->ansiFormat("Data inserted successfully.\n\n", Console::FG_GREEN);

        } else {
            echo $this->ansiFormat("Error! Your selection has not been recognized.\n\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        echo "\n";
        return ExitCode::OK;
    }
}
