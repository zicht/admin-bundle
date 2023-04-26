<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\DataTransformer\BaseDateTimeTransformer;

/**
 * Support transformer for ZichtDatePickerType
 *
 * @extends BaseDateTimeTransformer<string>
 * @implements DataTransformerInterface<string, string>
 */
class HumanReadableDateTransformer extends BaseDateTimeTransformer
{
    public function transform($value)
    {
        if (!empty($value)) {
            $dateTime = new \DateTime($value);
            $value = $dateTime->format('d-m-Y @ H:i:s');
        }
        return $value;
    }

    public function reverseTransform($value)
    {
        if (!empty($value)) {
            $value = str_replace('@', '', $value);
            $dateTime = new \DateTime($value, new \DateTimeZone($this->inputTimezone));
            $value = $dateTime->format('Y-m-d H:i:s T');
        }
        return $value;
    }
}
