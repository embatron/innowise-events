<?php

namespace Drupal\innowise_events\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\innowise_events\Service\WeatherService;

/**
 * @Block(
 *   id = "event_weather_block",
 *   admin_label = @Translation("Event Weather Block"),
 *   context_definitions = {
 *     "event" = @ContextDefinition("entity:event", required = TRUE, label = @Translation("Event"))
 *   }
 * )
 */
class EventWeatherBlock extends BlockBase implements ContainerFactoryPluginInterface {

  protected WeatherService $weatherService;

  /**
   * Constructs a new EventWeatherBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\innowise_events\Service\WeatherService $weatherService
   *   The weather service to fetch weather data.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, WeatherService $weatherService) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->weatherService = $weatherService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('innowise_events.weather_service')
    );
  }

  /**
   * {@inheritdoc}
   */
public function build() {
  $event = $this->getContextValue('event', FALSE);

  if (!$event) {
    return [
      '#markup' => $this->t('Event context is not available.'),
    ];
  }

  $latitude = $event->get('latitude')->value;
  $longitude = $event->get('longitude')->value;

  \Drupal::logger('innowise_events')->info('Event coordinates: lat=@lat, lon=@lon', [
    '@lat' => $latitude,
    '@lon' => $longitude,
  ]);

  $weather = $this->weatherService->getWeather($latitude, $longitude);

  \Drupal::logger('innowise_events')->info('Weather data: @weather', [
    '@weather' => print_r($weather, TRUE),
  ]);

  if (empty($weather)) {
    return [
      '#markup' => $this->t('Weather data is not available.'),
    ];
  }

  return [
    '#theme' => 'event_weather',
    '#weather' => $weather,
    '#attributes' => [
      'class' => ['event-weather-block', 'event-weather-block-bottom'],
    ],
    '#cache' => [
      'contexts' => ['url.path'],
      'tags' => ['event:' . $event->id()],
      'max-age' => 600,
    ],
    '#weight' => -10,
  ];
}
}
