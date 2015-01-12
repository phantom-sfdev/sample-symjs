<?php
namespace Acme\ChatBundle\Command;

use Doctrine\ORM\EntityManager;
use Acme\ChatBundle\Entity\User;
use Acme\ChatBundle\Entity\ChatMessages;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateChatMessageCommand extends ContainerAwareCommand
{
    public function configure(){

        $this->setName('acmechat:send:chatmessage')
            ->addArgument('username', InputArgument::REQUIRED, 'Registered username who send message into chat')
            ->addArgument('message', InputArgument::REQUIRED, 'Message that you want send into Chat system.')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command send "message" into chat system:

<info>php %command.full_name%</info> Anonymous "Test message from user"

<info>Please wrap the message in quotation marks as you can see in example above.</info>
EOF
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     */
    public function execute(InputInterface $input, OutputInterface $output){

        $out_msg = '';
        $msg_args = array();

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();
        $user_repository = $em->getRepository('AcmeChatBundle:User');

        /** @var User $user_entity */
        $user_entity = $user_repository->findOneBy(array('user_name' => $input->getArgument('username')));

        if (!$user_entity) {
            $out_msg = PHP_EOL.'<error>Sorry, but username <%s> is not registered in our Chat system. Please register new user and after that you can send messages from command line and web interface.</error>'.PHP_EOL;
            $msg_args[0] = $input->getArgument('username');
        } else {

            $new_message = new ChatMessages();
            $new_message->setMsg($input->getArgument('message'));
            $new_message->setUser($user_entity);
            $new_message->setPosted(time());

            try{
                $em->persist($new_message);
                $em->flush();

                $out_msg = PHP_EOL.'<error>Your message was sent successfully.</error>'.PHP_EOL;
            } catch(\Exception $e){
                $out_msg = PHP_EOL.'<error>Oops, we have error exception when try save message. Error message: %s; error code: %s</error>'.PHP_EOL;
                $msg_args[0] = $e->getMessage();
                $msg_args[1] = $e->getCode();
            }
        }

        $output->writeln(sprintf($out_msg, implode(', ', $msg_args)));
    }
}