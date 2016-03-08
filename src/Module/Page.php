<?php
/**
 * Page module
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
namespace Xpressengine\Plugins\Page\Module;

use XeConfig;
use Xpressengine\Plugins\Page\PageHandler;
use View;
use Xpressengine\Module\AbstractModule;
use Route;
use App;

/**
 * Page module class
 *
 * @category    Page
 * @package     Xpressengine\Plugins\Page
 * @author      XE Team (develop) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class Page extends AbstractModule
{
    const FILE_UPLOAD_PATH = 'attached/page';
    const THUMBNAIL_TYPE = 'spill';

    /**
     * boot
     *
     * @return void
     */
    public static function boot()
    {
        self::registerManageRoute();
        self::registerInstanceRoute();
    }

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
     * Register Plugin Manage Route
     *
     *
     * @return void
     */
    protected static function registerManageRoute()
    {
        Route::settings(self::getId(), function () {
            Route::get('edit/{pageId}', ['as' => 'manage.plugin.page.edit', 'uses' => 'PageManageController@edit']);
            Route::put(
                'update/{pageId}',
                ['as' => 'manage.plugin.page.update', 'uses' => 'PageManageController@update']
            );
            Route::post(
                'file/{pageId}/upload',
                [
                    'as' => 'manage.plugin.page.upload',
                    'uses' => 'PageManageController@fileUpload'
                ]
            );
            Route::get('suggestion/{pageId}/hashTag/{id?}', [
                'as' => 'manage.plugin.page.hashTag',
                'uses' => 'PageManageController@suggestionHashTag'
            ]);
            Route::get('suggestion/{pageId}/mention/{id?}', [
                'as' => 'manage.plugin.page.mention',
                'uses' => 'PageManageController@suggestionMention'
            ]);
        }, ['namespace' => 'Xpressengine\Plugins\Page\Controller']);
    }

    /**
     * Register Plugin Instance Route
     *
     *
     * @return void
     */
    protected static function registerInstanceRoute()
    {
        Route::instance(self::getId(), function () {
            Route::get('/', ['as' => 'index', 'uses' => 'PageUserController@index']);
            Route::put('/preview', ['as' => 'preview', 'uses' => 'PageUserController@preview']);

            Route::get('/file/source/{id}', ['as' => 'source', 'uses' => 'PageUserController@fileSource']);
            Route::get('/file/download/{id}', ['as' => 'download', 'uses' => 'PageUserController@fileDownload']);

        }, ['namespace' => 'Xpressengine\Plugins\Page\Controller']);
    }

    /**
     * Return Create Form View
     *
     * @return mixed
     */
    public function createMenuForm()
    {
        $config = XeConfig::get(self::getId());   // 기본 설정
        $form = View::file(__DIR__ . '/../../views/menuType/menuCreate.blade.php', [
            'config' => $config,
        ]);
        return $form;
    }

    /**
     * Process to Store
     *
     * @param string $instanceId     page instance id
     * @param array  $menuTypeParams for menu type store param array
     * @param array  $itemParams     except menu type param array
     *
     * @return mixed
     * @internal param $inputs
     *
     */
    public function storeMenu($instanceId, $menuTypeParams, $itemParams)
    {
        $this->getPageHandler()->createPageInstance($instanceId, $menuTypeParams, app('xe.translator')->getLocale());
    }

    /**
     * Return Edit Form View
     *
     * @param string $instanceId to edit instance id
     *
     * @return mixed
     *
     */
    public function editMenuForm($instanceId)
    {
        $config = $this->getPageHandler()->getPageConfig($instanceId);   // 기본 설정

        $form = View::file(__DIR__ . '/../../views/menuType/menuEdit.blade.php', [
            'pageId' => $instanceId,
            'config' => $config,
        ])->render();
        return $form;
    }

    /**
     * Process to Update
     *
     * @param string $instanceId     to store instance id
     * @param array  $menuTypeParams for menu type store param array
     * @param array  $itemParams     except menu type param array
     *
     * @return mixed
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
     * @param string $instanceId to delete instance id
     *
     * @return mixed
     *
     */
    public function deleteMenu($instanceId)
    {
        $this->getPageHandler()->dropPage($instanceId);
    }

    /**
     * summary
     *
     * @param string $instanceId page instance id
     *
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
     * @param string $instanceId page instance id
     *
     * @return string|null
     */
    public static function getInstanceSettingURI($instanceId)
    {
        return route('manage.plugin.page.edit', $instanceId);
    }
}