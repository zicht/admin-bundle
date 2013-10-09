<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormBuilderInterface;

use Zicht\Bundle\AdminBundle\DataTransformer\MultipleTransformer;
use Zicht\Bundle\AdminBundle\DataTransformer\ClassTransformer;
use Zicht\Bundle\AdminBundle\Service\Quicklist;

class AutocompleteType extends AbstractType
{
    function __construct(Quicklist $quicklist)
    {
        $this->quicklist = $quicklist;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        if ($options['multiple']) {
            $builder->addViewTransformer(new MultipleTransformer(
                new ClassTransformer($this->quicklist, $options['repo'])
            ));
        } else {
            $builder->addViewTransformer(new ClassTransformer($this->quicklist, $options['repo']));
        }
    }

    public function getParent()
    {
        return 'text';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(array('repo'))
            ->setDefaults(array(
                'multiple' => false,
                'route' => 'zicht_admin_quicklist_quicklist',
                'route_params' => array()
            ));
    }


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