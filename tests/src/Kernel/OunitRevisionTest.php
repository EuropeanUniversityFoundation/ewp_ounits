<?php

declare(strict_types=1);

namespace Drupal\Tests\ewp_ounits\Kernel;

/**
 * Tests validation constraints that apply to ounit entities.
 *
 * @group ewp_ounits
 */
final class OunitRevisionTest extends OunitKernelTestBase {

  public static function provideModifiedOunitData() {
    return [
      'label' => 'Original OUnit modified',
      'ounit_code' => 'OU3 modified',
      'ounit_id' => '00000000-0000-0000-0000-aaaaaaaaaaaa',
      'name' => [
        [
          'string' => 'Original OUnit modified',
          'lang' => 'de',
        ],
      ],
      'parent_hei' => 1,
      'abbreviation' => 'OU THREE modified',
      'parent_ounit' => 1,
      'mailing_address' => [
        'recipient_name' => 'OU THREE modified',
        'country' => 'HU',
        'address_line_1' => null,
        'address_line_2' => null,
        'address_line_3' => null,
        'address_line_4' => null,
        'building_number' => null,
        'building_name' => null,
        'street_name' => null,
        'unit' => null,
        'floor' => null,
        'post_office_box' => null,
        'delivery_point_code' => null,
        'postal_code' => null,
        'locality' => null,
        'region' => null,
      ],
      'website_url' => [
        'uri' => 'https://example.com/OU3-modified',
        'title' => 'OU3 webpage modified',
        'options' => [],
        'lang' => 'en',
      ],
      'mobility_factsheet_url' => [
        'uri' => 'https://example.com/OU3-modified/mfs',
        'title' => 'OU3 mobility factsheet modified',
        'options' => [],
        'lang' => 'en',
      ],
      'logo_url' => [
        'uri' => 'https://example.com/OU3-modified/logo',
        'title' => 'OU3 logo modified',
        'options' => [],
        'lang' => 'en',
      ],
    ];
  }

  /**
   * Test OUnit revision creation and validation.
   */
   public function testOunitRevisionability(): void {
    $original_vid = $this->originalOunit->getRevisionId();

    foreach ($this->provideModifiedOunitData() as $field => $data) {
      $this->originalOunit->set($field, $data);
    }

    $this->originalOunit->setNewRevision(TRUE);

    $violations = $this->originalOunit->validate();
    $this->assertCount(0, $violations, 'The entity should be valid with modified data.');

    $this->originalOunit->save();
    $new_vid = $this->originalOunit->getRevisionId();

    $this->assertNotEquals($original_vid, $new_vid, 'A new revision ID was generated.');

    /** @var \Drupal\Core\Entity\RevisionableStorageInterface $storage */
    $storage = \Drupal::entityTypeManager()->getStorage('ounit');
    $revision = $storage->loadRevision($new_vid);

    $this->assertEquals('Original OUnit modified', $revision->label());
  }

  /**
   * Tests if old revision values match original data.
   */
  public function testOunitRevisionedFields(): void {
    /** @var \Drupal\Core\Entity\RevisionableStorageInterface $storage */
    $storage = \Drupal::entityTypeManager()->getStorage('ounit');

    $original_vid = $this->originalOunit->getRevisionId();
    $field_keys = array_keys(self::provideModifiedOunitData());
    $original_values = [];

    foreach ($field_keys as $field) {
      $original_values[$field] = $this->originalOunit->get($field)->getValue();
    }

    foreach (self::provideModifiedOunitData() as $field => $data) {
      $this->originalOunit->set($field, $data);
    }

    $this->originalOunit->setNewRevision(TRUE);
    $this->originalOunit->save();
    $new_vid = $this->originalOunit->getRevisionId();

    $old_revision = $storage->loadRevision($original_vid);

    foreach ($original_values as $field => $old_value) {
      $actual_value = $old_revision->get($field)->getValue();

      // Special handling for URL/Link fields which often lose 'lang' or 'options' defaults.
      if (in_array($field, ['logo_url', 'website_url', 'mobility_factsheet_url'])) {
        foreach ($old_value as $delta => $properties) {
          // Only compare the core keys.
          $this->assertEquals($properties['uri'], $actual_value[$delta]['uri'], "URI mismatch in $field");
          $this->assertEquals($properties['title'], $actual_value[$delta]['title'], "Title mismatch in $field");
        }
      } else {
        $this->assertEquals(
          $old_value,
          $actual_value,
          "Field '$field' in the old revision should match the original data."
        );
      }
    }
  }

}
