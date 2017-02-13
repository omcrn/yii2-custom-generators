<?php
/**
 * @link http://www.omicronsoft.net/
 * @copyright Copyright (c) 2017 Omicronsoft GSC
 * @license http://www.omicronsoft.net/license/
 */

namespace omcrn\gii\generators\model;

use omcrn\gii\helpers\Column;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\gii\CodeFile;

/**
 *
 * @inheritdoc
 * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package omcrn\gii\generators\model
 */
class Generator extends \yii\gii\generators\model\Generator
{
    public $possibleCreatedAtAttributes = ['create_date', 'created_at'];
    public $possibleUpdatedAtAttributes = ['update_date', 'updated_at'];

    public function init()
    {
        $timestampBehavior = new TimestampBehavior();
        if (!in_array($timestampBehavior->createdAtAttribute, $this->possibleCreatedAtAttributes)){
            $this->possibleCreatedAtAttributes[] = $timestampBehavior->createdAtAttribute;
        }
        if (!in_array($timestampBehavior->updatedAtAttribute, $this->possibleUpdatedAtAttributes)){
            $this->possibleUpdatedAtAttributes[] = $timestampBehavior->updatedAtAttribute;
        }
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $files = [];
        $relations = $this->generateRelations();
        $db = $this->getDbConnection();
        foreach ($this->getTableNames() as $tableName) {
            // model :
            $modelClassName = $this->generateClassName($tableName);
            $queryClassName = ($this->generateQuery) ? $this->generateQueryClassName($modelClassName) : false;
            $tableSchema = $db->getTableSchema($tableName);
            $params = [
                'tableName' => $tableName,
                'className' => $modelClassName,
                'queryClassName' => $queryClassName,
                'tableSchema' => $tableSchema,
                'labels' => $this->generateLabels($tableSchema),
                'rules' => $this->generateRules($tableSchema),
                'relations' => isset($relations[$tableName]) ? $relations[$tableName] : [],
                'behaviors' => $this->generateBehaviors($tableSchema)
            ];
//            \centigen\base\helpers\UtilHelper::vardump($tableName, $modelClassName, $queryClassName, $tableSchema);

            $files[] = new CodeFile(
                Yii::getAlias('@' . str_replace('\\', '/', $this->ns)) . '/' . $modelClassName . '.php',
                $this->render('model.php', $params)
            );

            // query :
            if ($queryClassName) {
                $params['className'] = $queryClassName;
                $params['modelClassName'] = $modelClassName;
                $files[] = new CodeFile(
                    Yii::getAlias('@' . str_replace('\\', '/', $this->queryNs)) . '/' . $queryClassName . '.php',
                    $this->render('query.php', $params)
                );
            }
        }

        return $files;
    }

    /**
     * Generates the behaviors for the specified table.
     * @param \yii\db\TableSchema $table the table schema
     * @return array the generated behaviors
     */
    public function generateBehaviors($table)
    {
        $behaviors = [];
        $createdAtAttribute = false;
        $updatedAtAttribute = false;

        foreach ($this->possibleCreatedAtAttributes as $columnName) {
            if (Column::isUnixTimestampColumn($table, $columnName)){
                $createdAtAttribute = $columnName;
                break;
            }
        }
        foreach ($this->possibleUpdatedAtAttributes as $columnName) {
            if (Column::isUnixTimestampColumn($table, $columnName)){
                $updatedAtAttribute = $columnName;
                break;
            }
        }

        if ($createdAtAttribute || $updatedAtAttribute){
            $behaviors[] = "[
                'class' => \yii\behaviors\TimestampBehavior::className(),
                'createdAtAttribute' => ".($createdAtAttribute ? "'$createdAtAttribute'" : "false").",
                'updatedAtAttribute' => ".($updatedAtAttribute ? "'$updatedAtAttribute'" : "false")."
            ],".PHP_EOL;
        }

        return $behaviors;
    }
}
