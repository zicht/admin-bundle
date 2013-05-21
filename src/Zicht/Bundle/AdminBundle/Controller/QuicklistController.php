<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class QuicklistController extends Controller
{
    /**
     * @Template
     * @Route("quick-list")
     */
    function quicklistAction(Request $request)
    {
        $quicklist = $this->get('zicht_admin.quicklist');
        if ($request->get('repo') && $request->get('pattern')) {
            return new JsonResponse($quicklist->getResults($request->get('repo'), $request->get('pattern')));
        }

        return array(
            'repos' => $quicklist->getRepositoryConfigs()
        );
    }
}