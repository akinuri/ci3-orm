<?php

namespace Akinuri\CI3_ORM\Helpers;

final class RowRegistry {
    
    private function __construct() {}
    
    
    #region ==================== REGISTRY
    
    private static $registry = [
        // "Row::class" => [
        //     "pk" => object(Row),
        //     "pk" => object(Row),
        // ],
    ];
    
    public static function getRegistry() {
        $result = [];
        foreach (self::$registry as $class => $rows) {
            $result[$class] = [];
            foreach ($rows as $id => $row) {
                $result[$class][$id] = $row->getRowData();
            }
        }
        return $result;
    }
    
    #endregion
    
    
    #region ==================== CHECK
    
    public static function isRegistered(Row $row) {
        return \in_array(
            $row->getPrimaryKey(),
            \array_keys(self::$registry[$row::class] ?? [])
        );
    }
    
    public static function find(string $primaryKey, string $rowClass) {
        $result = self::$registry[$rowClass][$primaryKey] ?? null;
        return $result;
    }
    
    #endregion
    
    
    #region ==================== REGISTER
    
    public static function register(Row $row) {
        if (self::isRegistered($row)) {
            return;
        }
        self::$registry[$row::class][ $row->getPrimaryKey() ] = $row;
    }
    
    public static function deregister(Row $row) {
        unset(self::$registry[$row::class][ $row->getPrimaryKey() ]);
    }
    
    #endregion
    
    
}