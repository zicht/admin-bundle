<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\AdminBundle\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class MultipleTransformer implements DataTransformerInterface
{
    public function __construct(DataTransformerInterface $innerTransformer)
    {
        $this->innerTransformer = $innerTransformer;
    }

    /**
     * @{inheritDoc}
     */
    public function transform($values)
    {
        $ret = array();
        if ($values === null) {
            return $values;
        }
        foreach ($values as $item) {
            $ret[]= $this->innerTransformer->transform($item);
        }
        return $ret;
    }

    /**
     * @{inheritDoc}
     */
    public function reverseTransform($values)
    {
        $ret = array();
        foreach ($values as $item) {
            if ($value = $this->innerTransformer->reverseTransform($item)) {
                $ret[]= $value;
            }
        }
        return $ret;
    }
}