<?php

namespace App\Adapters;

use CodeIgniter\Pager\PagerInterface;

class PagerAdapter implements \JsonSerializable
{
    private PagerInterface $pager;

    public function __construct(PagerInterface $pager)
    {
        $this->pager = $pager;
    }

    public function jsonSerialize()
    {
        return [
            'first' => $this->pager->getPageURI($this->pager->getFirstPage()),
            'last' => $this->pager->getPageURI($this->pager->getLastPage()),
            'previous' => $this->pager->getPreviousPageURI(),
            'next' => $this->pager->getNextPageURI(),
            'pages' => array_map(
                fn (int $page) => ['page' => $page, 'link' => $this->pager->getPageURI($page)],
                range($this->pager->getFirstPage(), $this->pager->getLastPage())),
            'total' => $this->pager->getPageCount(),
            'current' => $this->pager->getCurrentPage(),
        ];
    }
}