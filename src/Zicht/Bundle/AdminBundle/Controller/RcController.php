<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\AdminBundle\Controller;

class RcController
{
    public function __construct($configs)
    {
        $this->configs = $configs;
    }

    /**
     * Renders all RC controls
     *
     * @return array
     *
     * @Template
     */
    public function controlsAction()
    {
        return [
            'configs' => $this->configs
        ];
    }
}