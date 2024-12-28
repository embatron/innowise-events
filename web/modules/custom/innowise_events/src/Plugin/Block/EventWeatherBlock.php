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

  /**
   * The weather service.
   *
   * @var \Drupal\innowise_events\Service\WeatherService
   */
  protected WeatherService $weatherService;

  /**
   * Constructs a new EventWeatherBlock instance.
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
    $entity = $this->getContextValue('event') ?? $this->getContextValue('mock_event');

    if (!$entity) {
      return [
        '#markup' => $this->t('No valid event or mock event context is available.'),
      ];
    }

    $coordinates = $this->getCoordinates($entity);

    if (!$coordinates) {
      return [
        '#markup' => $this->t('Geolocation data is not available for this entity.'),
      ];
    }

    [$latitude, $longitude] = $coordinates;

    $weather = $this->weatherService->getWeather($latitude, $longitude);

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
        'tags' => [$entity->getEntityTypeId() . ':' . $entity->id()],
        'max-age' => 600,
      ],
      '#weight' => -10,
    ];
  }

  /**
   * Retrieves coordinates from the entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   *
   * @return array|null
   *   An array with latitude and longitude, or NULL if not available.
   */
  protected function getCoordinates($entity) {
    if ($entity->getEntityTypeId() === 'event') {
      $latitude = $entity->get('latitude')->value;
      $longitude = $entity->get('longitude')->value;

      if (is_numeric($latitude) && is_numeric($longitude)) {
        return [$latitude, $longitude];
      }
    }

    if ($entity->getEntityTypeId() === 'mock_event') {
      $geolocation = $entity->get('field_event_s_location')->value;
      if (!empty($geolocation)) {
        $coordinates = explode(',', $geolocation);
        if (count($coordinates) === 2 && is_numeric($coordinates[0]) && is_numeric($coordinates[1])) {
          return $coordinates;
        }
      }
    }

    return NULL;
  }

}
