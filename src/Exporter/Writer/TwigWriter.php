<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Exporter\Writer;

use Sonata\Exporter\Writer\TypedWriterInterface;
use Twig\Environment;
use Twig\Template;

class TwigWriter implements TypedWriterInterface
{
    /** @var Environment */
    protected $twig;

    /** @var resource */
    protected $file;

    /** @var string */
    protected $template;

    /** @var string */
    protected $filename;

    /** @var bool */
    protected $addEol;

    /**
     * @param string $file
     * @param \Twig_Environment $twig
     * @param string $template
     * @param bool $addEol
     */
    public function __construct($file, Environment $twig, $template, $addEol = true)
    {
        $this->filename = $file;
        $this->template = $template;
        $this->twig = $twig;
        $this->addEol = $addEol;
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultMimeType(): string
    {
        return 'text/plain';
    }

    /**
     * {@inheritDoc}
     */
    public function getFormat(): string
    {
        return 'text';
    }

    /**
     * {@inheritDoc}
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
        /** @var Template $template */
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
     * {@inheritDoc}
     */
    public function close()
    {
        fclose($this->file);
    }
}
