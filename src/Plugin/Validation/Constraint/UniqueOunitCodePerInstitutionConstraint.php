<?php

declare(strict_types=1);

namespace Drupal\ewp_ounits\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;

/**
 * Provides a UniqueOunitCodePerInstitution constraint.
 */
#[Constraint(
  id: 'UniqueOunitCodePerInstitution',
  label: new TranslatableMarkup('Unique OUnit code per Institution', [], ['context' => 'Validation'])
)]
final class UniqueOunitCodePerInstitutionConstraint extends SymfonyConstraint {

  /**
   * The error message if the OUnit code is already in use at the Institution.
   *
   * @var string
   */
  public string $message = "The OUnit code %ounit_code is already in use at @hei.";

}
