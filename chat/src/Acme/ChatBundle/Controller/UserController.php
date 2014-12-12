<?php
namespace Acme\ChatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends Controller
{
    /**
     * @Route("/chatjoin", name="_api_chat_login")
     * @Template()
     * @Method("POST")
     */
    public function loginAction(Request $request){

        $username = $request->request->get('username', 'anonymous');

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
            'SELECT u.id, u.user_name FROM AcmeChatBundle:User as u
            WHERE u.user_name = :username
            ORDER BY u.id ASC'
        )->setParameter('username', $username);

        $registered_user = $query->getResult();

        if (!$registered_user) {

        }

        $response = new JsonResponse();
        $response->setData(array('status' => 'true'));

        return $response;

    }
}