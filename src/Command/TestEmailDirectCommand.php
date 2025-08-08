<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(
    name: 'app:test-email-direct',
    description: 'Test email sending directly without messenger',
)]
class TestEmailDirectCommand extends Command
{
    public function __construct(
        private MailerInterface $mailer
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $io->note('Sending test email directly via SMTP...');
            
            $email = (new Email())
                ->from('marwenazouzi44@gmail.com')
                ->to('efsaminoschabbeh@gmail.com')
                ->subject('Direct SMTP Test Email')
                ->text('This is a direct SMTP test email sent at ' . date('Y-m-d H:i:s'))
                ->html('<p>This is a <strong>direct SMTP test email</strong> sent at ' . date('Y-m-d H:i:s') . '</p>');

            $this->mailer->send($email);

            $io->success('Direct SMTP test email sent successfully!');
            $io->note('Check your email inbox and spam folder for the test email.');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to send direct SMTP test email: ' . $e->getMessage());
            $io->note('This indicates an SMTP configuration issue.');
            
            return Command::FAILURE;
        }
    }
} 