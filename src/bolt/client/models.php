<?php

namespace bolt\client;
use \b;

use Doctrine\ORM\Tools\SchemaTool;


class models extends command {

    public static $name = "models";

    public static $configure = [
        'arguments' => [
            'cmd' => [
                'mode' => self::REQUIRED
            ]
        ]
    ];

    private $_dir = false;
    private $_loaders = [];

    public function init() {

        if (!$this->app->pluginExists('models')) {
            $this->writeError("Models plugin does not exist");
        }


        $this->em = $this->app['models']->getEntityManager();


        $this->app['models']->loadFromDirectories();


        $this->metadatas = $this->em->getMetadataFactory()->getAllMetadata();


        // tool
        $this->tool = new SchemaTool($this->em);

    }

    public function schemaCreate() {

        $this->writeln("Creating Database Schema...");
        $this->tool->createSchema($this->metadatas);
        $this->writeln('Database schema created successfully');


    }

    public function schemaUpdate() {

        $saveMode = true;

        $sqls = $this->tool->getUpdateSchemaSql($this->metadatas, $saveMode);


        $this->writeln('Updating database schema...');
        $this->tool->updateSchema($this->metadatas, $saveMode);
        $this->writeln(sprintf('Database schema updated successfully! "<info>%s</info>" queries were executed', count($sqls)));

    }


    public function schemaDrop() {




        $saveMode = true;

        $sqls = $this->tool->getUpdateSchemaSql($this->metadatas, $saveMode);


        $this->writeln('Dropping database schema...');
        $this->tool->dropSchema($this->metadatas);
        $this->writeln('Database schema dropped successfully!');

    }

}