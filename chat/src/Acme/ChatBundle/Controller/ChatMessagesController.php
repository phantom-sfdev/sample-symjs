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
use Symfony\Component\HttpFoundation\Session\Session;
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

        $endDate = time(); //current timestamp
        $startDate = $endDate - ($minLoad * 60);

        $chat_loaded_messages = array();

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var ChatMessages[] $messages_repo */
        $messages = $em->getRepository("AcmeChatBundle:ChatMessages")->getMessagesByTime($startDate, $endDate);
        var_dump(count($messages));
        foreach($messages as $message_obj){
            var_dump($message_obj->getMsg());
        }
        //var_dump($messages_repo);

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
    function postMessageAction(Request $request){

        $posted_time = time();
        $session = new Session();
        $errors = array();
        $message_posted = true;

        $message = $request->request->get('msg', null);
        $uname_in_session = $session->get('chat_symjs_uname');

        $em = $this->getDoctrine()->getManager();

        if (!isset($message)) $errors[] = "No message";
        if (!isset($uname_in_session)) {
            $errors[] = "No username in session";
        } else {
            $user_entity = $em->getRepository("AcmeChatBundle:User")->findOneBy(array('user_name' => $uname_in_session));

            if (!$user_entity) $errors[] = "Can't find User by username";
        }

        if (!count($errors)) {

            $new_message  = new ChatMessages();
            $new_message->setMsg($message);
            $new_message->setUser($user_entity);
            $new_message->setPosted(time());

            $em->persist($new_message);
            $em->flush();

            $statusCode = 200;
            $statusMsg = "Ok";
        } else {
            $message_posted = false;
            $statusCode = 400;
            $statusMsg = "Bad request";
        }

        $response = new JsonResponse();
        $response->setData(array('message_posted' => $message_posted, "errors" => $errors, "total_errors" => count($errors)));

        return $response;

    }
}