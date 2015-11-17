<?php
/**
 * Page plugin
 *
 * PHP version 5
 *
 * @category    Page
 * @package     Page
 * @author      XE Team (develop) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
namespace Xpressengine\Plugins\Page;

use Cfg;
use Xpressengine\Plugin\AbstractPlugin;
use App;
use XeLang;

/**
 * Page plugin
 *
 * @category    Page
 * @package     Page
 * @author      XE Team (develop) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class PagePlugin extends AbstractPlugin
{
    /**
     * install
     *
     * @return void
     */
    public function install()
    {
//        XeLang::putFromLangDataSource(self::getId(), __DIR__.'/lang/lang.php');
    }

    /**
     * activate
     *
     * @param null $installedVersion installed version
     * @return void
     */
    public function activate($installedVersion = null)
    {
        if (Cfg::get('module/page@page') === null) {
            Cfg::add('module/page@page', []);
        }
    }

    /**
     * @return boolean
     */
    public function unInstall()
    {
        // TODO: Implement unInstall() method.
    }

    /**
     * @return boolean
     */
    public function checkInstalled()
    {
        // TODO: Implement checkInstall() method.

        return true;
    }

    /**
     * @return boolean
     */
    public function checkUpdated()
    {
        // TODO: Implement checkUpdate() method.
    }

    /**
     * boot
     *
     * @return void
     */
    public function boot()
    {
        $this->bindClasses();
    }

    /**
     * bindClasses
     *
     * @return void
     */
    protected function bindClasses()
    {
        $app = app();

        $app->singleton(
            'xe.page.handler',
            function ($app) {
                return new PageHandler($app['xe.document'], $app['xe.config'], $app['xe.auth']);
            }
        );

        $app->bind(
            'Xpressengine\Plugins\Page\PageHandler',
            'xe.page.handler'
        );
    }
}
