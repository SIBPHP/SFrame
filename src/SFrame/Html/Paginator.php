<?php

namespace SFrame\Html;

/**
 * Paginator
 */
class Paginator
{
    protected $_itemCount = 0;
    protected $_pageCount = 1;
    protected $_perPage = 20;
    protected $_curPage = 1;
    protected $_pageRange = 12;
    protected $_pages = null;
    protected $_pageurl = '';

    /**
     * construct
     *
     * @param int $page current page number
     * @param int $count total count
     * @param int $perPage iterms per page
     * @param int $pageRange page range show
     */
    public function __construct($page, $count, $perPage = null, $pageRange = null)
    {
        if ($perPage) {
            $this->setPerPage($perPage);
        }
        if ($pageRange) {
            $this->setPageRange($pageRange);
        }
        $this->_itemCount = (int) $count;
        $this->_curPage = ($page < 1) ? 1 : (int) $page;
        $this->_pageCount = ceil($count / $this->_perPage);
    }

    /**
     * current url for pagination
     */
    public function setPageurl($pageurl)
    {
        $this->_pageurl = $pageurl;
        return $this;
    }

    /**
     * Get page data
     *
     * @return stdClass
     */
    public function getPages()
    {
        if (null === $this->_pages) {
            $this->_pages = $this->_createPages();
        }
        return $this->_pages;
    }

    /**
     * Render the pagination html
     */
    public function render()
    {
        echo $this->getHtml();
    }

    /**
     * generate the pagination html
     * @return string html
     */
    public function getHtml()
    {
        $url = $this->_pageurl ? $this->_pageurl : $_SERVER['REQUEST_URI'];
        $part = explode('?', $url);
        if (isset($part[1])) {
            $part[1] = trim(preg_replace('/&?page=\d+/i', '', $part[1]), '&');
        }
        $url = $part[0] . '?' . (empty($part[1]) ? '' : $part[1] . '&');

        $html = '';
        if ($this->getPageCount() > 1) {
            $page = $this->getPages();
            $html = '<div id="page">';
            $html .= '<div class="count">共' . $page->itemCount . '条记录， ' . $page->curPage . '/' . $page->pageCount . '</div>';
            $html .= '<ul>';
            $html .= '<li class="first"><a href="' . $url . 'page=1">第一页</a></li>';
            if (isset($page->pre)) {
                $html .= '<li class="pre"><a href="' . $url . 'page=' . $page->pre . '">上一页</a></li>';
            }
            foreach ($page->ranges as $p) {
                $cur = ($p == $page->curPage) ? ' class="cur"' : '';
                $html .= '<li' . $cur . '><a href="' . $url . 'page=' . $p . '">' . $p . '</a></li>';
            }
            if (isset($page->next)) {
                $html .= '<li class="next"><a href="' . $url . 'page=' . $page->next . '">下一页</a></li>';
            }
            $html .= '<li class="last"><a href="' . $url . 'page=' . $page->pageCount . '">最后一页</a></li>';
            $html .= '</ul></div>';
        }
        return $html;
    }

    /**
     * Create page
     *
     * @param PaginatorInterface $pageStyle
     * @return object stdClass
     */
    protected function _createPages()
    {
        $pages = new stdClass();
        $pages->itemCount = $this->_itemCount;
        $pages->pageCount = $this->_pageCount;
        $pages->perPage = $this->_perPage;
        $pages->curPage = $this->_curPage;
        $pages->pageRange = $this->_pageRange;
        $pages->pageUrl = $this->_pageurl;
        
        // Previous page
        if ($pages->curPage > 1) {
            $pages->pre = $this->_curPage - 1;
        }
        
        // Next page
        if ($pages->curPage < $this->_pageCount) {
            $pages->next = $this->_curPage + 1;
        }

        $pages->ranges = $this->_loadPageStyle();
        $pages->rangeFirst = empty($pages->ranges) ? 1 : min($pages->ranges);
        $pages->rangeLast = empty($pages->ranges) ? 1 : max($pages->ranges);
        return $pages;
    }

    /**
     * 分页基础分析
     *
     * 加载分页样式
     * 如无扩展算法，则加载默认分页算法
     * @return array
     */
    protected function _loadPageStyle()
    {
        $pageRange = $this->getPageRange();
        $pageNumber = $this->getCurPage();
        $pageCount = $this->getPageCount();
        if ($pageRange > $pageCount) {
            $pageRange = $pageCount;
        }
        $delta = ceil($pageRange / 2);
        if ($pageNumber - $delta > $pageCount - $pageRange) {
            $lowerBound = $pageCount - $pageRange + 1;
            $upperBound = $pageCount;
        } else {
            if ($pageNumber - $delta < 0) {
                $delta = $pageNumber;
            }
            $offset = $pageNumber - $delta;
            $lowerBound = $offset + 1;
            $upperBound = $offset + $pageRange;
        }
        $pages = array();
        for ($i = $lowerBound; $i <= $upperBound; $i++) {
            $pages[] = $i;
        }
        return $pages;
    }

    /**
     * @return int
     */
    public function getItemCount()
    {
        return $this->_itemCount;
    }

    /**
     * @return int
     */
    public function getPageCount()
    {
        return $this->_pageCount;
    }

    /**
     * @return int
     */
    public function getPerPage()
    {
        return $this->_perPage;
    }

    /**
     * @return int
     */
    public function getCurPage()
    {
        return $this->_curPage;
    }

    /**
     * @return int
     */
    public function getPageRange()
    {
        return $this->_pageRange;
    }

    /**
     * @param int $perPage
     */
    public function setPerPage($perPage)
    {
        $this->_perPage = (int) $perPage;
    }

    /**
     * @param int $pageRange
     */
    public function setPageRange($pageRange)
    {
        $this->_pageRange = (int) $pageRange;
    }

}
