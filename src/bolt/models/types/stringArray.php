<?php

namespace bolt\models\types;

use \Doctrine\DBAL\Types\Type,
    \Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * string_array type
 */
class stringArray extends Type {

    const STRING_ARRAY = 'string_array';

    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform) {
        return $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform) {
        if ($value === null) {
            return array();
        }
        if (is_array($value)) {
            return $value;
        }
        $value = (is_resource($value)) ? stream_get_contents($value) : $value;
        return json_decode($value, true);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform) {
        if ($value === null) {
            return null;
        }
        if (is_string($value)) {
            return $value;
        }
        return json_encode($value);
    }

    public function getName(){
        return self::STRING_ARRAY;
    }
}