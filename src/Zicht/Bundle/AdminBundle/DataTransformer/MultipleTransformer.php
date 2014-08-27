<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\AdminBundle\DataTransformer;

use \Symfony\Component\Form\DataTransformerInterface;

/**
 * Multiple transformer that delegates both the transform and the reverseTransform to the specified inner transformer
 */
class MultipleTransformer implements DataTransformerInterface
{
    /**
     * Constructor.
     *
     * @param \Symfony\Component\Form\DataTransformerInterface $innerTransformer
     */
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
        foreach ((array)$values as $item) {
            if ($value = $this->innerTransformer->reverseTransform($item)) {
                $ret[]= $value;
            }
        }
        return $ret;
    }
}