<?php

declare(strict_types=1);

namespace Drupal\graphql_compose\Plugin\GraphQLCompose\FieldType;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Url;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql_compose\Plugin\GraphQL\DataProducer\FieldProducerItemInterface;
use Drupal\graphql_compose\Plugin\GraphQL\DataProducer\FieldProducerTrait;
use Drupal\graphql_compose\Plugin\GraphQLCompose\GraphQLComposeFieldTypeBase;
use Drupal\link\LinkItemInterface;

/**
 * {@inheritdoc}
 *
 * @GraphQLComposeFieldType(
 *   id = "link",
 *   type_sdl = "Link",
 * )
 */
class LinkItem extends GraphQLComposeFieldTypeBase implements FieldProducerItemInterface {

  use FieldProducerTrait;

  /**
   * {@inheritdoc}
   */
  public function resolveFieldItem(FieldItemInterface $item, FieldContext $context) {

    if ($item->isEmpty()) {
      return NULL;
    }

    $url = ($item instanceof LinkItemInterface)
      ? $this->getUrlFromLink($item)
      : $this->getUrlFromOther($item);

    // Match the Url language to the field item language.
    $url->setOption('language', $this->languageManager->getLanguage($item->getLangcode()));

    $link = $url->toString(TRUE);
    $context->addCacheableDependency($link);

    return [
      'title' => $this->getTitle($item),
      'url' => $link->getGeneratedUrl(),
      'internal' => !$url->isExternal(),
    ];
  }

  /**
   * Get the title from a FieldItemInterface.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   The field item.
   *
   * @return string|null
   *   The title.
   */
  protected function getTitle(FieldItemInterface $item): ?string {
    return $item->title ?? NULL;
  }

  /**
   * Get the URL from a LinkItemInterface.
   *
   * @param \Drupal\link\LinkItemInterface $item
   *   The link item.
   *
   * @return \Drupal\Core\Url
   *   The URL.
   */
  protected function getUrlFromLink(LinkItemInterface $item): Url {
    return $item->getUrl();
  }

  /**
   * Get the URL from a FieldItemInterface.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   The field item.
   *
   * @return \Drupal\Core\Url
   *   The URL.
   */
  protected function getUrlFromOther(FieldItemInterface $item): Url {
    $path = $item->uri ?? NULL;

    return UrlHelper::isExternal($path)
      ? Url::fromUri($path)
      : Url::fromUserInput($path);
  }

}
