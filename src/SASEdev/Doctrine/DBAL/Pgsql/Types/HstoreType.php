<?php
namespace SASEdev\Doctrine\DBAL\Pgsql\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 *
 * @author sasedev <sasedev.bifidis@gmail.com>
 *
 */
class HstoreType extends Type
{

    /**
     *
     * @var string
     */
    const HSTORE = 'hstore';

    /**
     * (non-PHPdoc)
     *
     * @see \Doctrine\DBAL\Types\Type::getName()
     */
    public function getName()
    {
        return self::HSTORE;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Doctrine\DBAL\Types\Type::getSQLDeclaration()
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return "hstore";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return array();
        }

        $value = preg_replace('/([$])/u', "\\\\$1", $value);

        $hstore = array();

        @eval(sprintf("\$hstore = array(%s);", $value));

            if (!(isset($hstore) and is_array($hstore))) {
                return array();
            }

        $array = array();

        foreach ($hstore as $k => $v) {
            if (is_numeric($v)) {
                if (false === strpos($v, '.')) {
                    $v = (int) $v;
                } else {
                    $v = (float) $v;
                }
            } elseif (in_array($v, array('true', 'false'))) {
                $v = $v == 'true';
            }

            $array[$k] = $v;
        }
        return $array;
    }

    /**
     * @param mixed            $value
     * @param AbstractPlatform $platform
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return '';
        }
        if ($value instanceof \stdClass) {
            $value = get_object_vars($value);
        }
        if (!is_array($value)) {
            throw new \InvalidArgumentException("Hstore value must be off array or \stdClass.");
        }

        $hstoreString = '';

        foreach ($value as $k => $v) {
            if (!is_string($v) && !is_numeric($v) && !is_bool($v)) {
                throw new \InvalidArgumentException("Cannot save 'nested arrays' into hstore.");
            }
            if ($v !== null) {
                $hstoreString .= "\"{$this->quoteValue($k)}\"=>\"{$this->quoteValue($v)}\",";
            } else {
                $hstoreString .= "\"{$this->quoteValue($k)}\"=>NULL,";
            }
        }

        return $hstoreString;
    }

    /**
     * @param $value
     *
     * @return string
     */
    protected function quoteValue($value)
    {
        return addslashes($value);
    }
}
