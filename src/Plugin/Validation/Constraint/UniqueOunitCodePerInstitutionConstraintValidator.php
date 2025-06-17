<?php

declare(strict_types=1);

namespace Drupal\ewp_ounits\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\ewp_ounits\Entity\OunitInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the UniqueOunitCodePerInstitution constraint.
 */
final class UniqueOunitCodePerInstitutionConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  const OUNIT = 'ounit';
  const OUNIT_CODE = 'ounit_code';

  const HEI = 'hei';
  const PARENT_HEI = 'parent_hei';

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs the object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
  ) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $entity, Constraint $constraint): void {
    if (!$entity instanceof OunitInterface) {
      throw new \InvalidArgumentException(
        sprintf('The validated value must be instance of \Drupal\ewp_ounits\Entity\OunitInterface, %s was given.', get_debug_type($entity))
      );
    }

    $ounit_code = $entity->get(self::OUNIT_CODE)->value;
    $parent_hei = $entity->get(self::PARENT_HEI)->target_id;

    $props = [
      self::OUNIT_CODE => $ounit_code,
      self::PARENT_HEI => $parent_hei,
    ];

    $query = $this->entityTypeManager
      ->getStorage(self::OUNIT)
      ->getQuery()
      ->accessCheck(FALSE);

    foreach ($props as $field_name => $field_value) {
      if (empty($field_value)) {
        $query->notExists($field_name);
      }
      else {
        $query->condition($field_name, $field_value);
      }
    }

    $match_ids = $query->execute();

    if (!empty($match_ids)) {
      foreach ($match_ids as $id) {
        if ($id !== $entity->id()) {
          $hei = $this->entityTypeManager
            ->getStorage(self::HEI)
            ->load($parent_hei);

          $this->context->buildViolation($constraint->message, [
            '%ounit_code' => $ounit_code,
            '@hei' => $hei->label(),
          ])->atPath(self::OUNIT_CODE)
            ->addViolation();
        }
      }
    }
  }

}
