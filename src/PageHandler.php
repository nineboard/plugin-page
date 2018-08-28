<?php
/**
 * Page handler
 *
 * @category    Page
 * @package     Xpressengine\Plugins\Page
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     LGPL-2.1
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html
 * @link        https://xpressengine.io
 */

namespace Xpressengine\Plugins\Page;

use XeDB;
use Xpressengine\Config\ConfigEntity;
use Xpressengine\Config\ConfigManager;
use Xpressengine\Document\DocumentHandler;
use Xpressengine\Document\Models\Document;
use Xpressengine\Plugins\Comment\Handler as CommentHandler;
use Xpressengine\Plugins\Page\Models\PageComment;
use Xpressengine\Plugins\Page\Models\PageModel;
use Xpressengine\Plugins\Page\Module\Page as PageModule;
use Xpressengine\User\GuardInterface;
use Xpressengine\User\UserInterface;

/**
 * Page handler
 *
 * @category    Page
 * @package     Xpressengine\Plugins\Page
 */
class PageHandler
{
    /**
     * @var \Xpressengine\Document\DocumentHandler
     */
    protected $document;

    /**
     * @var CommentHandler
     */
    protected $comment;

    /**
     * @var \Xpressengine\Config\ConfigManager
     */
    protected $configManager;

    /**
     * @var GuardInterface
     */
    private $auth;

    /**
     * @param DocumentHandler $document document handler
     * @param CommentHandler  $comment comment handler
     * @param ConfigManager   $configManager config manager
     * @param GuardInterface  $auth auth interface
     */
    public function __construct(
        DocumentHandler $document,
        CommentHandler $comment,
        ConfigManager $configManager,
        GuardInterface $auth
    ) {
        $this->document = $document;
        $this->comment = $comment;
        $this->configManager = $configManager;
        $this->auth = $auth;
    }

    /**
     * saveDefaultConfig
     *
     * @return void
     * @throws \Xpressengine\Config\Exceptions\InvalidArgumentException
     */
    public function saveDefaultConfig()
    {
        $this->configManager->add(PageModule::getId(), $this->getDefaultConfig());
    }

    /**
     * getDefaultConfig
     *
     * @return array
     */
    protected function getDefaultConfig()
    {
        return [
            'pageId' => null,
            'pageTitle' => null,
        ];
    }

    /**
     * getPageConfig
     *
     * @param string $pageId instance id
     *
     * @return ConfigEntity
     */
    public function getPageConfig($pageId)
    {
        return $this->configManager->get($this->getConfigKeyString($pageId));
    }

    /**
     * has locale
     *
     * @param array  $ids    pc or mobile Uids
     * @param string $locale find locale
     * @return bool
     */
    public function hasLocale(array $ids, $locale)
    {
        return isset($ids[$locale]);
    }

    /**
     * @param string $pageId page instance id
     *
     * @return PageComment
     */
    public function getPageCommentTarget($pageId)
    {
        $pageCommentTarget = PageComment::where('page_instance_id', $pageId)->first();

        return $pageCommentTarget;
    }

    /**
     * create or get pageCommentTargetId
     *
     * @param  string $pageId page_instance_id
     *
     * @return string|null
     */
    public function getPageCommentTargetId($pageId)
    {
        $pageCommentTarget = $this->getPageCommentTarget($pageId);

        $targetId = null;
        if ($pageCommentTarget == null) {
            $targetId = app('xe.keygen')->generate();
        } else {
            $targetId = $pageCommentTarget->page_target_id;
        }

        return $targetId;
    }

    /**
     * getPageEntity
     *
     * @param string $pageId page instance id
     * @param string $mode   'pc' or 'mobile'
     * @param string $locale locale
     *
     * @return PageEntity|null
     *
     * @deprecated since rc.4. use getPageModel instead of
     */
    public function getPageEntity($pageId, $mode, $locale)
    {
        $pageEntity = PageEntity::where('page_instance_id', $pageId)->first();

        $pageModel = $pageEntity->getPageModel($mode, $locale);

        return $pageModel;
    }

    /**
     * getPageModel
     *
     * @param string $pageId page instance id
     * @param string $mode   'pc' or 'mobile'
     * @param string $locale locale
     *
     * @return PageModel|null
     */
    public function getPageModel($pageId, $mode = PageComment::MODE_PC, $locale = 'ko')
    {
        $pageCommentTarget = PageComment::where('page_instance_id', $pageId)->first();

        $pageModel = $pageCommentTarget->getPageModel($mode, $locale);

        return $pageModel;
    }

