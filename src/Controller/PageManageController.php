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
use Request;
use Redirect;
use XePresenter;
use XeEditor;
use XeStorage;
use XeTag;
use App;
use Xpressengine\Document\Models\Document;
use Xpressengine\Plugins\Page\Module\Page as PageModule;
use Xpressengine\Plugins\Page\PageEntity;
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
     * @param string              $pageId page instance id
     *
     * @return \Xpressengine\Presenter\RendererInterface
     */
    public function edit($pageId)
    {
        $handler = $this->pageHandler;
        $item = app('xe.menu')->getItem($pageId);
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
                'content' => new Document,
            ]);
        } else {
            $pcPage = $handler->getPageEntity($pageId, 'pc', $currentLocale);
        }

        if ($handler->hasLocale($config->get('mobileUids'), $currentLocale) === false) {
            // create page entity
            $mobilePage = new PageEntity([
                'pageId' => $pageId,
                'uid' => null,
                'content' => new Document,
            ]);
        } else {
            $mobilePage = $handler->getPageEntity($pageId, 'mobile', $currentLocale);
        }

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
}
