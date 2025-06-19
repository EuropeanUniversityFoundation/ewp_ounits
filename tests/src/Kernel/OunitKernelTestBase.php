<?php

declare(strict_types=1);

namespace Drupal\Tests\ewp_ounits\Kernel;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * Defines an abstract test base for OUnit entity kernel tests.
 *
 * @group ewp_ounits
 */
abstract class OunitKernelTestBase extends EntityKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'user',
    'system',
    'field',
    'link',
    'options',
    'text',
    'ewp_core',
    'ewp_flexible_address',
    'telephone',
    'ewp_phone_number',
    'views',
    'ewp_contact',
    'ewp_institutions',
    'entity_reference_validators',
    'ewp_ounits',
  ];

  /**
   * Home Institution entity.
   *
   * @var \Drupal\ewp_institutions\Entity\InstitutionEntityInterface
   */
  protected $homeHei;

  /**
   * Home Institution entity data.
   *
   * @var array
   */
  protected $homeHeiData = [
    'id' => 1,
    'type' => 'hei',
    'status' => 1,
    'label' => 'Home Institution',
    'hei_id' => 'example.com',
    'name' => [
      [
        'string' => 'Home Institution',
        'lang' => 'en',
      ],
    ],
  ];

  /**
   * Home OUnit entity.
   *
   * @var \Drupal\ewp_ounits\Entity\OunitInterface
   */
  protected $homeOunit;

  /**
   * Home OUnit entity data.
   *
   * @var array
   */
  protected $homeOunitData = [
    'id' => 1,
    'type' => 'ounit',
    'status' => 1,
    'label' => 'Home OUnit',
    'ounit_code' => 'OU1',
    'ounit_id' => '11223344-5566-7788-9900-aabbccddeeff',
    'name' => [
      [
        'string' => 'Home OUnit',
        'lang' => 'en',
      ],
    ],
    'parent_hei' => 1,
  ];

  /**
   * Host Institution entity.
   *
   * @var \Drupal\ewp_institutions\Entity\InstitutionEntityInterface
   */
  protected $otherHei;

  /**
   * Host Institution entity data.
   *
   * @var array
   */
  protected $otherHeiData = [
    'id' => 2,
    'type' => 'hei',
    'status' => 1,
    'label' => 'Other Institution',
    'hei_id' => 'domain.tld',
    'name' => [
      [
        'string' => 'Other Institution',
        'lang' => 'en',
      ],
    ],
  ];

  /**
   * New OUnit entity.
   *
   * @var \Drupal\ewp_ounits\Entity\OunitInterface
   */
  protected $newOunit;

  /**
   * New OUnit entity data.
   *
   * @var array
   */
  protected $newOunitData = [
    'id' => 2,
    'type' => 'ounit',
    'status' => 1,
    'label' => 'New OUnit',
    'ounit_code' => 'OU2',
    'ounit_id' => '12345678-90ab-cdef-1234-567890abcdef',
    'name' => [
      [
        'string' => 'New OUnit',
        'lang' => 'en',
      ],
    ],
    'parent_hei' => 2,
  ];

  /**
   * User entity with required permissions.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $userWithPermissions;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installConfig('ewp_core');
    $this->installConfig('ewp_flexible_address');
    $this->installConfig('ewp_phone_number');
    $this->installConfig('ewp_contact');
    $this->installConfig('ewp_institutions');
    $this->installConfig('ewp_ounits');

    $this->installEntitySchema('user');
    $this->installEntitySchema('contact');
    $this->installEntitySchema('hei');
    $this->installEntitySchema('ounit');

    $heiStorage = \Drupal::entityTypeManager()->getStorage('hei');
    $ounitStorage = \Drupal::entityTypeManager()->getStorage('ounit');

    $homeHei = $heiStorage->create($this->homeHeiData);
    /** @var \Drupal\ewp_institutions\Entity\InstitutionEntityInterface $homeHei */
    $this->homeHei = $homeHei;
    $this->homeHei->save();

    $homeOunit = $ounitStorage->create($this->homeOunitData);
    /** @var \Drupal\ewp_ounits\Entity\OunitInterface $homeOunit */
    $this->homeOunit = $homeOunit;
    $this->homeOunit->save();

    $otherHei = $heiStorage->create($this->otherHeiData);
    /** @var \Drupal\ewp_institutions\Entity\InstitutionEntityInterface $otherHei */
    $this->otherHei = $otherHei;
    $this->otherHei->save();

    $newOunit = $ounitStorage->create($this->newOunitData);
    /** @var \Drupal\ewp_ounits\Entity\OunitInterface $newOunit */
    $this->newOunit = $newOunit;
    $this->newOunit->save();

    $this->userWithPermissions = $this->createUser([
      'view published institution entities',
      'add organizational unit entities',
      'edit organizational unit entities',
      'view published institution entities',
    ]);

    /** @var \Drupal\Core\Session\AccountSwitcherInterface $account_switcher */
    $account_switcher = \Drupal::service('account_switcher');
    $account_switcher->switchTo($this->userWithPermissions);
  }

}
