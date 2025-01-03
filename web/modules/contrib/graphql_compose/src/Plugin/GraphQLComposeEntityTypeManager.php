<?php

declare(strict_types=1);

namespace Drupal\graphql_compose\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\graphql_compose\Annotation\GraphQLComposeEntityType;
use Drupal\graphql_compose\Plugin\GraphQLCompose\GraphQLComposeEntityTypeInterface;

/**
 * Manager that collects and exposes GraphQL Compose entity plugins.
 *
 * A entity type is a plugin that defines how to resolve a Drupal Entity Type.
 */
class GraphQLComposeEntityTypeManager extends DefaultPluginManager {

  /**
   * Definition storage property.
   *
   * @var \Drupal\graphql_compose\Plugin\GraphQLCompose\GraphQLComposeEntityTypeInterface[]
   */
  private array $instances = [];

  /**
   * Constructs a GraphQLComposeEntityTypeManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   The cache backend.
   * @param array $config
   *   The configuration service parameter.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(
    \Traversable $namespaces,
    ModuleHandlerInterface $module_handler,
    CacheBackendInterface $cache_backend,
    array $config,
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {
    parent::__construct(
      'Plugin/GraphQLCompose/EntityType',
      $namespaces,
      $module_handler,
      GraphQLComposeEntityTypeInterface::class,
      GraphQLComposeEntityType::class
    );

    $this->alterInfo('graphql_compose_entity_type');
    $this->useCaches(empty($config['development']));
    $this->setCacheBackend($cache_backend, 'graphql_compose_entity_type', ['graphql_compose_entity_type']);
  }

  /**
   * Get instances.
   *
   * @return \Drupal\graphql_compose\Plugin\GraphQLCompose\GraphQLComposeEntityTypeInterface[]
   *   Definitions calculated by data manager.
   */
  public function getPluginInstances(): array {
    if (!empty($this->instances)) {
      return $this->instances;
    }

    $entity_types = $this->entityTypeManager->getDefinitions();
    $entity_type_plugins = $this->getDefinitions();

    foreach ($entity_type_plugins as $entity_type_plugin_id => $entity_type_plugin) {
      $entity_type_id = $entity_type_plugin['id'];
      if (array_key_exists($entity_type_id, $entity_types)) {
        $this->instances[$entity_type_id] = $this->createInstance($entity_type_plugin_id);
      }
    }

    return $this->instances;
  }

  /**
   * Get instances for entity type.
   *
   * @param string $entity_type_id
   *   Type to get settings for. Eg 'media'.
   *
   * @return \Drupal\graphql_compose\Plugin\GraphQLCompose\GraphQLComposeEntityTypeInterface|null
   *   Definitions calculated by data manager.
   */
  public function getPluginInstance(string $entity_type_id): ?GraphQLComposeEntityTypeInterface {
    $instances = $this->getPluginInstances();

    return $instances[$entity_type_id] ?? NULL;
  }

}
