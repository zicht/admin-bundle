<?php
/**
 * @author    Philip Bergman <philip@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */
namespace Zicht\Bundle\AdminBundle\Exporter\Writer;

use Exporter\Writer\TypedWriterInterface;

/**
 * Class TwigWriter
 *
 * @package Zicht\Bundle\AdminBundle\Exporter\Writer
 */
class TwigWriter implements TypedWriterInterface
{
    /** @var \Twig_Environment  */
    protected $twig;
    /** @var resource  */
    protected $file;
    /** @var string */
    protected $template;
    /** @var string  */
    protected $filename;
    /** @var bool  */
    protected $addEol;

    /**
     * TwigWriter constructor.
     * @param string $file
     * @param \Twig_Environment $twig
     * @param string $template
     * @param bool $addEol
     */
    public function __construct($file, \Twig_Environment $twig, $template, $addEol = true)
    {
        $this->filename = $file;
        $this->template = $template;
        $this->twig = $twig;
        $this->addEol = $addEol;
    }


    /**
     * @inheritdoc
     */
    public function getDefaultMimeType()
    {
        return 'text/plain';
    }

    /**
     * @inheritdoc
     */
    public function getFormat()
    {
        return 'text';
    }

    /**
     * @inheritdoc
     */
    public function open()
    {
        $this->file = fopen($this->filename, 'w', false);
    }

    /**
     * @param array $data
     */
    public function write(array $data)
    {
        /** @var \Twig_Template $template */
        static $template;

        if (!$template) {
            $template = $this->twig->createTemplate($this->template);
        }

        fwrite($this->file, $template->render($data));

        if ($this->addEol) {
            fwrite($this->file, PHP_EOL);
        }
    }

    /**
     * @inheritdoc
     */
    public function close()
    {
        fclose($this->file);
    }
}
