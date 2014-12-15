<?php
namespace Acme\ChatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Acme\ChatBundle\Entity\User;
use Symfony\Component\HttpFoundation\Session\Session;

class UserController extends Controller
{
    /**
     * @Route("/chatjoin", name="_api_chat_login")
     * @Template()
     * @Method("POST")
     */
    public function loginAction(Request $request){

        $username = $request->request->get('username', 'anonymous');
        $user_allowed = false;
        $message = "";

        $session = new Session();
        $session->start();

        $em = $this->getDoctrine()->getManager();

        /*$query = $em->createQuery(
            'SELECT u.id, u.user_name FROM AcmeChatBundle:User as u
            WHERE u.user_name = :username
            ORDER BY u.id ASC'
        )->setParameter('username', $username);

        $registered_user = $query->getResult();*/

        //$registered_user = $em->getRepository('AcmeChatBundle:User')->findAllUsersOrderById();

        $registered_user = $em->getRepository('AcmeChatBundle:User')->findUserByName($username);

        if (!count($registered_user)) {

            $new_user = new User();
            $new_user->setUserName($username);
            $new_user->setRegistered(time());

            $em->persist($new_user);
            $em->flush();

            $message = 'New user';
            $user_allowed = true;
            $session->set('chat_symjs_uname', $username);
        }

        $response = new JsonResponse();
        $response->setData(array('user_allowed' => $user_allowed, 'username' => $username, 'message' => $message));

        return $response;
    }

    /**
     * @Route("/chatusers", name="_api_chat_users_list")
     * @Template()
     * @Method("GET")
     */
    public function listUsersAction(){

        $session = new Session();

        $uname_in_session = $session->get('chat_symjs_uname');

        $response = new JsonResponse();
        $response->setData(array());

        return $response;
    }
}