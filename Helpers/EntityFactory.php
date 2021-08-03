<?php

namespace Akinuri\CI3_ORM\Helpers;

abstract class EntityFactory {
    
    /**
     * Creates an entity (domain object) and populates it with the data.
     */
    public static function create(
        ?string $entityClass, ?array $data, array $fieldExceptions = []
    ) {
        
        if (empty($entityClass) || empty($data)) {
            return $data;
        }
        
        $entity = new $entityClass();
        $entity->populate($data, $fieldExceptions, true);
        
        if ($entity->hasIdentity() && $entity->isComplete()) {
            RowRegistry::register($entity);
        }
        
        return $entity;
    }
    
    /**
     * Creates multiple entities (domain objects) and populates them with the data.
     */
    public static function createMultiple(
        ?string $entityClass, ?array $rowsOfData, array $fieldExceptions = []
    ): array {
        
        if (empty($entityClass) || empty($rowsOfData)) {
            return $rowsOfData;
        }
        
        $entities = [];
        
        foreach ($rowsOfData as $key => $data) {
            $entities[$key] = self::create($entityClass, $data, $fieldExceptions);
        }
        
        return $entities;
    }
    
}