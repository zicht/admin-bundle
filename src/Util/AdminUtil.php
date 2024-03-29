<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Util;

use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Exception\LogicException;

/**
 * @template T of object
 * @method $this tab($name, array $options = array())
 * @method $this with($name, array $options = array())
 * @method $this end()
 * @method $this remove($key)
 */
final class AdminUtil
{
    /** @var FormMapper */
    protected $formMapper = null;

    /** @var string|null */
    protected $helpPrefix;

    /** @var bool */
    protected $addHelp = false;

    /**
     * Allows to reorder Tabs
     *
     * Need the formMapper since the used methods to set the tabs
     * are protected in the original Sonata implementation
     *
     * @template Tr of object
     * @param FormMapper<Tr> $formMapper
     */
    public static function reorderTabs(FormMapper $formMapper, array $tabOrder): void
    {
        $tabsOriginal = $formMapper->getAdmin()->getFormTabs();

        // filter out tabs that doesn't exist (yet)
        $tabOrder = array_filter(
            $tabOrder,
            function ($key) use ($tabsOriginal) {
                return array_key_exists($key, $tabsOriginal);
            }
        );

        $tabs = array_merge(array_flip($tabOrder), $tabsOriginal);
        $formMapper->getAdmin()->setFormTabs($tabs);
    }

    /**
     * Start a mapping of fields on the given formMapper
     *
     * @param FormMapper<T> $formMapper
     * @param string|null $helpPrefix
     * @return $this
     * @deprecated since v7.0.1. You should set help text directly in the field definition
     */
    public function map(FormMapper $formMapper, $helpPrefix = null)
    {
        $this->formMapper = $formMapper;
        $this->helpPrefix = $helpPrefix;
        return $this;
    }

    /**
     * Toggle wether or not to add help text
     *
     * @return $this
     * @deprecated since v7.0.1. You should set help text directly in the field definition
     */
    public function toggleHelp()
    {
        $this->addHelp = !$this->addHelp;
        return $this;
    }

    /**
     * Add a field to the given formMapper
     *
     * @param string $name
     * @param string|null $type
     * @return $this
     * @deprecated since v7.0.1. You should set help text directly in the field definition
     */
    public function add($name, $type = null, array $options = [], array $fieldDescriptionOptions = [])
    {
        if (null === $this->formMapper) {
            throw new LogicException('No FormMapper to add fields to, please make sure you start with AdminUtil->map');
        }

        if ($this->addHelp && !isset($options['help'])) {
            $options['help'] = 'help' . (null !== $this->helpPrefix ? sprintf('.%s', $this->helpPrefix) : '') . '.' . $name;
        }

        $this->formMapper->add($name, $type, $options, $fieldDescriptionOptions);

        return $this;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return $this
     * @deprecated since v7.0.1. You should set help text directly in the field definition
     */
    public function __call($name, $arguments = [])
    {
        if (method_exists($this, $name)) {
            call_user_func_array([$this, $name], $arguments);
        } else {
            if (null === $this->formMapper) {
                throw new LogicException('No FormMapper to add fields to, please make sure you start with AdminUtil->map');
            }
            call_user_func_array([$this->formMapper, $name], $arguments);
        }
        return $this;
    }
}
