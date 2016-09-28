<?php

namespace Wassa\MPSBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Wassa\MPS\PushData;

class PushCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('wassa:mps:send-push')
            ->setDescription('Send push notification');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $customProperty = array(
            "hello" => "world",
            "answer" => 42,
        );
        $title = "Push test";
        $body = "push de test";

        do {
            // Add a token
            $addTokenQuestion = new ChoiceQuestion(
                'Which device do you want to target ?',
                array(1 => 'ios', 2 => 'android'),
                1
            );
            $addTokenQuestion->setErrorMessage('Choice is invalid');
            $response = $helper->ask($input, $output, $addTokenQuestion);

            $tokenQuestion = new Question('Enter the device token / registration id : ');
            $token = $helper->ask($input, $output, $tokenQuestion);

            $pushData = new PushData();
            switch ($response) {
                case 'ios':
                    $pushData->setApnsCategory("debug");
                    $pushData->setApnsBadge(1);
                    $customProperty = $this->getContainer()->get('serializer')->serialize($customProperty, 'json');
                    $pushData->setApnsCustomProperties($customProperty);
                    $pushData->setApnsSound("default");
                    $pushData->setApnsText($body);
                    break;
                case 'android':
                    $payload = array(
                        'notification' => array(
                            'title' => $title,
                            'body' => $body,
                            'icon' => 'icon'
                        ),
                        'data' => $customProperty,
                    );
                    $pushData->setGcmPayloadData($payload);
                    break;
            }

            // Send push
            $output->writeln("");
            $sendQuestion = new ConfirmationQuestion("Sending a push message to $token, confirm push submission (y/n) ? ", true);
            if ($helper->ask($input, $output, $sendQuestion)) {
                $this->getContainer()->get('wassa_mps')->sendToMultipleDevices($pushData, array($token));
                $output->writeln("Push send to " . $token);
            }

            // Ask for another token
            $continueQuestion = new ConfirmationQuestion('Do you want to send another push (y/n) ? ', true);
        } while ($helper->ask($input, $output, $continueQuestion));

    }
}