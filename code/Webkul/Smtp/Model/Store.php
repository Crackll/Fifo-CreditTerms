<?php
/**
 * Webkul_Smtp
 * @package   Webkul_Smtp
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Smtp\Model;

class Store
{
    /**
     * @var int/null
     */
    private $storeId = null;

    /**
     * @var string
     */
    private $from = null;
    
    /**
     * @return int|null
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * @param $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }

    /**
     * @return string|array
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param string|array $from
     * @return $this
     */
    public function setFrom($from)
    {
        $this->from = $from;
        return $this;
    }
}
