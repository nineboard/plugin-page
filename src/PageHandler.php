<?php
/**
 * Page handler
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
namespace Xpressengine\Plugins\Page;

use Auth;
use XeDB;
use Xpressengine\Member\GuardInterface;
use Xpressengine\Plugins\CommentService\Module as CommentModule;
use Xpressengine\Plugins\Page\Module\Page as PageModule;
use Xpressengine\Document\DocumentHandler;
use Xpressengine\Keygen\Keygen;
use Xpressengine\Config\ConfigEntity;
use Xpressengine\Document\DocumentEntity;
use Xpressengine\Config\ConfigManager;

/**
 * Page handler
 *
 * @category    Page
 * @package     Xpressengine\Plugins\Page
 * @author      XE Team (develop) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class PageHandler
{
    /**
     * @var \Xpressengine\Document\DocumentHandler
     */
    protected $document;

    /**
     * @var \Xpressengine\Config\ConfigManager
     */
    protected $configManager;
    /**
     * @var GuardInterface
     */
    private $auth;

    /**
     * @param DocumentHandler $document      document handler
     * @param ConfigManager   $configManager config manager
     * @param GuardInterface  $auth          auth interface
     */
    public function __construct(
        DocumentHandler $document,
        ConfigManager $configManager,
        GuardInterface $auth
    ) {
        $this->document = $document;
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
     * getPageEntity
     *
     * @param string $pageId page instance id
     * @param string $mode   'pc' or 'mobile'
     * @param string $locale locale
     *
     * @return PageEntity
     */
    public function getPageEntity($pageId, $mode, $locale)
    {
        $config = $this->getPageConfig($pageId);
        $uids = $config->get('pcUids');
        if ($mode == 'mobile') {
            $uids = $config->get('mobileUids');
        }

        if (isset($uids[$locale])) {
            $uid = $uids[$locale];
        } else {
            $uid = array_shift($uids);
        }

        $content = $this->document->get($uid, $pageId);

        $pageEntity = new PageEntity(
            [
                'pageId' => $pageId,
                'uid' => $uid,
                'content' => $content,
            ]
        );

        return $pageEntity;
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
            $pcDocUid = $this->createPageDocument($pageId, $pageTitle, $siteLocale);
            $mobileDocUid = $this->createPageDocument($pageId, $pageTitle, $siteLocale);
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
     * @throws \Xpressengine\Keygen\UnknownGeneratorException
     */
    public function createPageDocument($pageId, $pageTitle, $locale)
    {
        $doc = new DocumentEntity();
        $doc->id = (new Keygen())->generate();
        $doc->instanceId = $pageId;
        $doc->title = $pageTitle;
        $doc->locale = $locale;
        $doc->setAuthor($this->auth->user());

        XeDB::beginTransaction();
        try {
            $this->document->add($doc);
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
     * @return DocumentEntity
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
            $comment = CommentModule::getHandler();
            if (!$comment->existInstance($pageId)) {
                $comment->createInstance($pageId);
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

        $this->document->getInstanceManager()->add($documentConfig);
    }
}
