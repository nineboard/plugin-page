<?php
/**
 * DefaultSkin.php
 *
 * This file is part of the Xpressengine package.
 *
 * PHP version 7
 *
 * @category    Page
 * @package     Xpressengine\Plugins\Page
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        http://www.xpressengine.com
 */

namespace Xpressengine\Plugins\Page\Skin;

use Xpressengine\Skin\AbstractSkin;
use View;

/**
 * DefaultSkin
 *
 * @category    Page
 * @package     Xpressengine\Plugins\Page
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
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
        return __DIR__ . '/../../views/defaultSkin/' . $view . '.blade.php';
    }
}
