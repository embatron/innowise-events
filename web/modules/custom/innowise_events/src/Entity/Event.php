<?php

namespace Drupal\innowise_events\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the Event entity.
 *
 * @ContentEntityType(
 *   id = "event",
 *   label = @Translation("Event"),
 *   label_collection = @Translation("Events"),
 *   handlers = {
 *     "storage" = "Drupal\Core\Entity\Sql\SqlContentEntityStorage",
 *     "storage_schema" = "Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema",
 *     "access" = "Drupal\innowise_events\EventAccessControlHandler",
 *     "list_builder" = "Drupal\innowise_events\EventListBuilder",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "form" = {
 *       "default" = "Drupal\Core\Entity\ContentEntityForm",
 *       "add" = "Drupal\innowise_events\Form\EventForm",
 *       "edit" = "Drupal\innowise_events\Form\EventForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "event",
 *   admin_permission = "administer event entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "title",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "canonical" = "/event/{event}",
 *     "add-form" = "/admin/content/event/add",
 *     "edit-form" = "/admin/content/event/{event}/edit",
 *     "delete-form" = "/admin/content/event/{event}/delete",
 *     "collection" = "/admin/content/events"
 *   },
 *   field_ui_base_route = "entity.event.collection"
 * )
 */


class Event extends ContentEntityBase {

  /**
   * Defines base fields for the Event entity.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   *
   * @return \Drupal\Core\Field\BaseFieldDefinition[]
   *   An array of base field definitions.
   */
public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
  $fields = parent::baseFieldDefinitions($entity_type);

  // Заголовок события.
  $fields['title'] = BaseFieldDefinition::create('string')
    ->setDescription(t('The title of the event.'))
    ->setRequired(TRUE)
    ->setSettings([
      'max_length' => 255,
    ])
    ->setDisplayOptions('view', [
      'label' => 'above',
      'type' => 'string',
      'weight' => 0,
    ])
    ->setDisplayOptions('form', [
      'type' => 'string_textfield',
      'weight' => 0,
    ]);

$fields['event_start_date'] = BaseFieldDefinition::create('datetime')
  ->setLabel(t('Start Date'))
  ->setDescription(t('Start date of the event (without time).'))
  ->setSettings([
    'datetime_type' => 'date', // Указываем, что используется только дата
  ])
  ->setDisplayOptions('form', [
    'type' => 'datetime_default',
    'weight' => 1,
    'settings' => [
      'datetime_type' => 'date', // Указываем, что используется только дата
    ],
  ])
  ->setDisplayOptions('view', [
    'label' => 'above',
    'type' => 'datetime_default',
    'weight' => 1,
  ])
  ->setRequired(TRUE);

$fields['event_end_date'] = BaseFieldDefinition::create('datetime')
  ->setLabel(t('End Date'))
  ->setDescription(t('End date of the event (without time).'))
  ->setSettings([
    'datetime_type' => 'date', // Указываем, что используется только дата
  ])
  ->setDisplayOptions('form', [
    'type' => 'datetime_default',
    'weight' => 2,
    'settings' => [
      'datetime_type' => 'date', // Указываем, что используется только дата
    ],
  ])
  ->setDisplayOptions('view', [
    'label' => 'above',
    'type' => 'datetime_default',
    'weight' => 2,
  ])
  ->setRequired(TRUE);

  // Описание события.
  $fields['description'] = BaseFieldDefinition::create('string_long')
    ->setLabel(t('Description'))
    ->setDescription(t('A detailed description of the event.'))
    ->setDisplayOptions('view', [
      'label' => 'above',
      'type' => 'text_default',
      'weight' => 3,
    ])
    ->setDisplayOptions('form', [
      'type' => 'text_textarea',
      'weight' => 3,
    ]);

$fields['city'] = BaseFieldDefinition::create('list_string')
  ->setLabel(t('Location'))
  ->setDescription(t('Select a city from the list.'))
  ->setSettings([
    'allowed_values' => array_reduce(
      innowise_events_get_cities(),
      function ($carry, $city) {
        $carry[$city['name']] = $city['name'];
        return $carry;
      },
      []
    ),
  ])
  ->setDisplayOptions('form', [
    'type' => 'options_select',
    'weight' => 4,
  ])
  ->setDisplayOptions('view', [
    'label' => 'above',
    'type' => 'string',
    'weight' => 4,
  ])
  ->setRequired(TRUE);

$fields['max_participants'] = BaseFieldDefinition::create('integer')
  ->setLabel(t('Participants'))
  ->setDescription(t('The maximum number of participants allowed for this event.'))
  ->setRequired(TRUE)
  ->setDefaultValue(1)
  ->setSetting('unsigned', TRUE)
  ->setSetting('min', 1)
  ->setDisplayOptions('form', [
    'type' => 'number',
    'weight' => 5,
    'settings' => [
      'min' => 1,
      'step' => 1,
    ],
  ])
  ->setDisplayOptions('view', [
    'label' => 'above',
    'type' => 'number_integer',
    'weight' => 5,
  ])
  ->setDisplayConfigurable('form', TRUE)
  ->setDisplayConfigurable('view', TRUE);

$fields['latitude'] = BaseFieldDefinition::create('float')
  ->setLabel(t('Latitude'))
  ->setDescription(t('The latitude of the event location.'))
  ->setRequired(TRUE)
  ->setDefaultValue(0.0)
  ->setDisplayOptions('view', [])
  ->setDisplayConfigurable('form', FALSE)
  ->setDisplayConfigurable('view', FALSE);

$fields['longitude'] = BaseFieldDefinition::create('float')
  ->setLabel(t('Longitude'))
  ->setDescription(t('The longitude of the event location.'))
  ->setRequired(TRUE)
  ->setDefaultValue(0.0)
  ->setDisplayOptions('view', [])
  ->setDisplayConfigurable('form', FALSE)
  ->setDisplayConfigurable('view', FALSE);


  // Статус события.
  $fields['status'] = BaseFieldDefinition::create('boolean')
    ->setLabel(t('Active'))
    ->setDescription(t('Indicates whether the event is active.'))
    ->setDefaultValue(TRUE)
    ->setDisplayOptions('view', [
      'label' => 'above',
      'type' => 'boolean',
      'weight' => 6,
    ])
    ->setDisplayOptions('form', [
      'type' => 'boolean_checkbox',
      'weight' => 6,
    ]);

$fields['participants'] = BaseFieldDefinition::create('entity_reference')
  ->setLabel(t('Participants'))
  ->setDescription(t('Users registered for the event.'))
  ->setSetting('target_type', 'user') // Ссылка на сущности пользователей.
  ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED) // Позволяет хранить несколько участников.
  ->setDisplayOptions('view', [
    'label' => 'hidden',
    'type' => 'entity_reference_label',
    'weight' => 8,
  ])
  ->setDisplayOptions('view', [])
  ->setDisplayConfigurable('form', TRUE)
  ->setDisplayConfigurable('view', TRUE);

  return $fields;
}
}
