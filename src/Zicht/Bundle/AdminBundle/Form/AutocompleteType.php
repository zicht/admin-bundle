<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\AdminBundle\Form;
use Symfony\Component\Form\AbstractType;
use Zicht\Bundle\AdminBundle\Service\Quicklist;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormBuilderInterface;

class ClassTransformer implements \Symfony\Component\Form\DataTransformerInterface
{
    function __construct(Quicklist $lister, $repo)
    {
        $this->lister = $lister;
        $this->repo = $repo;
    }

    public function transform($value)
    {
        return array(
            'id' => (null !== $value ? $value->getId() : null),
            'value' => (null !== $value ? (string) $value : null)
        );
    }

    public function reverseTransform($value)
    {
        return $this->lister->getOne($this->repo, $value);
    }
}


class AutocompleteType extends AbstractType
{
    function __construct(Quicklist $quicklist)
    {
        $this->quicklist = $quicklist;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->addViewTransformer(new ClassTransformer($this->quicklist, $options['repo']));
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