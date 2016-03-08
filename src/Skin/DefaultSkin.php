<?php
/**
 * Page user skin
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
namespace Xpressengine\Plugins\Page\Skin;

use Xpressengine\Skin\AbstractSkin;
use View;

/**
 * Page user skin
 *
 * @category    Page
 * @package     Xpressengine\Plugins\Page
 * @author      XE Team (develop) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
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
     * @param string $view view name for render
     *
     * @return string
     */
    public function getViewFilePath($view)
    {
        return __DIR__.'/../../views/defaultSkin/'.$view.'.blade.php';
    }
}