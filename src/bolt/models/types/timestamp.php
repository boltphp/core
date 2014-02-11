<?php

namespace bolt\models\types;

use \Doctrine\DBAL\Types\Type,
    \Doctrine\DBAL\Platforms\AbstractPlatform,
    \DateTime;

/**
 * Timestamp type
 */
class timestamp extends Type {

    const TIMESTAMP = 'timestamp';


    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform) {
        return $platform->getIntegerTypeDeclarationSQL($fieldDeclaration);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform) {
        if ($value === null OR $value instanceof DateTime) {
            return $value;
        }
        $dt = new DateTime();
        $dt->setTimestamp(intval($value));
        return $dt;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform) {
        return $value !== null ? $value->getTimestamp() : null;
    }

    public function getName(){
        return self::TIMESTAMP;
    }
}