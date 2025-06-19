# EWP Organizational Units

Drupal implementation of the EWP Organizational Units API.

See the **Erasmus Without Paper** specification for more information:

  - [Organizational Units API](https://github.com/erasmus-without-paper/ewp-specs-api-ounits/tree/v2.1.1)

## Installation

Include the repository in your project's `composer.json` file:

    "repositories": [
        ...
        {
            "type": "vcs",
            "url": "https://github.com/EuropeanUniversityFoundation/ewp_ounits"
        }
    ],

Then you can require the package as usual:

    composer require euf/ewp_ounits

Finally, install the module:

    drush en ewp_ounits

## Usage

A custom content entity named **Organizational Unit** is provided with initial configuration to match the EWP specification. It can be configured like any other fieldable entity on the system. The administration paths are placed under `/admin/ewp/`.

## Constraint validation

The **Organizational Unit** entity type includes constraint validation to ensure that:

  - `ounit_code` and `ounit_id` values are unique per `parent_hei` value;
  - `parent_ounit` can only reference `ounit` entities with the same `parent_hei` value.

This module also has a dependency on the [Entity reference validators](https://www.drupal.org/project/entity_reference_validators) contributed module to prevent circular (direct or recursive) references in `parent_ounit`.

The implementation of circular reference constraint validation is accomplished via third party settings, since `parent_ounit` is a bundle field (see `config/install/field.field.ounit.ounit.parent_ounit.yml`).

**Note:** Implementing the Entity reference validators on base fields requires custom code.
