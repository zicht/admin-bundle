<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\DataTransformer;

use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Form\DataTransformerInterface;
use Zicht\Bundle\AdminBundle\Service\Quicklist;

/**
 * Simple utility transformer used for the autocomplete using the Quicklist service.
 */
class ClassTransformer implements DataTransformerInterface
{
    /**
     * @var Quicklist
     */
    private $lister;

    /**
     * @var string
     */
    private $repo;

    /**
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
        try {
            return [
                'id' => (null !== $value ? $value->getId() : null),
                'value' => (null !== $value ? $value->__toString() : null),
            ];
        } catch (EntityNotFoundException $e) {
            return ['id' => null, 'value' => '-- ENTITY NOT FOUND --'];
        }
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