    /**
     * createPageInstance
     *
     * @param string $pageId     instance id
     * @param array  $inputs     to create input array
     * @param string $siteLocale site default locale
     * @return void
     * @throws \Exception
     */
    public function createPageInstance($pageId, array $inputs, $siteLocale)
    {
        if ($this->existPageInstance($pageId)) {
            throw new \Exception("Already {$pageId} is existed");
        }

        XeDB::beginTransaction();

        try {
            $pageTitle = '';
            $this->createDocumentInstance($pageId, $pageTitle);
            $targetId = $this->getPageCommentTargetId($pageId);

            $pcDocUid = $this->createPageDocument($pageId, $pageTitle, $siteLocale);
            $this->createPageCommentTarget($targetId, $pageId, $pcDocUid, PageComment::MODE_PC, $siteLocale);

            $mobileDocUid = $this->createPageDocument($pageId, $pageTitle, $siteLocale);
            $this->createPageCommentTarget($targetId, $pageId, $mobileDocUid, PageComment::MODE_MOBILE, $siteLocale);

            $this->addPageConfig(
                $pageId,
                array_merge(
                    $inputs,
                    [
                        'pcUids' => [$siteLocale => $pcDocUid],
                        'mobileUids' => [$siteLocale => $mobileDocUid]
                    ]
                )
            );

            $this->createCommentInstance($pageId, $inputs['comment']);
        } catch (\Exception $e) {
            XeDB::rollBack();
            throw $e;
        }

        XeDB::commit();
    }

    /**
     * create pageCommentTarget
     *
     * @param string $targetId pageCommentTargetId
     * @param string $pageId   page_instance_id
     * @param string $docId    page_document_id
     * @param string $mode     device_mode
     * @param string $locale   locale
     *
     * @return void
     */
    public function createPageCommentTarget($targetId, $pageId, $docId, $mode, $locale)
    {
        $pageCommentTarget = $this->getPageCommentTarget($pageId);

        if ($pageCommentTarget == null) {
            $pageCommentTarget = new PageComment();

            $pageCommentTarget->page_target_id = $targetId;
            $pageCommentTarget->page_instance_id = $pageId;
            $pageCommentTarget->author_id = $this->auth->user()->getId();

            $data[$mode][$locale] = $docId;
            $pageCommentTarget->data = json_encode($data);

            $pageCommentTarget->save();
        } else {
            $oldData = json_decode($pageCommentTarget['data'], true);

            $oldData[$mode][$locale] = $docId;

            $pageCommentTarget['data'] = json_encode($oldData);

            $pageCommentTarget->save();
        }
    }

    /**
     * remove pageCommentTarget
     *
     * @param string $pageId page_instance_id
     *
     * @return void
     */
    public function removePageCommentTarget($pageId)
    {
        PageComment::where('page_instance_id', $pageId)->delete();
    }

    /**
     * existPageInstance
     *
     * @param string $pageId page instance id
     *
     * @return bool
     */
    protected function existPageInstance($pageId)
    {
        $configName = $this->getConfigKeyString($pageId);

        return ($this->configManager->get($configName) !== null);
    }

    /**
     * addPageConfig
     *
     * @param string $pageId      page instance id
     * @param array  $pageConfigs parameter array
     *
     * @return void
     * @throws \Xpressengine\Config\Exceptions\InvalidArgumentException
     */
    protected function addPageConfig($pageId, $pageConfigs)
    {
        $configName = $this->getConfigKeyString($pageId);
        $this->configManager->add($configName, $pageConfigs);
    }

    /**
     * createPageDocument
     *
     * @param string $pageId    page instance id
     * @param string $pageTitle page title
     * @param string $locale    locale
     *
     * @return string
     * @throws \Exception
     */
    public function createPageDocument($pageId, $pageTitle, $locale)
    {
        /** @var UserInterface $user */
        $user = $this->auth->user();
        $data = [
            'instance_id' => $pageId,
            'type' => PageModule::getId(),
            'title' => $pageTitle,
            'locale' => $locale,
            'user_id' => $user->getId(),
            'writer' => $user->getDisplayName(),
            'certify_key' => '',
            'format' => Document::FORMAT_HTML,
            'content' => '',
        ];

        XeDB::beginTransaction();
        try {
            $doc = $this->document->add($data);
        } catch (\Exception $e) {
            XeDB::rollback();
            throw $e;
        }
        XeDB::commit();

        return $doc->id;
    }

