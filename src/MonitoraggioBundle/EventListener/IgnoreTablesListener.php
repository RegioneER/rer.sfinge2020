<?php

namespace MonitoraggioBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Command\Proxy\UpdateSchemaDoctrineCommand;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;

/**
 * Remove ignored tables /entities from Schema
 */
class IgnoreTablesListener extends UpdateSchemaDoctrineCommand {

    /**
     * @var string[]
     */
    protected $ignoredClasses = [];

    public function __construct(array $ignoredClasses)
    {
        parent::__construct();

        $this->ignoredClasses = $ignoredClasses;
    }

    public function postGenerateSchema(GenerateSchemaEventArgs $args) {
        $em = $args->getEntityManager();
        
        $ignoredTables = \array_map(function(string $entityName) use($em) {
            return $em->getClassMetadata($entityName)->getTableName();
        }, $this->ignoredClasses);
        
        $schema = $args->getSchema();
        foreach ($schema->getTables() as $table) {
            if (in_array($table->getName(), $ignoredTables)) {
                // remove table from schema
                $schema->dropTable($table->getName());
            }
        }
    }

}
