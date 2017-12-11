<?php

class FbInstantArticlesTransformer
{
    /**
     * @param $content
     *
     * @return mixed
     */
    public function getTransformContent($content)
    {
        if (!$content) {
            return false;
        }

        \Logger::configure(
            [
                'rootLogger' => [
                    'appenders' => ['facebook-instantarticles-transformer'],
                ],
                'appenders' => [
                    'facebook-instantarticles-transformer' => [
                        'class' => 'LoggerAppenderConsole',
                        'threshold' => 'INFO',
                        'layout' => [
                            'class' => 'LoggerLayoutSimple',
                        ],
                    ],
                ],
            ]
        );

        $instantArticle = \Facebook\InstantArticles\Elements\InstantArticle::create();
        $transformer = new \Facebook\InstantArticles\Transformer\Transformer();

        // Load the rules from a file
        $rules = file_get_contents('rules/transformer-rules.json', true);
        $transformer->loadRules($rules);

        libxml_use_internal_errors(true);
        $document = new DOMDocument();
        $document->preserveWhiteSpace = true;
        $document->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
        libxml_use_internal_errors(false);

        $transformer->transform($instantArticle, $document);
        $result = $instantArticle->render('', true)."\n";

        // $warnings = $transformer->getWarnings();

        return $result;
    }
}
