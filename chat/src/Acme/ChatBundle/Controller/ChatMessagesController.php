<?php
namespace Acme\ChatBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Acme\ChatBundle\Entity\ChatMessages;
//use Symfony\Component\HttpFoundation\Session\Session;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations as Rest;

class ChatMessagesController extends Controller
{
    /**
     * @Route("/loadchat", name="_api_chat_load_chat")
     * @Template()
     * @Method("GET")
     *
     *
     * @ApiDoc(
     * resource=false,
     * description="Load chat messages by a time",
     * requirements={
     *      {"name"="min", "dataType"="integer", "requirement"="\d+", "description"="Number of minutes to load messages"}
     * },
     * statusCodes = {
     *      200="Ok",
     *      400="Bad request",
     *      404="Not Found",
     *      500="Server Error"
     * }
     * )
     *
     */
    public function loadChatAction(Request $request){

        $minLoad = $request->query->get('min', 10);
        $session = $this->get('request')->getSession();

        $current_timestamp = time();

        $startDate = $current_timestamp - ($minLoad * 60);
        //$endDate = $current_timestamp;

        $chat_loaded_messages = array();

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var ChatMessages[] $messages */
        $messages = $em->getRepository("AcmeChatBundle:ChatMessages")->getMessagesByTime($startDate);
        $session->set('last_successful_msg_loaded_time', $current_timestamp);

        if (count($messages)){
            foreach($messages as $message_obj){
                $chat_loaded_messages[] = array(
                    'user_id' => $message_obj->getUser()->getId(),
                    'user_name' => $message_obj->getUser()->getUserName(),
                    'message' => $message_obj->getMsg(),
                    'posted_date' => $message_obj->getPosted(),
                );
            }


        }

        $response = new JsonResponse();
        $response->setData(array('loaded_messages' => $chat_loaded_messages));

        return $response;
    }

    /**
     * @Route("/postmsg", name="_api_chat_post_message")
     * @Template()
     * @Method("POST")
     *
     * @ApiDoc(
     * resource=false,
     * description="Post new message into the chat"
     * )
     *
     */
    public function postMessageAction(Request $request){

        $session = $this->get('request')->getSession();
        $errors = array();
        $message_posted = true;

        $message = $request->request->get('msg', null);
        $uname_in_session = $session->get('chat_symjs_uname');

        $em = $this->getDoctrine()->getManager();

        if (!isset($message)) throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException("Bad request. You did not specify a message.");
        if (!isset($uname_in_session)) throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException("Bad request. Can't find username in Session.");

        $user_entity = $em->getRepository("AcmeChatBundle:User")->findOneBy(array('user_name' => $uname_in_session));

        if (!$user_entity) throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Could not found username in our system");


        /*if (!count($errors)) {*/

            $new_message  = new ChatMessages();
            $new_message->setMsg($message);
            $new_message->setUser($user_entity);
            $new_message->setPosted(time());

            $em->persist($new_message);
            $em->flush();

        /*    $statusCode = 200;
            $statusMsg = "Ok";
        } else {
            $message_posted = false;
            $statusCode = 400;
            $statusMsg = "Bad request";
        }*/

        $response = new JsonResponse();
        $response->setData(array('message_posted' => $message_posted));

        return $response;

    }

    /**
     * @Route("/getupdate", name="_api_chat_get_chat_update", defaults={"timestamp" = null})
     * @Route("/g", name="_api_chat_get_update_alias", defaults={"timestamp" = null})
     * @Template()
     * @Method("GET")
     *
     * @ApiDoc(
     *  resource=false,
     *  description="Update chat messages",
     *  requirements={
     *      {"name"="timestamp", "dataType"="integer", "requirement"="\d+", "description"="Latest timestamp when successful GetUpdate or LoadChat has been performed"}
     *  }
     * )
     */
    public function getUpdateAction($timestamp){

        $new_messages = array();
        $startDate = null;

        $errors = array();

        $session = $this->get('request')->getSession();

        if (isset($timestamp)) {
            $startDate = $timestamp;
        } else {
            $startDate = $session->get('last_successful_msg_loaded_time');
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var ChatMessages[] $messages */
        $messages = $em->getRepository("AcmeChatBundle:ChatMessages")->getMessagesByTime($startDate);
        $session->set('last_successful_msg_loaded_time', time());

        if (count($messages)){
            foreach($messages as $message_obj){
                $new_messages[] = array(
                    'user_id' => $message_obj->getUser()->getId(),
                    'user_name' => $message_obj->getUser()->getUserName(),
                    'message' => $message_obj->getMsg(),
                    'posted_date' => $message_obj->getPosted(),
                );
            }
        }

        $response = new JsonResponse();
        $response->setData(array('loaded_messages' => $new_messages));

        return $response;
    }
}