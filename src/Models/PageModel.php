<?php
/**
 * Page Model
 *
 * PHP version 7
 *
 * @category    Page
 * @package     Xpressengine\Plugins\Page
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
namespace Xpressengine\Plugins\Page\Models;

use Xpressengine\Document\Models\Document;
use Xpressengine\Plugins\Comment\CommentUsable;
use Xpressengine\Routing\InstanceRoute;
use Xpressengine\User\UserInterface;
use Xpressengine\Seo\SeoUsable;
use Xpressengine\Media\Models\Media;
use Xpressengine\Media\MediaManager;

/**
 * Page Model
 *
 * @category    Page
 * @package     Xpressengine\Plugins\Page
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class PageModel extends Document implements CommentUsable, SeoUsable
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

    /**
     * Returns title
     *
     * @return string
     */
    public function getTitle()
    {
        $title = str_replace('"', '\"', $this->getAttribute('title'));

        return $title;
    }

    /**
     * get compiled content
     *
     * @return string
     */
    public function getContent()
    {
        return compile($this->instance_id, $this->content, $this->format === static::FORMAT_HTML);
    }

    /**
     * Returns description
     *
     * @return string
     */
    public function getDescription()
    {
        return str_replace(
            ['"', "\n"],
            ['\"', ''],
            $this->getAttribute('pure_content')
        );
    }

    /**
     * Returns keyword
     *
     * @return string|array
     */
    public function getKeyword()
    {
        return [];
    }

    /**
     * Returns url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->canonical;
    }

    /**
     * Set canonical url
     *
     * @param string $url url
     * @return $this
     */
    public function setCanonical($url)
    {
        $this->canonical = $url;

        return $this;
    }

    /**
     * Returns image url list
     *
     * @return array
     */
    public function getImages()
    {
        $images = [];

        /** @var PageHandler $handler */
        $handler = app('xe.page.handler');
        $config = $handler->getPageConfig($this->instance_id);
        $thumbId = $handler->getThumbId($this->instance_id, $this->id);

        /** @var MediaManager $mediaManager */
        $mediaManager = app('xe.media');
        $imageHandler = $mediaManager->getHandler(Media::TYPE_IMAGE);
        $images = [];
        if ($thumbId != null) {
            $file = \XeStorage::find($thumbId);
            if ($mediaManager->getFileType($file) === Media::TYPE_IMAGE) {
                $images[] = $imageHandler->make($file);
            }
        }

        return $images;
    }

}
