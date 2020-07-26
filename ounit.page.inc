<?php

/**
 * @file
 * Contains ounit.page.inc.
 *
 * Page callback for Organizational Unit entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Organizational Unit templates.
 *
 * Default template: ounit.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_ounit(array &$variables) {
  // Fetch Ounit Entity Object.
  $ounit = $variables['elements']['#ounit'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
