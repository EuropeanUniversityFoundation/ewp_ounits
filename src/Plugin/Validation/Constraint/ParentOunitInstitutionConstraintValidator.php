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
 * Validates the ParentOunitInstitution constraint.
 */
final class ParentOunitInstitutionConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  const OUNIT = 'ounit';
  const PARENT_OUNIT = 'parent_ounit';

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

    $parent_ounit = $entity->get(self::PARENT_OUNIT)->target_id;
    $parent_hei = $entity->get(self::PARENT_HEI)->target_id;

    if (!empty($parent_ounit)) {
      $parent_ounit_parent_hei = $this->entityTypeManager
        ->getStorage(self::OUNIT)
        ->load($parent_ounit)
        ->get(self::PARENT_HEI)->target_id;

      if ($parent_ounit_parent_hei !== $parent_hei) {
        $this->context->buildViolation($constraint->message)
          ->atPath(self::PARENT_OUNIT)
          ->addViolation();
      }
    }
  }

}
