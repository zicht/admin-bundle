<?php
/**
 * @author    Philip Bergman <philip@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */
namespace Zicht\Bundle\AdminBundle\Command;

use Exporter\Source\DoctrineDBALConnectionSourceIterator;
use Exporter\Handler;
use Doctrine\DBAL\Driver\Connection;
use Exporter\Source\SourceIteratorInterface;
use Exporter\Writer\WriterInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ExportSqlCommand
 *
 * @package Zicht\Bundle\AdminBundle\Command
 */
class ExportSqlCommand extends ContainerAwareCommand
{
    protected $typeMapping = [
        'xls'       => 'Exporter\\Writer\\XlsWriter',
        'xml'       => 'Exporter\\Writer\\XmlWriter',
        'xmlexcel'  => 'Exporter\\Writer\\XmlExcelWriter',
        'json'      => 'Exporter\\Writer\\JsonWriter',
        'csv'       => 'Exporter\\Writer\\CsvWriter',
        'twig'      => 'Zicht\\Bundle\\AdminBundle\\Exporter\\Writer\\TwigWriter',
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
            ->addOption("option", "o", InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, "Extra options given to the exporter.")
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

        if (!array_key_exists(strtolower($input->getOption('type')), $this->typeMapping)) {
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

        $this->getHandler($file, $input)->export();
    }

    /**
     * @param InputInterface $input
     * @return Handler
     */
    private function getHandler($file, InputInterface $input)
    {
        return Handler::create(
            $this->getSourceIteratorInter($input->getArgument('sql')),
            $this->getWriter($input->getOption('type'), $file, $input)
        );
    }


    /**
     * @param $type
     * @param $file
     * @param InputInterface $input
     * @return WriterInterface
     */
    private function getWriter($type, $file, InputInterface $input)
    {
        return new $this->typeMapping[$type]($file, ...$this->getArgs($type, $input));
    }

    /**
     * @return SourceIteratorInterface
     */
    private function getSourceIteratorInter($sql)
    {
        return new DoctrineDBALConnectionSourceIterator($this->getConnection(), $sql);
    }

    /**
     * @return Connection
     */
    private function getConnection()
    {
        return $this->getContainer()->get('doctrine')->getConnection();
    }

    /**
     * @param $type
     * @param InputInterface $input
     * @return array
     */
    private function getArgs($type, InputInterface $input)
    {
        switch ($type) {
            case 'xls':
            case 'xmlexcel':
                return [
                    !$input->getOption('no-headers'),
                ];
                break;
            case 'xml':
                return [
                    $input->getOption('main_element'),
                    $input->getOption('child_element'),
                ];
                break;
            case 'csv':
                return [
                    $input->getOption('delimiter'),
                    $input->getOption('enclosure'),
                    $input->getOption('escape'),
                    !$input->getOption('no-headers'),
                    !$input->getOption('no-bom'),
                    $input->getOption('terminate'),
                ];
                break;
            case 'twig':
                return [
                    $this->getTwig(),
                    $input->getOption('template'),
                    !$input->getOption('no-eol'),
                ];
                break;
            default:
                return [];

        }
    }

    /**
     * @param InputInterface $input
     * @return string
     */
    private function getType(InputInterface $input)
    {
        return strtolower($input->getParameterOption('--type', $input->getParameterOption('-t', 'xml')));
    }

    /**
     * @return \Twig_Environment
     */
    private function getTwig()
    {
        $twig = $this->getContainer()->get('twig');

        $twig->addFilter(
            new \Twig_SimpleFilter(
                'sql_escape',
                function($line) {
                    return $this->getContainer()->get('doctrine.dbal.default_connection')->quote($line);
                }
            )
        );

        $twig->addFunction(
            new \Twig_SimpleFunction(
                'print',
                function(\Twig_Environment $env, $context) {
                    $globals = array_keys($env->getGlobals());
                    return print_r(
                        array_filter(
                            $context,
                            function($key) use ($globals) {
                                return !in_array($key, $globals);
                            },
                            ARRAY_FILTER_USE_KEY
                        ),
                        true
                    );
                },
                [
                    'needs_environment' => true,
                    'needs_context' => true,
                ]
            )
        );

        return $twig;
    }

    /**
     * @inheritdoc
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        switch ($this->getType($input)) {
            case 'xls':
            case 'xmlexcel':
                $this->getDefinition()->addOptions([
                    new InputOption('no-headers', null, InputOption::VALUE_NONE)
                ]);
                break;
            case 'xml':
                $this->getDefinition()->addOptions([
                    new InputOption('main_element', null, InputOption::VALUE_REQUIRED, '', 'datas'),
                    new InputOption('child_element', null, InputOption::VALUE_REQUIRED, '', 'data'),
                ]);
                break;
            case 'csv':
                $this->getDefinition()->addOptions([
                    new InputOption('delimiter', null, InputOption::VALUE_REQUIRED, '', ','),
                    new InputOption('enclosure', null, InputOption::VALUE_REQUIRED, '', '"'),
                    new InputOption('escape', null, InputOption::VALUE_REQUIRED, '', '\\'),
                    new InputOption('no-headers', null, InputOption::VALUE_NONE),
                    new InputOption('no-bom', null, InputOption::VALUE_NONE),
                    new InputOption('terminate', null, InputOption::VALUE_REQUIRED, '', "\n"),
                ]);
                break;
            case 'twig':
                $this->getDefinition()->addOptions([
                    new InputOption('template', null, InputOption::VALUE_REQUIRED, '', '{{ print()|raw }}'),
                    new InputOption('no-eol', null, InputOption::VALUE_NONE),
                ]);
                break;
        }
        return parent::run($input, $output);
    }

    /**
     * @inheritdoc
     */
    public function getHelp()
    {
        return <<<EOH
This command can be used to create an export from an raw query and write it to the given output in the selected format.

Based on the selected export type there will e some extra options to configure the the exporter. 
   
Options:
    
<comment>XLS</comment>
    <info>no-headers</info>      Disable the headers in the xls <comment>[default: true]</comment>
        
<comment>XML</comment>
    <info>main_element</info>    set the xml element name where all the elements are wrapped in <comment>[default: "datas"]</comment> 
    <info>child_element</info>   set the xml element name for each element <comment>[default: "data"]</comment>
        
<comment>XMLEXCEL</comment>
    <info>no-headers</info>      Disable the headers in the xls <comment>[default: true]</comment>
    
<comment>CSV</comment>
    <info>delimiter</info>       Set the delimiter <comment>[default: ","]</comment>
    <info>enclosure</info>       Set the enclosure <comment>[default: "]</comment>
    <info>no-headers</info>      Disable the headers in the csv <comment>[default: true]</comment>
    <info>no-bom</info>          Disable the bom (utf8) headers <comment>[default: true]</comment>
    <info>terminate</info>       Set the terminate string <comment>[default: "\\n"]</comment>
    
<comment>TWIG</comment>
    <info>template</info>        Set the output template <comment>[default: "{{ print()|raw }}"]</comment> 
    <info>no-eol</info>          Disable the extra EOL character after each row
   
    the twig instance, will get two extra filter: 
        print       this will print the result row
        sql_escape  this will do an sql escape in the input
    
    to us a template for printing the rows you could use the internal include from twig:       
        
         --template "{% include '\$PWD/include.tmpl' %}"
        
    An example to create update SQL based on sql results: 
    
    php app/console zicht:export:sql \
        --env=development \
        --type twig \
        --template "UPDATE form_config SET opt_in = {{opt_in}}, form_note = {{form_note|sql_escape|raw}} WHERE id = {{id}};" \
    'SELECT id, opt_in, form_note FROM form_config limit 2;'
    
EOH;
    }
}
