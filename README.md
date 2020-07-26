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
