<?php
/**
* 2007-2021 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class RequestHelper
{
    /**
     * Performs a consulting request to the Bayonet API
     *
     * @param array $requestBody
     * @return array
     */
    public function consulting($requestBody)
    {
        $consultingResponse = $this->request('sigma/consult', $requestBody, 'bayonet', 0, '');

        return $consultingResponse;
    }

    /**
     * Performs a feedback historical request to the Bayonet API
     *
     * @param array $requestBody
     * @return array
     */
    public function feedbackHistorical($requestBody)
    {
        $historicalResponse = $this->request('sigma/feedback-historical', $requestBody, 'bayonet', 0, '');

        return $historicalResponse;
    }

    /**
     * Performs an update transaction request to the Bayonet API
     *
     * @param array $requestBody
     * @return array
     */
    public function updateTransaction($requestBody)
    {
        $updateResponse = $this->request('sigma/update-transaction', $requestBody, 'bayonet', 0, '');

        return $updateResponse;
    }

    /**
     * Performs a request to the Fingerprint API
     * Used only to validate fingerprint API keys
     *
     * @param string $requestBody
     * @return array
     */
    public function deviceFingerprint($requestBody)
    {
        $deviceFingerprintResponse = $this->request('', $requestBody, 'js', 0, '');

        return $deviceFingerprintResponse;
    }

    public function apiValidation($requestBody, $apiVersion)
    {
        $validationResult = $this->request('sigma/consult', $requestBody, 'bayonet', 1, $apiVersion);

        return $validationResult;
    }

    /**
     * Defines "whitelist/add" as the call to be executed in the request method
     *
     * @param array $requestBody
     * @return array
     */
    public function addWhitelist($requestBody)
    {
        $listResponse = $this->request('sigma/labels/whitelist/add', $requestBody, 'bayonet', 0, '');

        return $listResponse;
    }

    /**
     * Defines "whitelist/remove" as the call to be executed in the request method
     *
     * @param array $requestBody
     * @return array
     */
    public function removeWhitelist($requestBody)
    {
        $listResponse = $this->request('sigma/labels/whitelist/remove', $requestBody, 'bayonet', 0, '');

        return $listResponse;
    }

    /**
     * Defines "block/add" as the call to be executed in the request method
     *
     * @param array $requestBody
     * @return array
     */
    public function addBlocklist($requestBody)
    {
        $listResponse = $this->request('sigma/labels/block/add', $requestBody, 'bayonet', 0, '');

        return $listResponse;
    }

    /**
     * Defines "block/remove" as the call to be executed in the request method
     *
     * @param array $requestBody
     * @return array
     */
    public function removeBlocklist($requestBody)
    {
        $listResponse = $this->request('sigma/labels/block/remove', $requestBody, 'bayonet', 0, '');

        return $listResponse;
    }

    private function request($endpoint, $requestBody, $api, $versionValidation, $version)
    {   
        $apiVersion = '';

        if (0 === (int) $versionValidation) {
            $apiVersion = Configuration::get('BAYONET_AF_API_VERSION');
        } elseif(1 === (int) $versionValidation) {
            $apiVersion = $version;
        }

        $requestJson = json_encode($requestBody);
        $requestUrl = strcmp($api, 'bayonet') === 0 ? 'https://api.bayonet.io/'.$apiVersion.'/'.$endpoint :
            'https://fingerprinting.bayonet.io/v2/generate-fingerprint-token';

        $ch = curl_init($requestUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestJson);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $responseJson = json_decode($response);
        curl_close($ch);

        return $responseJson;
    }
}