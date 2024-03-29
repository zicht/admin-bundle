<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Security\Voter;

use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Delegates to the sonata ROLE_ voter based on an entity
 */
class AdminVoter implements VoterInterface
{
    /**
     * The 'view' attribute
     *
     * @deprecated
     */
    const VIEW = 'VIEW';

    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var AccessDecisionManagerInterface
     */
    private $decisionManager;

    /**
     * @var SecurityHandlerInterface
     */
    private $securityHandler;

    /**
     * AdminVoter constructor.
     * The passed attributes are mapped to ROLE_* attributes delegated to the authorization checker
     */
    public function __construct(array $attributes, Pool $pool, AccessDecisionManagerInterface $decisionManager, SecurityHandlerInterface $securityHandler)
    {
        $this->attributes = $attributes;
        $this->pool = $pool;
        $this->decisionManager = $decisionManager;
        $this->securityHandler = $securityHandler;
    }

    public function supportsAttribute($attribute)
    {
        return in_array($attribute, $this->attributes);
    }

    public function supportsClass($class)
    {
        // support any class that has an associated sonata admin
        return $this->pool->hasAdminByClass($class);
    }

    /**
     * @param object|null $object
     * @return int
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        // check if class of this object is supported by this voter
        if (!is_null($object) && $this->supportsClass(get_class($object))) {
            $class = get_class($object);
            /** @var AccessDecisionManagerInterface $accessDecisionManager */
            $accessDecisionManager = $this->decisionManager;
            foreach ($this->mapAttributesToRoles($class, $attributes) as $mappedAttr) {
                if ($accessDecisionManager->decide($token, [$mappedAttr], $object)) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    /**
     * Maps regular attributes such as VIEW, EDIT, etc, to their respective SONATA role name,
     * such as ROLE_FOO_BAR_BAZ_EDIT
     *
     * @param string $class
     * @param string[] $attributes
     * @return array
     */
    protected function mapAttributesToRoles($class, $attributes)
    {
        $mappedAttributes = [];
        foreach ($this->pool->getAdminClasses() as $adminClass => $adminCodes) {
            if ($class === $adminClass || $class instanceof $adminClass) {
                foreach ($adminCodes as $adminCode) {
                    $admin = $this->pool->getAdminByAdminCode($adminCode);
                    $baseRole = $this->securityHandler->getBaseRole($admin);

                    foreach ($attributes as $attr) {
                        if ($this->supportsAttribute($attr)) {
                            $mappedAttributes[] = sprintf($baseRole, $attr);
                        }
                    }
                }
            }
        }

        return $mappedAttributes;
    }
}
