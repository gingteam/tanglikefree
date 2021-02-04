<?php

declare(strict_types=1);

namespace Gingdev\Tools;

use Curl\Curl;
use League\CLImate\CLImate;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class Tanglikefree
{
    const BASE_URL = 'https://tanglikefree.com/api/auth';
    const REFERER  = 'https://tanglikefree.com/makemoney';

    private $curl;

    public function __construct()
    {
        $cache = new FilesystemAdapter();
        $curl  = new Curl();
        $token = $cache->getItem('tanglikefree.token');

        $curl->setHeader('X-Requested-With', 'XMLHttpRequest');
        $curl->setHeader('Referer', self::REFERER);

        if (!$token->isHit()) {
            $climate  = new CLImate();
            $username = $climate->input('Enter user:')->prompt();
            $password = $climate->input('Enter pass:')->prompt();

            $curl->post(self::BASE_URL.'/login', [
                'username' => $username,
                'password' => $password,
                'disable'  => true,
            ]);
            $response = $curl->response;

            if ($response->error) {
                throw new \Exception('Login failed.');
            }

            $token->set($response->data->access_token);
            $cache->save($token);
        }

        $curl->setHeader(
            'Authorization',
            'Bearer '.$token->get()
        );

        $this->curl = $curl;
    }

    protected function get(string $path)
    {
        $this->curl->get(self::BASE_URL.'/'.$path);

        return $this->response();
    }

    protected function post(string $path, array $params = [])
    {
        $this->curl->post(self::BASE_URL.'/'.$path, $params);

        return $this->response();
    }

    protected function response()
    {
        return $this->curl->response;
    }

    public function receiveCoins(string $id)
    {
        return $this->post('Post/submitpost', [
            'idpost'     => $id,
            'request_id' => $this->csrf(),
        ]);
    }

    public function getPostTask()
    {
        return $this->get('Post/getpost');
    }

    private function csrf(): string
    {
        return $this->get('creat_request')->request_id;
    }
}
