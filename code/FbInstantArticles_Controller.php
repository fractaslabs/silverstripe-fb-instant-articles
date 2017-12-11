<?php

class FbInstantArticles_Controller extends Controller
{
    private static $allowed_actions = array(
        'fbiafeed',
    );

    private static $url_handlers = array(
        'fbiafeed' => 'index',
    );

    public function init()
    {
        parent::init();
        Requirements::clear();
    }

    /**
     * @return mixed
     */
    public function LastEditedIso()
    {
        return $this->LastEdited()->Rfc3339();
    }

    public function LastEdited()
    {
        return SS_Datetime::now();
    }

    /**
     * @return mixed
     */
    public function index()
    {
        if (!$cacheKey = Config::inst()->get('FbInstantArticlesHelper', 'cache_key')) {
            return false;
        }

        return $this->getCachedFeed($cacheKey);
    }

    /**
     * @return mixed
     */
    public function getIAFeed()
    {
        $prevState = Config::inst()->get('SSViewer', 'source_file_comments');
        Config::inst()->update('SSViewer', 'source_file_comments', false);

        $response = Controller::curr()->getResponse();

        if (null !== $this->LastEdited()) {
            HTTP::register_modification_timestamp($this->LastEdited()->value);
            $response->addHeader('Last-Modified', gmdate('D, d M Y H:i:s', strtotime($this->LastEdited()->value)).' GMT');
        }

        if (!empty($this->etag)) {
            HTTP::register_etag($this->etag);
        }

        if (!headers_sent()) {
            HTTP::add_cache_headers();
            $response->addHeader('Content-Type', 'application/rss2+xml; charset=utf-8');
        }

        Config::inst()->update('SSViewer', 'source_file_comments', $prevState);

        $out = $this->customise(
            new ArrayData(
                array(
                    'AbsoluteLink' => Director::absoluteBaseURL(),
                    'ContentLocale' => i18n::get_locale(),
                    'Items' => $this->Items(),
                )
            )
        );

        $out = $out->renderWith('FbInstantArticlesFeed');
        $out = $this->getContentImagesParsed($out);
        $out = $this->getContentParsed($out);
        Requirements::clear();

        return $out;
    }

    /**
     * @return mixed
     */
    private function Items()
    {
        $items = ArticlePage::get()->exclude('Tags.ID', 55)->exclude('ImageID', 0)->sort('PublishedDate', 'DESC')->limit(100);
        $out = new ArrayList();

        foreach ($items as $item) {
            $desc = $item->dbObject('Content')->NoHTML();
            $desc = mb_strimwidth($desc, 0, 255, '...', 'utf-8');
            $outItem = array(
                'ID' => $item->ID,
                'Title' => $item->Title,
                'Description' => $desc,
                'Content' => $this->fetchItemContent($item),
                'AbsoluteLink' => $item->AbsoluteLink(),
                'PublishedDateLong' => $this->fetchItemPublishedDate($item)->Long(),
                'PublishedDateIso' => $this->fetchItemPublishedDate($item)->Rfc3339(),
                'LastEditedLong' => $item->dbObject('LastEdited')->Long(),
                'LastEditedIso' => $item->dbObject('LastEdited')->Rfc3339(),
                'Authors' => $this->fetchItemAuthors($item),
            );
            $out->push($outItem);
        }

        return $out;
    }

