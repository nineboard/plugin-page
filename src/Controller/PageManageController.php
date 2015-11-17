<?php
/**
 * Page Manage Controller
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
use Redirect;
use Presenter;
use App;
use Xpressengine\Menu\MenuRetrieveHandler;
use Xpressengine\Document\DocumentEntity;
use Xpressengine\Plugins\CommentService\ManageSection as CommentSection;
use Xpressengine\Plugins\Page\Module\Page as PageModule;
use Xpressengine\Plugins\Page\PageEntity;
use Xpressengine\Plugins\Page\PageHandler;

/**
 * Page Manage Controller
 *
 * @category    Page
 * @package     Xpressengine\Plugins\Page\Controller
 * @author      XE Team (develop) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class PageManageController extends Controller
{
    /**
     * @var PageHandler $pageHandler
     */
    protected $pageHandler;

    /**
     *
     */
    public function __construct()
    {
        Presenter::setSettingsSkin(PageModule::getId());
        $this->pageHandler = app('xe.page.handler');
    }

    /**
     * edit
     *
     * @param MenuRetrieveHandler $menuHandler
     * @param string              $pageId page instance id
     *
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function edit(MenuRetrieveHandler $menuHandler, $pageId)
    {
        $handler = $this->pageHandler;

        $item = $menuHandler->getItem($pageId);
        $menuId = $item->menuId;

        $locales = app('config')->get('xe.lang.locales');
        $siteLocale = $locales[0];
        $currentLocale = Request::get('locale', $siteLocale);

        $config = $handler->getPageConfig($pageId);

        if ($handler->hasLocale($config->get('pcUids'), $currentLocale) === false) {
            // create page entity
            $pcPage = new PageEntity([
                'pageId' => $pageId,
                'uid' => null,
                'content' => new DocumentEntity,
            ]);
        } else {
            $pcPage = $handler->getPageEntity($pageId, 'pc', $currentLocale);
        }

        if ($handler->hasLocale($config->get('mobileUids'), $currentLocale) === false) {
            // create page entity
            $mobilePage = new PageEntity([
                'pageId' => $pageId,
                'uid' => null,
                'content' => new DocumentEntity,
            ]);
        } else {
            $mobilePage = $handler->getPageEntity($pageId, 'mobile', $currentLocale);
        }

        $commentSection = new CommentSection();
        $sectionPresenter = $commentSection->setting($pageId);

        return Presenter::make('edit', [
            'pcPage' => $pcPage,
            'mobilePage' => $mobilePage,
            'menuId' => $menuId,
            'pageId' => $pageId,
            'config' => $config,
            'commentSection' => $sectionPresenter,
            'currentLocale' => $currentLocale,
            'locales' => $locales,
            'siteLocale' => $siteLocale,
        ]);
    }

    /**
     * update
     *
     * @param string $pageId page instance id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update($pageId)
    {
        $handler = $this->pageHandler;

        $documentId = Request::get('id');
        $content = Request::get('content');
        $title = Request::get('pageTitle');
        $locale = Request::get('locale');
        $mode = Request::get('mode');

        // check document exists
        $config = $handler->getPageConfig($pageId);
        $uids = $config->get('pcUids');
        if ($mode == 'mobile') {
            $uids = $config->get('mobileUids');
        }

        if ($documentId == '' || $handler->hasLocale($uids, $locale) === false) {
            // create new page document for pc
            $documentId = $handler->createNewLocalePageContent($pageId, '', $locale, $mode);
        }

        $handler->updatePageContent($documentId, $pageId, $content, $title, $locale);

        return Redirect::back();
    }

    /**
     * file upload
     *
     * @return string|\Xpressengine\Presenter\RendererInterface
     * @throws \Xpressengine\Media\Exceptions\NotAvailableException
     * @throws \Xpressengine\Storage\Exceptions\InvalidFileException
     */
    public function fileUpload()
    {
        /** @var \Xpressengine\Storage\Storage $storage */
        $storage = app('xe.storage');

        $uploadedFile = null;
        if (Request::file('file') !== null) {
            $uploadedFile = Request::file('file');
        } elseif (Request::file('image') !== null) {
            $uploadedFile = Request::file('image');
        }

        if ($uploadedFile === null) {
            throw new \Exception;
        }

        $file = $storage->upload($uploadedFile, PageModule::FILE_UPLOAD_PATH);

        /** @var \Xpressengine\Media\MediaManager $mediaManager */
        $mediaManager = \App::make('xe.media');
        $media = null;
        $thumbnails = null;

        if ($mediaManager->is($file) === true) {
            $media = $mediaManager->make($file);
            $thumbnails = $mediaManager->createThumbnails($media, PageModule::THUMBNAIL_TYPE);

            $media = $media->toArray();

            if (!empty($thumbnails)) {
                $info['thumbnails'] = $thumbnails;
            }
        }

        return Presenter::makeApi([
            'file' => $file->toArray(),
            'media' => $media,
            'thumbnails' => $thumbnails,
        ]);
    }

    /**
     * 해시태그 suggestion 리스트
     *
     * @param string $url url
     * @param string $id  id
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function suggestionHashTag($url, $id = null)
    {
        /** @var \Xpressengine\Tag\TagHandler tag */
        $tag = \App::make('xe.tag');
        $terms = $tag->autoCompletion(\Request::get('string'));

        $words = [];
        foreach ($terms as $tagEntity) {
            $words[] = $tagEntity->word;
        }

        return Presenter::makeApi($words);
    }

    /**
     * 멘션 suggestion 리스트
     *
     * @param string $url url
     * @param string $id  id
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function suggestionMention($url, $id = null)
    {
        $userIds = [];

        $string = Request::get('string');

        /** @var \Xpressengine\Member\Repositories\Database\MemberRepository $member */
        $member = app('xe.members');

        // 10개 안되면 전체 DB 에서 찾아보자
        if (count($userIds) < 10) {
            $users = $member->getConnection()->table('member')->whereNotIn('id', $userIds)
                ->where('displayName', 'like', $string . '%')->get(['id']);
            foreach ($users as $user) {
                $userIds[] = $user['id'];
            }
        }

        $users = $member->getConnection()->table('member')->whereIn('id', $userIds)
            ->where('displayName', 'like', $string . '%')->get(['id', 'displayName', 'profileImage']);

        foreach ($users as $user) {
            $key = array_search($user['id'], $userIds);
            if ($key !== null && $key !== false) {
                unset($userIds[$key]);
            }
        }

        // 본인은 안나오게 하자..
        $suggestions = [];
        foreach ($users as $user) {
            $suggestions[] = [
                'id' => $user['id'],
                'displayName' => $user['displayName'],
                'profileImage' => $user['profileImage'],
            ];
        }
        return Presenter::makeApi($suggestions);
    }
}
