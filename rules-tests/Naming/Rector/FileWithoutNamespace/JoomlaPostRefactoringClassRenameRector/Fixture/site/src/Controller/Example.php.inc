<?php

namespace Acme\Example\Site\Controller;

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

namespace Acme\Example\Site\Controller;

class ExampleController extends \Joomla\CMS\MVC\Controller\BaseController
{
    /**
     * Returns the model
     *
     * @return \Acme\Example\Site\Model\FoobarModel
     */
    public function theModel(): \Acme\Example\Site\Model\FoobarModel
    {
        return $this->getModel('Foobar');
    }
}

?>