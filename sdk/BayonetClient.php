<?php
/**
 * 2007-2019 PrestaShop SA and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
 
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class BayonetClient
{
    private $config;
    private $client;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->config['base_uri'] = 'https://api.bayonet.io/v2/';
        $this->client = new Client();
    }
    
    /**
     * Defines "consulting" as the call to be executed in the request method
     *
     * @param array $config Client configuration
     */
    public function consulting(array $config = [])
    {
        $this->request('sigma/consult', $config);
    }
    
    /**
     * Defines "update-transaction" as the call to be executed in the request method
     *
     * @param array $config Client configuration
     */
    public function updateTransaction(array $config = [])
    {
        $this->request('sigma/update-transaction', $config);
    }

    /**
     * Defines "feedback-historical" as the call to be executed in the request method
     *
     * @param array $config Client configuration
     */
    public function feedbackHistorical(array $config = [])
    {
        $this->request('sigma/feedback-historical', $config);
    }

    /**
     * Defines "whitelist/add" as the call to be executed in the request method
     *
     * @param array $config Client configuration
     */
    public function addWhiteList(array $config = [])
    {
        $this->request('sigma/labels/whitelist/add', $config);
    }

    /**
     * Defines "whitelist/remove" as the call to be executed in the request method
     *
     * @param array $config Client configuration
     */
    public function removeWhiteList(array $config = [])
    {
        $this->request('sigma/labels/whitelist/remove', $config);
    }

    /**
     * Defines "block/add" as the call to be executed in the request method
     *
     * @param array $config Client configuration
     */
    public function addBlackList(array $config = [])
    {
        $this->request('sigma/labels/block/add', $config);
    }

    /**
     * Defines "block/remove" as the call to be executed in the request method
     *
     * @param array $config Client configuration
     */
    public function removeBlackList(array $config = [])
    {
        $this->request('sigma/labels/block/remove', $config);
    }
    
    /**
     * Executes a call to the Bayonet API
     *
     * @param string $api Call specification (consulting, update-transaction)
     * @param array $config Client configuration
     */
    private function request($api, array $config = [])
    {
        if (!isset($config['body'])) {
            $config['body'] = [];
        }

        $config['body']['auth']['api_key'] = $this->config['api_key'];
        $base_uri = $this->config['base_uri'];

        try {
            $response = $this->client->post($base_uri . $api, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($config['body'])
            ]);

            if (isset($config['on_success'])) {
                $config['on_success'](
                    json_decode(
                        $response->getBody()
                    )
                );
            }
        } catch (\Exception $e) {
            if (isset($config['on_failure'])) {
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
