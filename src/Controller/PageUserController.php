<?php
/**
 * Page User Controller
 *
 * PHP version 5
 *
 * @category    Page
 * @package     Xpressengine\Plugins\Page\Controller
 * @author      XE Team (develop) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
namespace Xpressengine\Plugins\Page\Controller;

use App\Http\Controllers\Controller;
use XePresenter;
use XeLang;
use Xpressengine\Document\Exceptions\DocumentNotFoundException;
use Xpressengine\Document\Models\Document;
use Xpressengine\Http\Request;
use Xpressengine\Plugins\Page\Module\Page as PageModule;
use Xpressengine\Plugins\Page\PageEntity;
use Xpressengine\Plugins\Page\PageHandler;
use Xpressengine\Routing\InstanceConfig;

/**
 * Page User Controller
 *
 * @category    Page
 * @package     Xpressengine\Plugins\Page\Controller
 * @author      XE Team (develop) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
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
     * @param PageHandler $pageHandler page handler
     *
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function index(PageHandler $pageHandler)
    {
        $request = app('request');

        $pageId = $this->pageId;
        $config = $pageHandler->getPageConfig($pageId);
        $mode = 'pc';
        if ($config->get('mobile') && $request->isMobile()) {
            $mode = 'mobile';
        }

        $page = $pageHandler->getPageEntity($pageId, $mode, XeLang::getLocale());

        return XePresenter::make('show', [
            'pageId' => $pageId,
            'page' => $page,
            'title' => $page->content->title,
            'content' => $page->content->content,
            'config' => $config
        ]);
    }

    /**
     * preview
     *
     * @param Request     $request
     * @param PageHandler $pageHandler page handler
     *
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function preview(Request $request, PageHandler $pageHandler)
    {
        $pageId = $this->pageId;
        $config = $pageHandler->getPageConfig($pageId);
        $user = $request->user();

        /** @var \Illuminate\Http\Request $request */
        $request = app('request');

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

    /**
     * get file's source
     *
     * @param string $url url
     * @param string $id  id
     *
     * @return void
     */
    public function fileSource($url, $id)
    {
        /** @var \Xpressengine\Storage\Storage $storage */
        $storage = app('xe.storage');
        $file = $storage->get($id);

        /** @var \Xpressengine\Media\MediaManager $mediaManager */
        $mediaManager = \App::make('xe.media');
        if ($mediaManager->is($file) === true) {
            /** @var \Xpressengine\Media\Handlers\ImageHandler $handler */
            $handler = $mediaManager->getHandler(\Xpressengine\Media\Spec\Media::TYPE_IMAGE);
            $dimension = 'L';
            if (\Agent::isMobile() === true) {
                $dimension = 'M';
            }
            $media = $handler->getThumbnail($mediaManager->make($file), PageModule::THUMBNAIL_TYPE, $dimension);
            $file = $media->getFile();
        }

        header('Content-type: ' . $file->mime);
        echo $storage->read($file);
    }

    /**
     * download file
     *
     * @param string $url url
     * @param string $id  id
     *
     * @throws \Xpressengine\Storage\Exceptions\NotExistsException
     * @return void
     */
    public function fileDownload($url, $id)
    {
        /** @var \Xpressengine\Storage\Storage $storage */
        $storage = app('xe.storage');
        $file = $storage->get($id);

        header('Content-type: ' . $file->mime);

        $storage->download($file);
    }

    /**
     * getPageDocument
     *
     * @return mixed
     */
    protected function getPageDocument()
    {
        $pageId = $this->pageId;
        $handler = app('xe.page.handler');

        $pageConfig = $handler->getConfig($pageId);
        $documentUid = $pageConfig->get('pageUid');

        try {
            $doc = $handler->getPageContent($documentUid, $pageId);
        } catch (DocumentNotFoundException $e) {
            throw new $e;
        }

        return $doc;
    }
}
