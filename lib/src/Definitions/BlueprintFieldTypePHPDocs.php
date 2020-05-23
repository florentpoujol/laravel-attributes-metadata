<?php

namespace FlorentPoujol\LaravelAttributePresets\Definitions;

/**
 * @method DbColumn char($length = null)
 * @method DbColumn string($length = null)
 *
 * @method DbColumn text()
 * @method DbColumn mediumText()
 * @method DbColumn longText()
 *
 * @method DbColumn integer($autoIncrement = false, $unsigned = false)
 * @method DbColumn tinyInteger($autoIncrement = false, $unsigned = false)
 * @method DbColumn smallInteger($autoIncrement = false, $unsigned = false)
 * @method DbColumn mediumInteger($autoIncrement = false, $unsigned = false)
 * @method DbColumn bigInteger($autoIncrement = false, $unsigned = false)
 *
 * @method DbColumn float($total = 8, $places = 2)
 * @method DbColumn double($total = 8, $places = 2)
 * @method DbColumn decimal($total = 8, $places = 2)
 * @method DbColumn unsignedDecimal($total = 8, $places = 2)
 *
 * @method DbColumn boolean()
 * @method DbColumn enum(array $allowed)
 * @method DbColumn set(array $allowed)
 *
 * @method DbColumn json()
 * @method DbColumn jsonb()
 *
 * @method DbColumn date()
 * @method DbColumn dateTime($precision = 0)
 * @method DbColumn dateTimeTz($precision = 0)
 * @method DbColumn time($precision = 0)
 * @method DbColumn timeTz($precision = 0)
 * @method DbColumn timestamp($precision = 0)
 * @method DbColumn timestampTz($precision = 0)
 * @method DbColumn year()
 *
 * @method DbColumn binary()
 * @method DbColumn uuid()
 * @method DbColumn ipAddress()
 * @method DbColumn macAddress()
 *
 * @method DbColumn geometry()
 * @method DbColumn point($srid = null)
 * @method DbColumn linestring()
 * @method DbColumn polygon()
 * @method DbColumn geometryCollection()
 * @method DbColumn multiPoint()
 * @method DbColumn multiLineString()
 * @method DbColumn multiPolygon()
 * @method DbColumn multiPolygonZ()
 *
 * @method DbColumn computed($expression)
 */
trait BlueprintFieldTypePHPDocs
{
    //
}
