<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\AdminBundle\Form;

use \Symfony\Component\Form\AbstractType;
use \Symfony\Component\OptionsResolver\OptionsResolverInterface;
use \Symfony\Component\Form\FormInterface;
use \Symfony\Component\Form\FormView;
use \Symfony\Component\Form\FormBuilderInterface;

use \Zicht\Bundle\AdminBundle\DataTransformer\MultipleTransformer;
use \Zicht\Bundle\AdminBundle\DataTransformer\ClassTransformer;
use Zicht\Bundle\AdminBundle\DataTransformer\NoneTransformer;
use \Zicht\Bundle\AdminBundle\Service\Quicklist;

/**
 * A type utilizing autocomplete functionality within the CMS.
 */
class AutocompleteType extends AbstractType
{
    /**
     * Constructor.
     *
     * @param \Zicht\Bundle\AdminBundle\Service\Quicklist $quicklist
     */
    public function __construct(Quicklist $quicklist)
    {
        $this->quicklist = $quicklist;
    }


    /**
     * @{inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $transformerStrategy = $options['transformer'];
        if ($transformerStrategy === 'auto') {
            $transformerStrategy = $options['multiple'] ? 'multiple' : 'class';
        }

        switch ($transformerStrategy) {
            case 'class':
                $builder->addViewTransformer(new ClassTransformer($this->quicklist, $options['repo']));
                break;

            case 'none':
                $builder->addViewTransformer(new NoneTransformer($this->quicklist, $options['repo']));
                break;

            case 'multiple':
                $builder->addViewTransformer(new MultipleTransformer(new ClassTransformer($this->quicklist, $options['repo'])));
                break;

            case 'multiple_none':
                $builder->addViewTransformer(new MultipleTransformer(new NoneTransformer($this->quicklist, $options['repo'])));
                break;
        }
    }


    /**
     * @{inheritDoc}
     */
    public function getParent()
    {
        return 'text';
    }


    /**
     * @{inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(array('repo'))
            ->setDefaults(array(
                'multiple' => false,
                'transformer' => 'auto',
                'route' => 'zicht_admin_quicklist_quicklist',
                'route_params' => array()
            ));
    }


    /**
     * @{inheritDoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['route'] = $options['route'];
        $view->vars['route_params'] = $options['route_params'] + array(
            'repo' => $options['repo']
        );
        $view->vars['multiple'] = $options['multiple'];

        if ($options['multiple']) {
            $view->vars['full_name'] = $view->vars['full_name'] . '[]';
        }
    }


    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'zicht_quicklist_autocomplete';
    }
}