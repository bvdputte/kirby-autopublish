<?php

require __DIR__ . DS . "src" . DS . "Autopublish.php";

// For composer
@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('bvdputte/kirbyAutopublish', [
    'options' => [
        'fieldNamePublish' => 'autopublish',
        'fieldNameUnpublish' => 'autounpublish',
        'poormanscron' => false,
        'poormanscron.interval' => 1, // in minutes
        'cache.poormanscron' => true,
        'webhookToken' => false,
    ],
    'collections' => [
        'autoPublishedDrafts' => function ($site) {
            $autopublishfield = option("bvdputte.kirbyAutopublish.fieldNamePublish");
            $drafts = $site->pages()->drafts();
            $autoPublishedDrafts = $drafts->filter(function ($draft) use ($autopublishfield) {
                return ($draft->$autopublishfield()->exists()) && (!$draft->$autopublishfield()->isEmpty()) && (empty($draft->errors()) === true);
            });
            return $autoPublishedDrafts;
        },
        'autounPublishedListed' => function ($site) {
            $autounpublishfield = option("bvdputte.kirbyAutopublish.fieldNameUnpublish");
            $listed = $site->pages()->children()->listed();
            $autounPublishedListed = $listed->filter(function ($find) use ($autounpublishfield) {
                return ($find->$autounpublishfield()->exists()) && (!$find->$autounpublishfield()->isEmpty()) && (empty($find->errors()) === true);
            });
            return $autounPublishedListed;
        }
    ],
    'hooks' => [
        'route:before' => function ($route, $path, $method) {
            /*
             * For servers without cron, enable "poormanscron"
             * ⚠️ Ugly, non-performant hack to bypass cache
             * @TODO: Fix this as soon as this is possible:
             * https://github.com/getkirby/ideas/issues/23
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
                return new Response('done', 'text/html');
            }
        ],
    ]
]);
