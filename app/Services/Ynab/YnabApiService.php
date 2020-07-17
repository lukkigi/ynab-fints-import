<?php

namespace App\Services\Ynab;

use App\Constants\AppConstants;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

class YnabApiService
{
    private $apiUrl = 'https://api.youneedabudget.com/v1/';

    private $apiKey;
    private $budgetId;
    private $apiClient;

    /**
     * YnabApiService constructor.
     * @param $budgetId
     */
    public function __construct($budgetId)
    {
        $apiKey = Config::get(AppConstants::$ENV_YNAB_API_KEY);

        if ($apiKey == null || strlen($apiKey) < 20) {
            throw new InvalidArgumentException('Invalid or no YNAB Api Key supplied');
        }

        $this->apiKey = $apiKey;
        $this->budgetId = $budgetId;
        $this->apiClient = new Client();
    }

    /**
     * @return array|mixed
     */
    public function fetchAllPayees()
    {
        $fetchUrl = $this->apiUrl . 'budgets/' . $this->budgetId . '/payees';

        $response = $this->callGetEndpoint($fetchUrl);

        if ($response->getStatusCode() == 200) {
            $jsonResponse = json_decode($response->getBody()->getContents(), true);

            return $jsonResponse['data']['payees'];
        }

        return [];
    }

    /**
     * @param $transactions
     * @return ResponseInterface
     */
    public function createTransactions($transactions)
    {
        $url = $this->apiUrl . 'budgets/' . $this->budgetId . '/transactions';

        return $this->callPostEndpoint($url, [
            'transactions' => $transactions
        ]);
    }

    /**
     * @param $url
     * @return ResponseInterface
     */
    private function callGetEndpoint($url)
    {
        return $this->apiClient->get($url, ['headers' => $this->getHeaders()]);
    }

    /**
     * @param $url
     * @param $body
     * @return ResponseInterface
     */
    private function callPostEndpoint($url, $body)
    {
        return $this->apiClient->post($url, [
            'headers' => $this->getHeaders(),
            'json' => $body
        ]);
    }

    /**
     * @return string[]
     */
    private function getHeaders()
    {
        return [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept' => 'application/json'
        ];
    }
}
