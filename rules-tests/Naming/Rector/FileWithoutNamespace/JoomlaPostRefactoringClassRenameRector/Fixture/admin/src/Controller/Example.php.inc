<?php

namespace Acme\Example\Administrator\Controller;

class ExampleController extends \Joomla\CMS\MVC\Controller\BaseController
{
    /**
     * Returns the model
     *
     * @return \ExampleModelFoobar
     */
    public function theModel(): \ExampleModelFoobar
    {
        return $this->getModel('Foobar');
    }
}

?>
-----
<?php

namespace Acme\Example\Administrator\Controller;

class ExampleController extends \Joomla\CMS\MVC\Controller\BaseController
{
    /**
     * Returns the model
     *
     * @return \Acme\Example\Administrator\Model\FoobarModel
     */
    public function theModel(): \Acme\Example\Administrator\Model\FoobarModel
    {
        return $this->getModel('Foobar');
    }
}

?>