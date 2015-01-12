<?php
namespace Acme\ChatBundle\Command;

use Doctrine\ORM\EntityManager;
use Acme\ChatBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use symfony\Component\Console\Output\OutputInterface;

class CreateUserCommand extends ContainerAwareCommand
{
    protected function configure(){

        $this->setName('acmechat:create:user')
            ->setDescription('Create user for chat')
            ->addArgument('name', InputArgument::REQUIRED, "New username.")
            ->setHelp(<<<EOF
The <info>%command.name%</info> command create new user in chat:

<info>php %command.full_name%</info> Anonymous
EOF
        );

    }

    protected function execute(InputInterface $input, OutputInterface $output){

        $out_msg = '';
        $msg_args = array();

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();
        $user_repository = $em->getRepository('AcmeChatBundle:User');

        /** @var User $user */
        $user = $user_repository->findUserIdByName($input->getArgument('name'));

        if (count($user)) {
            $out_msg = PHP_EOL.'<error>Error: Username <%s> is already in Chat system. Please try other name.</error>'.PHP_EOL;
            $msg_args[0] = $input->getArgument('name');
        } else {

            $new_user = new User();
            $new_user->setUserName($input->getArgument('name'));
            $new_user->setRegistered(time());

            try {
                $em->persist($new_user);
                $em->flush();

                $out_msg = PHP_EOL.'New username <%s> was added successfully to Chat system!'.PHP_EOL;
                $msg_args[0] = $input->getArgument('name');
            } catch (\Exception $e) {
                $out_msg = PHP_EOL.'<error>Sorry, but we can\'t save username. Error message: %s; error code: %s</error>'.PHP_EOL;
                $msg_args[0] = $e->getMessage();
                $msg_args[1] = $e->getCode();
            }

        }

        $output->writeln(sprintf($out_msg, implode(', ', $msg_args)));
    }
}