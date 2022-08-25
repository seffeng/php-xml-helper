<?php
/**
 * @link http://github.com/seffeng/
 * @copyright Copyright (c) 2022 seffeng
 */
namespace Seffeng\XmlHelper;

use DOMDocument;
use DOMElement;
use DOMText;
use DOMException;
use Seffeng\StrHelper\Str;

/**
 *
 * @author zxf
 * @date   2020年6月10日
 */
class Xml
{
    /**
     * @var string the Content-Type header for the response
     */
    protected static $contentType = 'application/xml';
    /**
     * @var string the XML version
     */
    protected static $version = '1.0';
    /**
     * @var string the XML encoding. If not set, it will use the value of [[Response::charset]].
     */
    protected static $charset = 'UTF-8';
    /**
     * @var string the name of the root element. If set to false, null or is empty then no root tag should be added.
     */
    protected static $rootTag = 'response';
    /**
     * [true-使用Class作为DOMElement名称,false-无DOMElement名称,字符串-该字符串作为DOMElement名称]
     * @var boolean|string
     */
    protected static $useObjectTags = true;
    /**
     * 使用Class作为DOMElement名称时，DOMElement名称使用小写字母
     * @var boolean
     */
    protected static $lowercase  = true;
    /**
     * 过滤 NULL 值
     * @var boolean
     */
    protected static $filter = false;
    /**
     *
     * @var string
     */
    protected static $itemTag = 'item';

    /**
     *
     * @author zxf
     * @date    2020年6月20日
     * @param  mixed $data
     * @return string
     */
    public static function toXml($data)
    {
        $dom = new DOMDocument(static::$version, static::$charset);
        if (!empty(static::$rootTag)) {
            $root = new DOMElement(static::$rootTag);
            $dom->appendChild($root);
            static::buildXml($root, $data);
        } else {
            static::buildXml($dom, $data);
        }
        return $dom->saveXML();
    }

    /**
     *
     * @author zxf
     * @date    2020年6月20日
     * @param  DOMElement $element
     * @param  mixed $data
     */
    protected static function buildXml($element, $data)
    {
        if (is_array($data)) {
            foreach ($data as $name => $value) {
                if (is_int($name) && is_object($value)) {
                    static::buildXml($element, $value);
                } elseif (is_array($value) || is_object($value)) {
                    $child = new DOMElement(static::getValidXmlElementName($name));
                    $element->appendChild($child);
                    static::buildXml($child, $value);
                } else {
                    $child = new DOMElement(static::getValidXmlElementName($name));
                    $element->appendChild($child);
                    $child->appendChild(new DOMText(static::formatScalarValue($value)));
                }
            }
        } elseif (is_object($data)) {
            if (is_string(static::$useObjectTags)) {
                $child = new DOMElement(static::$useObjectTags);
                $element->appendChild($child);
            } elseif (static::$useObjectTags) {
                $rootTag = static::$lowercase ? strtolower(Str::basename(get_class($data))) : Str::basename(get_class($data));
                $child = new DOMElement($rootTag);
                $element->appendChild($child);
            } else {
                $child = $element;
            }
            $array = [];
            foreach ($data as $name => $value) {
                if (static::$filter) {
                    !is_null($value) && $array[$name] = $value;
                } else {
                    $array[$name] = $value;
                }
            }
            static::buildXml($child, $array);
        } else {
            $element->appendChild(new DOMText(static::formatScalarValue($data)));
        }
    }

    /**
     * Formats scalar value to use in XML text node.
     *
     * @param int|string|bool|float $value a scalar value.
     * @return string string representation of the value.
     */
    protected static function formatScalarValue($value)
    {
        if ($value === true) {
            return 'true';
        }
        if ($value === false) {
            return 'false';
        }
        if (is_float($value)) {
            return Str::floatToString($value);
        }
        return strval($value);
    }

    /**
     * Returns element name ready to be used in DOMElement if
     * name is not empty, is not int and is valid.
     *
     * Falls back to [[itemTag]] otherwise.
     *
     * @param mixed $name
     * @return string
     */
    protected static function getValidXmlElementName($name)
    {
        if (empty($name) || is_int($name) || !static::isValidXmlName($name)) {
            return static::$itemTag;
        }
        return $name;
    }

    /**
     * Checks if name is valid to be used in XML.
     *
     * @param mixed $name
     * @return boolean
     */
    protected static function isValidXmlName($name)
    {
        try {
            new DOMElement($name);
            return true;
        } catch (DOMException $e) {
            return false;
        }
    }
}
