<?php

class FBInstantArticlesCachedFeedJob extends AbstractQueuedJob implements QueuedJob
{
    private static $cache_key = false;

    public function __construct()
    {
        self::$cache_key = Config::inst()->get('FbInstantArticlesHelper', 'cache_key');
    }

    public function getTitle()
    {
        return 'Facebook Instant Articles Cached Feed Job';
    }

    /**
     * Indicate to the system which queue we think we should be in based
     * on how many objects we're going to touch on while processing.
     *
     * We want to make sure we also set how many steps we think we might need to take to
     * process everything - note that this does not need to be 100% accurate, but it's nice
     * to give a reasonable approximation
     */
    public function getJobType()
    {
        return QueuedJob::QUEUED;
    }

    /**
     * This is called immediately before a job begins - it gives you a chance
     * to initialise job data and make sure everything's good to go.
     *
     * When we go through, we'll constantly add and remove from this queue, meaning
     * we never overload it with content
     */
    public function setup()
    {
        $this->totalSteps = 1;
    }

    public function process()
    {
        $iaHelper = new FbInstantArticlesHelper();
        $iaHelper->addCachedFeed(self::$cache_key);

        ++$this->currentStep;
        $this->isComplete = true;
    }
}
