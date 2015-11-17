<?php
/**
 * Page Entity
 *
 * PHP version 5
 *
 * @category    Page
 * @package     Xpressengine\Plugins\Page
 * @author      XE Team (develop) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
namespace Xpressengine\Plugins\Page;

use Xpressengine\Document\DocumentEntity;
use Xpressengine\Member\Entities\MemberEntityInterface;
use Xpressengine\Plugins\CommentService\CommentUsable;
use Xpressengine\Support\EntityTrait;

/**
 * Page Entity
 *
 * @property string id
 * @property string pcContent
 * @property string pcUid
 * @property string mobileContent
 * @property string mobileUid
 *
 * @category    Page
 * @package     Xpressengine\Plugins\Page
 * @author      XE Team (develop) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class PageEntity implements CommentUsable
{
    use EntityTrait;

    /**
     * Returns unique identifier
     *
     * @return string
     */
    public function getUid()
    {
        return $this->__get('pcUid');
    }

    /**
     * Returns instance identifier
     *
     * @return string
     */
    public function getInstanceId()
    {
        return $this->__get('pageId');
    }

    /**
     * Returns author
     *
     * @return MemberEntityInterface
     */
    public function getAuthor()
    {
        return $this->__get('pcContent')->getAuthor();
    }
}

