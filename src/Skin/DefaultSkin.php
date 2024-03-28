<?php
/**
 * DefaultSkin.php
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
use Xpressengine\Skin\AbstractSkin;

/**
 * DefaultSkin
 *
 * @category    Page
 *
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 *
 * @link        https://xpressengine.io
 */
class DefaultSkin extends AbstractSkin
{
    protected $frame = '_frame';

    /**
     * @var string|null
     */
    protected static $type = 'page';

    /**
     * render
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $view = View::file($this->getViewFilePath($this->frame), $this->data);
        $view->content = View::file($this->getViewFilePath($this->view), $this->data)->render();

        return $view;
    }

    /**
     * getViewFilePath
     *
     * @param  string  $view  view name for render
     * @return string
     */
    public function getViewFilePath($view)
    {
        return __DIR__.'/../../views/defaultSkin/'.$view.'.blade.php';
    }
}
