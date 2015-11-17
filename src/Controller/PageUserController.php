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
use Request;
use XeLang;
use Xpressengine\Member\GuardInterface;
use Xpressengine\Plugins\Page\Module\Page as PageModule;
use Xpressengine\Plugins\Page\PageEntity;
use Presenter;
use Xpressengine\Document\Exceptions\DocumentNotExistsException;
use Xpressengine\Plugins\Page\PageHandler;
use Xpressengine\Routing\InstanceConfig;
use Xpressengine\Keygen\Keygen;
use Xpressengine\Document\DocumentEntity;

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
        Presenter::setSkin(PageModule::getId());
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

        return Presenter::make('show', [
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
     * @param PageHandler    $pageHandler page handler
     * @param GuardInterface $guard       member
     *
     * @return \Xpressengine\Presenter\RendererInterface
     * @throws \Xpressengine\Keygen\UnknownGeneratorException
     */
    public function preview(PageHandler $pageHandler, GuardInterface $guard)
    {
        $pageId = $this->pageId;
        $config = $pageHandler->getPageConfig($pageId);
        $user = $guard->user();

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

        $previewDoc = new DocumentEntity($documentInputs);
        $previewDoc->id = 'preview-' . (new Keygen())->generate();
        $previewDoc->instanceId = $pageId;
        $previewDoc->setAuthor($user);

        if ($user instanceof Guest) {
            $previewDoc->setUserType($previewDoc::USER_TYPE_GUEST);
        }

        $page = new PageEntity([
            'pageId' => $pageId,
            'uid' => $previewDoc->id,
            'content' => $previewDoc,
        ]);
        $content = $previewDoc->content;

        return Presenter::make('show', [
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
        } catch (DocumentNotExistsException $e) {
            throw new $e;
        }

        return $doc;
    }
}
