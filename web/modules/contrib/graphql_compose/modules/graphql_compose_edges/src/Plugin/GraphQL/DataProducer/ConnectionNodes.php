<?php

declare(strict_types=1);

namespace Drupal\graphql_compose_edges\Plugin\GraphQL\DataProducer;

use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\DataProducerPluginCachingInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\graphql_compose_edges\ConnectionInterface;

/**
 * Produces the edges from a connection object.
 *
 * @DataProducer(
 *   id = "connection_nodes",
 *   name = @Translation("Connection nodes"),
 *   description = @Translation("Returns the nodes of a connection."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Nodes"),
 *   ),
 *   consumes = {
 *     "connection" = @ContextDefinition("any",
 *       label = @Translation("EntityConnection"),
 *     ),
 *   },
 * )
 */
class ConnectionNodes extends DataProducerPluginBase implements DataProducerPluginCachingInterface {

  /**
   * Resolves the request.
   *
   * @param \Drupal\graphql_compose_edges\ConnectionInterface $connection
   *   The connection to return the edges from.
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $context
   *   The cache context for this query.
   *
   * @return mixed
   *   The edges for the connection.
   */
  public function resolve(ConnectionInterface $connection, FieldContext $context) {
    return $connection->setCacheContext($context)->nodes();
  }

}
