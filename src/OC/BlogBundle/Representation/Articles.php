<?php

namespace OC\BlogBundle\Representation;

use Pagerfanta\Pagerfanta;
use JMS\Serializer\Annotation\Type;

class Articles
{
    /**
     * @var Pagerfanta
     *
     * @Type("array<OC\BlogBundle\Entity\Article>")
     */
    public $data;

    public $meta;

    public function __construct(Pagerfanta $pager)
    {
        $this->data = $pager;

        $this->addMeta('limit', $pager->getMaxPerPage());
        $this->addMeta('current_items', count($pager->getCurrentPageResults()));
        $this->addMeta('total_items', $pager->getNbResults());
        $this->addMeta('offset', $pager->getCurrentPageOffsetStart());
    }

    public function addMeta($name, $value)
    {
        if(isset($this->meta[$name])) {
            throw new \LogicException(sprintf('This meta already exists. You are trying to override this meta, use the setMeta method instead for the %s meta'), $name);
        }

        $this->setMeta($name, $value);
    }

    public function setMeta($name, $value)
    {
        $this->meta[$name] = $value;
    }
}