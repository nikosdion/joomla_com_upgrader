# Joomla 3 Component Upgrade Rectors

Rector rules to easily upgrade Joomla 3 components to Joomla 4 MVC

Copyright (C) 2022  Nicholas K. Dionysopoulos

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

## Sponsors welcome

Do you have a Joomla extensions development business? Are you a web agency using tons of custom components? Maybe you can sponsor this work! It will save you tons of time — in the order of dozens of hours per component.

Sponsorships will help me spend more time working on this tool, the Joomla extension developer's documentation and core Joomla code.

If you're interested hit me up at [the Contact Me page](https://www.dionysopoulos.me/contact-me.html?view=item)! You'll get my gratitude and your logo on this page.

## Requirements

* Rector 0.14
* PHP 7.2 or later; 8.1 or later with XDebug _turned off_ recommended for best performance
* Composer 2.x

Your component project must have the structure described below.

* Your component's backend code must be in a folder named `administrator`, `admin`, `backend` or `administrator/components/com_yourcomponent` (where `com_yourcomponent` is the name of your component).

* Your component's frontend code must be in a folder named `site`, `frontend`, or `components/com_yourcomponent` (where `com_yourcomponent` is the name of your component).

* Your component's media files must be in a folder named `media`, or `media/com_yourcomponent` (where `com_yourcomponent` is the name of your component).

## What is this all about?

This repository provides Rector rules to automatically refactor your legacy Joomla 3 component into Joomla 4 MVC. It does not do everything, it just handles the boring, repeated and soul–crushing work. You will still need to do a few things.

**What it does**
* Namespace all of your MVC (Model, Controller, View and Table) classes and place them into the appropriate directories.
* Refactor and namespace helper classes.
* Change static type hints in PHP code and docblocks.

**What I would like to add**
* Refactor and namespace HTML helper classes into services.
  * Stored in <root>/helpers/html
  * The <root> always has a views directory
  * The class name starts with JHtml
* Refactor and namespace custom form field classes.
* Refactor and namespace custom form rule classes.
* Refactor static getInstance calls to the base model and table classes.
* Refactor getModel and getView calls in controllers.
* Rename language files so that they do NOT have a language prefix.
* Move view templates into the new folder structure.
* Update the XML manifest with the namespace prefix.
* Update the XML manifest with the new language file prefixes.
* Create a basic `services/provider.php` file.
* Move backend and frontend XML forms to the appropriate folders.
* Replace `addfieldpath` with `addfieldprefix` in XML forms.

**What it CAN NOT and WILL NOT do**
* Remove your old entry point file, possibly converting it to a custom Dispatcher.
* Refactor static getInstance calls to _descendants of_ the base model and table classes.

## How to use

Checkout your component's repository.

Update your `composer.json` file with the following:

```json
{
  "minimum-stability": "dev",
  "prefer-stable": true,
  "repositories": [
    {
      "name": "nikosdion/joomla_typehints",
      "type": "vcs",
      "url": "https://github.com/nikosdion/joomlatypehints"
    },
    {
      "name": "nikosdion/joomla_com_upgrader",
      "type": "vcs",
      "url": "https://github.com/nikosdion/joomla_com_upgrader"
    }
  ],
  "require-dev": {
    "rector/rector": "^0.14.0",
    "nikosdion/joomla_typehints": "*",
    "nikosdion/joomla_com_upgrader": "*",
    "friendsofphp/php-cs-fixer": "^3.0"
  }
}
```

Run `composer update --dev` to install the dependencies.

Create a new `rector.php` in your component project's root with the following contents:

