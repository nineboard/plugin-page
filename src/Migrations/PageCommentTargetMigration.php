<?php
/**
 * PageCommentTargetMigration
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

namespace Xpressengine\Plugins\Page\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Schema;
use XeDB;
use Xpressengine\Menu\Models\MenuItem;
use Xpressengine\Plugins\Comment\Models\Target;
use Xpressengine\Plugins\Page\Models\PageComment;
use Xpressengine\Plugins\Page\Models\PageModel;
use Xpressengine\Plugins\Page\PageHandler;

/**
 * PageCommentTargetMigration
 *
 * @category    Page
 *
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 *
 * @link        https://xpressengine.io
 */
class PageCommentTargetMigration
{
    const TABLE_NAME = 'page_comment_target';

    /**
     * migration table create
     *
     * @return void
     */
    public function up()
    {
        Schema::create(self::TABLE_NAME, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('page_target_id', 36);
            $table->string('page_instance_id', 36);
            $table->text('data');
            $table->string('author_id');

            $table->index('page_instance_id');
        });
    }

    /**
     * migration table drop
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }

    /**
     * check table exist
     *
     * @return bool
     */
    public function tableExists()
    {
        return Schema::hasTable(self::TABLE_NAME);
    }

    /**
     * origin data migration
     *
     * @return void
     *
     * @throws \Exception
     */
    public function originDataMigration()
    {
        /** @var PageHandler $handler */
        $handler = app('xe.page.handler');

        $pages = MenuItem::where('type', 'page@page')->get();

        try {
            XeDB::beginTransaction();

            foreach ($pages as $page) {
                if ($handler->getPageCommentTarget($page['id']) != null) {
                    continue;
                }

                $pageId = $page['id'];
                $originDocIds = [];
                $data = [];
                $pageCommentTargetId = $handler->getPageCommentTargetId($pageId);
                $config = $handler->getPageConfig($pageId);

                $pcLocales = $config->get('pcUids');
                foreach ($pcLocales as $locale => $docId) {
                    $data[PageComment::MODE_PC][$locale] = $docId;
                    $originDocIds[] = $docId;
                }

                $mobileLocales = $config->get('mobileUids');
                foreach ($mobileLocales as $locale => $docId) {
                    $data[PageComment::MODE_MOBILE][$locale] = $docId;
                    $originDocIds[] = $docId;
                }

                $authorId = PageModel::where('id', $originDocIds[0])->first()['user_id'];

                $pageCommentTarget = new PageComment();
                $pageCommentTarget->page_target_id = $pageCommentTargetId;
                $pageCommentTarget->page_instance_id = $pageId;
                $pageCommentTarget->data = json_encode($data);
                $pageCommentTarget->author_id = $authorId;
                $pageCommentTarget->save();

                Target::whereIn('target_id', $originDocIds)->update(['target_id' => $pageCommentTargetId]);
            }

            Target::where('target_type', 'Xpressengine\\Plugins\\Page\\PageEntity')
                ->update(['target_type' => 'Xpressengine\\Plugins\\Page\\Models\\PageComment']);

            XeDB::commit();
        } catch (\Exception $e) {
            XeDB::rollback();

            throw $e;
        }
    }
}
