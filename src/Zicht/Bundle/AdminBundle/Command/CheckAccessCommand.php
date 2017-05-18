<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;

/**
 * Utility to check the security system for a specific role -> privilege mapping for entities.
 */
class CheckAccessCommand extends ContainerAwareCommand
{
    /**
     * @{inheritDoc}
     */
    protected function configure()
    {
        $this->setName('zicht:admin:check-access');
        $this->addArgument('role', InputArgument::REQUIRED, 'Check the rights for a specific role');
        $this->addArgument('attribute', InputArgument::REQUIRED, 'The attribute to check for');
        $this->addOption('entity', '', InputOption::VALUE_REQUIRED, 'Entity to check the specified attribute on');
        $this->addOption('id', '', InputOption::VALUE_REQUIRED, 'ID of the entity to check the specified attribute on');
    }

    /**
     * @{inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $role = $input->getArgument('role');
        $entity = $input->getOption('entity');
        $entityId = $input->getOption('id');

        $objects = [];

        if ($entity) {
            if ($entityId) {
                $object = $this->getContainer()->get('doctrine')->getManager()->find($entity, $entityId);
                if (!$object) {
                    $output->writeln('<error>Object not found: '. $entity . ':' . $entityId);
                } else {
                    $objects[]= $object;
                }
            } else {
                $objects = $this->getContainer()->get('doctrine')->getRepository($entity)->findAll();
            }
        } else {
            $object = null;
        }

        $this->getContainer()->get('security.token_storage')->setToken(
            new PreAuthenticatedToken('_', '', 'chain_provider', [$role])
        );
        $table = new Table($output);
        foreach ($objects as $object) {
            $table->addRow(
                [
                    get_class($object),
                    is_callable([$object, 'getId']) ? $object->getId() : '',
                    $this->getContainer()->get('security.authorization_checker')->isGranted(
                        [$input->getArgument('attribute')],
                        $object
                    ) ? 'GRANTED' : 'DENIED'
                ]
            );
        }
        $table->render();
    }
}
