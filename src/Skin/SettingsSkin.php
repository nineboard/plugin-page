<?php
/**
 * Page settings skin
 *
 * @category    Page
 * @package     Xpressengine\Plugins\Page
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     LGPL-2.1
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html
 * @link        https://xpressengine.io
 */

namespace Xpressengine\Plugins\Page\Skin;

use XeMenu;
use Xpressengine\Skin\AbstractSkin;
use View;

/**
 * Page settings skin
 *
 * @category    Page
 * @package     Xpressengine\Plugins\Page
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
     * @param string $view view name
     *
     * @return string
     */
    public function getViewFilePath($view)
    {
        return __DIR__.'/../../views/settingsSkin/'.$view.'.blade.php';
    }
}
