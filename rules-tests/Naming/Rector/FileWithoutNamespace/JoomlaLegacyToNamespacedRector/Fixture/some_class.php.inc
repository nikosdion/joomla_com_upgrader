<?php

namespace Rector\Tests\Naming\Rector\FileWithoutNamespace\JoomlaLegacyToNamespacedRector\Fixture;

/** @var FooModelBar $someModel */
$model = new FooModelBar;

?>
-----
<?php

namespace Rector\Tests\Naming\Rector\FileWithoutNamespace\JoomlaLegacyToNamespacedRector\Fixture;

/** @var \Acme\Foo\BarModel $someModel */
$model = new BarModel;

?>
