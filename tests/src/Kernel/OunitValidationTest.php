<?php

declare(strict_types=1);

namespace Drupal\Tests\ewp_ounits\Kernel;

/**
 * Tests validation constraints that apply to ounit entities.
 *
 * @group ewp_ounits
 */
final class OunitValidationTest extends OunitKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
  }

  /**
   * Test OUnit entity validation.
   */
  public function testOunitValidation(): void {
    $this->assertEquals(0, $this->homeOunit->validate()->count());

    $this->homeOunit->set('ounit_code', NULL);
    $this->assertEquals(1, $this->homeOunit->validate()->count());

    $this->homeOunit->set('ounit_id', NULL);
    $this->assertEquals(2, $this->homeOunit->validate()->count());

    $this->homeOunit->set('name', NULL);
    $this->assertEquals(3, $this->homeOunit->validate()->count());
  }

  /**
   * Test OUnit unique fields per Institution constraints.
   */
  public function testOunitUniqueFieldsPerInstitution(): void {
    $this->newOunit->set('parent_hei', $this->homeOunit->get('parent_hei')->target_id);
    $this->assertEquals(0, $this->newOunit->validate()->count());

    $this->newOunit->set('ounit_code', $this->homeOunit->get('ounit_code')->value);
    $this->assertEquals(1, $this->newOunit->validate()->count());

    $this->newOunit->set('ounit_id', $this->homeOunit->get('ounit_id')->value);
    $this->assertEquals(2, $this->newOunit->validate()->count());
  }

  /**
   * Test OUnit parent reference constraints.
   */
  public function testOunitParentReference(): void {
    $this->newOunit->set('parent_hei', 1);
    $this->assertEquals(0, $this->newOunit->validate()->count());
    $this->newOunit->save();

    $this->newOunit->set('parent_ounit', 2);
    $this->assertEquals(1, $this->newOunit->validate()->count());

    $this->newOunit->set('parent_ounit', 1);
    $this->newOunit->save();

    $this->homeOunit->set('parent_ounit', 2);
    $this->assertEquals(1, $this->homeOunit->validate()->count());
  }

}
