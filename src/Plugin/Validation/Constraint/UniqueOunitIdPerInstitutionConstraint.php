<?php

declare(strict_types=1);

namespace Drupal\ewp_ounits\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;

/**
 * Provides a UniqueOunitIdPerInstitution constraint.
 */
#[Constraint(
  id: 'UniqueOunitIdPerInstitution',
  label: new TranslatableMarkup('Unique OUnit ID per Institution', [], ['context' => 'Validation'])
)]
final class UniqueOunitIdPerInstitutionConstraint extends SymfonyConstraint {

  /**
   * The error message if the OUnit ID is already in use at the Institution.
   *
   * @var string
   */
  public string $message = "The OUnit ID %ounit_id is already in use at @hei.";

}
