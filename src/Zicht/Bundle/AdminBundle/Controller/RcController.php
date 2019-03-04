<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Class RcController
 *
 * @package Zicht\Bundle\AdminBundle\Controller
 */
class RcController
{
    /**
     * RcController constructor.
     *
     * @param array $configs
     */
    public function __construct($configs)
    {
        $this->configs = $configs;
    }

    /**
     * Renders all RC controls
     *
     * @return array
     *
     * @Template("@ZichtAdmin/Rc/controls.html.twig")
     */
    public function controlsAction()
    {
        return [
            'configs' => $this->configs
        ];
    }
}
