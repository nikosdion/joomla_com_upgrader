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

## What is this all about?

This repository provides Rector rules to automatically refactor your legacy Joomla 3 component into Joomla 4+ MVC.

It does not do everything. It will definitely _not_ result in a _fully working_ Joomla 4 component. The goal of this tool is to automate the boring, repeated and soul–crushing work. It sets you off to a great start into refactoring a legacy Joomla 3 component into a new Joomla 4+ MVC modern component. I wish I had that tool when I refactored by hand 20 extensions between March 2020 and October 2021.

If you don't know much about the Joomla 4+ MVC and trying to divine how it works by reading its source code isn't your jam you may want to take a look at the [Joomla Extensions Development](https://github.com/nikosdion/joomla_extensions_development) book I'm writing. Like most of my work it's available free of charge, under an open source license, with full source code available, on a platform that fosters open collaboration.

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

## What can this tool do for me?

**What it already does**
* Namespace all of your MVC (Model, Controller, View and Table) classes and place them into the appropriate directories.
* Refactor and namespace helper classes (e.h. ExampleHelper, ExampleHelperSomething, etc).
* Refactor and namespace HTML helper classes (e.g. JHtmlExample) into HTML services.
* Refactor and namespace custom form field classes (e.g. JFormFieldExample, JFormFieldModal_Example, etc).
* Change static type hints in PHP code and docblocks.

**What I would like to add**
* ⚙️ Refactor and namespace custom form rule classes.
* ⚙️ Refactor static getInstance calls to the base model and table classes.
* ⚙️ Refactor getModel and getView calls in controllers.
* 📁 Update the XML manifest with the namespace prefix.
* 📁 Rename language files so that they do NOT have a language prefix.
* 📁 Update the XML manifest with the new language file prefixes.
* 📁 Move view templates into the new folder structure.
* 📁 Move backend and frontend XML forms to the appropriate folders.
* 📁 Replace `addfieldpath` with `addfieldprefix` in XML forms.
* ❓ Create a basic `services/provider.php` file. This is NOT a complete file, you still have to customise it!

**What it CAN NOT and WILL NOT do**
* Remove your old entry point file, possibly converting it to a custom Dispatcher. This is impossible. It requires understanding what your component does and make informed decisions on refactoring.
* Refactor your frontend SEF URL Router. It's best to read my book to figure out how to proceed manually.
* Create a custom component extension class to register Html, Category, Router, Tags etc. services. This requires knowing how your component works. 
* Refactor static getInstance calls to _descendants of_ the base model and table classes. It's not impossible, I just don't have the time to figure it out (yet?).

In short, this tool tries to do the 30% of the migration work which would have taken you 70% of the time. Instead of spending _days, or weeks,_ or repetitive, boring, error–prone, soul–crushing grind you spend less than half an hour to read this README, set up Rector and another minute or so to automate all that mind–boggling drudgery. You can instead spend these few days to read my book, learn how Joomla 4+ MVC works and convert your component faster than you thought is possible!

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
use Rector\Naming\Rector\FileWithoutNamespace\JoomlaHtmlHelpersRector;
use Rector\Naming\Rector\FileWithoutNamespace\JoomlaFormFieldsRector;

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
    $rectorConfig->ruleWithConfiguration(JoomlaHtmlHelpersRector::class, $joomlaNamespaceMaps);
    $rectorConfig->ruleWithConfiguration(JoomlaFormFieldsRector::class, $joomlaNamespaceMaps);
    // Dual purpose. 1st pass: collect renamed classes. 2nd pass: apply the renaming to type hints.
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

## How this tool came to be

There's been a discussion on Joomla's GitHub repository about how “hard” it is to convert a Joomla 3 component to the new MVC shipped with Joomla 4. Having had the experience of converting 20 extensions myself — and several more dozens of plugins and modules which came with three quarters of them — I realised it's not “hard” but two crucial things were missing: documentation and a tool to get you started.

The lack of documentation is something I lamented when I started trying to figure out how to support Joomla 4 in my own extensions. I decided to address it with my [Joomla Extensions Development](https://github.com/nikosdion/joomla_extensions_development) book.

How to get started is a pained story. Most of my own code was already namespaced (as I was using FOF for my components which since version 3, released in 2015, required namespacing the code), therefore my experience was mostly changing namespaces and converting the internals from FOF MVC to core Joomla 4 MVC. I had two components written in plain old Joomla 3 MVC and _that_ experience sucked! I totally get the people who say it's hard. It's so boring and you need to do so much work before you see any results that it feel intimidating and unapproachable.

At this point I've been using Rector for years to massage my code whenever I am changing something — albeit it's mostly been renaming classes. I looked at how to write custom Rector rules and I realised I actually understood what's going on! Apparently a summer spent 24 years ago writing my own compiler following a tutorial gave me a good background to write Rector rules today. Huh!

So, here we are. Custom Rector rules to start converting legacy Joomla 3 MVC components to Joomla 4, free of charge, because **community matters**. ☮️