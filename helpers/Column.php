<?php
/**
 * User: zura
 * Date: 2/13/17
 * Time: 12:14 PM
 */

namespace omcrn\gii\helpers;

use yii\db\TableSchema;


/**
 * Class Column
 *
 * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package omcrn\gii\helpers
 */
class Column
{
    /**
     * Check if given column is type of timestamp or not
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param \yii\db\TableSchema|\yii\db\ColumnSchema $tableOrColumnSchema
     * @param string $columnName
     * @return bool
     */
    public static function isUnixTimestampColumn($tableOrColumnSchema, $columnName = null)
    {
        if ($tableOrColumnSchema instanceof TableSchema){
            return array_key_exists($columnName, $tableOrColumnSchema->columns) && strtolower($tableOrColumnSchema->columns[$columnName]->dbType) === 'int(11)';
        }
        if ($tableOrColumnSchema instanceof \yii\db\ColumnSchema){
            return $tableOrColumnSchema->dbType === 'int(11)';
        }
        return false;
    }

}