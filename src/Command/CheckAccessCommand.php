<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Command;

use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Utility to check the security system for a specific role -> privilege mapping for entities.
 */
class CheckAccessCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'zicht:admin:check-access';

    /** @var ManagerRegistry */
    private $doctrine;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    public function __construct(ManagerRegistry $doctrine, TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authorizationChecker, string $name = null)
    {
        parent::__construct($name);
        $this->doctrine = $doctrine;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    protected function configure()
    {
        $this->addArgument('role', InputArgument::REQUIRED, 'Check the rights for a specific role');
        $this->addArgument('attribute', InputArgument::REQUIRED, 'The attribute to check for');
        $this->addOption('entity', '', InputOption::VALUE_REQUIRED, 'Entity to check the specified attribute on');
        $this->addOption('id', '', InputOption::VALUE_REQUIRED, 'ID of the entity to check the specified attribute on');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $role = $input->getArgument('role');
        $entity = $input->getOption('entity');
        $entityId = $input->getOption('id');

        $objects = [];

        if ($entity) {
            if ($entityId) {
                $object = $this->doctrine->getManager()->find($entity, $entityId);
                if (!$object) {
                    $output->writeln('<error>Object not found: ' . $entity . ':' . $entityId);
                } else {
                    $objects[] = $object;
                }
            } else {
                $objects = $this->doctrine->getRepository($entity)->findAll();
            }
        } else {
            $object = null;
        }

        $this->tokenStorage->setToken(
            new PreAuthenticatedToken('_', '', 'chain_provider', [$role])
        );
        $table = new Table($output);
        foreach ($objects as $object) {
            $table->addRow(
                [
                    get_class($object),
                    is_callable([$object, 'getId']) ? $object->getId() : '',
                    $this->authorizationChecker->isGranted(
                        [$input->getArgument('attribute')],
                        $object
                    ) ? 'GRANTED' : 'DENIED',
                ]
            );
        }
        $table->render();
    }
}
