<?php

namespace Drupal\innowise_events\Service;

use GuzzleHttp\ClientInterface;

class WeatherService {

  protected ClientInterface $httpClient;
  protected string $apiKey;

  /**
   * WeatherService constructor.
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
   * Fetches weather data for a given location.
   *
   * @param float $latitude
   *   The latitude of the location.
   * @param float $longitude
   *   The longitude of the location.
   *
   * @return array
   *   The weather data or an error message.
   */
  public function getWeather(float $latitude, float $longitude): array {
    if (!$this->validateCoordinates($latitude, $longitude)) {
      \Drupal::logger('innowise_events')->error('Invalid coordinates provided: latitude=@lat, longitude=@lon', [
        '@lat' => $latitude,
        '@lon' => $longitude,
      ]);
      return ['error' => 'Invalid coordinates'];
    }

    try {
      $url = 'https://api.openweathermap.org/data/2.5/weather';

      $response = $this->httpClient->get($url, [
        'query' => [
          'lat' => $latitude,
          'lon' => $longitude,
          'appid' => $this->apiKey,
          'units' => 'metric',
        ],
      ]);

      $data = json_decode($response->getBody()->getContents(), TRUE);

      return $data;
    }
    catch (\Exception $e) {
      \Drupal::logger('innowise_events')->error('Weather API request failed: @message', ['@message' => $e->getMessage()]);
      return ['error' => 'Unable to fetch weather data'];
    }
  }

  /**
   * Validates the coordinates.
   *
   * @param float $latitude
   *   The latitude of the location.
   * @param float $longitude
   *   The longitude of the location.
   *
   * @return bool
   *   TRUE if coordinates are valid, FALSE otherwise.
   */
  protected function validateCoordinates(float $latitude, float $longitude): bool {
    return $latitude >= -90 && $latitude <= 90 && $longitude >= -180 && $longitude <= 180;
  }

}
