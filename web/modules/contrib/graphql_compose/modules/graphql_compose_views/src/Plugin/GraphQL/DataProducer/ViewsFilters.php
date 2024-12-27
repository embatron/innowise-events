<?php

declare(strict_types=1);

namespace Drupal\graphql_compose_views\Plugin\GraphQL\DataProducer;

use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Drupal\views\ViewExecutable;

/**
 * Get pager info for a view.
 *
 * @DataProducer(
 *   id = "views_filters",
 *   name = @Translation("Views filters"),
 *   description = @Translation("Values for the exposed filters of a view."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Filter values."),
 *   ),
 *   consumes = {
 *     "executable" = @ContextDefinition("any",
 *       label = @Translation("View executable"),
 *     ),
 *   },
 * )
 */
class ViewsFilters extends DataProducerPluginBase {

  /**
   * Resolve filter configuration on a view.
   *
   * @param \Drupal\views\ViewExecutable $view
   *   View executable.
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $context
   *   The cache context.
   *
   * @return array
   *   The filter values.
   */
  public function resolve(ViewExecutable $view, FieldContext $context): array {

    $results = [];

    /** @var \Drupal\graphql_compose_views\Plugin\views\display\GraphQL $display */
    $display = $view->getDisplay();

    if (!$display->usesExposed() || empty($view->exposed_widgets)) {
      return [];
    }

    /** @var \Drupal\views\Plugin\views\filter\FilterPluginBase[] $exposed_filters */
    $exposed_filters = array_filter(
      $display->getHandlers('filter'),
      fn (FilterPluginBase $filter) => $filter->isExposed()
    );

    foreach ($exposed_filters as $filter) {
      $info = $filter->exposedInfo();

      $form_element = $view->exposed_widgets[$info['value']];

      $required = $filter->isAGroup()
        ? !$filter->options['group_info']['optional']
        : $filter->options['expose']['required'];

      $multiple = $filter->isAGroup()
        ? $filter->options['group_info']['multiple']
        : $filter->options['expose']['multiple'];

      // Pick out the default value from the form.
      $default_value = $form_element['#default_value'] ?? ($multiple ? [] : NULL);

      if ($filter->isAGroup()) {
        $default_value = $filter->multipleExposedInput()
          ? $filter->options['group_info']['default_group_multiple']
          : $filter->options['group_info']['default_group'];
      }

      // Replace with the views exposed input.
      $exposed_input = $view->getExposedInput();
      $value = $exposed_input[$info['value']] ?? $default_value;

      // Reshape default data a bit to make it a bit more consistent.
      if (is_array($value)) {
        $value = array_map('strval', array_values($value));
        $value = $multiple ? $value : current($value);
      }

      // Any added attributes except for the ones we don't want.
      $ignored_attributes = [
        'data-drupal-selector',
        'id',
        'name',
        'multiple',
        'required',
        'class',
      ];

      $attributes = array_filter(
        ($form_element['#attributes'] ?? []),
        fn ($key) => !in_array($key, $ignored_attributes),
        ARRAY_FILTER_USE_KEY
      );

      // Ensure all keys are strval.
      $options = $form_element['#options'] ?? NULL;
      $options = $options ? (object) $options : NULL;

      $results[] = [
        'id' => $info['value'],
        'plugin' => $filter->getPluginId(),
        'type' => $form_element['#type'] ?? 'unknown',
        'label' => $info['label'] ?: NULL,
        'description' => $info['description'] ?: NULL,
        'value' => $value,
        'options' => $options ?? NULL ?: NULL,
        'required' => $required,
        'multiple' => $multiple,
        'attributes' => $attributes,
      ];
    }

    return $results;
  }

}
