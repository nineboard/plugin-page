<?php
/**
 * Page Model
 *
 * PHP version 7
 *
 * @category    Page
 * @package     Xpressengine\Plugins\Page
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        https://xpressengine.io
 */
namespace Xpressengine\Plugins\Page\Models;

use Xpressengine\Document\Models\Document;
use Xpressengine\Plugins\Comment\CommentUsable;
use Xpressengine\Routing\InstanceRoute;
use Xpressengine\User\UserInterface;

/**
 * Page Model
 *
 * @category    Page
 * @package     Xpressengine\Plugins\Page
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        https://xpressengine.io
 */
class PageModel extends Document implements CommentUsable
{
    /**
     * Returns unique identifier
     *
     * @return string
     */
    public function getUid()
    {
        return $this->id;
    }

    /**
     * Returns instance identifier
     *
     * @return string
     */
    public function getInstanceId()
    {
        return $this->instance_id;
    }

    /**
     * Returns author
     *
     * @return UserInterface
     */
    public function getAuthor()
    {
        return $this->user;
    }

    /**
     * Returns the link
     *
     * @param InstanceRoute $route route instance
     * @return string
     */
    public function getLink(InstanceRoute $route)
    {
        return $route->url;
    }
}
