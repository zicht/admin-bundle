<?php
/**
 * @author Boudewijn Schoon <boudewijn@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Security\Voter;

use \Sonata\AdminBundle\Admin\Pool;
use \Symfony\Component\DependencyInjection\ContainerInterface;
use \Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use \Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use \Symfony\Component\Security\Core\Authentication\Token\TokenInterface;


/**
 * Delegates to the sonata ROLE_ voter based on an entity
 */
class AdminVoter implements VoterInterface
{
    /**
     * The 'view' attribute
     */
    const VIEW = 'VIEW';

    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var ContainerInterface
     */
    private $serviceContainer;

    function __construct(Pool $pool, ContainerInterface $serviceContainer)
    {
        $this->pool = $pool;
        $this->serviceContainer = $serviceContainer;
    }

    /**
     * @{inheritDoc}
     */
    public function supportsAttribute($attribute)
    {
        return in_array($attribute, array('EDIT', 'DELETE', 'VIEW', 'CREATE'));
    }

    /**
     * @{inheritDoc}
     */
    public function supportsClass($class)
    {
        // support any class that has an associated sonata admin
        return $this->pool->hasAdminByClass($class);
    }

    /**
     * @{inheritDoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $class = get_class($object);

        // check if class of this object is supported by this voter
        if ($this->supportsClass($class)) {
            /** @var AccessDecisionManagerInterface $accessDecisionManager */
            $accessDecisionManager = $this->serviceContainer->get('security.access.decision_manager');
            foreach ($this->mapAttributesToRoles($class, $attributes) as $mappedAttr) {
                if ($accessDecisionManager->decide($token, array($mappedAttr), $object)) {
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
        /** @var \Sonata\AdminBundle\Security\Handler\RoleSecurityHandler */
        $roleSecurityHandler = $this->serviceContainer->get('sonata.admin.security.handler');
        $admin = $this->pool->getAdminByClass($class);
        $baseRole = $roleSecurityHandler->getBaseRole($admin);

        $mappedAttributes = array();
        foreach ($attributes as $attr) {
            if ($this->supportsAttribute($attr)) {
                $mappedAttributes[]= sprintf($baseRole, $attr);
            }
        }

        return $mappedAttributes;
    }
}
