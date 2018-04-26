<?php

namespace ShopBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunTelegramBotCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('RunTelegram')
            ->setDescription('Telegram bot for customer')
            ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $c = $this->getContainer();
        $token = $c->getParameter('telegram_bot');
        $botService = $c->get('bot');
        $output->writeln('Bot Telegram success run ');
        $botService->listen($token);

        $output->writeln('Bot Telegram die ');
    }
}
