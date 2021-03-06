<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormBuilderInterface;

use Zicht\Bundle\AdminBundle\DataTransformer\MultipleTransformer;
use Zicht\Bundle\AdminBundle\DataTransformer\ClassTransformer;
use Zicht\Bundle\AdminBundle\DataTransformer\NoneTransformer;
use Zicht\Bundle\AdminBundle\Service\Quicklist;

/**
 * A type utilizing autocomplete functionality within the CMS.
 */
class AutocompleteType extends AbstractType
{
    /**
     * @var Quicklist
     */
    private $quicklist;

    /**
     * @param Quicklist $quicklist
     */
    public function __construct(Quicklist $quicklist)
    {
        $this->quicklist = $quicklist;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
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
     * {@inheritDoc}
     */
    public function getParent()
    {
        return TextType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['repo'])
            ->setDefaults(
                [
                    'attr' => ['placeholder' => 'zicht_quicklist.autocomplete_type.search_placeholder'],
                    'multiple' => false,
                    'translation_domain' => 'admin',
                    'transformer' => 'auto',
                    'route' => 'zicht_admin_quicklist_quicklist',
                    'route_params' => []
                ]
            );
    }

    /**
     * {@inheritDoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['route'] = $options['route'];
        $view->vars['route_params'] = $options['route_params'] + [
                'repo' => $options['repo'],
            ];
        $view->vars['multiple'] = $options['multiple'];

        if ($options['multiple']) {
            $view->vars['full_name'] = $view->vars['full_name'] . '[]';
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix()
    {
        return 'zicht_quicklist_autocomplete';
    }
}
