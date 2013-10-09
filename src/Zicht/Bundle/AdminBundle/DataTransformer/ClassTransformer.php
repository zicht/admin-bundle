<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\AdminBundle\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Zicht\Bundle\AdminBundle\Service\Quicklist;

class ClassTransformer implements \Symfony\Component\Form\DataTransformerInterface
{
    function __construct(Quicklist $lister, $repo)
    {
        $this->lister = $lister;
        $this->repo = $repo;
    }

    public function transform($value)
    {
        return array(
            'id' => (null !== $value ? $value->getId() : null),
            'value' => (null !== $value ? (string) $value : null)
        );
    }

    public function reverseTransform($value)
    {
        return $this->lister->getOne($this->repo, $value);
    }
}

