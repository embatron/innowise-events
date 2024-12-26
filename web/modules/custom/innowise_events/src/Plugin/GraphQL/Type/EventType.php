<?php

namespace Drupal\innowise_events\Plugin\GraphQL\Types;

use Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase;

/**
 * Defines the Event type for GraphQL.
 *
 * @GraphQLType(
 *   id = "event",
 *   name = "Event",
 *   fields = {
 *     "id" = "Int",
 *     "title" = "String",
 *     "city" = "String",
 *     "eventStartDate" = "String",
 *     "eventEndDate" = "String",
 *     "status" = "Boolean",
 *     "maxParticipants" = "Int",
 *     "participantsCount" = "Int"
 *   }
 * )
 */
class EventType extends TypePluginBase {

}
