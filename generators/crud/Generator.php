<?php
/**
 * @link http://www.omicronsoft.net/
 * @copyright Copyright (c) 2017 Omicronsoft GSC
 * @license http://www.omicronsoft.net/license/
 */

namespace omcrn\gii\generators\crud;
use omcrn\gii\helpers\Column;
use yii\behaviors\TimestampBehavior;

/**
 *
 * @inheritdoc
 * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package omcrn\gii\generators\crud
 */
class Generator extends \yii\gii\generators\crud\Generator
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
     *
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @return \yii\db\ColumnSchema[]
     */
    public function getColumns()
    {
        return $this->tableSchema->columns;
    }

    public function getColumnsForGeneration()
    {
        $columns = [];
        $table = $this->getTableSchema();
        foreach ($this->getColumns() as $attribute => $column) {
//            \centigen\base\helpers\UtilHelper::vardump($column);
            if ((in_array($attribute, $this->possibleCreatedAtAttributes) || in_array($attribute, $this->possibleUpdatedAtAttributes))
                && Column::isUnixTimestampColumn($table, $attribute)){
                continue;
            }
            $columns[$attribute] = $column;
        }
        return $columns;
    }

    public function generateActiveField($attribute)
    {
        $tableSchema = $this->getTableSchema();
        if ($tableSchema && isset($tableSchema->columns[$attribute])){
            $column = $tableSchema->columns[$attribute];
            if ($column->type === 'datetime' || $column->type === 'timestamp'){
                return "\$form->field(\$model, '$attribute')->widget('trntv\yii\datetime\DateTimeWidget', [
            'momentDatetimeFormat' => Yii::\$app->formatter->getMomentDatetimeFormat() ?: 'yyyy-MM-dd\'T\'HH:mm:ssZZZZZ',
        ]) ";
            } else if (in_array($attribute, ['status', 'is_active'])){
                return "\$form->field(\$model, '$attribute')->checkbox()";
            }
        }
        return parent::generateActiveField($attribute);
    }

    /**
     * @inheritdoc
     */
    public function generateColumnFormat($columnSchema)
    {
        if ($columnSchema->type === 'datetime' || $columnSchema->type === 'timestamp' ||
            ((in_array($columnSchema->name, $this->possibleCreatedAtAttributes) || in_array($columnSchema->name, $this->possibleUpdatedAtAttributes))
                && Column::isUnixTimestampColumn($columnSchema))){
            return 'datetime';
        }
        return parent::generateColumnFormat($columnSchema);
    }

}
