<?php

//namespace Bayonet;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class BayonetClient {
    private $config;

    public function __construct(array $config = []) {
        $this->config = $config;
        $this->config['base_uri'] = 'https://api.bayonet.io/v2/';
        $this->client = new Client();
    }

    public function getConfig() {
        return $this->config;
    }

    public function consulting(array $config = []) {
        $this->request('sigma/consult', $config);
    }

    public function feedback(array $config = []) {
        $this->request('feedback', $config);
    }

    private function request($api, array $config = []) {
        if(!isset($config['body']))
            $config['body'] = [];

        $config['body']['auth']['api_key'] = $this->config['api_key'];
        
        $base_uri = $this->config['base_uri'];

        try {
            $response = $this->client->post($base_uri . $api,  [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode($config['body'])
            ]);

            if(isset($config['on_success'])) {
                $config['on_success'](
                    json_decode(
                        $response->getBody()
                    )
                );
            }
        } catch(\Exception $e) {
            if(isset($config['on_failure'])) {
                $config['on_failure'](
                    json_decode(
                        $e->getResponse()->getBody()->getContents()
                    )
                );
            } else {
                // let the client know the request wasnt successful
                throw $e;
            }
        }
    }
}