```php
<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Naming\Config\JoomlaLegacyPrefixToNamespace;
use Rector\Naming\Rector\FileWithoutNamespace\JoomlaHelpersToJ4Rector;
use Rector\Naming\Rector\FileWithoutNamespace\JoomlaLegacyMVCToJ4Rector;
use Rector\Naming\Rector\FileWithoutNamespace\RenamedClassHandlerService;
use Rector\Naming\Rector\JoomlaPostRefactoringClassRenameRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->disableParallel();

    $rectorConfig->paths([
        __DIR__ . '/admin',
        __DIR__ . '/site',
        __DIR__ . '/script.php',
        // Add any more directories or files your project may be using here
    ]);
    
    $rectorConfig->skip([
        // These are our auto-generated renamed class maps for the second pass 
        __DIR__ . '_classmap.php',
        __DIR__ . '_classmap.json',
    ]);
    
    // Required to autowire the custom services used by our Rector rules
    $services = $rectorConfig
        ->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    // Register our custom services and configure them
	$services->set(RenamedClassHandlerService::class)
	         ->arg('$directory', __DIR__);

    // Basic refactorings
    $rectorConfig->sets([
        // Auto-refactor code to at least PHP 7.2 (minimum Joomla version)
        LevelSetList::UP_TO_PHP_72,
        // Replace legacy class names with the namespaced ones
        __DIR__ . '/vendor/nikosdion/joomla_typehints/rector/joomla_4_0.php',
        // Use early returns in if-blocks (code quality)
        SetList::EARLY_RETURN,
    ]);

    // Configure the namespace mappings
    $joomlaNamespaceMaps = [
        new JoomlaLegacyPrefixToNamespace('Helloworld', 'Acme\HelloWorld', []),
        new JoomlaLegacyPrefixToNamespace('HelloWorld', 'Acme\HelloWorld', []),
    ];

    // Auto-refactor the Joomla MVC classes
    $rectorConfig->ruleWithConfiguration(JoomlaLegacyMVCToJ4Rector::class, $joomlaNamespaceMaps);
    $rectorConfig->ruleWithConfiguration(JoomlaHelpersToJ4Rector::class, $joomlaNamespaceMaps);
    $rectorConfig->rule(JoomlaPostRefactoringClassRenameRector::class);

    // Replace Fully Qualified Names (FQN) of classes with `use` imports at the top of the file.
    $rectorConfig->importNames();
    // Do NOT import short class names such as `DateTime`
    $rectorConfig->importShortClasses(false);
};
```

The lines you need to change are:
```php
    $joomlaNamespaceMaps = [
        new JoomlaLegacyPrefixToNamespace('Helloworld', 'Acme\HelloWorld', []),
        new JoomlaLegacyPrefixToNamespace('HelloWorld', 'Acme\HelloWorld', []),
    ];
```
where `HelloWorld` is the name of your component without the `com_` prefix and `Acme\HelloWorld` is the namespace prefix you want to use for your component. It is recommended to use the convention `CompanyName\ComponentNameWithoutCom` or `CompanyName\Component\ComponentNameWithoutCom` for your namespace prefix.

**CAUTION!** Note that I added two lines here with the legacy Joomla 3 namespace being `Helloworld` in one and `HelloWorld` in another. That's because in Joomla 3 the case of the prefix of your component does not matter. `Helloworld`, `HelloWorld` and `HELLOWORLD` would work just fine. The code refactoring rules are, however, case–sensitive. As a result you need to add as many lines as you have different cases in your component.

The third argument, the empty array `[]`, is a list of class names which begin with the old prefix that you do not want to namespace. I can't think of a reason why you want to do that but I can neither claim I can think of any use case. So I added that option _just in case_ you need it.

Now you can run Rector to do _a hell of a lot_ of the refactoring necessary to convert your component to Joomla 4 MVC.

First, we tell it to collect the classes which will be renamed but without doing any changes to the files. **THIS STEP IS MANDATORY**.

```bash
php ./vendor/bin/rector --dry-run --clear-cache
```

Note: The `--dry-run` parameter prints out the changes. Now is a good time to make sure they are not wrong.

Then we can run it for real (**this step modifies the files in your project**):

```bash
php ./vendor/bin/rector --clear-cache
```
