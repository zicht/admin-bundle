<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\AdminBundle\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Zicht\Bundle\AdminBundle\Service\Quicklist;

/**
 * Simple utility transformer used for the autocomplete using the Quicklist service.
 */
class ClassTransformer implements DataTransformerInterface
{
    /**
     * Constructor
     *
     * @param \Zicht\Bundle\AdminBundle\Service\Quicklist $lister
     * @param string $repo
     */
    public function __construct(Quicklist $lister, $repo)
    {
        $this->lister = $lister;
        $this->repo = $repo;
    }

    /**
     * Transform the class into an hash containing 'id' and 'value' (string repr of the object).
     *
     * @param mixed $value
     * @return array|mixed
     */
    public function transform($value)
    {
        return array(
            'id' => (null !== $value ? $value->getId() : null),
            'value' => (null !== $value ? (string)$value : null)
        );
    }

    /**
     * Return the class associated with the specified value (id)
     *
     * @param mixed $value
     * @return object
     */
    public function reverseTransform($value)
    {
        return $this->lister->getOne($this->repo, $value);
    }
}