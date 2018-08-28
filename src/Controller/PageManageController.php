<?php
/**
 * Page Manage Controller
 *
 * @category    Page
 * @package     Xpressengine\Plugins\Page\Controller
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     LGPL-2.1
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html
 * @link        https://xpressengine.io
 */

namespace Xpressengine\Plugins\Page\Controller;

use App\Http\Controllers\Controller;
use App\Http\Sections\EditorSection;
use App\Http\Sections\SkinSection;
use Request;
use Redirect;
use XePresenter;
use XeEditor;
use XeLang;
use XeMenu;
use XeStorage;
use XeTag;
use App;
use Xpressengine\Plugins\Page\Models\PageComment;
use Xpressengine\Plugins\Page\Module\Page as PageModule;
use Xpressengine\Plugins\Page\Module\Page;
use Xpressengine\Plugins\Page\PageHandler;

/**
 * Page Manage Controller
 *
 * @category    Page
 * @package     Xpressengine\Plugins\Page\Controller
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
        XePresenter::setSettingsSkinTargetId(PageModule::getId());
        $this->pageHandler = app('xe.page.handler');
    }

    /**
     * edit
     *
     * @param string $pageId page instance id
     *
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function edit($pageId)
    {
        $handler = $this->pageHandler;
        $item = XeMenu::items()->find($pageId);
        $menuId = $item->menu_id;

        $locales = XeLang::getLocales();
        $siteLocale = $locales[0];
        $currentLocale = Request::get('locale', $siteLocale);
        $targetId = $handler->getPageCommentTargetId($pageId);

        $config = $handler->getPageConfig($pageId);
        if ($handler->hasLocale($config->get('pcUids'), $currentLocale) === false) {
            $pcDocumentId = $handler->createNewLocalePageContent($pageId, '', $currentLocale, PageComment::MODE_PC);

            $handler->createPageCommentTarget($targetId, $pageId, $pcDocumentId, PageComment::MODE_PC, $currentLocale);

            $pcPage = $handler->getPageModel($pageId, PageComment::MODE_PC, $currentLocale);
        } else {
            $pcPage = $handler->getPageModel($pageId, PageComment::MODE_PC, $currentLocale);
        }

        if ($handler->hasLocale($config->get('mobileUids'), $currentLocale) === false) {
            $mobileDocumentId = $handler->createNewLocalePageContent($pageId, '', $currentLocale, PageComment::MODE_MOBILE);

            $handler->createPageCommentTarget($targetId, $pageId, $mobileDocumentId, PageComment::MODE_MOBILE, $currentLocale);

            $mobilePage = $handler->getPageModel($pageId, PageComment::MODE_MOBILE, $currentLocale);
        } else {
            $mobilePage = $handler->getPageModel($pageId, PageComment::MODE_MOBILE, $currentLocale);
        }

        XePresenter::widgetParsing(false);
        return XePresenter::make('edit', [
            'pcPage' => $pcPage,
            'mobilePage' => $mobilePage,
            'menuId' => $menuId,
            'pageId' => $pageId,
            'config' => $config,
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

        $inputs = Request::all();
        /** @var \Xpressengine\Editor\AbstractEditor $editor */
        $editor = XeEditor::get($pageId);
        // file 처리
        XeStorage::sync($documentId, array_get($inputs, $editor->getFileInputName(), []));
        // tag 처리
        XeTag::set($documentId, array_get($inputs, $editor->getTagInputName(), []), $pageId);

        return Redirect::back();
    }

    public function editEditor($pageId)
    {
        $handler = $this->pageHandler;
        $config = $handler->getPageConfig($pageId);

        $editorSection = new EditorSection($pageId);

        return XePresenter::make('editor', [
            'config' => $config,
            'pageId' => $pageId,
            'editorSection' => $editorSection,
        ]);
    }

    public function editSkin($pageId)
    {
        $skinSection = new SkinSection(Page::getId(), $pageId);

        return XePresenter::make('skin', [
            'pageId' => $pageId,
            'skinSection' => $skinSection,
        ]);
    }
}
