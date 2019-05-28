<?php
/**
 * PageUserController.php
 *
 * This file is part of the Xpressengine package.
 *
 * PHP version 7
 *
 * @category    Page
 * @package     Xpressengine\Plugins\Page
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        http://www.xpressengine.com
 */

namespace Xpressengine\Plugins\Page\Controller;

use App\Http\Controllers\Controller;
use XePresenter;
use XeLang;
use Xpressengine\Document\Models\Document;
use Xpressengine\Http\Request;
use Xpressengine\Plugins\Page\Module\Page as PageModule;
use Xpressengine\Plugins\Page\PageEntity;
use Xpressengine\Plugins\Page\PageHandler;
use Xpressengine\Routing\InstanceConfig;

/**
 * PageUserController
 *
 * @category    Page
 * @package     Xpressengine\Plugins\Page
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        http://www.xpressengine.com
 */
class PageUserController extends Controller
{
    /**
     * @var string $pageId page instance id
     */
    protected $pageId;

    /**
     * PageUserController constructor.
     */
    public function __construct()
    {
        XePresenter::setSkinTargetId(PageModule::getId());
        $instanceConfig = InstanceConfig::instance();
        $this->pageId = $instanceConfig->getInstanceId();
    }

    /**
     * index
     *
     * @param Request     $request     request
     * @param PageHandler $pageHandler page handler
     *
     * @return \Xpressengine\Presenter\Presentable
     */
    public function index(Request $request, PageHandler $pageHandler)
    {
        $pageId = $this->pageId;
        $config = $pageHandler->getPageConfig($pageId);
        $mode = 'pc';
        if ($config->get('mobile') && $request->isMobile()) {
            $mode = 'mobile';
        }

        $page = $pageHandler->getPageModel($pageId, $mode, XeLang::getLocale());
        if ($page === null) {
            $locales = XeLang::getLocales();
            foreach ($locales as $locale) {
                $page = $pageHandler->getPageModel($pageId, $mode, $locale);
                if ($page !== null) {
                    break;
                }
            }
        }

        $pageCommentTarget = $pageHandler->getPageCommentTarget($pageId);

        return XePresenter::make('show', [
            'pageId' => $pageId,
            'page' => $page,
            'pageCommentTarget' => $pageCommentTarget,
            'title' => $page->title,
            'content' => $page->content,
            'config' => $config
        ]);
    }

    /**
     * preview
     *
     * @param Request     $request     request
     * @param PageHandler $pageHandler page handler
     *
     * @return \Xpressengine\Presenter\Presentable
     */
    public function preview(Request $request, PageHandler $pageHandler)
    {
        $pageId = $this->pageId;
        $config = $pageHandler->getPageConfig($pageId);
        $user = $request->user();

        $title = $request->get('pageTitle');
        $mode = $request->get('mode');
        $documentInputs = $request->except(
            '_token',
            'certifyKey_confirmation',
            'anonymity',
            'queryString',
            'notice',
            '_codes',
            '_links',
            '_hashTags',
            '_mentions',
            '_files'
        );

        $previewDoc = new Document($documentInputs);
        $previewDoc->id = 'preview-' . app('xe.keygen')->generate();
        $previewDoc->instanceId = $pageId;
        $previewDoc->user()->associate($user);

        $page = new PageEntity([
            'pageId' => $pageId,
            'uid' => $previewDoc->id,
            'content' => $previewDoc,
        ]);
        $content = $previewDoc->content;

        return XePresenter::make('show', [
            'pageId' => $pageId,
            'page' => $page,
            'title' => $title,
            'content' => $content,
            'config' => $config
        ]);
    }
}
