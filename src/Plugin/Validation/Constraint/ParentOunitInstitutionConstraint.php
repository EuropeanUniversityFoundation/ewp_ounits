<?php

declare(strict_types=1);

namespace Drupal\ewp_ounits\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;

/**
 * Provides a ParentOunitInstitution constraint.
 */
#[Constraint(
  id: 'ParentOunitInstitution',
  label: new TranslatableMarkup('Parent OUnit Institution', [], ['context' => 'Validation'])
)]
final class ParentOunitInstitutionConstraint extends SymfonyConstraint {

  /**
   * Error message for the Parent OUnit not referencing the same Institution.
   *
   * @var string
   */
  public string $message = "The Parent OUnit must have the same Parent Institution.";

}
