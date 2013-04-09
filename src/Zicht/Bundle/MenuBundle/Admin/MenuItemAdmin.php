<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\MenuBundle\Admin;

use \Sonata\AdminBundle\Form\FormMapper;
use \Zicht\Bundle\MenuBundle\Entity;
use \Zicht\Bundle\FrameworkExtraBundle\Admin\TreeAdmin;

class MenuItemAdmin extends TreeAdmin
{
    public function configureFormFields(FormMapper $formMapper)
    {
        parent::configureFormFields($formMapper);
        $formMapper
            ->add('path', 'zicht_url')
            ->add('name')
        ;

        $formMapper->setHelps(array(
            'name' => 'Name can be used as an url reference in code'
        ));
    }
}