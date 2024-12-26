<?php

namespace Drupal\innowise_events\Service;

use GuzzleHttp\ClientInterface;

/**
 * Provides weather-related services.
 */
class WeatherService {

  protected ClientInterface $httpClient;
  protected string $apiKey;

  /**
   * Constructs the WeatherService object.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client for making requests.
   * @param string $api_key
   *   The API key for the weather service.
   */
  public function __construct(ClientInterface $http_client, string $api_key) {
    $this->httpClient = $http_client;
    $this->apiKey = $api_key;
  }

  /**
   * Fetches weather data based on coordinates.
   *
   * @param float $latitude
   *   The latitude of the location.
   * @param float $longitude
   *   The longitude of the location.
   *
   * @return array
   *   The weather data.
   */
public function getWeather(float $latitude, float $longitude): array {
  try {
    $response = $this->httpClient->get('https://api.openweathermap.org/data/2.5/weather', [
      'query' => [
        'lat' => $latitude,
        'lon' => $longitude,
        'appid' => $this->apiKey,
        'units' => 'metric',
      ],
    ]);

    $data = json_decode($response->getBody()->getContents(), TRUE);

    \Drupal::logger('innowise_events')->info('Weather API response: @response', [
      '@response' => print_r($data, TRUE),
    ]);

    return $data;
  }
  catch (\Exception $e) {
    \Drupal::logger('innowise_events')->error('Weather API error: @message', ['@message' => $e->getMessage()]);
    return [];
  }
}


}
