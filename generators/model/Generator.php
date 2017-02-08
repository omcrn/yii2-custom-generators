<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace omcrn\gii\generators\model;

use Yii;
use yii\gii\CodeFile;

/**
 * This generator will generate one or multiple ActiveRecord classes for the specified database table.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Generator extends \yii\gii\generators\model\Generator
{
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

        if ($this->isTimestampColumn($table, 'created_at')){
            $createdAtAttribute = 'created_at';
        } else if ($this->isTimestampColumn($table, 'create_date')) {
            $createdAtAttribute = 'create_date';
        }

        if ($this->isTimestampColumn($table, 'updated_at')){
            $updatedAtAttribute = 'updated_at';
        } else if ($this->isTimestampColumn($table, 'update_date')) {
            $updatedAtAttribute = 'update_date';
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

    /**
     * Check if given column is type of timestamp or not
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param \yii\db\TableSchema $table
     * @param string $columnName
     * @return bool
     */
    protected function isTimestampColumn($table, $columnName)
    {
        return array_key_exists($columnName, $table->columns) && strtolower($table->columns[$columnName]->dbType) === 'int(11)';
    }
}
