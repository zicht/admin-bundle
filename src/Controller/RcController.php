<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as TwigEnvironment;

class RcController
{
    /** @var TwigEnvironment */
    private $twig;

    /**
     * @param array $configs
     */
    public function __construct(TwigEnvironment $twig, $configs)
    {
        $this->twig = $twig;
        $this->configs = $configs;
    }

    /** Renders all RC controls */
    public function controlsAction(): Response
    {
        return $this->twig->render('@ZichtAdmin/Rc/controls.html.twig', ['configs' => $this->configs]);
    }
}
