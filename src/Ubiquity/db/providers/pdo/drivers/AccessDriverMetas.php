<?php

namespace Ubiquity\db\providers\pdo\drivers;

class AccessDriverMetas extends AbstractDriverMetaDatas {

    /**
     * @inheritDoc
     */
    public function getTablesName(): array {
        $query = $this->dbInstance->query ('SELECT MSysObjects.Name AS table_name
                                            FROM MSysObjects
                                            WHERE (((Left([Name],1))<>"~") 
                                                  AND ((Left([Name],4))<>"MSys") 
                                                  AND ((MSysObjects.Type) In (1,4,6)))
                                            order by MSysObjects.Name' );
        return $query->fetchAll ( \PDO::FETCH_COLUMN );
    }

    /**
     * @inheritDoc
     */
    public function getPrimaryKeys(string $tableName): array
    {
        // TODO: Implement getPrimaryKeys() method.
    }

    /**
     * @inheritDoc
     */
    public function getForeignKeys(string $tableName, string $pkName, ?string $dbName = null): array
    {
        // TODO: Implement getForeignKeys() method.
    }

    /**
     * @inheritDoc
     */
    public function getFieldsInfos(string $tableName): array
    {
        // TODO: Implement getFieldsInfos() method.
    }

    /**
     * @inheritDoc
     */
    public function getRowNum(string $tableName, string $pkName, string $condition): int
    {
        // TODO: Implement getRowNum() method.
    }

    /**
     * @inheritDoc
     */
    public function groupConcat(string $fields, string $separator): string
    {
        // TODO: Implement groupConcat() method.
    }
}