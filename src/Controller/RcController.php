<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class RcController
{
    /**
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
            'configs' => $this->configs,
        ];
    }
}
