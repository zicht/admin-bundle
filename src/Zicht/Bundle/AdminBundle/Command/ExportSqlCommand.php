<?php
/**
 * @author    Philip Bergman <philip@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */
namespace Zicht\Bundle\AdminBundle\Command;

use Exporter\Source\DoctrineDBALConnectionSourceIterator;
use Exporter\Writer\WriterInterface;
use Exporter\Handler;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ExportSqlCommand
 *
 * @package Zicht\Bundle\PameijerPortalBundle\Command
 */
class ExportSqlCommand extends ContainerAwareCommand
{
    protected $typeMapping = [
        'xls'       => 'Exporter\\Writer\\XlsWriter',
        'xml'       => 'Exporter\\Writer\\XmlWriter',
        'xmlexcel'  => 'Exporter\\Writer\\XmlExcelWriter',
        'json'      => 'Exporter\\Writer\\JsonWriter',
        'csv'       => 'Exporter\\Writer\\CsvWriter',
    ];

    /**
     * @{inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('zicht:export:sql')
            ->addArgument("sql", InputArgument::REQUIRED, 'The raw SELECT query.')
            ->addOption("file", "f", InputOption::VALUE_REQUIRED, "File to write to, if none given it will use stdout.")
            ->addOption("type", "t", InputOption::VALUE_REQUIRED, "The export type.", 'xls')
            ->setDescription('Will export all sql result from given sql select query.');
    }

    /**
     * @{inheritDoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        // support for posix stdin pipe
        if ('-' === $input->getArgument('sql')) {
            $input->setArgument('sql', trim(stream_get_contents(STDIN)));
        }

        // remove writers that don`t exists
        $this->typeMapping = array_filter(
            $this->typeMapping,
            function ($className) {
                return class_exists($className);
            }
        );

        if (false == @preg_match('#(\s+)?SELECT#i', $input->getArgument('sql'))) {
            throw new \RuntimeException(
                sprintf(
                    '"%s" is not a valid SELECT query.',
                    preg_replace(
                        '/(\s{2,}|\n)/',
                        ' ',
                        $input->getArgument('sql')
                    )
                )
            );
        }

        if (!array_key_exists($input->getOption('type'), $this->typeMapping)) {
            throw new \RuntimeException(
                sprintf(
                    '"%s" is not a valid export type. valid type are "%s"',
                    $input->getOption('type'),
                    implode(
                        '", "',
                        array_keys(
                            $this->typeMapping
                        )
                    )
                )
            );
        }
    }

    /**
     * @{inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (null === ($file = $input->getOption('file'))) {
            $file = 'php://output';
        }

        Handler::create(
            new DoctrineDBALConnectionSourceIterator(
                $this->getConnection(),
                $input->getArgument('sql')
            ),
            new $this->typeMapping[$type]($file)
        )
            ->export();
    }

    /**
     * @return Connection
     */
    protected function getConnection()
    {
        return $this->getContainer()->get('doctrine')->getConnection();
    }
}
