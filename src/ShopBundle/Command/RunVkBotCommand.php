<?php

namespace ShopBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunVkBotCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('RunVk')
            ->setDescription('...')
            ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $c = $this->getContainer();
        $botService = $c->get('bot');
        $output->writeln('Bot VK success run ');
        $botService->listen();

        $output->writeln('Bot die ');
    }
}
