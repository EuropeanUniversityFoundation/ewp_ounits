<?php

/**
 * @file
 * Hooks for the ewp_ounits_get module.
 */

/**
* Alter the Organizational Unit data before saving it to temporary storage.
*
* @param string $data
*   Data being retrieved.
 */
function hook_ounit_data_get_alter(string &$data) {
  // Count the number of resources in the data set.
  $resource_type = 'ounit';

  $type_string = '"type":"' . $resource_type . '"';

  $count = substr_count($data, $type_string);

  $message = t('Retrieved @count %type resources.', [
    '@count' => $count,
    '%type' => $resource_type,
  ]);

  \Drupal::logger('my_module')->addMessage($message);
}

/**
 * Alter the Organizational Unit data once loaded from temporary storage.
 *
 * @param string $data
 *   Data being loaded.
 */
function hook_ounit_data_load_alter(string &$data) {
  // Fix a known typo while it gets fixed.
  $replacements = [
    'ouint' => 'ounit',
  ];

  foreach ($replacements as $wrong => $right) {
    $data = str_replace($wrong, $right, $data, $count);
  }
}
