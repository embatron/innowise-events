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
 *     "event" = @ContextDefinition("entity:event", label = @Translation("Event"), required = FALSE),
 *     "mock_event" = @ContextDefinition("entity:mock_event", label = @Translation("Mock Event"), required = FALSE)
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
  $mock_event = $this->getContextValue('mock_event', FALSE);

  if (!$event && !$mock_event) {
    return [
      '#markup' => $this->t('No valid event or mock event context is available.'),
    ];
  }

  if ($event) {
    $latitude = $event->get('latitude')->value;
    $longitude = $event->get('longitude')->value;

    \Drupal::logger('innowise_events')->info('Event coordinates: lat=@lat, lon=@lon', [
      '@lat' => $latitude,
      '@lon' => $longitude,
    ]);
  }
  elseif ($mock_event) {
    $geolocation = $mock_event->get('field_event_s_location')->value;

    if (empty($geolocation)) {
      return [
        '#markup' => $this->t('Geolocation data is not available for this mock event.'),
      ];
    }

    [$latitude, $longitude] = explode(',', $geolocation);

    \Drupal::logger('innowise_events')->info('Mock Event coordinates: lat=@lat, lon=@lon', [
      '@lat' => $latitude,
      '@lon' => $longitude,
    ]);
  }

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
      'tags' => $event ? ['event:' . $event->id()] : ['mock_event:' . $mock_event->id()],
      'max-age' => 600,
    ],
    '#weight' => -10,
  ];
}
}
