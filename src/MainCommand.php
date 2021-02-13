<?php

// src/Command/CreateUserCommand.php

namespace Gingdev\Tools;

use Facebook\FacebookSession;
use Gingdev\Facebook\Facebook;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MainCommand extends Command
{
    protected static $defaultName = 'default';
    protected $errorList          = [];
    protected $fb;
    protected $task;

    protected function configure()
    {
        FacebookSession::enableAppSecretProof(false);
        $this->fb   = new Facebook('default');
        $this->task = new Tanglikefree();
    }

    protected function runTask(OutputInterface $output)
    {
        $output->writeln('Đang lấy dữ liệu...');
        $posts   = $this->task->getPostTask();
        $success = 0;
        foreach ($posts as $post) {
            if (in_array($post, $this->errorList, true)) {
                continue;
            }
            $output->writeln('ID: '.$post);
            try {
                $this->fb->request('POST', '/'.$post.'/likes')->execute();
                $result = $this->task->receiveCoins($post);
                $output->writeln('<'.($result->error ? 'comment' : 'info').'>'.$result->messages.'</>');
                ++$success;
            } catch (\Throwable $e) {
                $output->writeln('<error>'.$e->getMessage().'</>');
                $this->errorList[] = $post;
            }
        }
        if (0 == $success) {
            $output->writeln('<comment>Đã hết nhiệm vụ</>');

            return;
        }

        return $this->runTask($output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('TangLikeFree Tool v1.0');

        $this->runTask($output);

        return Command::SUCCESS;

        // return Command::FAILURE;
    }
}