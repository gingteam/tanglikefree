<?php

require __DIR__.'/vendor/autoload.php';

use Gingdev\Facebook;
use Gingdev\Tools\Tanglikefree;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\Console\Style\SymfonyStyle;

(new SingleCommandApplication())
    ->setName('Tanglikefree Tool')
    ->setVersion('1.0.1')
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input, $output->section());
        $fb = new Facebook();

        $section = $output->section();

        $io->title('Gingdev - Tool TangLikeFree v1.0.1');

        try {
            $fb->setSession('default');
        } catch (\LogicException $e) {
            $io->error('Run `./vendor/bin/console facebook:login default`');

            return;
        }

        $task = new Tanglikefree();
        $response = $fb->get('/me');
        $user = $response->getGraphNode()
            ->asArray();

        $io->table(
            ['ID', 'Name'],
            [
                [$user['id'], $user['name']],
            ]
        );

        while (true) {
            $data = $task->getPostTask();
            $progressBar = new ProgressBar($section, count($data));
            $progressBar->start();
            foreach ($data as $post) {
                $io->text('ID: '.$post->idpost);
                try {
                    $fb->post("/{$post->idpost}/likes");

                    $result = $task->receiveCoins($post->idpost);
                    $io->text('<'.($result->error ? 'comment' : 'info').'>'.$result->messages.'</>');

                    $fb->delete("{$post->idpost}/likes");
                } catch (\Facebook\Exceptions\FacebookResponseException $e) {
                    $io->text('<error>Không thể like: '.$post->idpost.'</>');
                }
                $progressBar->advance();
            }
            $progressBar->finish();
            $section->clear();
        }
    })
    ->run();
