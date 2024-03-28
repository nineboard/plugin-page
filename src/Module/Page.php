<?php
/**
 * Page.php
 *
 * This file is part of the Xpressengine package.
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

namespace Xpressengine\Plugins\Page\Module;

use View;
use XeConfig;
use XeEditor;
use Xpressengine\Menu\AbstractModule;
use Xpressengine\Plugins\Page\PageHandler;

/**
 * Page
 *
 * @category    Page
 *
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 *
 * @link        https://xpressengine.io
 */
class Page extends AbstractModule
{
    const FILE_UPLOAD_PATH = 'public/plugin/page';

    const THUMBNAIL_TYPE = 'spill';

    /**
     * getSettingsURI
     *
     * @return null
     */
    public static function getSettingsURI()
    {
        return null;
    }

    /**
     * isRouteAble
     *
     * @return bool
     */
    public static function isRouteAble()
    {
        return true;
    }

    /**
     * Return Create Form View
     *
     * @return string
     */
    public function createMenuForm()
    {
        $config = XeConfig::get(self::getId());   // 기본 설정

        return View::file(__DIR__.'/../../views/menuType/menuCreate.blade.php', [
            'config' => $config,
        ])->render();
    }

    /**
     * Process to Store
     *
     * @param  string  $instanceId  page instance id
     * @param  array  $menuTypeParams  for menu type store param array
     * @param  array  $itemParams  except menu type param array
     * @return void
     *
     * @internal param $inputs
     *
     * @throws \Exception
     */
    public function storeMenu($instanceId, $menuTypeParams, $itemParams)
    {
        $this->getPageHandler()->createPageInstance($instanceId, $menuTypeParams, app('xe.translator')->getLocale());

        XeEditor::setInstance($instanceId, 'editor/ckeditor@ckEditor');
    }

    /**
     * Return Edit Form View
     *
     * @param  string  $instanceId  to edit instance id
     * @return mixed
     */
    public function editMenuForm($instanceId)
    {
        $config = $this->getPageHandler()->getPageConfig($instanceId);   // 기본 설정

        $form = View::file(__DIR__.'/../../views/menuType/menuEdit.blade.php', [
            'pageId' => $instanceId,
            'config' => $config,
        ])->render();

        return $form;
    }

    /**
     * Process to Update
     *
     * @param  string  $instanceId  to store instance id
     * @param  array  $menuTypeParams  for menu type store param array
     * @param  array  $itemParams  except menu type param array
     * @return void
     *
     * @throws \Exception
     */
    public function updateMenu($instanceId, $menuTypeParams, $itemParams)
    {
        $handler = $this->getPageHandler();

        $pageConfig = $handler->getPageConfig($instanceId);
        if ($pageConfig === null) {
            throw new \Exception('PageId-Not-Exist', 100);
        }

        $pageConfig->set('comment', ($menuTypeParams['comment'] === 'true'));
        $pageConfig->set('mobile', ($menuTypeParams['mobile'] === 'true'));

        $handler->updatePageConfig($pageConfig);
        $handler->createCommentInstance($instanceId, $menuTypeParams['comment']);
    }

    /**
     * Process to delete
     *
     * @param  string  $instanceId  to delete instance id
     * @return void
     *
     * @throws \Exception
     */
    public function deleteMenu($instanceId)
    {
        $this->getPageHandler()->dropPage($instanceId);
    }

    /**
     * summary
     *
     * @param  string  $instanceId  page instance id
     * @return string
     */
    public function summary($instanceId)
    {
        return "{$instanceId} 의 메뉴에서는 하나의 config 와 다수의 문서들을 가지고 있습니다.";
    }

    /**
     * getPageHandler
     *
     * @return PageHandler
     */
    protected function getPageHandler()
    {
        return app('xe.page.handler');
    }

    /**
     * Return URL about module's detail setting
     * getInstanceSettingURI
     *
     * @param  string  $instanceId  page instance id
     * @return string|null
     */
    public static function getInstanceSettingURI($instanceId)
    {
        return route('manage.plugin.page.edit', $instanceId);
    }

    /**
     * Get menu type's item object
     *
     * @param  string  $id  item id of menu type
     * @return mixed
     */
    public function getTypeItem($id)
    {
        $request = app('request');

        $doc = app('xe.document')->get($id);
        $pageId = $doc->instanceId;
        $config = $this->getPageHandler()->getPageConfig($pageId);

        $mode = 'pc';
        if ($config->get('mobile') && $request->isMobile()) {
            $mode = 'mobile';
        }

        return $this->getPageHandler()->getPageModel($pageId, $mode, \XeLang::getLocale());
    }
}
