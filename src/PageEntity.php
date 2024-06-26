<?php
/**
 * Page Entity
 *
 * PHP version 7
 *
 * @category    Page
 *
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 *
 * @link        https://xpressengine.io
 */

namespace Xpressengine\Plugins\Page;

use Illuminate\Database\Eloquent\Model;
use Xpressengine\Plugins\Comment\CommentUsable;
use Xpressengine\Plugins\Page\Migrations\PageCommentTargetMigration;
use Xpressengine\Plugins\Page\Models\PageModel;
use Xpressengine\Routing\InstanceRoute;
use Xpressengine\User\UserInterface;

/**
 * Page Entity
 *
 * @category    Page
 *
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 *
 * @link        https://xpressengine.io
 */
class PageEntity extends Model implements CommentUsable
{
    const MODE_PC = 'pc';

    const MODE_MOBILE = 'mobile';

    protected $table = PageCommentTargetMigration::TABLE_NAME;

    public $timestamps = false;

    protected $fillable = ['page_target_id', 'page_instance_id', 'data', 'author_id'];

    public $primaryKey = 'page_target_id';

    public $incrementing = false;

    /**
     * Returns unique identifier
     *
     * @return string
     */
    public function getUid()
    {
        return $this->page_target_id;
    }

    /**
     * Returns instance identifier
     *
     * @return string
     */
    public function getInstanceId()
    {
        return $this->page_instance_id;
    }

    /**
     * Returns author
     *
     * @return UserInterface
     */
    public function getAuthor()
    {
        return $this->getPageModel()->getAuthor();
    }

    /**
     * Returns the link
     *
     * @param  InstanceRoute  $route  route instance
     * @return string
     */
    public function getLink(InstanceRoute $route)
    {
        return $this->getPageModel()->getLink($route);
    }

    /**
     * Get PageModel
     *
     * @param  string  $mode  'pc' or 'mobile'
     * @param  string  $locale  locale
     * @return PageModel|null
     */
    public function getPageModel($mode = self::MODE_PC, $locale = 'ko')
    {
        $data = json_decode($this->data, true);

        /** @var PageHandler $handler */
        $handler = app('xe.page.handler');
        $config = $handler->getPageConfig($this->page_instance_id);

        $dataModes = $data[self::MODE_PC];
        if ($mode == self::MODE_MOBILE && $config->get('mobile') === true) {
            $dataModes = $data[$mode];
        }

        if (isset($dataModes[$locale]) === true) {
            $documentId = $dataModes[$locale];
        } else {
            $documentId = array_shift($dataModes);
        }

        $pageModel = PageModel::where('id', $documentId)->first();

        return $pageModel;
    }
}