    /**
     * updatePageContent
     *
     * @param string $documentUid page content document id
     * @param string $pageId      page instance id
     * @param array  $content     content string
     * @param array  $title       title string
     * @param array  $locale      locale
     *
     * @return void
     * @throws \Exception
     */
    public function updatePageContent($documentUid, $pageId, $content, $title, $locale)
    {
        $document = $this->document->get($documentUid, $pageId);

        $document->content = $content;
        $document->title = $title;
        $document->locale = $locale;

        XeDB::beginTransaction();
        try {
            $this->document->put($document);
        } catch (\Exception $e) {
            XeDB::rollback();
            throw $e;
        }
        XeDB::commit();
    }

    /**
     * dropPage
     *
     * @param string $pageId page instance id
     *
     * @return void
     * @throws \Exception
     */
    public function dropPage($pageId)
    {
        XeDB::beginTransaction();

        try {
            $documentConfig = $this->document->getConfigHandler()->get($pageId);
            $instanceManager = $this->document->getInstanceManager();
            $instanceManager->remove($documentConfig);
            $this->removePageConfig($pageId);
            $this->removePageCommentTarget($pageId);
        } catch (\Exception $e) {
            XeDB::rollback();
            throw $e;
        }

        XeDB::commit();
    }

    /**
     * create new locale page content by mode
     *
     * @param string $pageId    page instance id
     * @param string $pageTitle page title
     * @param string $locale    locale
     * @param string $mode      pc or mobile
     * @return string
     */
    public function createNewLocalePageContent($pageId, $pageTitle, $locale, $mode)
    {
        $config = $this->getPageConfig($pageId);
        $uid = $this->createPageDocument($pageId, $pageTitle, $locale);
        if ($mode == 'pc') {
            $config->set('pcUids', array_merge($config->get('pcUids'), [$locale  => $uid]));
        } else {
            $config->set('mobileUids', array_merge($config->get('mobileUids'), [$locale  => $uid]));
        }

        $this->updatePageConfig($config);

        return $uid;
    }

    /**
     * removePageConfig
     *
     * @param string $pageId page instance id
     *
     * @return void
     */
    protected function removePageConfig($pageId)
    {
        $configName = $this->getConfigKeyString($pageId);
        $this->configManager->removeByName($configName);
    }

    /**
     * getPageContent
     *
     * @param string $id     page content id
     * @param string $pageId page instance id
     *
     * @return Document
     */
    public function getPageContent($id, $pageId)
    {
        return $this->document->get($id, $pageId);
    }

    /**
     * getConfigKeyString
     *
     * @param string $pageId page instance id
     *
     * @return string
     */
    protected function getConfigKeyString($pageId)
    {
        return sprintf('%s.%s', PageModule::getId(), $pageId);
    }

    /**
     * createCommentInstance
     *
     * @param string $pageId       page instance id
     * @param string $commentInput comment parameter
     *
     * @return void
     */
    public function createCommentInstance($pageId, $commentInput)
    {
        if ($commentInput === 'true') {
            if ($this->comment->getInstanceId($pageId) === null) {
                $this->comment->createInstance($pageId);
                $this->comment->configure($this->comment->getInstanceId($pageId), ['useWysiwyg' => true]);
            }
        }
    }

    /**
     * updatePageConfig
     *
     * @param ConfigEntity $config page config entity
     *
     * @return void
     * @throws \Xpressengine\Config\Exceptions\InvalidArgumentException
     */
    public function updatePageConfig($config)
    {
        $this->configManager->modify($config);
    }

    /**
     * createDocumentInstance
     *
     * @param string $pageId    page instance id
     * @param string $pageTitle page title
     *
     * @return void
     */
    protected function createDocumentInstance($pageId, $pageTitle)
    {
        $documentConfig = new ConfigEntity;
        $documentConfig->set('instanceId', $pageId);
        $documentConfig->set('instanceName', $pageTitle);
        $documentConfig->set('revision', true);

        $this->document->getInstanceManager()->add($documentConfig);
    }
}