    /**
     * @param $item
     *
     * @return mixed
     */
    private function fetchItemContent($item)
    {
        Requirements::clear();
        $content = $item->dbObject('Content')->forTemplate();

        return $this->customise(
            new ArrayData::create(
                array(
                    'ID' => $item->ID,
                    'Title' => $item->Title,
                    'SubTitle' => $item->SubTitle,
                    'LeadText' => $item->LeadText,
                    'Content' => $content,
                    'AbsoluteLink' => $item->AbsoluteLink(),
                    'ContentLocale' => i18n::get_locale(),
                    'PublishedDateLong' => $this->fetchItemPublishedDate($item)->Long(),
                    'PublishedDateIso' => $this->fetchItemPublishedDate($item)->Rfc3339(),
                    'LastEditedLong' => $item->dbObject('LastEdited')->Long(),
                    'LastEditedIso' => $item->dbObject('LastEdited')->Rfc3339(),
                    'Authors' => $this->fetchItemAuthors($item),
                    'Video' => $item->Video,
                    'Image' => $item->Image(),
                    'Images' => $item->Images(),
                    'TagsList' => $this->fetchItemTags($item),
                )
            )
        )->renderWith('FbInstantArticle');
    }

    /**
     * @param $item
     *
     * @return mixed
     */
    private function fetchItemPublishedDate($item)
    {
        if ($item->dbObject('PublishedDate')) {
            return $item->dbObject('PublishedDate');
        } elseif ($item->dbObject('Embargo')) {
            return $item->dbObject('Embargo');
        } else {
            return $item->dbObject('Created');
        }
    }

    /**
     * @param $item
     */
    private function fetchItemTags($item)
    {
        if ($item->many_many('Tags')) {
            $results = $item->Tags() ? $item->Tags()->column('Title') : '';

            return implode('; ', $results);
        }

        return null;
    }

    /**
     * @param $item
     *
     * @return mixed
     */
    private function fetchItemAuthors($item)
    {
        if ($item->many_many('Authors')) {
            $results = $item->Authors() ? $item->Authors() : '';
            $out = new ArrayList();

            if ($results && $results->exists()) {
                foreach ($results as $item) {
                    $outItem = array(
                        'FullName' => trim($item->FirstName.' '.$item->Surname),
                    );
                    $out->push($outItem);
                }

                return $out;
            }
        }

        return null;
    }

    /**
     * @param $item
     */
    private function getContentParsed($content)
    {
        $content = preg_replace('/<h4>/', '<h2>', $content);
        $content = preg_replace('/<h5>/', '<h2>', $content);
        $content = preg_replace("/<\/h4>/", '</h2>', $content);
        $content = preg_replace("/<\/h5>/", '</h2>', $content);
        $content = trim($content);

        return $content;
    }

    /**
     * @param $item
     */
    private function getContentImagesParsed($content)
    {
        $content = preg_replace("/<p>\n<div class=\"embed-wrapper sc-image-full\">/", '', $content);
        $content = preg_replace("/<span data-picture data-alt=(.*)>\n/", '', $content);
        $content = preg_replace("/<span data-picture=(.*)>\n/", '', $content);
        $content = preg_replace("/<!--\[if \(lt IE 9\) & \(!IEMobile\)\]>\n/", '', $content);
        $content = preg_replace("/<span data-src=(.*)>\n/", '', $content);
        $content = preg_replace("/<!\[endif\]-->\n/", '', $content);
        $content = preg_replace("/<noscript>\n/", '', $content);
        $content = preg_replace("/<\/noscript>\n<\/span>\n/", '', $content);
        $content = preg_replace('/<figcaption class="img-desc">/', '<figcaption>', $content);
        $content = preg_replace("/<p class=\"img-credits\">(.*?)<\/p>/", '<cite>$1</cite>', $content);
        $content = preg_replace("/<\/figcaption><\/figure><\/div>\n<\/p>/", '</figcaption></figure>', $content);
        $content = trim($content);

        return $content;
    }

    private function getCachedFeed($cacheKey = false)
    {
        if (!isset($cacheKey) || (false == $cacheKey)) {
            return false;
        }

        $cache = SS_Cache::factory($cacheKey);
        SS_Cache::set_cache_lifetime($cacheKey, 60 * 60 * 1, 10); // cache for one hour

        if (!($result = unserialize($cache->load($cacheKey)))) {
            $result = $this->getIAFeed();

            if ($result) {
                $cache->save(serialize($result), $cacheKey);

                return $result;
            }
        }

        return $result;
    }
}
