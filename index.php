<?php

require __DIR__ . DS . "src" . DS . "Autopublish.php";

// For composer
@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('bvdputte/kirbyAutopublish', [
    'options' => [
        'fieldName' => 'autopublish',
        'fieldNameUnpublish' => 'autounpublish',
        'poormanscron' => false,
        'poormanscron.interval' => 1, // in minutes
        'cache.poormanscron' => true,
        'webhookToken' => false,
    ],
    'collections' => [
        'autoPublishedDrafts' => function ($site) {
            $autopublishfield = option("bvdputte.kirbyAutopublish.fieldName");
            $drafts = $site->index()->drafts();
            $autoPublishedDrafts = $drafts->filter(function ($draft) use ($autopublishfield) {
                return ($draft->$autopublishfield()->exists()) && ($draft->$autopublishfield()->isNotEmpty()) && (empty($draft->errors()) === true);
            });
            return $autoPublishedDrafts;
        },
        'autoUnpublishedListed' => function ($site) {
            $autounpublishfield = option("bvdputte.kirbyAutopublish.fieldNameUnpublish");
            $listed = $site->index()->children()->listed();
            $autoUnpublishedListed = $listed->filter(function ($listedPage) use ($autounpublishfield) {
                return ($listedPage->$autounpublishfield()->exists()) && ($listedPage->$autounpublishfield()->isNotEmpty()) && (empty($listedPage->errors()) === true);
            });
            return $autoUnpublishedListed;
        }
    ],
    'hooks' => [
        'route:before' => function ($route, $path, $method) {
            /*
             * For servers without cron, enable "poormanscron"
             * ⚠️ Ugly, non-performant hack to bypass cache
             */
            if (option("bvdputte.kirbyAutopublish.poormanscron")) {
                bvdputte\kirbyAutopublish\Autopublish::poorManCronRun();
            }
        }
    ],
    'routes' => [
        [
            'pattern' => 'kirby-autopublish/(:any)',
            'action' => function ($token) {
                if (
                    $token !== option('bvdputte.kirbyAutopublish.webhookToken', false) ||
                    option('bvdputte.kirbyAutopublish.webhookToken', false) === false
                ) {
                    throw new Exception('Invalid token');
                    return false;
                }

                bvdputte\kirbyAutopublish\Autopublish::publish();
                bvdputte\kirbyAutopublish\Autopublish::unpublish();
                return new Response('done', 'text/html');
            }
        ],
    ]
]);
