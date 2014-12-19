<?php
namespace Acme\ChatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Acme\ChatBundle\Entity\User;
//use Symfony\Component\HttpFoundation\Session\Session;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations as Rest;

class UserController extends Controller
{
    /**
     * @Route("/chatjoin", name="_api_chat_login")
     * @Template()
     * @Method("POST")
     */
    public function loginAction(Request $request){

        $username = $request->request->get('username', '');
        $user_allowed = false;
        $message = "";

        $session = $this->get('request')->getSession();

        $em = $this->getDoctrine()->getManager();

        if (empty($username)) {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException("Bad request. You did not specify a username.");
        }

        /** @var User[] $registered_user */
        $registered_user = $em->getRepository('AcmeChatBundle:User')->findUserIdByName($username);

        if (!count($registered_user)) {

            $new_user = new User();
            $new_user->setUserName($username);
            $new_user->setRegistered(time());

            $em->persist($new_user);
            $em->flush();

            $message = 'New user added';
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

        $session = $this->get('request')->getSession();

        $uname_in_session = $session->get('chat_symjs_uname');

        $em = $this->getDoctrine()->getManager();

        $users_array = $em->getRepository('AcmeChatBundle:User')->findAllUsersOrderByName();

        $response = new JsonResponse();
        $response->setData(array('users' => $users_array));

        return $response;
    }
}