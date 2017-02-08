<?php
/*
 * This file is part of the Chanmix51/JcDecauxBikeApi package.
 *
 * See the LICENSE file provided with this package.
 */
namespace Chanmix51\JcDecauxBikeApi\Tests\Unit;

use Atoum;

use Mock\GuzzleHttp\ClientInterface;
use Mock\Psr\Http\Message\ResponseInterface;

class JcDecauxBikeApi extends Atoum
{
    public function getBikeApi(ClientInterface $client, string $json)
    {
        $client->getMockController()->request = function (string $method, string $url, array $header) use ($json) {

            $response = new ResponseInterface;
            $response->getMockController()->getStatusCode = function() { return 200; };
            $response->getMockController()->getBody = function() use ($json) { return $json; };

            return $response;
        };

        return parent::newTestedInstance($client, 'secretToken');
    }

    public function testGetStationInfo()
    {
        $json = <<<JSON
{
   "number": 6,
   "name": "00006-PLACE DU CIRQUE",
   "address": "PLACE DU CIRQUE - 7 ALLEE DUQUESNE",
   "position": {
       "lat": 47.217596682406,
       "lng": -1.5569376453789
   },
   "banking": true,
   "bonus": false,
   "status": "OPEN",
   "contract_name": "Nantes",
   "bike_stands": 15,
   "available_bike_stands": 1,
   "available_bikes": 14,
   "last_update": 1486582958000
}
JSON;

        $this
            ->assert("Test getStationInfo().")
            ->given($client = new ClientInterface)
            ->given($api = $this->getBikeApi($client, $json))
                ->array($api->getStationInfo(1, 'whatever'))
                ->isEqualTo([
                    "number" => 6,
                    "name" => "00006-PLACE DU CIRQUE",
                    "address" => "PLACE DU CIRQUE - 7 ALLEE DUQUESNE",
                    "position" => [
                        "lat" => 47.217596682406,
                        "lng" => -1.5569376453789,
                    ],
                    "banking" => true,
                    "bonus" => false,
                    "status" => "OPEN",
                    "contract_name" => "Nantes",
                    "bike_stands" => 15,
                    "available_bike_stands" => 1,
                    "available_bikes" => 14,
                    "last_update" => 1486582958000.0,
                ])
                ->mock($client)
                    ->call('request')
                    ->withArguments(
                        'GET',
                        'https://api.jcdecaux.com/vls/v1/stations/1?contract=whatever&apiKey=secretToken',
                        [
                            'accept' => 'application/json',
                        ]
                    )
                    ->once()
            ;
    }
}
