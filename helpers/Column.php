<?php
/**
 * User: zura
 * Date: 2/13/17
 * Time: 12:14 PM
 */

namespace omcrn\gii\helpers;


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
     * @param \yii\db\TableSchema $table
     * @param string $columnName
     * @return bool
     */
    public static function isUnixTimestampColumn($table, $columnName)
    {
        return array_key_exists($columnName, $table->columns) && strtolower($table->columns[$columnName]->dbType) === 'int(11)';
    }

}