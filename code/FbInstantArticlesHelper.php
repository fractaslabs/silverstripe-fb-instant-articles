<?php

class FbInstantArticlesHelper extends DataExtension
{
    /**
     * Event handler called after Publishing SiteTree DataObject, overloaded from parent.
     *
     * @uses \DataObject->onAfterPublish()
     */
    public function onAfterPublish()
    {
        $regenerateTime = SS_Datetime::now(); // sync every $recheck_every_hours

        $nextGeneration = new FBInstantArticlesCachedFeedJob();
        singleton('QueuedJobService')->queueJob($nextGeneration, $regenerateTime);
    }

    public function addCachedFeed($cacheKey)
    {
        if (!isset($cacheKey) || (false == $cacheKey)) {
            return false;
        }

        $cache = SS_Cache::factory($cacheKey);
        SS_Cache::set_cache_lifetime($cacheKey, 60 * 60 * 1, 10); // cache for one hour

        $contr = new FbInstantArticles_Controller();
        $result = $contr->getIAFeed();

        if ($result) {
            $cache->save(serialize($result), $cacheKey);

            return true;
        }

        return false;
    }
}
