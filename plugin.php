<?php
/**
 * Plugin.php
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

namespace Xpressengine\Plugins\Page;

use Route;
use XeConfig;
use XeLang;
use Xpressengine\Plugin\AbstractPlugin;
use Xpressengine\Plugins\Page\Migrations\PageCommentTargetMigration;
use Xpressengine\Plugins\Page\Module\Page;

/**
 * Plugin
 *
 * @category    Page
 *
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 *
 * @link        https://xpressengine.io
 */
class Plugin extends AbstractPlugin
{
    /**
     * install
     *
     * @return void
     */
    public function install()
    {
        $this->importLang();
        (new PageCommentTargetMigration())->up();
    }

    /**
     * update
     *
     * @return void
     *
     * @throws \Exception
     */
    public function update()
    {
        $this->importLang();

        // v0.9.11
        $pageCommentTargetMigration = new PageCommentTargetMigration();
        if ($pageCommentTargetMigration->tableExists() === false) {
            $pageCommentTargetMigration->up();

            $pageCommentTargetMigration->originDataMigration();
        }
    }

    /**
     * import lang
     *
     * @return void
     */
    private function importLang()
    {
        XeLang::putFromLangDataSource(self::getId(), __DIR__.'/langs/lang.php');
    }

    /**
     * activate
     *
     * @param  null  $installedVersion  installed version
     * @return void
     */
    public function activate($installedVersion = null)
    {
        if (XeConfig::get('module/page@page') === null) {
            XeConfig::add('module/page@page', []);
        }
    }

    /**
     * boot
     *
     * @return void
     */
    public function boot()
    {
        $this->routes();
    }

    /**
     * register
     *
     * @return void
     */
    public function register()
    {
        $app = app();

        $app->singleton(PageHandler::class, function ($app) {
            return new PageHandler(
                $app['xe.document'],
                $app['xe.plugin.comment']->getHandler(),
                $app['xe.config'],
                $app['auth']->guard()
            );
        });

        $app->alias(PageHandler::class, 'xe.page.handler');
    }

    /**
     * register route
     *
     * @return void
     */
    private function routes()
    {
        Route::settings(Page::getId(), function () {
            Route::get('edit/{pageId}', ['as' => 'manage.plugin.page.edit', 'uses' => 'PageManageController@edit']);
            Route::post(
                'update/{pageId}',
                ['as' => 'manage.plugin.page.update', 'uses' => 'PageManageController@update']
            );
            Route::get(
                'editor/edit/{pageId}',
                ['as' => 'manage.plugin.page.editor', 'uses' => 'PageManageController@editEditor']
            );
            Route::get(
                'skin/edit/{pageId}',
                ['as' => 'manage.plugin.page.skin', 'uses' => 'PageManageController@editSkin']
            );
        }, ['namespace' => 'Xpressengine\Plugins\Page\Controller']);

        Route::instance(Page::getId(), function () {
            Route::get('/', ['as' => 'index', 'uses' => 'PageUserController@index']);
            Route::post('/preview', ['as' => 'preview', 'uses' => 'PageUserController@preview']);
        }, ['namespace' => 'Xpressengine\Plugins\Page\Controller']);
    }

    /**
     * check update
     *
     * @return bool
     */
    public function checkUpdated()
    {
        $checkUpdate = true;
        // v0.9.11
        if ((new PageCommentTargetMigration())->tableExists() === false) {
            $checkUpdate = false;
        }

        return $checkUpdate;
    }
}
