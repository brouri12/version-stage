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
    name: 'app:test-mailjet',
    description: 'Test Mailjet email configuration',
)]
class TestMailjetCommand extends Command
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
            $io->note('Sending test email via Mailjet...');
            
            $email = (new Email())
                ->from($_ENV['MAILJET_SENDER_EMAIL'])
                ->to('efsaminoschabbeh@gmail.com')
                ->subject('Mailjet Test Email')
                ->text('This is a test email sent via Mailjet at ' . date('Y-m-d H:i:s'))
                ->html('<p>This is a <strong>test email</strong> sent via Mailjet at ' . date('Y-m-d H:i:s') . '</p>');

            $this->mailer->send($email);

            $io->success('Mailjet test email sent successfully!');
            $io->note('Check your email inbox and spam folder for the test email.');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to send Mailjet test email: ' . $e->getMessage());
            $io->note('This indicates a Mailjet configuration issue.');
            
            return Command::FAILURE;
        }
    }
}