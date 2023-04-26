<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Multiple transformer that delegates both the transform and the reverseTransform to the specified inner transformer
 *
 * @implements DataTransformerInterface<mixed, mixed[]>
 */
class MultipleTransformer implements DataTransformerInterface
{
    /**
     * @var DataTransformerInterface
     */
    private $innerTransformer;

    public function __construct(DataTransformerInterface $innerTransformer)
    {
        $this->innerTransformer = $innerTransformer;
    }

    public function transform($values)
    {
        $ret = [];
        if ($values === null) {
            return $values;
        }
        foreach ($values as $item) {
            $ret[] = $this->innerTransformer->transform($item);
        }
        return $ret;
    }

    public function reverseTransform($values)
    {
        $ret = [];
        foreach ((array)$values as $item) {
            if ($value = $this->innerTransformer->reverseTransform($item)) {
                $ret[] = $value;
            }
        }
        return $ret;
    }
}
