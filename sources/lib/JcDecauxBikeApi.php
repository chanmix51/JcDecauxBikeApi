<?php
/*
 * This file is part of the Chanmix51/JcDecauxBikeApi package.
 *
 * See the LICENSE file provided with this package.
 */
namespace Chanmix51\JcDecauxBikeApi;

use GuzzleHttp\ClientInterface;

use Psr\Http\Message\ResponseInterface;

/**
 * JcDecauxBikeApi
 *
 * API wrapper to perform call against JcDecaux bike API.
 *
 * @package     JcDecauxBikeApi
 * @copyright   2017 Grégoire HUBERT
 * @author      Grégoire HUBERT <hubert.greg@gmail.com>
 * @license     X11 {@link http://opensource.org/licenses/mit-license.php}
 */
class JcDecauxBikeApi
{
    protected $client;
    private   $api_key;

    const CONTRACT_LIST_URL = 'https://api.jcdecaux.com/vls/v1/contracts';
    const STATIONS_LIST_URL = 'https://api.jcdecaux.com/vls/v1/stations';
    const STATIONS_INFO     = 'https://api.jcdecaux.com/vls/v1/stations/{station_number}';

    /**
     * __construct
     *
     * Initialize the API requester.
     *
     * @param   Client $client
     */
    public function __construct(ClientInterface $client, $key)
    {
        $this->client   = $client;
        $this->api_key  = $key;
    }

    /**
     * getContractList
     *
     * Return the list of the contracts.
     *
     * @return  array
     *
     * @see     https://developer.jcdecaux.com/#/opendata/vls?page=dynamic
     */
    public function getContractList()
    {
        $url = sprintf(
            "%s?apiKey=%s",
            self::CONTRACT_LIST_URL,
            $this->api_key
        );

        return $this->performCall($url);
    }

    /**
     * getStationList
     *
     * Return the list of all stations associated to a contract.
     *
     * @return  array
     *
     * @see     https://developer.jcdecaux.com/#/opendata/vls?page=dynamic
     */
    public function getStationList($contract_name)
    {
        $url = sprintf(
            "%s?contract=%s&apiKey=%s",
            self::STATIONS_LIST_URL,
            $contract_name,
            $this->api_key
        );

        return $this->performCall($url);
    }

    /**
     * getStationInfo
     *
     * Return the information about a specific station.
     *
     * @param   int $station_number
     * @param   string $contract_name
     * @return  array
     */
    public function getStationInfo($station_number, $contract_name)
    {
        $url = sprintf(
            "%s?contract=%s&apiKey=%s",
            strtr(
                self::STATIONS_INFO,
                [
                    '{station_number}' => $station_number,
                ]
            ),
            $contract_name,
            $this->api_key
        );

        return $this->performCall($url);
    }

    /**
     * performCall
     *
     * Perform a call to the API.
     *
     * @param   string $url
     * @return  array
     */
    protected function performCall($url)
    {
        $response = $this->client->request(
            'GET',
            $url,
            [
                'accept' => 'application/json',
            ]
        );

        $this->checkStatusCode($response);

        return $this->checkJson($response->getBody());
    }
    /**
     * checkJson
     *
     * Check the given string is valid JSON an decode it.
     *
     * @param   string $json
     * @throws  TransportException
     * @return  array
     */
    protected function checkJson($json)
    {
        $output = json_decode($json, true);

        if ($output === false) {

            throw new TransportException(
                "The API seems to have provided a bad formatted JSON response."
            );
        }

        return $output;
    }

    /**
     * checkStatusCode
     *
     * Check the HTTP status code is 200, throws an exception otherwise.
     *
     * @param   ResponseInterface $response
     * @return  self
     */
    protected function checkStatusCode(ResponseInterface $response)
    {
        if ($response->getStatusCode() !== 200) {

            throw new TransportException(
                sprintf(
                    "HTTP request to '%s' returned a %d status code (expected 200).",
                    $url,
                    $response->getStatusCode()
                )
            );
        }

        return $this;
    }
}
