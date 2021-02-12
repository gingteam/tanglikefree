<?php

require __DIR__.'/vendor/autoload.php';

use Gingdev\Facebook\Facebook;
use Facebook\FacebookSession;
use Facebook\FacebookRequestException;
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
        FacebookSession::enableAppSecretProof(false);
        $io = new SymfonyStyle($input, $output->section());

        $section = $output->section();

        $io->title('Gingdev - Tool TangLikeFree v1.0.2');

        try {
            $fb = new Facebook('default');
        } catch (\LogicException $e) {
            $io->error('Run `./vendor/bin/console facebook:login default`');

            return;
        }

        $task = new Tanglikefree();
        $response = $fb->request('GET', '/me')
            ->execute();
        $user = $response->getGraphObject()
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
                    $fb->request('POST', sprintf('/%s/likes', $post->idpost))
                        ->execute();

                    $result = $task->receiveCoins($post->idpost);
                    $io->text('<'.($result->error ? 'comment' : 'info').'>'.$result->messages.'</>');
                } catch (FacebookRequestException $e) {
                    $io->text('<error>'.$e->getMessage().'</>');
                }
                $progressBar->advance();
            }
            $progressBar->finish();
            $section->clear();
        }
    })
    ->run();
