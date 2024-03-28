<?php
/**
 * SettingsSkin.php
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

namespace Xpressengine\Plugins\Page\Skin;

use View;
use XeMenu;
use Xpressengine\Skin\AbstractSkin;

/**
 * SettingsSkin
 *
 * @category    Page
 *
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 *
 * @link        https://xpressengine.io
 */
class SettingsSkin extends AbstractSkin
{
    /**
     * @var string|null
     */
    protected static $type = 'page';

    protected $frame = '_frame';

    /**
     * render
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $this->data['_active'] = $this->view;

        $menuItem = XeMenu::items()->find($this->data['pageId']);

        $view = View::file($this->getViewFilePath($this->frame), $this->data, compact('menuItem'));
        $view->content = View::file($this->getViewFilePath($this->view), $this->data);

        return $view;
    }

    /**
     * getViewFilePath
     *
     * @param  string  $view  view name
     * @return string
     */
    public function getViewFilePath($view)
    {
        return __DIR__.'/../../views/settingsSkin/'.$view.'.blade.php';
    }
}
